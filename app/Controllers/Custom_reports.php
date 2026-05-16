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
        $this->projectsModel = model('App\Models\Projects_model');
        $this->Timesheets_model = model('App\Models\Timesheets_model');
        $this->Users_model = model('App\Models\Users_model');
        $this->Tasks_model = model('App\Models\Tasks_model');
        $this->db = \Config\Database::connect();
        $this->Team_model = model('App\Models\Team_model');
    }

    public function index()
    {

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

        $custom_reports_permission = get_array_value($this->login_user->permissions, "custom_reports");

        if ($custom_reports_permission === "own") {
            $member_id = $this->login_user->id;
        }

        $projects_where = array("deleted" => 0);
        if ($custom_reports_permission === "own") {
            $projects_where["user_id"] = $member_id;
        }
        $projects = $this->projectsModel->get_details($projects_where)->getResult();
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
        if ($project_id) {
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

        // Resource Utilization Report
        $users_where = array("user_type" => "staff", "deleted" => 0, "status" => "active");
        if ($member_id) {
            $users_where["id"] = $member_id;
        }
        $users = $this->Users_model->get_all_where($users_where)->getResult();
        $resource_utilization_data = [];

        $start = new \DateTime($start_date);
        $end = new \DateTime($end_date);
        $end->modify('+1 day');
        $interval = new \DateInterval('P1D');
        $date_range = new \DatePeriod($start, $interval, $end);

        $working_days = 0;
        foreach ($date_range as $date) {
            $day_of_week = $date->format('N');
            if ($day_of_week < 6) { // Monday to Friday
                $working_days++;
            }
        }

        foreach ($users as $user) {
            $user_id = $user->id;

            // Get leave
            $leave_table = $this->db->prefixTable('leave_applications');
            $sql_leave = "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as total_leave FROM $leave_table WHERE applicant_id = $user_id AND status = 'approved' AND ((start_date BETWEEN '$start_date' AND '$end_date') OR (end_date BETWEEN '$start_date' AND '$end_date'))";
            $leave_result = $this->db->query($sql_leave)->getRow();
            $total_leave = $leave_result->total_leave ? $leave_result->total_leave : 0;

            // Get time logs
            $sql_time_logs = "SELECT DATE(start_time) as log_date, SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) as seconds FROM $project_time_table WHERE user_id = $user_id AND status = 'logged' AND (DATE(start_time) BETWEEN '$start_date' AND '$end_date') GROUP BY DATE(start_time)";
            $time_logs_result = $this->db->query($sql_time_logs)->getResult();

            $daily_logs = [];
            $total_seconds_worked = 0;
            foreach ($time_logs_result as $log) {
                // Use decimal hours for calculation but formatted string for view if needed? 
                // Wait, view iterates daily_logs to show hours. User wants time format here too.
                $daily_logs[$log->log_date] = convert_seconds_to_time_format($log->seconds);
                $total_seconds_worked += $log->seconds;
            }

            $availability = $working_days - $total_leave;
            $daily_work_hours = 8; // Office hours
            $utilization_decimal = $total_seconds_worked / 3600; // Keep for calculation
            $utilization_display = convert_seconds_to_time_format($total_seconds_worked); // Format for display

            $utilization_rate = $availability > 0 ? ($utilization_decimal / ($availability * $daily_work_hours)) * 100 : 0;
            $capacity_loss = $working_days > 0 ? ($total_leave / $working_days) * 100 : 0;

            $resource_utilization_data[] = [
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'designation' => $user->job_title,
                'total_leave' => $total_leave,
                'availability' => $availability,
                'utilization' => $utilization_display,
                'utilization_rate' => round($utilization_rate, 2),
                'capacity_loss' => round($capacity_loss, 2),
                'daily_logs' => $daily_logs
            ];
        }

        $view_data['resource_utilization_data'] = $resource_utilization_data;
        $view_data['date_range'] = $date_range;


        // =====================================================
        // Team-wise Project Report  (Project × Task-Status columns)
        // =====================================================
        $tasks_table_tw      = $this->db->prefixTable('tasks');
        $activity_logs_table = $this->db->prefixTable('activity_logs');
        $projects_table_tw   = $this->db->prefixTable('projects');

        // Re-use $task_statuses already fetched above for the project-report section
        // Build dynamic per-status SUM columns (date-filtered via LEFT JOIN)
        $tw_status_columns = "";
        $tw_overall_status_columns = "";
        foreach ($task_statuses as $ts) {
            $tw_status_columns .= ", COUNT(DISTINCT CASE WHEN t.status_id = {$ts->id} THEN t.id ELSE NULL END) AS status_{$ts->id}_count";
            // overall_status built as subquery placeholder — will be filled per-team inside loop
        }

        // Get all active teams
        $all_teams = $this->Team_model->get_details()->getResult();

        $team_wise_report = [];

        foreach ($all_teams as $team) {
            // Parse & sanitize comma-separated member IDs
            $member_ids_raw = array_filter(array_map('trim', explode(',', $team->members)));
            $member_ids     = array_filter($member_ids_raw, 'is_numeric');
            if (empty($member_ids)) {
                continue;
            }
            $member_ids_str = implode(',', $member_ids);

            // Date condition: task created OR status-changed OR has activity log in range
            $date_cond = "";
            if ($start_date && $end_date) {
                $date_cond = "AND (
                    (t.created_date BETWEEN '$start_date' AND '$end_date')
                    OR (t.status_changed_at IS NOT NULL AND DATE(t.status_changed_at) BETWEEN '$start_date' AND '$end_date')
                    OR EXISTS (
                        SELECT 1 FROM $activity_logs_table al
                        WHERE al.log_type = 'task'
                          AND al.log_type_id = t.id
                          AND al.deleted = 0
                          AND DATE(al.created_at) BETWEEN '$start_date' AND '$end_date'
                    )
                )";
            }

            $proj_filter_where = "";
            if ($project_id) {
                $proj_filter_where = "AND p.id = " . (int)$project_id;
            }

            $project_members_tw = $this->db->prefixTable('project_members');

            // Build date condition for LEFT JOIN ON clause (tasks within date range for team members)
            $task_date_join = "t.deleted = 0 AND t.assigned_to IN ($member_ids_str)";
            if ($start_date && $end_date) {
                $task_date_join .= " AND (
                    (t.created_date BETWEEN '$start_date' AND '$end_date')
                    OR (t.status_changed_at IS NOT NULL AND DATE(t.status_changed_at) BETWEEN '$start_date' AND '$end_date')
                    OR EXISTS (
                        SELECT 1 FROM $activity_logs_table al
                        WHERE al.log_type = 'task'
                          AND al.log_type_id = t.id
                          AND al.deleted = 0
                          AND DATE(al.created_at) BETWEEN '$start_date' AND '$end_date'
                    )
                )";
            }

            // Build per-status overall subqueries (no date filter, per-team member set)
            $tw_overall_status_sq = "";
            foreach ($task_statuses as $ts) {
                $tw_overall_status_sq .= ",\n                    (SELECT COUNT(*) FROM $tasks_table_tw tall
                     WHERE tall.project_id = p.id AND tall.deleted = 0
                       AND tall.assigned_to IN ($member_ids_str)
                       AND tall.status_id = {$ts->id}) AS overall_status_{$ts->id}_count";
            }

            // Get ALL projects where ANY team member is a project_member
            // Then LEFT JOIN tasks (with date+member filter) — project shows even if tasks=0
            $sql_proj = "
                SELECT
                    p.id        AS project_id,
                    p.title     AS project_name,
                    COUNT(DISTINCT t.id) AS total_tasks,
                    (
                        SELECT COUNT(*)
                        FROM $tasks_table_tw tall
                        WHERE tall.project_id = p.id
                          AND tall.deleted = 0
                          AND tall.assigned_to IN ($member_ids_str)
                    ) AS overall_tasks
                    $tw_overall_status_sq
                    $tw_status_columns
                FROM $projects_table_tw p
                INNER JOIN $project_members_tw pm
                    ON pm.project_id = p.id
                    AND pm.deleted = 0
                    AND pm.user_id IN ($member_ids_str)
                LEFT JOIN $tasks_table_tw t
                    ON $task_date_join
                    AND t.project_id = p.id
                WHERE
                    p.deleted = 0
                    $proj_filter_where
                GROUP BY p.id, p.title
                ORDER BY p.title
            ";

            $projects_data = $this->db->query($sql_proj)->getResult();

            // Compute team-level totals (total + overall + per-status + overall-per-status)
            $team_total_tasks          = 0;
            $team_overall_tasks        = 0;
            $team_status_totals        = [];
            $team_overall_status_totals = [];
            foreach ($task_statuses as $ts) {
                $team_status_totals[$ts->id]         = 0;
                $team_overall_status_totals[$ts->id] = 0;
            }
            foreach ($projects_data as $proj) {
                $team_total_tasks   += $proj->total_tasks;
                $team_overall_tasks += $proj->overall_tasks;
                foreach ($task_statuses as $ts) {
                    $col         = "status_{$ts->id}_count";
                    $overall_col = "overall_status_{$ts->id}_count";
                    $team_status_totals[$ts->id]         += ($proj->$col ?? 0);
                    $team_overall_status_totals[$ts->id] += ($proj->$overall_col ?? 0);
                }
            }

            $team_wise_report[] = [
                'team_id'                    => $team->id,
                'team_name'                  => $team->title,
                'projects'                   => $projects_data,
                'total_tasks'                => $team_total_tasks,
                'overall_tasks'              => $team_overall_tasks,
                'status_totals'              => $team_status_totals,
                'overall_status_totals'      => $team_overall_status_totals,
            ];
        }

        $view_data['team_wise_report']      = $team_wise_report;
        $view_data['team_task_statuses']    = $task_statuses;   // reuse for column headers
        $view_data['team_report_date_label'] = $start_date . ' to ' . $end_date;

        // =====================================================
        // Project Wise Effort Report
        // =====================================================
        $pt  = $this->db->prefixTable('project_time');
        $ptb = $this->db->prefixTable('projects');
        $utb = $this->db->prefixTable('users');
        $ttb = $this->db->prefixTable('tasks');

        // All active staff — filter by member_id if set
        $effort_staff_where = ['user_type' => 'staff', 'deleted' => 0, 'status' => 'active'];
        if ($member_id) {
            $effort_staff_where['id'] = $member_id;
        }
        $effort_staff = $this->Users_model->get_all_where($effort_staff_where)->getResult();

        $proj_filter = $project_id ? "AND p.id = " . (int)$project_id : "";
        // If member filter is set, restrict to projects where that member has logged time
        $member_proj_filter = "";
        if ($member_id) {
            $member_proj_filter = "AND p.id IN (SELECT DISTINCT project_id FROM $pt WHERE deleted=0 AND status='logged' AND user_id=" . (int)$member_id . ")";
        }
        $effort_member_filter_prec = $member_id ? " AND pt2.user_id = " . (int)$member_id : "";
        $effort_member_filter_curr = $member_id ? " AND pt2.user_id = " . (int)$member_id : "";

        $sql_effort = "
            SELECT
                p.id              AS project_id,
                p.title           AS project_name,
                p.project_type,
                COALESCE((SELECT SUM(t.estimated_time)
                           FROM $ttb t
                           WHERE t.project_id = p.id AND t.deleted = 0), 0) AS estimated_hours,
                COALESCE((
                    SELECT (
                        COALESCE(SUM(IF(pt2.end_time IS NOT NULL, TIME_TO_SEC(TIMEDIFF(pt2.end_time, pt2.start_time)), 0)), 0)
                        + COALESCE(SUM(pt2.hours * 3600), 0)
                    ) / 3600
                    FROM $pt pt2
                    WHERE pt2.project_id = p.id AND pt2.deleted = 0 AND pt2.status = 'logged'
                      AND DATE(pt2.start_time) < '$start_date' $effort_member_filter_prec
                ), 0) AS preceding_hours,
                COALESCE((
                    SELECT (
                        COALESCE(SUM(IF(pt2.end_time IS NOT NULL, TIME_TO_SEC(TIMEDIFF(pt2.end_time, pt2.start_time)), 0)), 0)
                        + COALESCE(SUM(pt2.hours * 3600), 0)
                    ) / 3600
                    FROM $pt pt2
                    WHERE pt2.project_id = p.id AND pt2.deleted = 0 AND pt2.status = 'logged'
                      AND DATE(pt2.start_time) BETWEEN '$start_date' AND '$end_date' $effort_member_filter_curr
                ), 0) AS current_hours
            FROM $ptb p
            WHERE p.deleted = 0 $proj_filter $member_proj_filter
            ORDER BY p.title
        ";
        $effort_projects = $this->db->query($sql_effort)->getResult();

        // Per-project per-member hours (current period only)
        $effort_member_hours = []; // [project_id][user_id] = hours
        if (!empty($effort_projects)) {
            $proj_ids = implode(',', array_column($effort_projects, 'project_id'));
            $staff_ids = implode(',', array_column($effort_staff, 'id') ?: [0]);
            if ($proj_ids && $staff_ids) {
                $sql_mh = "
                    SELECT project_id, user_id,
                        (
                            COALESCE(SUM(IF(end_time IS NOT NULL, TIME_TO_SEC(TIMEDIFF(end_time, start_time)), 0)), 0)
                            + COALESCE(SUM(hours * 3600), 0)
                        ) / 3600 AS hours
                    FROM $pt
                    WHERE deleted = 0 AND status = 'logged'
                      AND project_id IN ($proj_ids)
                      AND user_id IN ($staff_ids)
                      AND DATE(start_time) BETWEEN '$start_date' AND '$end_date'
                    GROUP BY project_id, user_id
                ";
                foreach ($this->db->query($sql_mh)->getResult() as $row) {
                    $effort_member_hours[$row->project_id][$row->user_id] = (float)$row->hours;
                }
            }
        }

        // Working days in period (Fri=5, Sat=6 are weekends)
        $effort_working_days = 0;
        $d = new \DateTime($start_date);
        $end_dt = new \DateTime($end_date);
        while ($d <= $end_dt) {
            $dow = (int)$d->format('N');
            if ($dow !== 5 && $dow !== 6) {
                $effort_working_days++;
            }
            $d->modify('+1 day');
        }

        $view_data['effort_projects']      = $effort_projects;
        $view_data['effort_staff']         = $effort_staff;
        $view_data['effort_member_hours']  = $effort_member_hours;
        $view_data['effort_working_days']  = $effort_working_days;
        $view_data['effort_date_label']    = "From: " . date('d-F-y', strtotime($start_date)) . " To: " . date('d-F-y', strtotime($end_date));

        return $this->template->rander("custom_reports/index", $view_data);
    }

    public function team_wise_tasks_modal()
    {
        $team_id    = $this->request->getPost('team_id');
        $project_id = $this->request->getPost('project_id');
        $status_id  = $this->request->getPost('status_id');
        $start_date = $this->request->getPost('start_date');
        $end_date   = $this->request->getPost('end_date');
        $type       = $this->request->getPost('type'); // 'overall' or 'active'
        
        $team = $this->Team_model->get_one($team_id);
        if (!$team) {
            show_404();
        }
        
        $member_ids_raw = array_filter(array_map('trim', explode(',', $team->members)));
        $member_ids     = array_filter($member_ids_raw, 'is_numeric');
        if (empty($member_ids)) {
            $view_data['tasks'] = [];
            return $this->template->view("custom_reports/team_wise_tasks_modal", $view_data);
        }
        $member_ids_str = implode(',', $member_ids);
        $tasks_table         = $this->db->prefixTable('tasks');
        $projects_table      = $this->db->prefixTable('projects');
        $project_members     = $this->db->prefixTable('project_members');
        $task_status_table   = $this->db->prefixTable('task_status');
        $activity_logs_table = $this->db->prefixTable('activity_logs');
        $users_table         = $this->db->prefixTable('users');

        $where = " AND $tasks_table.deleted = 0 ";
        $where .= " AND $tasks_table.assigned_to IN ($member_ids_str) ";

        if ($project_id) {
            // Project-level: exact match
            $where .= " AND $tasks_table.project_id = " . (int)$project_id;
        } else {
            // Team total: only non-deleted projects where a team member is a project member
            $where .= " AND $tasks_table.project_id IN (
                SELECT DISTINCT pm.project_id
                FROM $project_members pm
                INNER JOIN $projects_table p ON p.id = pm.project_id AND p.deleted = 0
                WHERE pm.deleted = 0 AND pm.user_id IN ($member_ids_str)
            )";
        }

        if ($status_id) {
            $where .= " AND $tasks_table.status_id = " . (int)$status_id;
        }

        // Date filter for "active" type — same condition as the main count query
        if ($type === 'active' && $start_date && $end_date) {
            $where .= " AND (
                ($tasks_table.created_date BETWEEN '$start_date' AND '$end_date')
                OR ($tasks_table.status_changed_at IS NOT NULL AND DATE($tasks_table.status_changed_at) BETWEEN '$start_date' AND '$end_date')
                OR EXISTS (
                    SELECT 1 FROM $activity_logs_table al
                    WHERE al.log_type = 'task'
                      AND al.log_type_id = $tasks_table.id
                      AND al.deleted = 0
                      AND DATE(al.created_at) BETWEEN '$start_date' AND '$end_date'
                )
            )";
        }

        $sql = "
            SELECT
                $tasks_table.id,
                $tasks_table.title,
                $projects_table.title AS project_title,
                $task_status_table.title AS status_title,
                $task_status_table.color AS status_color,
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS assigned_to_user,
                $users_table.image AS assigned_to_avatar
            FROM $tasks_table
            LEFT JOIN $projects_table   ON $projects_table.id   = $tasks_table.project_id
            LEFT JOIN $task_status_table ON $task_status_table.id = $tasks_table.status_id
            LEFT JOIN $users_table      ON $users_table.id      = $tasks_table.assigned_to
            WHERE 1=1 $where
            ORDER BY $tasks_table.id DESC
        ";

        $tasks = $this->db->query($sql)->getResult();
        $view_data['tasks']       = $tasks;
        $view_data['modal_title'] = "🏁 " . htmlspecialchars($team->title) . " — Tasks (" . ucfirst($type) . ") — " . count($tasks) . " task(s)";

        return $this->template->view("custom_reports/team_wise_tasks_modal", $view_data);
    }
}
