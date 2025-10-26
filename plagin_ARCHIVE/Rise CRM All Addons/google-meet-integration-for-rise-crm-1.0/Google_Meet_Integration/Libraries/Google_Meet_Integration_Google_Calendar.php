<?php

namespace Google_Meet_Integration\Libraries;

class Google_Meet_Integration_Google_Calendar {

    public function __construct() {
        //load resources
        require_once(APPPATH . "ThirdParty/Google/google-api-php-client/vendor/autoload.php");
    }

    //authorize connection
    public function authorize() {
        $client = $this->_get_client_credentials();
        $this->_check_access_token($client, true);
    }

    //check access token
    private function _check_access_token($client, $redirect_to_settings = false) {
        //load previously authorized token from database, if it exists.
        $accessToken = get_google_meet_integration_setting('oauth_access_token');

        if (!$redirect_to_settings && $accessToken && get_google_meet_integration_setting('google_meet_authorized')) {
            $client->setAccessToken(json_decode($accessToken, true));
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                if ($redirect_to_settings) {
                    app_redirect("google_meet_integration_settings");
                }
            } else {
                $authUrl = $client->createAuthUrl();
                app_redirect($authUrl, true);
            }
        } else {
            if ($redirect_to_settings) {
                app_redirect("google_meet_integration_settings");
            }
        }
    }

    //fetch access token with auth code and save to database
    public function save_access_token($auth_code) {
        $client = $this->_get_client_credentials();

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($auth_code);

        $error = get_array_value($accessToken, "error");
        if ($error)
            die($error);

        $client->setAccessToken($accessToken);

        // Save the token to database
        $new_access_token = json_encode($client->getAccessToken());

        if ($new_access_token) {
            $Google_Meet_Integration_settings_model = new \Google_Meet_Integration\Models\Google_Meet_Integration_settings_model();
            $Google_Meet_Integration_settings_model->save_setting('oauth_access_token', $new_access_token);

            //got the valid access token. store to setting that it's authorized
            $Google_Meet_Integration_settings_model->save_setting('google_meet_authorized', "1");
        }
    }

    //get client credentials
    private function _get_client_credentials() {
        $url = get_uri("google_meet_integration_settings/save_access_token_of_meet");

        $client = new \Google_Client();
        $client->setApplicationName(get_setting('app_title'));
        $client->setRedirectUri($url);
        $client->setAccessType("offline");
        $client->setPrompt('select_account consent');
        $client->setClientId(get_google_meet_integration_setting('google_client_id'));
        $client->setClientSecret(get_google_meet_integration_setting('google_client_secret'));
        $client->setScopes(\Google_Service_Calendar::CALENDAR);

        return $client;
    }

    //get google calendar service
    private function _get_calendar_service() {
        $client = $this->_get_client_credentials();
        $this->_check_access_token($client);

        return new \Google_Service_Calendar($client);
    }

    //add/update the event of google calendar
    public function save_event($data = array(), $id = 0) {
        if (!$data) {
            return false;
        }

        //prepare data
        $calendar_event_info = new \stdClass();
        $service = $this->_get_calendar_service();

        $start_time = get_array_value($data, "start_time");
        $in_time = is_date_exists($start_time) ? convert_date_utc_to_local($start_time) : "";
        if (get_setting("time_format") == "24_hours") {
            $in_time_value = $in_time ? date("H:i", strtotime($in_time)) : "";
        } else {
            $in_time_value = $in_time ? convert_time_to_12hours_format(date("H:i:s", strtotime($in_time))) : "";
        }

        $start_date = $in_time ? date("Y-m-d", strtotime($in_time)) : "";
        $start_time = $in_time_value;
        $time_object_of_google_calendar = $this->_get_start_end_date_time($start_date, $start_time);

        $event = new \Google_Service_Calendar_Event(array(
            'summary' => get_array_value($data, "title"),
            'description' => get_array_value($data, "description"),
            'start' => $time_object_of_google_calendar,
            'end' => $time_object_of_google_calendar,
            'transparency' => "transparent", //show as available on the event
            'reminders' => array(
                'useDefault' => FALSE, //we've to add this functionality after adding the reminder of events
            ),
            'conferenceData' => array(
                'createRequest' => array(
                    'requestId' => make_random_string()
                )
            )
        ));

        $calendarId = 'primary'; //insert to own google calendar only

        $Google_Meet_meetings_model = new \Google_Meet_Integration\Models\Google_Meet_meetings_model();
        $event_info = $Google_Meet_meetings_model->get_one($id);
        if ($event_info->google_event_id) {
            //update operation
            $calendar_event_info = $service->events->update($calendarId, $event_info->google_event_id, $event);
        } else if (!$event_info->google_event_id) {
            //insert operation
            $calendar_event_info = $service->events->insert($calendarId, $event, array('conferenceDataVersion' => 1));
        }

        //save newly added event information
        if (isset($calendar_event_info->id) && isset($calendar_event_info->hangoutLink)) {
            $data = array(
                "google_event_id" => $calendar_event_info->id,
                "join_url" => $calendar_event_info->hangoutLink
            );

            return $data;
        }
    }

    //get start/end date and time 
    private function _get_start_end_date_time($start_date = "", $start_time = "") {
        $time_array = array("timeZone" => get_setting("timezone"));

        $date_time = new \DateTime($start_date . " " . $start_time, new \DateTimeZone(get_setting("timezone")));
        $time_array["dateTime"] = $date_time->format(\DateTime::RFC3339);

        return $time_array;
    }

    //delete event
    public function delete($google_event_id = "") {
        if (!$google_event_id) {
            return false;
        }

        $service = $this->_get_calendar_service();
        $service->events->delete('primary', $google_event_id);
    }

}
