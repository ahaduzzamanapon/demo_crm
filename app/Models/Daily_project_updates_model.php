<?php

namespace App\Models;

class Daily_project_updates_model extends Crud_model
{
    protected $table = null;

    public function __construct()
    {
        $this->table = 'daily_project_updates';
        parent::__construct($this->table);
    }

    public function get_details($options = [])
    {
        $daily_project_updates_table = $this->db->prefixTable('daily_project_updates');
        $projects_table = $this->db->prefixTable('projects');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        
        $id = $this->_get_clean_value($options, 'id');
        if ($id) {
            $where .= " AND $daily_project_updates_table.id = $id";
        }

        $project_id = $this->_get_clean_value($options, 'project_id');
        if ($project_id) {
            $where .= " AND $daily_project_updates_table.project_id = $project_id";
        }

        $date = $this->_get_clean_value($options, 'date');
        if ($date) {
            $where .= " AND $daily_project_updates_table.date = '$date'";
        }

        $sql = "SELECT $daily_project_updates_table.*, p.title AS project_title,
                       CONCAT(u.first_name, ' ', u.last_name) AS creator_name
                FROM $daily_project_updates_table
                LEFT JOIN $projects_table p ON p.id = $daily_project_updates_table.project_id
                LEFT JOIN $users_table u ON u.id = $daily_project_updates_table.created_by
                WHERE $daily_project_updates_table.deleted = 0 $where
                ORDER BY p.title ASC";

        return $this->db->query($sql);
    }
}
