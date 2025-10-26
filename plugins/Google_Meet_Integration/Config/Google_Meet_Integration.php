<?php

/* Don't change or add any new config in this file */

namespace Google_Meet_Integration\Config;

use CodeIgniter\Config\BaseConfig;
use Google_Meet_Integration\Models\Google_Meet_Integration_settings_model;

class Google_Meet_Integration extends BaseConfig {

    public $app_settings_array = array();

    public function __construct() {
        $google_meet_integration_settings_model = new Google_Meet_Integration_settings_model();

        $settings = $google_meet_integration_settings_model->get_all_settings()->getResult();
        foreach ($settings as $setting) {
            $this->app_settings_array[$setting->setting_name] = $setting->setting_value;
        }
    }

}
