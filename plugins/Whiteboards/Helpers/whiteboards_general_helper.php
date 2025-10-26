<?php

use App\Controllers\Security_Controller;

/**
 * get the defined config value by a key
 * @param string $key
 * @return config value
 */
if (!function_exists('get_whiteboards_setting')) {

    function get_whiteboards_setting($key = "") {
        $config = new Whiteboards\Config\Whiteboards();

        $setting_value = get_array_value($config->app_settings_array, $key);
        if ($setting_value !== NULL) {
            return $setting_value;
        } else {
            return "";
        }
    }

}

if (!function_exists('can_manage_whiteboards')) {

    function can_manage_whiteboards() {
        $instance = new Security_Controller();
        if ($instance->login_user->is_admin || get_array_value($instance->login_user->permissions, "whiteboards")) {
            return true;
        }
    }

}
