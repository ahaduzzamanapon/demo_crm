<?php

namespace Zoom_Integration\Libraries;

class Zoom_Integration {

    private $zoom_account_id;
    private $zoom_client_id;
    private $zoom_client_secret;
    private $baseUrl = 'https://api.zoom.us/v2';
    private $tokenUrl = 'https://zoom.us/oauth/token';
    private $timeout = 30;
    private $responseCode = 0;
    private $accessToken = "";

    public function __construct() {
        $this->zoom_account_id = get_zoom_integration_setting("zoom_account_id");
        $this->zoom_client_id = get_zoom_integration_setting("zoom_client_id");
        $this->zoom_client_secret = get_zoom_integration_setting("zoom_client_secret");
        $this->tokenUrl = "https://zoom.us/oauth/token?grant_type=account_credentials&account_id={$this->zoom_account_id}";
    }

    //authorize connection
    public function authorize() {
        $this->_check_access_token(true);
    }

    //check access token
    private function _check_access_token($redirect_to_settings = false) {

        //load previously authorized token from database, if it exists.
        $accessToken = get_zoom_integration_setting("oauth_access_token");

        if (get_zoom_integration_setting("zoom_authorized") && $accessToken && !$this->isAccessTokenExpired($accessToken) && !$redirect_to_settings) {
            $accessToken = unserialize($accessToken);
            $this->accessToken = $accessToken->access_token;
        } else {

            $this->generate_new_access_token();

            if ($redirect_to_settings) {
                app_redirect("settings/integration");
            }
        }
    }

    function isAccessTokenExpired($accessToken) {
        $accessToken = unserialize($accessToken);
        return isset($accessToken->token_expires) && time() > $accessToken->token_expires;
    }

    //fetch access token with auth code and save to database
    public function generate_new_access_token() {

        $new_access_token = $this->doRequest("POST", "", "", true);

        if ($new_access_token && isset($new_access_token->access_token)) {
            $new_access_token->token_expires = time() + $new_access_token->expires_in;
            $this->accessToken = $new_access_token->access_token;

            $Zoom_Integration_settings_model = new \Zoom_Integration\Models\Zoom_Integration_settings_model();
            $Zoom_Integration_settings_model->save_setting("oauth_access_token", serialize($new_access_token));

            //got the valid access token. store to setting that it's authorized
            $Zoom_Integration_settings_model->save_setting("zoom_authorized", "1");
        } else {
            die("Failed to get the access token. Please check your credentials.");
        }
    }

    private function headers() {
        return array(
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'Accept: application/json',
        );
    }

    private function tokenHeaders() {
        return array(
            'Host: zoom.us',
            'Authorization: Basic ' . base64_encode($this->zoom_client_id . ":" . $this->zoom_client_secret),
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        );
    }

    private function doRequest($method, $path = "", $body = array(), $isToken = false) {
        if (is_array($body)) {
            // Treat an empty array in the body data as if no body data was set
            if (!count($body)) {
                $body = '';
            } else {
                if ($isToken) {
                    $body = http_build_query($body);
                } else {
                    $body = json_encode($body);
                }
            }
        }

        $method = strtoupper($method);
        $url = $this->baseUrl . $path;
        $headers = $this->headers();
        if ($isToken) {
            $url = $this->tokenUrl;
            $headers = $this->tokenHeaders();
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (in_array($method, array('DELETE', 'PATCH', 'POST', 'PUT', 'GET'))) {

            // All except DELETE can have a payload in the body
            if ($method != 'DELETE' && strlen($body)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $result = curl_exec($ch);
        $err = curl_error($ch);

        $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        try {
            $result = json_decode($result);
        } catch (\Exception $ex) {
            echo json_encode(array("success" => false, 'message' => $ex->getMessage()));
            exit();
        }

        if ($err) {
            //got curl error
            echo json_encode(array("success" => false, 'message' => "cURL Error #:" . $err));
            exit();
        }

        return $result;
    }

    //add/update the meeting of zoom
    public function save_meeting($data = array(), $id = 0) {
        if (!$data) {
            return false;
        }

        $this->_check_access_token();

        //prepare data
        $start_time = get_array_value($data, "start_time");
        $in_time = is_date_exists($start_time) ? convert_date_utc_to_local($start_time) : "";
        if (get_setting("time_format") == "24_hours") {
            $in_time_value = $in_time ? date("H:i", strtotime($in_time)) : "";
        } else {
            $in_time_value = $in_time ? convert_time_to_12hours_format(date("H:i:s", strtotime($in_time))) : "";
        }

        $start_date = $in_time ? date("Y-m-d", strtotime($in_time)) : "";
        $start_time = $in_time_value;

        $timezone = get_setting("timezone");
        $meeting_start_date_time = new \DateTime($start_date . " " . $start_time, new \DateTimeZone($timezone));
        $meeting_start_time = $meeting_start_date_time->format(\DateTime::RFC3339);

        $meeting_data = array(
            "topic" => get_array_value($data, "title"),
            "type" => 2, //for scheduled meeting
            "start_time" => $meeting_start_time,
            "timezone" => $timezone,
            "agenda" => get_array_value($data, "description"),
            "duration" => get_array_value($data, "duration"),
            "settings" => array(
                "waiting_room" => get_array_value($data, "waiting_room") ? true : false
            )
        );

        $Zoom_meetings_model = new \Zoom_Integration\Models\Zoom_meetings_model();
        $meeting_info = $Zoom_meetings_model->get_one($id);
        $zoom_meeting_info = new \stdClass();

        if ($meeting_info->zoom_meeting_id) {
            //update operation
            $this->doRequest("PATCH", "/meetings/{$meeting_info->zoom_meeting_id}", $meeting_data);
            return $this->responseCode === 204; //success update
        } else if (!$meeting_info->zoom_meeting_id) {
            //insert operation
            $zoom_meeting_info = $this->doRequest("POST", '/users/me/meetings', $meeting_data);
        }

        //save newly added meeting information
        if (isset($zoom_meeting_info->id) && isset($zoom_meeting_info->join_url)) {
            $data = array(
                "zoom_meeting_id" => $zoom_meeting_info->id,
                "join_url" => $zoom_meeting_info->join_url
            );

            return $data;
        }
    }

    //delete event
    public function delete($zoom_meeting_id = "") {
        if (!$zoom_meeting_id) {
            return false;
        }

        $this->_check_access_token();

        $this->doRequest("DELETE", "/meetings/{$zoom_meeting_id}");
        if ($this->responseCode === 204) {
            return true;
        }
    }
}
