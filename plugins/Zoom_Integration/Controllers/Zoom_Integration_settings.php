<?php

namespace Zoom_Integration\Controllers;

use App\Controllers\Security_Controller;
use Zoom_Integration\Libraries\Zoom_Integration;

class Zoom_Integration_settings extends Security_Controller {

    protected $Zoom_Integration_settings_model;

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->Zoom_Integration_settings_model = new \Zoom_Integration\Models\Zoom_Integration_settings_model();
    }

    function index() {
        return $this->template->view("Zoom_Integration\Views\settings\integration");
    }

    function save() {
        $settings = array("integrate_zoom", "zoom_account_id", "zoom_client_id", "zoom_client_secret");

        $integrate_zoom = $this->request->getPost("integrate_zoom");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            //if user change credentials, flag zoom as unauthorized
            if (get_zoom_integration_setting('zoom_authorized') && ($setting == "zoom_account_id" || $setting == "zoom_client_id" || $setting == "zoom_client_secret") && $integrate_zoom && get_zoom_integration_setting($setting) != $value) {
                $this->Zoom_Integration_settings_model->save_setting('zoom_authorized', "0");
            }

            $this->Zoom_Integration_settings_model->save_setting($setting, $value);
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    //authorize zoom
    function authorize_zoom() {
        $zoom = new Zoom_Integration();
        $zoom->authorize();
    }
}
