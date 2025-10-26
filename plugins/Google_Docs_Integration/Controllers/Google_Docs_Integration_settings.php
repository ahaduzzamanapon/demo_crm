<?php

namespace Google_Docs_Integration\Controllers;

use App\Controllers\Security_Controller;
use Google_Docs_Integration\Libraries\Google_Docs_Integration;

class Google_Docs_Integration_settings extends Security_Controller {

    protected $Google_Docs_Integration_settings_model;

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->Google_Docs_Integration_settings_model = new \Google_Docs_Integration\Models\Google_Docs_Integration_settings_model();
    }

    function index() {
        return $this->template->view("Google_Docs_Integration\Views\settings\integration");
    }

    function save() {
        $settings = array("integrate_google_docs", "google_docs_client_id", "google_docs_client_secret");

        $integrate_google_docs = $this->request->getPost("integrate_google_docs");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            //if user change credentials, flag it as unauthorized
            if (get_google_docs_integration_setting('google_docs_authorized') && ($setting == "google_docs_client_id" || $setting == "google_docs_client_secret") && $integrate_google_docs && get_google_docs_integration_setting($setting) != $value) {
                $this->Google_Docs_Integration_settings_model->save_setting('google_docs_authorized', "0");
            }

            $this->Google_Docs_Integration_settings_model->save_setting($setting, $value);
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    //authorize
    function authorize_google_docs() {
        $Google_Docs_Integration = new Google_Docs_Integration();
        $Google_Docs_Integration->authorize();
    }

    //get access token and save
    function save_access_token() {
        if (!empty($_GET)) {
            $Google_Docs_Integration = new Google_Docs_Integration();
            $Google_Docs_Integration->save_access_token(get_array_value($_GET, 'code'));
            app_redirect("settings/integration");
        }
    }
}
