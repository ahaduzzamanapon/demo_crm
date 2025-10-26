<?php

namespace Zoom_Integration\Controllers;

use App\Controllers\Security_Controller;
use Zoom_Integration\Libraries\Zoom_Integration;

class Zoom_Meetings extends Security_Controller {

    protected $Zoom_meetings_model;

    function __construct() {
        parent::__construct();
        if ($this->login_user->user_type === "client" && !get_zoom_integration_setting("client_can_access_zoom_meetings")) {
            app_redirect("forbidden");
        }

        $this->Zoom_meetings_model = new \Zoom_Integration\Models\Zoom_meetings_model();
    }

    function index() {
        return $this->template->rander('Zoom_Integration\Views\zoom_meetings\index');
    }

    private function can_manage_meetings() {
        if (!can_manage_zoom_integration()) {
            app_redirect("forbidden");
        }
    }

    function modal_form() {
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $this->can_manage_meetings();
        $id = $this->request->getPost("id");
        $model_info = $this->Zoom_meetings_model->get_one($id);

        $view_data['members_and_teams_dropdown'] = json_encode(get_team_members_and_teams_select2_data_list());
        $view_data['clients_dropdown'] = $this->get_client_contacts_dropdown();
        $view_data['model_info'] = $model_info;
        $view_data['time_format_24_hours'] = get_setting("time_format") == "24_hours" ? true : false;

        return $this->template->view('Zoom_Integration\Views\zoom_meetings\modal_form', $view_data);
    }

    private function get_client_contacts_dropdown() {
        $contacts_dropdown = array();

        $contacts = $this->Zoom_meetings_model->get_client_contacts_list()->getResult();

        foreach ($contacts as $contact) {
            $contact_name = $contact->first_name . " " . $contact->last_name . " - " . app_lang("client") . ": " . $contact->company_name . "";
            $contacts_dropdown[] = array("id" => "contact:" . $contact->id, "text" => $contact_name);
        }

        return json_encode($contacts_dropdown);
    }

    /* list data of meetings */

    function list_data() {
        $is_client = false;
        if ($this->login_user->user_type == "client") {
            $is_client = true;
        }

        $statuses = $this->request->getPost('status') ? implode(",", $this->request->getPost('status')) : "";

        $options = array(
            "is_admin" => $this->login_user->is_admin,
            "user_id" => $this->login_user->id,
            "team_ids" => $this->login_user->team_ids,
            "is_client" => $is_client,
            "statuses" => $statuses
        );

        $list_data = $this->Zoom_meetings_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    //prepare a row of meetings list table
    private function _make_row($data) {
        $image_url = get_avatar($data->created_by_avatar);
        $user = "<span class='avatar avatar-xs mr10'><img src='$image_url' alt=''></span> $data->created_by_name";

        //upcoming: future meetings
        $status = "<span class='badge bg-primary'>" . app_lang("zoom_integration_upcoming") . "</span>";

        $current_utc_time = get_current_utc_time();
        if ($data->start_time > subtract_period_from_date($current_utc_time, $data->duration, "minutes", "Y-m-d H:i:s") && $data->start_time < $current_utc_time) { //running: $now is between start time + duration
            $status = "<span class='badge' style='background:#F9A52D;'>" . app_lang("zoom_integration_running") . "</span>";
        } else if ($data->start_time < $current_utc_time) { //past: more than 1 hour past
            $status = "<span class='badge bg-success'>" . app_lang("zoom_integration_past") . "</span>";
        }

        $row_data = array(
            $data->title,
            process_images_from_content($data->description),
            $data->start_time,
            format_to_datetime($data->start_time),
            $data->duration . " " . app_lang("zoom_integration_minutes"),
            get_team_member_profile_link($data->created_by, $user),
            anchor($data->join_url, app_lang("zoom_integration_join_meeting") . "<i data-feather='external-link' class='icon-16 ml10'></i>", array("target" => "_blank")),
            $status,
            modal_anchor(get_uri("zoom_meetings/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('zoom_integration_edit_meeting'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('zoom_integration_delete_meeting'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("zoom_meetings/delete"), "data-action" => "delete-confirmation"))
        );

        return $row_data;
    }

    /* insert/update a meeting */

    function save() {
        $this->can_manage_meetings();
        if (!(get_zoom_integration_setting("integrate_zoom") && get_zoom_integration_setting('zoom_authorized'))) {
            show_404();
        }

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "start_date" => "required",
            "start_time" => "required",
            "duration" => "numeric|required"
        ));

        $id = $this->request->getPost('id');
        $start_time = $this->request->getPost('start_time');

        //convert to 24hrs time format
        if (get_setting("time_format") != "24_hours") {
            $start_time = convert_time_to_24hours_format($start_time);
        }

        $start_date_time = $this->request->getPost('start_date') . " " . $start_time; //join date with time
        $start_date_time = convert_date_local_to_utc($start_date_time);

        //prepare share with data
        $share_with_team_members = $this->request->getPost('share_with_team_members');
        if ($share_with_team_members == "specific") {
            $share_with_team_members = $this->request->getPost('share_with_specific_team_members');
        }
        $share_with_client_contacts = $this->request->getPost('share_with_client_contacts');
        if ($share_with_client_contacts == "specific") {
            $share_with_client_contacts = $this->request->getPost('share_with_specific_client_contacts');
        }

        $data = array(
            "title" => $this->request->getPost('title'),
            "description" => $this->request->getPost('description'),
            "duration" => $this->request->getPost('duration'),
            "start_time" => $start_date_time,
            "share_with_team_members" => $share_with_team_members,
            "share_with_client_contacts" => $share_with_client_contacts,
            "waiting_room" => $this->request->getPost('waiting_room') ? 1 : 0,
        );

        //save user_id only on insert and it will not be editable
        if (!$id) {
            $data["created_by"] = $this->login_user->id;
        }

        $data = clean_data($data);

        //add/modify the event to zoom
        //save to zoom first then save to RISE 
        $Zoom_Integration = new Zoom_Integration();
        $meeting_data = $Zoom_Integration->save_meeting($data, $id);
        if (!$meeting_data) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }

        if (is_array($meeting_data)) {
            $data = array_merge($data, $meeting_data);
        }

        $save_id = $this->Zoom_meetings_model->ci_save($data, $id);
        if ($save_id) {
            if ($id) {
                //updated
                log_notification("zoom_integration_meeting_updated", array("plugin_zoom_meeting_id" => $save_id));
            } else {
                //inserted
                log_notification("zoom_integration_new_meeting_scheduled", array("plugin_zoom_meeting_id" => $save_id));
            }

            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* permanently delete an meeting */

    function delete() {
        $this->can_manage_meetings();
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');

        $meeting_info = $this->Zoom_meetings_model->get_one($id);

        if ($this->Zoom_meetings_model->delete($id)) {
            //if there has event associated with this on zoom, delete that too
            if (get_zoom_integration_setting("integrate_zoom") && get_zoom_integration_setting('zoom_authorized') && $meeting_info->zoom_meeting_id) {
                $Zoom_Integration = new Zoom_Integration();
                $Zoom_Integration->delete($meeting_info->zoom_meeting_id);
            }

            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    /* return a row of meeting list table */

    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Zoom_meetings_model->get_details($options)->getRow();

        return $this->_make_row($data);
    }

    function view($meeting_id) {
        validate_numeric_value($meeting_id);

        $is_client = false;
        if ($this->login_user->user_type == "client") {
            $is_client = true;
        }

        $options = array(
            "id" => $meeting_id,
            "is_admin" => $this->login_user->is_admin,
            "user_id" => $this->login_user->id,
            "is_client" => $is_client
        );

        $meeting_info = $this->Zoom_meetings_model->get_details($options)->getRow();
        if (!$meeting_info) {
            show_404();
        }

        $view_data['model_info'] = $meeting_info;

        return $this->template->rander('Zoom_Integration\Views\zoom_meetings\view', $view_data);
    }
}
