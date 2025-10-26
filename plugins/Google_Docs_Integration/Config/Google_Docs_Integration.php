<?php

namespace Google_Docs_Integration\Config;

use CodeIgniter\Config\BaseConfig;
use Google_Docs_Integration\Models\Google_Docs_Integration_settings_model;

class Google_Docs_Integration extends BaseConfig {

    public $app_settings_array = array();

    public function __construct() {
        $google_docs_integration_settings_model = new Google_Docs_Integration_settings_model();

        $settings = $google_docs_integration_settings_model->get_all_settings()->getResult();
        foreach ($settings as $setting) {
            $this->app_settings_array[$setting->setting_name] = $setting->setting_value;
        }
    }

}
