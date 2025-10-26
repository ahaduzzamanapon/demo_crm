<?php

use App\Controllers\Security_Controller;

/**
 * get the defined config value by a key
 * @param string $key
 * @return config value
 */
if (!function_exists('get_zoom_integration_setting')) {

    function get_zoom_integration_setting($key = "") {
        $config = new Zoom_Integration\Config\Zoom_Integration();

        $setting_value = get_array_value($config->app_settings_array, $key);
        if ($setting_value !== NULL) {
            return $setting_value;
        } else {
            return "";
        }
    }

}

if (!function_exists('zoom_integration_count_upcoming_meetings')) {

    function zoom_integration_count_upcoming_meetings() {
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

        $Zoom_meetings_model = new Zoom_Integration\Models\Zoom_meetings_model();
        return count($Zoom_meetings_model->get_details($options)->getResult());
    }

}

if (!function_exists('can_manage_zoom_integration')) {

    function can_manage_zoom_integration() {
        $instance = new Security_Controller();
        if ($instance->login_user->is_admin || get_array_value($instance->login_user->permissions, "zoom_meeting")) {
            return true;
        }
    }

}
