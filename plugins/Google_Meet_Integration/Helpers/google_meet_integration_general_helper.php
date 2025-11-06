<?php

use App\Controllers\Security_Controller;

/**
 * get the defined config value by a key
 * @param string $key
 * @return config value
 */
if (!function_exists('get_google_meet_integration_setting')) {

    function get_google_meet_integration_setting($key = "") {
        $config = new Google_Meet_Integration\Config\Google_Meet_Integration();

        $setting_value = get_array_value($config->app_settings_array, $key);
        if ($setting_value !== NULL) {
            return $setting_value;
        } else {
            return "";
        }
    }

}

if (!function_exists('google_meet_integration_count_upcoming_meetings')) {

    function google_meet_integration_count_upcoming_meetings() {
        $instance = new Security_Controller();
        $is_client = false;
        if ($instance->login_user->user_type == "client") {
            $is_client = true;
        }

        $options = array(
            "is_admin" => $instance->login_user->is_admin,
            "user_id" => $instance->login_user->id,
            "team_ids" => $instance->login_user->team_ids,
            "is_client" => $is_client,
            "upcoming_only" => true
        );

        $Google_Meet_meetings_model = new Google_Meet_Integration\Models\Google_Meet_meetings_model();
        return count($Google_Meet_meetings_model->get_details($options)->getResult());
    }

}

if (!function_exists('can_manage_google_meet_integration')) {

    function can_manage_google_meet_integration() {
        return true;
        $google_meet_integration_users = get_google_meet_integration_setting("google_meet_integration_users");
        $google_meet_integration_users = explode(',', $google_meet_integration_users);
        $instance = new Security_Controller();

        if ($instance->login_user->is_admin || in_array($instance->login_user->id, $google_meet_integration_users)) {
        }
    }

}
