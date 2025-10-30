<?php

namespace App\Controllers;

class Custom_reports extends Security_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        return $this->template->rander("custom_reports/index");
    }
}
