<?php

namespace Zoom_Integration\Models;

use App\Models\Crud_model;

class Zoom_meetings_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'zoom_meetings';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $zoom_meetings_table = $this->db->prefixTable('zoom_meetings');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $zoom_meetings_table.id=$id";
        }

        $current_utc_time = get_current_utc_time();
        $upcoming_only = $this->_get_clean_value($options, "upcoming_only");
        if ($upcoming_only) {
            $where .= " AND $zoom_meetings_table.start_time>'$current_utc_time'";
        }

        $statuses = $this->_get_clean_value($options, "statuses");
        if ($statuses) {
            $statuses_array = explode(',', $statuses);
            $statuses_where = "";

            foreach ($statuses_array as $status) {
                if ($statuses_where) {
                    $statuses_where .= " OR ";
                }

                $subtract_duration = "DATE_SUB('$current_utc_time', INTERVAL $zoom_meetings_table.duration MINUTE)";
                if ($status === "upcoming") {
                    $statuses_where .= " $zoom_meetings_table.start_time>'$current_utc_time'";
                } else if ($status === "running") {
                    $statuses_where .= " ($zoom_meetings_table.start_time> $subtract_duration  AND $zoom_meetings_table.start_time<'$current_utc_time') ";
                } else if ($status === "past") {
                    $statuses_where .= " $zoom_meetings_table.start_time< $subtract_duration ";
                }
            }

            if ($statuses_where) {
                $where .= " AND ($statuses_where)";
            }
        }

        $is_admin = $this->_get_clean_value($options, "is_admin");
        $user_id = $this->_get_clean_value($options, "user_id");
        if (!$is_admin && $user_id) {

            //find meetings where share with the user or his/her team
            $team_ids = $this->_get_clean_value($options, "team_ids");
            $team_search_sql = "";

            //searh for teams
            if ($team_ids) {
                $teams_array = explode(",", $team_ids);
                foreach ($teams_array as $team_id) {
                    $team_search_sql .= " OR (FIND_IN_SET('team:$team_id', $zoom_meetings_table.share_with_team_members)) ";
                }
            }


            $is_client = $this->_get_clean_value($options, "is_client");
            if ($is_client) {
                //client user's can't see the meetings which has shared with all team members
                $where .= " AND (FIND_IN_SET('all', $zoom_meetings_table.share_with_client_contacts) OR FIND_IN_SET('contact:$user_id', $zoom_meetings_table.share_with_client_contacts))";
            } else {
                //searh for user and teams
                $where .= " AND ($zoom_meetings_table.created_by=$user_id 
                OR $zoom_meetings_table.share_with_team_members='all'
                    OR (FIND_IN_SET('member:$user_id', $zoom_meetings_table.share_with_team_members))
                        $team_search_sql
                        )";
            }

            $where .= " AND $users_table.deleted=0 AND $users_table.status='active'";
        }

        $sql = "SELECT $zoom_meetings_table.*, 
        CONCAT($users_table.first_name, ' ',$users_table.last_name) AS created_by_name, $users_table.image AS created_by_avatar, $users_table.job_title AS created_by_job_title
        FROM $zoom_meetings_table
        LEFT JOIN $users_table ON $users_table.id = $zoom_meetings_table.created_by
        WHERE $zoom_meetings_table.deleted=0 $where";

        return $this->db->query($sql);
    }

    function get_client_contacts_list() {
        $users_table = $this->db->prefixTable('users');
        $clients_table = $this->db->prefixTable('clients');

        $sql = "SELECT $users_table.id, $users_table.first_name, $users_table.last_name, $clients_table.company_name
        FROM $users_table
        LEFT JOIN $clients_table ON $clients_table.id = $users_table.client_id
        WHERE $users_table.deleted=0 AND $users_table.status='active' AND $users_table.user_type='client'
        ORDER BY $users_table.first_name ASC";
        return $this->db->query($sql);
    }

}
