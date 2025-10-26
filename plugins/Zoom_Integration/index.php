<?php

defined('PLUGINPATH') or exit('No direct script access allowed');

/*
  Plugin Name: Zoom Integration
  Description: Create and manage Zoom meetings with your team members and clients inside RISE CRM.
  Version: 1.3
  Requires at least: 3.1
  Author: ClassicCompiler
  Author URL: https://codecanyon.net/user/classiccompiler
 */

use App\Controllers\Security_Controller;

//add menu item to left menu
app_hooks()->add_filter('app_filter_staff_left_menu', 'zoom_integration_left_menu');
app_hooks()->add_filter('app_filter_client_left_menu', 'zoom_integration_left_menu');

if (!function_exists('zoom_integration_left_menu')) {

    function zoom_integration_left_menu($sidebar_menu) {
        // if (!(get_zoom_integration_setting("integrate_zoom") && get_zoom_integration_setting('zoom_authorized'))) {
        //     return $sidebar_menu;
        // }

        $instance = new Security_Controller();
        if ($instance->login_user->user_type === "client" && !get_zoom_integration_setting("client_can_access_zoom_meetings")) {
            return $sidebar_menu;
        }

        $sidebar_menu["zoom_meetings"] = array(
            "name" => "zoom_meetings",
            "url" => "zoom_meetings",
            "class" => "video",
            "position" => 6,
            "badge" => zoom_integration_count_upcoming_meetings(),
            "badge_class" => "bg-primary"
        );

        return $sidebar_menu;
    }

}

//add integration setting
app_hooks()->add_filter('app_filter_integration_settings_tab', function ($hook_tabs) {
    $hook_tabs[] = array(
        "title" => "Zoom",
        "url" => get_uri("zoom_integration_settings"),
        "target" => "zoom-integration"
    );

    return $hook_tabs;
});

//install dependencies
register_installation_hook("Zoom_Integration", function ($item_purchase_code) {
    include PLUGINPATH . "Zoom_Integration/install/do_install.php";
});

//add setting link to the plugin setting
app_hooks()->add_filter('app_filter_action_links_of_Zoom_Integration', function ($action_links_array) {
    $action_links_array = array(
        anchor(get_uri("settings/integration"), app_lang("settings"))
    );

    if (get_zoom_integration_setting("integrate_zoom") && get_zoom_integration_setting('zoom_authorized')) {
        $action_links_array[] = anchor(get_uri("zoom_meetings"), app_lang("zoom_integration_meetings"));
    }

    return $action_links_array;
});

//update plugin
use Zoom_Integration\Controllers\Zoom_Integration_Updates;

register_update_hook("Zoom_Integration", function () {
    $update = new Zoom_Integration_Updates();
    return $update->index();
});

//uninstallation: remove data from database
register_uninstallation_hook("Zoom_Integration", function () {
    $dbprefix = get_db_prefix();
    $db = db_connect('default');

    $sql_query = "DROP TABLE IF EXISTS `" . $dbprefix . "zoom_integration_settings`;";
    $db->query($sql_query);

    $sql_query = "DROP TABLE IF EXISTS `" . $dbprefix . "zoom_meetings`;";
    $db->query($sql_query);

    $sql_query = "DELETE FROM `" . $dbprefix . "notification_settings` WHERE `" . $dbprefix . "notification_settings`.`event`='zoom_integration_new_meeting_scheduled' OR `" . $dbprefix . "notification_settings`.`event`='zoom_integration_meeting_updated';";
    $db->query($sql_query);

    $sql_query = "DELETE FROM `" . $dbprefix . "notifications` WHERE `" . $dbprefix . "notifications`.`plugin_zoom_meeting_id`!='0';";
    $db->query($sql_query);

    $sql_query = "ALTER TABLE `" . $dbprefix . "notifications` DROP `plugin_zoom_meeting_id`;";
    $db->query($sql_query);
    
    $sql_query = "DELETE FROM `" . $dbprefix . "email_templates` WHERE `" . $dbprefix . "email_templates`.`template_name`='zoom_integration_new_meeting_scheduled' OR `" . $dbprefix . "email_templates`.`template_name`='zoom_integration_meeting_updated';";
    $db->query($sql_query);
});

//show permission in role setting
app_hooks()->add_action('app_hook_role_permissions_extension', function () {
    echo view("Zoom_Integration\Views\settings\permission");
});

//save role setting
app_hooks()->add_filter('app_filter_role_permissions_save_data', function ($permissions) {
    $request = \Config\Services::request();
    $permissions["zoom_meeting"] = $request->getPost('zoom_meeting_permission');

    return $permissions;
});

//show client permission setting
app_hooks()->add_action('app_hook_client_permissions_extension', function () {
    echo view("Zoom_Integration\Views\settings\client_permission");
});

//save client permission setting
app_hooks()->add_action('app_hook_client_permissions_save_data', function () {
    $request = \Config\Services::request();
    $client_can_access_zoom_meetings = $request->getPost("client_can_access_zoom_meetings");

    $Zoom_Integration_settings_model = new \Zoom_Integration\Models\Zoom_Integration_settings_model();
    $Zoom_Integration_settings_model->save_setting("client_can_access_zoom_meetings", $client_can_access_zoom_meetings);
});

//activation: activate notification settings and notifications
register_activation_hook("Zoom_Integration", function () {
    $dbprefix = get_db_prefix();
    $db = db_connect('default');

    $sql_query = "UPDATE `" . $dbprefix . "notification_settings` SET `deleted` = '0' WHERE `" . $dbprefix . "notification_settings`.`event`='zoom_integration_new_meeting_scheduled' OR `" . $dbprefix . "notification_settings`.`event`='zoom_integration_meeting_updated';";
    $db->query($sql_query);

    $sql_query = "UPDATE `" . $dbprefix . "notifications` SET `deleted` = '0' WHERE `" . $dbprefix . "notifications`.`plugin_zoom_meeting_id`!='0';";
    $db->query($sql_query);
});

//deactivation: deactivate notification settings and notifications
register_deactivation_hook("Zoom_Integration", function () {
    $dbprefix = get_db_prefix();
    $db = db_connect('default');

    $sql_query = "UPDATE `" . $dbprefix . "notification_settings` SET `deleted` = '1' WHERE `" . $dbprefix . "notification_settings`.`event`='zoom_integration_new_meeting_scheduled' OR `" . $dbprefix . "notification_settings`.`event`='zoom_integration_meeting_updated';";
    $db->query($sql_query);

    $sql_query = "UPDATE `" . $dbprefix . "notifications` SET `deleted` = '1' WHERE `" . $dbprefix . "notifications`.`plugin_zoom_meeting_id`!='0';";
    $db->query($sql_query);
});

//add notification config
app_hooks()->add_filter('app_filter_notification_category_suggestion', function ($category_suggestions) {
    $category_suggestions[] = array("id" => "zoom_meeting", "text" => app_lang("zoom_meeting"));

    return $category_suggestions;
});

//add notification config
app_hooks()->add_filter('app_filter_notification_config', function ($events_of_hook) {
    $meeting_link = function ($options) {
        if (isset($options->plugin_zoom_meeting_id) && $options->plugin_zoom_meeting_id) {
            return array("url" => get_uri("zoom_meetings/view/$options->plugin_zoom_meeting_id"));
        } else {
            return array("url" => get_uri("zoom_meetings"));
        }
    };

    $events_of_hook["zoom_integration_new_meeting_scheduled"] = array(
        "notify_to" => array("recipient"),
        "info" => $meeting_link
    );

    $events_of_hook["zoom_integration_meeting_updated"] = array(
        "notify_to" => array("recipient"),
        "info" => $meeting_link
    );

    return $events_of_hook;
});

//add create notification sql
app_hooks()->add_filter('app_filter_create_notification_where_query', function ($where_queries_from_hook, $data) {
    $event = get_array_value($data, "event");
    if (!($event === "zoom_integration_new_meeting_scheduled" || $event === "zoom_integration_meeting_updated")) {
        return $where_queries_from_hook;
    }

    $where = "";
    $options = get_array_value($data, "options");
    $notify_to_terms = get_array_value($data, "notify_to_terms");

    $db = db_connect('default');
    $zoom_meetings_table = $db->prefixTable('zoom_meetings');
    $users_table = $db->prefixTable('users');
    $team_table = $db->prefixTable('team');
    $plugin_zoom_meeting_id = get_array_value($options, "plugin_zoom_meeting_id");

    //find meeting recipients
    if (!(in_array("recipient", $notify_to_terms) && $plugin_zoom_meeting_id)) {
        return $where_queries_from_hook;
    }

    //find the meeting and check the recipient
    $meeting_info = $db->query("SELECT $zoom_meetings_table.* FROM $zoom_meetings_table WHERE $zoom_meetings_table.id=$plugin_zoom_meeting_id")->getRow();

    //we are saving the share with data like this:
    //for team members: member:1,member:2,team:1 or all
    //for client contacts: contact:1,contact:2 or all
    //so, we've to retrive the users 
    if ($meeting_info->share_with_team_members === "all" && $meeting_info->share_with_client_contacts === "all") {
        $where .= " OR $users_table.user_type = 'staff' OR $users_table.user_type = 'client' "; //all team members and all client contacts
    } else {
        if ($meeting_info->share_with_team_members === "all" && $meeting_info->share_with_client_contacts !== "all") {
            $where .= " OR $users_table.user_type = 'staff' "; //all team members
        } else if ($meeting_info->share_with_team_members !== "all" && $meeting_info->share_with_client_contacts === "all") {
            $where .= " OR $users_table.user_type = 'client' "; //all client contacts
        }

        $meeting_users = array();
        $meeting_team = array();
        $meeting_contact = array();

        if ($meeting_info->share_with_team_members !== "all") {
            $share_with_team_members_array = explode(",", $meeting_info->share_with_team_members); // found an array like this array("member:1", "member:2", "team:1")

            foreach ($share_with_team_members_array as $share) {

                $share_data = explode(":", $share);

                if (get_array_value($share_data, '0') === "member") {
                    $meeting_users[] = get_array_value($share_data, '1');
                } else if (get_array_value($share_data, '0') === "team") {
                    $meeting_team[] = get_array_value($share_data, '1');
                }
            }

            //find team members
            if (count($meeting_users)) {
                $where .= " OR FIND_IN_SET($users_table.id, '" . join(',', $meeting_users) . "') ";
            }

            //find team
            if (count($meeting_team)) {
                $where .= " OR FIND_IN_SET($users_table.id, (SELECT GROUP_CONCAT($team_table.members) AS team_users FROM $team_table WHERE $team_table.deleted=0 AND FIND_IN_SET($team_table.id, '" . join(',', $meeting_team) . "'))) ";
            }
        }

        if ($meeting_info->share_with_client_contacts !== "all") {
            $share_with_client_contacts_array = explode(",", $meeting_info->share_with_client_contacts); // found an array like this array("member:1", "member:2", "team:1")

            foreach ($share_with_client_contacts_array as $share) {

                $share_data = explode(":", $share);

                if (get_array_value($share_data, '0') === "contact") {
                    $meeting_contact[] = get_array_value($share_data, '1');
                }
            }

            //find client contacts
            if (count($meeting_contact)) {
                $where .= " OR FIND_IN_SET($users_table.id, '" . join(',', $meeting_contact) . "') ";
            }
        }
    }

    $where_queries_from_hook[] = $where;
    return $where_queries_from_hook;
});

//add notification description
app_hooks()->add_filter('app_filter_notification_description', function ($notification_descriptions, $notification) {
    $notification_descriptions[] = view("Zoom_Integration\Views\zoom_meetings\\notification_description", array("notification" => $notification));
    return $notification_descriptions;
});

//add notification description for slack
app_hooks()->add_filter('app_filter_notification_description_for_slack', function ($notification_descriptions, $notification) {
    $notification_descriptions[] = view("Zoom_Integration\Views\zoom_meetings\\notification_description_for_slack", array("notification" => $notification));
    return $notification_descriptions;
});

//add notification description for telegram
app_hooks()->add_filter('app_filter_notification_description_for_telegram', function ($notification_descriptions, $notification) {
    $notification_descriptions[] = view("Zoom_Integration\Views\zoom_meetings\\notification_description_for_telegram", array("notification" => $notification));
    return $notification_descriptions;
});

//add email template
app_hooks()->add_filter('app_filter_email_templates', function ($templates_array) {
    $templates_array["zoom_meeting"]["zoom_integration_new_meeting_scheduled"] = array("MEETING_TOPIC", "MEETING_DESCRIPTION", "MEETING_TIME", "MEETING_CREATED_BY", "JOIN_URL", "LOGO_URL", "SIGNATURE");
    $templates_array["zoom_meeting"]["zoom_integration_meeting_updated"] = array("MEETING_TOPIC", "MEETING_DESCRIPTION", "MEETING_TIME", "MEETING_CREATED_BY", "JOIN_URL", "LOGO_URL", "SIGNATURE");

    return $templates_array;
});

//modify email notification
app_hooks()->add_filter('app_filter_send_email_notification', function ($data) {
    $notification = get_array_value($data, "notification");
    $user_language = get_array_value($data, "user_language");
    if (!(isset($notification->event) && ($notification->event === "zoom_integration_new_meeting_scheduled" || $notification->event === "zoom_integration_meeting_updated"))) {
        return $data;
    }

    $Email_templates_model = model("App\Models\Email_templates_model");
    $Zoom_meetings_model = new \Zoom_Integration\Models\Zoom_meetings_model();
    $meeting_info = $Zoom_meetings_model->get_details(array("id" => $notification->plugin_zoom_meeting_id))->getRow();

    if ($notification->event === "zoom_integration_new_meeting_scheduled") {
        $email_template = $Email_templates_model->get_final_template("zoom_integration_new_meeting_scheduled", true);
    } else {
        $email_template = $Email_templates_model->get_final_template("zoom_integration_meeting_updated", true);
    }

    $parser_data = get_array_value($data, "parser_data");
    $parser_data["SIGNATURE"] = get_array_value($email_template, "signature_$user_language") ? get_array_value($email_template, "signature_$user_language") : get_array_value($email_template, "signature_default");
    $parser_data["MEETING_TOPIC"] = $meeting_info->title;
    $parser_data["MEETING_DESCRIPTION"] = $meeting_info->description ? process_images_from_content($meeting_info->description) : "";
    $parser_data["MEETING_TIME"] = format_to_datetime($meeting_info->start_time);
    $parser_data["MEETING_CREATED_BY"] = $meeting_info->created_by_name;
    $parser_data["JOIN_URL"] = $meeting_info->join_url;

    $parser = \Config\Services::parser();
    $message = $parser->setData($parser_data)->renderString(get_array_value($email_template, "message_$user_language") ? get_array_value($email_template, "message_$user_language") : get_array_value($email_template, "message_default"));
    $subject = $parser->setData($parser_data)->renderString(get_array_value($email_template, "subject_$user_language") ? get_array_value($email_template, "subject_$user_language") : get_array_value($email_template, "subject_default"));

    $info_array = array(
        "subject" => $subject,
        "message" => $message,
    );

    return $info_array;
});
