<?php

/* Don't change or add any new config in this file */

namespace Zoom_Integration\Config;

use CodeIgniter\Config\BaseConfig;
use Zoom_Integration\Models\Zoom_Integration_settings_model;

class Zoom_Integration extends BaseConfig {

    public $app_settings_array = array();

    public function __construct() {
        $zoom_integration_settings_model = new Zoom_Integration_settings_model();

        $settings = $zoom_integration_settings_model->get_all_settings()->getResult();
        foreach ($settings as $setting) {
            $this->app_settings_array[$setting->setting_name] = $setting->setting_value;
        }
    }

}
