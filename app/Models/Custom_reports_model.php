<?php

namespace App\Models;

class Custom_reports_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'custom_reports';
        parent::__construct($this->table);
    }
}
