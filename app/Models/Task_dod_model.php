<?php

namespace App\Models;

class Task_dod_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'task_dod';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $task_dod_table = $this->db->prefixTable('task_dod');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $task_dod_table.id=$id";
        }

        $task_id = $this->_get_clean_value($options, "task_id");
        if ($task_id) {
            $where .= " AND $task_dod_table.task_id=$task_id";
        }

        $sql = "SELECT $task_dod_table.*
        FROM $task_dod_table
        WHERE 1=1 $where
        ORDER BY $task_dod_table.created_at DESC";

        return $this->db->query($sql);
    }

    function get_by_task_id($task_id = 0) {
        $task_dod_table = $this->db->prefixTable('task_dod');
        $sql = "SELECT * FROM $task_dod_table WHERE task_id=" . intval($task_id) . " ORDER BY created_at DESC LIMIT 1";
        return $this->db->query($sql)->getRow();
    }

}
