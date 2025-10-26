<?php

namespace Whiteboards\Controllers;

use App\Controllers\Security_Controller;

class Whiteboards extends Security_Controller
{

    protected $Whiteboards_model;

    function __construct()
    {
        parent::__construct();
        if ($this->login_user->user_type === "client" && !get_whiteboards_setting("client_can_access_whiteboards")) {
            app_redirect("forbidden");
        }

        $this->Whiteboards_model = new \Whiteboards\Models\Whiteboards_model();
    }

    function index()
    {
        return $this->template->rander('Whiteboards\Views\whiteboards\index');
    }

    private function can_manage_whiteboards()
    {
        if (!can_manage_whiteboards()) {
            app_redirect("forbidden");
        }
    }

    function modal_form()
    {
        $this->can_manage_whiteboards();
        $id = $this->request->getPost("id");
        $model_info = $this->Whiteboards_model->get_one($id);

        $view_data['members_and_teams_dropdown'] = json_encode(get_team_members_and_teams_select2_data_list());
        $view_data['clients_dropdown'] = $this->get_client_contacts_dropdown();
        $view_data['model_info'] = $model_info;

        return $this->template->view('Whiteboards\Views\whiteboards\modal_form', $view_data);
    }

    private function get_client_contacts_dropdown()
    {
        $contacts_dropdown = array();

        $contacts = $this->Whiteboards_model->get_client_contacts_list()->getResult();

        foreach ($contacts as $contact) {
            $contact_name = $contact->first_name . " " . $contact->last_name . " - " . app_lang("client") . ": " . $contact->company_name . "";
            $contacts_dropdown[] = array("id" => "contact:" . $contact->id, "text" => $contact_name);
        }

        return json_encode($contacts_dropdown);
    }

    /* list data of Whiteboards */

    function list_data()
    {
        $is_client = false;
        if ($this->login_user->user_type == "client") {
            $is_client = true;
        }

        $options = array(
            "is_admin" => $this->login_user->is_admin,
            "user_id" => $this->login_user->id,
            "team_ids" => $this->login_user->team_ids,
            "is_client" => $is_client
        );

        $list_data = $this->Whiteboards_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    //prepare a row of whiteboards list table
    private function _make_row($data)
    {
        $image_url = get_avatar($data->created_by_avatar);
        $user = "<span class='avatar avatar-xs mr10'><img src='$image_url' alt=''></span> $data->created_by_name";

        $row_data = array(
            anchor(get_uri("whiteboards/view/" . $data->id), $data->title),
            process_images_from_content($data->description),
            get_team_member_profile_link($data->created_by, $user),
            modal_anchor(get_uri("whiteboards/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('whiteboards_edit_whiteboard'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('whiteboards_delete_whiteboard'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("whiteboards/delete"), "data-action" => "delete-confirmation"))
        );

        return $row_data;
    }

    /* insert/update a whiteboard */

    function save()
    {
        $this->can_manage_whiteboards();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
        ));

        $id = $this->request->getPost('id');

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
            "share_with_team_members" => $share_with_team_members,
            "share_with_client_contacts" => $share_with_client_contacts,
            "permission" => $this->request->getPost('permission'),
        );

        //save user_id only on insert and it will not be editable
        if (!$id) {
            $data["created_by"] = $this->login_user->id;
        }

        $save_id = $this->Whiteboards_model->ci_save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* permanently delete a whiteboard */

    function delete()
    {
        $this->can_manage_whiteboards();
        $id = $this->request->getPost('id');

        if ($this->Whiteboards_model->delete($id)) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    /* return a row of whiteboard list table */

    private function _row_data($id)
    {
        $options = array("id" => $id);
        $data = $this->Whiteboards_model->get_details($options)->getRow();

        return $this->_make_row($data);
    }

    function view($whiteboard_id = 0)
    {
        $is_client = false;
        if ($this->login_user->user_type == "client") {
            $is_client = true;
        }

        $options = array(
            "id" => $whiteboard_id,
            "is_admin" => $this->login_user->is_admin,
            "user_id" => $this->login_user->id,
            "is_client" => $is_client
        );

        $whiteboard_info = $this->Whiteboards_model->get_details($options)->getRow();
        if (!$whiteboard_info) {
            show_404();
        }

        $view_data['model_info'] = $whiteboard_info;

        return $this->template->rander('Whiteboards\Views\whiteboards\view', $view_data);
    }

    function save_whiteboard_content()
    {

        $this->validate_submitted_data(array(
            "id" => "numeric|required",
            "whiteboard_content" => "required",
        ));

        $id = $this->request->getPost('id');
        $whiteboard_content = json_decode($this->request->getPost('whiteboard_content'));

        $is_client = false;
        if ($this->login_user->user_type == "client") {
            $is_client = true;
        }

        $options = array(
            "id" => $id,
            "is_admin" => $this->login_user->is_admin,
            "user_id" => $this->login_user->id,
            "is_client" => $is_client
        );

        $whiteboard_info = $this->Whiteboards_model->get_details($options)->getRow();
        if (!$whiteboard_info) {
            show_404();
        }

        if ($whiteboard_info->permission === "viewer" && $whiteboard_info->created_by !== $this->login_user->id) {
            show_404();
        }

        $data["content"] = $whiteboard_content ? serialize($whiteboard_content) : serialize(array());
        $save_id = $this->Whiteboards_model->ci_save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }
}
