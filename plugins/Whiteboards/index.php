<?php

defined('PLUGINPATH') or exit('No direct script access allowed');

/*
  Plugin Name: Whiteboards
  Description: Create and manage Whiteboards with your team members and clients inside RISE CRM.
  Version: 1.0
  Requires at least: 3.1
  Author: ClassicCompiler
  Author URL: https://codecanyon.net/user/classiccompiler
 */

use App\Controllers\Security_Controller;

//add menu item to left menu
app_hooks()->add_filter('app_filter_staff_left_menu', 'whiteboards_left_menu');
app_hooks()->add_filter('app_filter_client_left_menu', 'whiteboards_left_menu');

if (!function_exists('whiteboards_left_menu')) {

    function whiteboards_left_menu($sidebar_menu) {
        $instance = new Security_Controller();
        if ($instance->login_user->user_type === "client" && !get_whiteboards_setting("client_can_access_whiteboards")) {
            return $sidebar_menu;
        }

        $sidebar_menu["whiteboards"] = array(
            "name" => "whiteboards",
            "url" => "whiteboards",
            "class" => "clipboard",
            "position" => 6,
            "badge_class" => "bg-primary"
        );

        return $sidebar_menu;
    }

}

//install dependencies
register_installation_hook("Whiteboards", function ($item_purchase_code) {
    include PLUGINPATH . "Whiteboards/install/do_install.php";
});

//add setting link to the plugin setting
app_hooks()->add_filter('app_filter_action_links_of_Whiteboards', function ($action_links_array) {
    $action_links_array = array(anchor(get_uri("whiteboards"), app_lang("whiteboards")));

    return $action_links_array;
});

//update plugin
use Whiteboards\Controllers\Whiteboards_Updates;

register_update_hook("Whiteboards", function () {
    $update = new Whiteboards_Updates();
    return $update->index();
});

//uninstallation: remove data from database
register_uninstallation_hook("Whiteboards", function () {
    $dbprefix = get_db_prefix();
    $db = db_connect('default');

    $sql_query = "DROP TABLE IF EXISTS `" . $dbprefix . "whiteboards_settings`;";
    $db->query($sql_query);

    $sql_query = "DROP TABLE IF EXISTS `" . $dbprefix . "whiteboards`;";
    $db->query($sql_query);
});

//show permission in role setting
app_hooks()->add_action('app_hook_role_permissions_extension', function () {
    echo view("Whiteboards\Views\settings\permission");
});

//save role setting
app_hooks()->add_filter('app_filter_role_permissions_save_data', function ($permissions) {
    $request = \Config\Services::request();
    $permissions["whiteboards"] = $request->getPost('whiteboards_permission');

    return $permissions;
});

//show client permission setting
app_hooks()->add_action('app_hook_client_permissions_extension', function () {
    echo view("Whiteboards\Views\settings\client_permission");
});

//save client permission setting
app_hooks()->add_action('app_hook_client_permissions_save_data', function () {
    $request = \Config\Services::request();
    $client_can_access_whiteboards = $request->getPost("client_can_access_whiteboards");

    $Whiteboards_settings_model = new \Whiteboards\Models\Whiteboards_settings_model();
    $Whiteboards_settings_model->save_setting("client_can_access_whiteboards", $client_can_access_whiteboards);
});