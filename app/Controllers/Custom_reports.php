<?php

namespace App\Controllers;

class Custom_reports extends Security_Controller
{


    public function __construct()
    {
        parent::__construct();
        helper(['form']);
        parent::__construct();
        $this->taskStatusModel = model('App\Models\Task_status_model');
        $this->projectsModel   = model('App\Models\Projects_model');
        $this->Timesheets_model = model('App\Models\Timesheets_model');
        $this->Users_model = model('App\Models\Users_model');
        $this->Tasks_model = model('App\Models\Tasks_model');
        $this->db              = \Config\Database::connect();
    }

    public function index() {
        $project_id = $this->request->getGet('project_id');
        $member_id = $this->request->getGet('member_id');
        $task_id = $this->request->getGet('task_id');
        $start_date = $this->request->getGet('start_date');
        $end_date = $this->request->getGet('end_date');

        if (!$start_date && !$end_date) {
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
        }
        
        $view_data['project_id'] = $project_id;
        $view_data['member_id'] = $member_id;
        $view_data['task_id'] = $task_id;
        $view_data['start_date'] = $start_date;
        $view_data['end_date'] = $end_date;

        $projects = $this->projectsModel->get_all_where(array("deleted" => 0))->getResult();
        $projects_dropdown = array("" => "- " . app_lang('project') . " -");
        foreach ($projects as $project) {
            $projects_dropdown[$project->id] = $project->title;
        }
        $view_data['projects_dropdown'] = $projects_dropdown;

        $members = $this->Users_model->get_all_where(array("user_type" => "staff", "deleted" => 0, "status" => "active"))->getResult();
        $members_dropdown = array("" => "- " . app_lang('member') . " -");
        foreach ($members as $member) {
            $members_dropdown[$member->id] = $member->first_name . ' ' . $member->last_name;
        }
        $view_data['members_dropdown'] = $members_dropdown;

        $tasks_where = array("deleted" => 0);
        if($project_id){
            $tasks_where["project_id"] = $project_id;
        }
        $tasks = $this->Tasks_model->get_all_where($tasks_where)->getResult();
        $tasks_dropdown = array("" => "- " . app_lang('task') . " -");
        foreach ($tasks as $task) {
            $tasks_dropdown[$task->id] = $task->id . ' - ' . $task->title;
        }
        $view_data['tasks_dropdown'] = $tasks_dropdown;

        $custom_reports_permission = get_array_value($this->login_user->permissions, "custom_reports");
        
        if ($custom_reports_permission === "own") {
            $member_id = $this->login_user->id;
        }

        //project report
        $task_statuses = $this->Task_status_model->get_details()->getResult();

        $tasks_table = $this->db->prefixTable('tasks');
        $projects_table = $this->db->prefixTable('projects');
        $project_members_table = $this->db->prefixTable('project_members');

        $status_columns = "";
        foreach ($task_statuses as $status) {
            $status_columns .= ", SUM(CASE WHEN $tasks_table.status_id = {$status->id} THEN 1 ELSE 0 END) AS status_" . $status->id . "_count";
        }

        $project_report_where = "";
        if ($project_id) {
            $project_report_where .= " AND $projects_table.id = $project_id";
        }

        if ($custom_reports_permission === "own" && $member_id) {
            $project_report_where .= " AND $projects_table.id IN (SELECT project_id FROM $project_members_table WHERE user_id = $member_id AND deleted = 0)";
        }

        $tasks_join_where = "";
        if ($member_id) {
            $tasks_join_where .= " AND $tasks_table.assigned_to = $member_id";
        }
        if ($task_id) {
            $tasks_join_where .= " AND $tasks_table.id = $task_id";
        }
        if ($start_date && $end_date) {
            $tasks_join_where .= " AND ($tasks_table.start_date BETWEEN '$start_date' AND '$end_date')";
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
                $tasks_table ON $projects_table.id = $tasks_table.project_id AND $tasks_table.deleted = 0 $tasks_join_where
            WHERE
                $projects_table.deleted = 0 $project_report_where
            GROUP BY
                $projects_table.id, $projects_table.title
        ";

        $view_data['project_report_data'] = $this->db->query($sql_projects)->getResult();
        $view_data['task_statuses'] = $task_statuses;

        //time tracking report
        $users_table = $this->db->prefixTable('users');
        $project_members_table = $this->db->prefixTable('project_members');
        $project_time_table = $this->db->prefixTable('project_time');
        
        $time_tracking_where = "";
        if ($project_id) {
            $time_tracking_where .= " AND p.id = $project_id";
        }
        if ($member_id) {
            $time_tracking_where .= " AND u.id = $member_id";
        }

        $est_where = "WHERE deleted = 0";
        if ($task_id) {
            $est_where .= " AND id = $task_id";
        }
        
        $spt_where = "WHERE deleted = 0 AND status = 'logged'";
        if ($task_id) {
            $spt_where .= " AND task_id = $task_id";
        }
        if ($start_date && $end_date) {
            $spt_where .= " AND (DATE(start_time) BETWEEN '$start_date' AND '$end_date')";
        }

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
                (SELECT assigned_to, project_id, SUM(estimated_time) as total_estimated_hr FROM $tasks_table $est_where GROUP BY assigned_to, project_id) as est
                ON est.assigned_to = u.id AND est.project_id = p.id
            LEFT JOIN
                (SELECT user_id, project_id, (COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time))), 0) + COALESCE(SUM(hours * 3600), 0)) as total_spent_seconds FROM $project_time_table $spt_where GROUP BY user_id, project_id) as spt
                ON spt.user_id = u.id AND spt.project_id = p.id
            WHERE
                u.deleted = 0 AND u.user_type = 'staff'
                AND (est.total_estimated_hr IS NOT NULL OR spt.total_spent_seconds IS NOT NULL)
                $time_tracking_where
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

        $total_time_logged_seconds_where = " WHERE $project_time_table.deleted = 0 AND $project_time_table.status = 'logged' ";
        if ($member_id) {
            $total_time_logged_seconds_where .= " AND $project_time_table.user_id = $member_id";
        }
        if ($project_id) {
            $total_time_logged_seconds_where .= " AND $project_time_table.project_id = $project_id";
        }
        if ($task_id) {
            $total_time_logged_seconds_where .= " AND $project_time_table.task_id = $task_id";
        }
        if ($start_date && $end_date) {
            $total_time_logged_seconds_where .= " AND (DATE($project_time_table.start_time) BETWEEN '$start_date' AND '$end_date')";
        }

        $sql_total_time = "
            SELECT (COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time))), 0) + COALESCE(SUM(hours * 3600), 0)) as total_seconds
            FROM $project_time_table
            $total_time_logged_seconds_where
        ";
        $total_time_logged_result = $this->db->query($sql_total_time)->getRow();
        $view_data['total_time_logged'] = format_to_time($total_time_logged_result->total_seconds);

        $user_time_log_where = "";
        if ($project_id) {
            $user_time_log_where .= " AND $project_time_table.project_id = $project_id";
        }
        if ($member_id) {
            $user_time_log_where .= " AND $project_time_table.user_id = $member_id";
        }
        if ($task_id) {
            $user_time_log_where .= " AND $project_time_table.task_id = $task_id";
        }
        if ($start_date && $end_date) {
            $user_time_log_where .= " AND (DATE($project_time_table.start_time) BETWEEN '$start_date' AND '$end_date')";
        }

        $sql_user_time_log = "
            SELECT
                CONCAT($users_table.first_name, ' ', $users_table.last_name) as member_name,
                $projects_table.title as project_name,
                $project_time_table.start_time as work_start_time,
                $project_time_table.end_time as work_end_time,
                $tasks_table.title as task_name,
                $tasks_table.estimated_time as task_estimated_time,
                (COALESCE(TIME_TO_SEC(TIMEDIFF($project_time_table.end_time, $project_time_table.start_time)), 0) + COALESCE($project_time_table.hours * 3600, 0)) as spent_seconds
            FROM
                $project_time_table
            JOIN
                $users_table ON $users_table.id = $project_time_table.user_id
            LEFT JOIN
                $projects_table ON $projects_table.id = $project_time_table.project_id
            LEFT JOIN
                $tasks_table ON $tasks_table.id = $project_time_table.task_id
            WHERE
                $project_time_table.deleted = 0
                AND $project_time_table.status = 'logged'
                $user_time_log_where
            ORDER BY
                member_name, $project_time_table.start_time DESC
        ";

        $view_data['user_time_log_report_data'] = $this->db->query($sql_user_time_log)->getResult();

        return $this->template->rander("custom_reports/index", $view_data);
    }
}
