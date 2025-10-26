<?php

namespace Google_Meet_Integration\Controllers;

use App\Controllers\Security_Controller;
use Google_Meet_Integration\Libraries\Google_Meet_Integration_Google_Calendar;

class Google_Meet_Integration_settings extends Security_Controller {

    protected $Google_Meet_Integration_settings_model;

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->Google_Meet_Integration_settings_model = new \Google_Meet_Integration\Models\Google_Meet_Integration_settings_model();
    }

    function index() {
        return $this->template->rander("Google_Meet_Integration\Views\settings\index");
    }

    function other_settings() {
        $team_members = $this->Users_model->get_all_where(array("deleted" => 0, "user_type" => "staff", "is_admin" => 0))->getResult();
        $members_dropdown = array();

        foreach ($team_members as $team_member) {
            $members_dropdown[] = array("id" => $team_member->id, "text" => $team_member->first_name . " " . $team_member->last_name);
        }

        $view_data['members_dropdown'] = json_encode($members_dropdown);

        return $this->template->view("Google_Meet_Integration\Views\settings\other_settings", $view_data);
    }

    function save_other_settings() {
        $settings = array(
            "google_meet_integration_users", "client_can_access_meetings"
        );

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Google_Meet_Integration_settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function save_google_meet_integration_settings() {
        $settings = array("integrate_google_meet", "google_client_id", "google_client_secret");

        $integrate_google_meet = $this->request->getPost("integrate_google_meet");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            //if user change credentials, flag google meet as unauthorized
            if (get_google_meet_integration_setting('google_meet_authorized') && ($setting == "google_client_id" || $setting == "google_client_secret") && $integrate_google_meet && get_google_meet_integration_setting($setting) != $value) {
                $this->Google_Meet_Integration_settings_model->save_setting('google_meet_authorized', "0");
            }

            $this->Google_Meet_Integration_settings_model->save_setting($setting, $value);
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    //authorize meet
    function authorize_meet() {
        $meet = new Google_Meet_Integration_Google_Calendar();
        $meet->authorize();
    }

    //get access code and save
    function save_access_token_of_meet() {
        if (!empty($_GET)) {
            $meet = new Google_Meet_Integration_Google_Calendar();
            $meet->save_access_token(get_array_value($_GET, 'code'));
            app_redirect("google_meet_integration_settings");
        }
    }

}
