<?php

namespace Whiteboards\Config;

use CodeIgniter\Config\BaseConfig;
use Whiteboards\Models\Whiteboards_settings_model;

class Whiteboards extends BaseConfig {

    public $app_settings_array = array();

    public function __construct() {
        $whiteboards_settings_model = new Whiteboards_settings_model();

        $settings = $whiteboards_settings_model->get_all_settings()->getResult();
        foreach ($settings as $setting) {
            $this->app_settings_array[$setting->setting_name] = $setting->setting_value;
        }
    }

}
