<?php

defined('PLUGINPATH') or exit('No direct script access allowed');

/*
  Plugin Name: Google Meet Integration
  Description: Create and manage Google Meet meetings with your team members and clients inside RISE CRM.
  Version: 1.0.1
  Requires at least: 3.5
  Author: MHL
  Author URL: https://codecanyon.net/user/MHL
 */

use App\Controllers\Security_Controller;

//add menu item to left menu
app_hooks()->add_filter('app_filter_staff_left_menu', 'google_meet_integration_left_menu');
app_hooks()->add_filter('app_filter_client_left_menu', 'google_meet_integration_left_menu');

if (!function_exists('google_meet_integration_left_menu')) {

    function google_meet_integration_left_menu($sidebar_menu) {
        if (!(get_google_meet_integration_setting("integrate_google_meet") && get_google_meet_integration_setting('google_meet_authorized'))) {
            return $sidebar_menu;
        }

        $instance = new Security_Controller();
        if ($instance->login_user->user_type === "client" && !get_google_meet_integration_setting("client_can_access_meetings")) {
            return $sidebar_menu;
        }

        $sidebar_menu["google_meet_meetings"] = array(
            "name" => "google_meet",
            "url" => "google_meet_meetings",
            "class" => "video",
            "position" => 6,
            "badge" => google_meet_integration_count_upcoming_meetings(),
            "badge_class" => "bg-primary"
        );

        return $sidebar_menu;
    }

}

//add admin setting menu item
app_hooks()->add_filter('app_filter_admin_settings_menu', function ($settings_menu) {
    $settings_menu["setup"][] = array("name" => "google_meet_integration", "url" => "google_meet_integration_settings");
    return $settings_menu;
});

//install dependencies
register_installation_hook("Google_Meet_Integration", function ($item_purchase_code) {
    include PLUGINPATH . "Google_Meet_Integration/install/do_install.php";
});

//add setting link to the plugin setting
app_hooks()->add_filter('app_filter_action_links_of_Google_Meet_Integration', function ($action_links_array) {
    $action_links_array = array(
        anchor(get_uri("google_meet_integration_settings"), app_lang("settings"))
    );

    if (get_google_meet_integration_setting("integrate_google_meet") && get_google_meet_integration_setting('google_meet_authorized')) {
        $action_links_array[] = anchor(get_uri("google_meet_meetings"), app_lang("google_meet_integration_meetings"));
    }

    return $action_links_array;
});

//update plugin
use Google_Meet_Integration\Controllers\Google_Meet_Integration_Updates;

register_update_hook("Google_Meet_Integration", function () {
    $update = new Google_Meet_Integration_Updates();
    return $update->index();
});

//uninstallation: remove data from database
register_uninstallation_hook("Google_Meet_Integration", function () {
    $dbprefix = get_db_prefix();
    $db = db_connect('default');

    $sql_query = "DROP TABLE IF EXISTS `" . $dbprefix . "google_meet_integration_settings`;";
    $db->query($sql_query);

    $sql_query = "DROP TABLE IF EXISTS `" . $dbprefix . "google_meet_meetings`;";
    $db->query($sql_query);
});
