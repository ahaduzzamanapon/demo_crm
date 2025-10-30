<?php

namespace App\Controllers;

class Custom_reports extends Security_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->taskStatusModel = model('App\\Models\\Task_status_model');
        $this->projectsModel   = model('App\\Models\\Projects_model');
        $this->Timesheets_model = model('App\\Models\\Timesheets_model');
        $this->db              = \Config\Database::connect();
    }

    public function index() {
        //project report
        $task_statuses = $this->Task_status_model->get_details()->getResult();

        $tasks_table = $this->db->prefixTable('tasks');
        $projects_table = $this->db->prefixTable('projects');

        $status_columns = "";
        foreach ($task_statuses as $status) {
            $status_columns .= ", SUM(CASE WHEN $tasks_table.status_id = {$status->id} THEN 1 ELSE 0 END) AS status_" . $status->id . "_count";
        }

        $sql_projects = "
            SELECT
                $projects_table.id AS project_id,
                $projects_table.title AS project_name,
                COUNT($tasks_table.id) AS total_tasks
                $status_columns
            FROM
                $projects_table
            LEFT JOIN
                $tasks_table ON $projects_table.id = $tasks_table.project_id AND $tasks_table.deleted = 0
            WHERE
                $projects_table.deleted = 0
            GROUP BY
                $projects_table.id, $projects_table.title
        ";

        $view_data['project_report_data'] = $this->db->query($sql_projects)->getResult();
        $view_data['task_statuses'] = $task_statuses;

        //time tracking report
        $users_table = $this->db->prefixTable('users');
        $project_members_table = $this->db->prefixTable('project_members');
        $projects_table = $this->db->prefixTable('projects');
        $tasks_table = $this->db->prefixTable('tasks');
        $project_time_table = $this->db->prefixTable('project_time');

        $sql_time = "
            SELECT
                u.id as assignee_id,
                CONCAT(u.first_name, ' ', u.last_name) as assignee_name,
                p.id as project_id,
                p.title as project_name,
                est.total_estimated_hr,
                spt.total_spent_seconds
            FROM
                $users_table u
            JOIN
                $project_members_table pm ON u.id = pm.user_id AND pm.deleted = 0
            JOIN
                $projects_table p ON pm.project_id = p.id AND p.deleted = 0
            LEFT JOIN
                (SELECT assigned_to, project_id, SUM(estimated_time) as total_estimated_hr FROM $tasks_table WHERE deleted = 0 GROUP BY assigned_to, project_id) as est
                ON est.assigned_to = u.id AND est.project_id = p.id
            LEFT JOIN
                (SELECT user_id, project_id, (COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time))), 0) + COALESCE(SUM(hours * 3600), 0)) as total_spent_seconds FROM $project_time_table WHERE deleted = 0 AND status = 'logged' GROUP BY user_id, project_id) as spt
                ON spt.user_id = u.id AND spt.project_id = p.id
            WHERE
                u.deleted = 0 AND u.user_type = 'staff'
                AND (est.total_estimated_hr IS NOT NULL OR spt.total_spent_seconds IS NOT NULL)
            ORDER BY
                assignee_name, project_name;
        ";

        $view_data['time_tracking_report_data'] = $this->db->query($sql_time)->getResult();

        //summary data
        $view_data['total_projects'] = count($view_data['project_report_data']);
        $total_tasks = 0;
        foreach ($view_data['project_report_data'] as $project) {
            $total_tasks += $project->total_tasks;
        }
        $view_data['total_tasks'] = $total_tasks;

        $total_time_logged_seconds = 0;
        foreach ($view_data['time_tracking_report_data'] as $item) {
            $total_time_logged_seconds += $item->total_spent_seconds;
        }
        $view_data['total_time_logged'] = format_to_time($total_time_logged_seconds);

        return $this->template->rander("custom_reports/index", $view_data);
    }
}
