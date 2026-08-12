<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Reports_api extends Controller
{
    public function __construct()
    {
        helper(['url', 'file', 'form', 'language', 'general', 'date_time', 'app_files', 'currency', 'reports']);
        
        $Settings_model = model("App\Models\Settings_model");
        $settings = $Settings_model->get_all_required_settings()->getResult();
        foreach ($settings as $setting) {
            config('Rise')->app_settings_array[$setting->setting_name] = $setting->setting_value;
        }
    }

    public function get_team_user_logs()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($this->request->getMethod() === 'options') {
            exit;
        }

        $db = \Config\Database::connect();
        $users_model = model("App\Models\Users_model");
        
        // 1. Authenticate (Session or Token)
        $login_user_id = $users_model->login_user_id();
        $is_authenticated = false;
        $login_user = null;

        if ($login_user_id) {
            $login_user = $users_model->get_access_info($login_user_id);
            if ($login_user && $login_user->id && $login_user->user_type === "staff") {
                if ($login_user->permissions) {
                    $permissions = unserialize($login_user->permissions);
                    $login_user->permissions = is_array($permissions) ? $permissions : array();
                } else {
                    $login_user->permissions = array();
                }
                
                $perm = get_array_value($login_user->permissions, "custom_reports");
                if ($login_user->is_admin || $perm === "all" || $perm === "own") {
                    $is_authenticated = true;
                }
            }
        }

        // If not authenticated via session, check API token (SSO_SECRET_KEY)
        if (!$is_authenticated) {
            $token = $this->request->getGet('token');
            if (!$token) {
                $auth_header = $this->request->getServer('HTTP_AUTHORIZATION');
                if ($auth_header && preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                    $token = $matches[1];
                }
            }

            $sso_key = env('SSO_SECRET_KEY');
            if ($token && $sso_key && $token === $sso_key) {
                $is_authenticated = true;
            }
        }

        if (!$is_authenticated) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Unauthorized access.'
            ]);
        }

        // 2. Read parameters
        $team_id = $this->request->getGet('team_id');
        $project_id = $this->request->getGet('project_id');
        $task_id = $this->request->getGet('task_id');
        $start_date = $this->request->getGet('start_date');
        $end_date = $this->request->getGet('end_date');

        // Apply permission filters if logged in with limited permissions
        $custom_reports_permission = $login_user ? get_array_value($login_user->permissions, "custom_reports") : "all";
        if ($custom_reports_permission === "own") {
            $user_id = $login_user->id;
        } else {
            $user_id = $this->request->getGet('user_id') ?: $this->request->getGet('member_id');
        }

        // Default to current month
        if (!$start_date && !$end_date) {
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
        } else {
            if ($start_date) $start_date = date('Y-m-d', strtotime($start_date));
            if ($end_date) $end_date = date('Y-m-d', strtotime($end_date));
        }

        // 3. Retrieve teams and construct mapping
        $team_table = $db->prefixTable('team');
        $users_table = $db->prefixTable('users');
        $project_time_table = $db->prefixTable('project_time');
        $projects_table = $db->prefixTable('projects');
        $tasks_table = $db->prefixTable('tasks');

        $team_query = "SELECT id, title, members FROM $team_table WHERE deleted = 0";
        if ($team_id) {
            $team_query .= " AND id = " . (int)$team_id;
        }
        $team_query .= " ORDER BY title ASC";
        $teams = $db->query($team_query)->getResult();

        $user_teams = [];
        $team_list = [];
        
        foreach ($teams as $team) {
            $team_list[$team->id] = [
                'team_id' => (int)$team->id,
                'team_title' => $team->title,
                'employees' => [],
                'total_team_spent_hours' => 0.00
            ];
            
            $member_ids = array_filter(array_map('trim', explode(',', $team->members ?? '')));
            foreach ($member_ids as $mid) {
                $mid = (int)$mid;
                if ($mid) {
                    $user_teams[$mid][] = (int)$team->id;
                }
            }
        }

        // Include virtual "No Team" if no team filter is selected
        $has_no_team_filter = empty($team_id);
        if ($has_no_team_filter) {
            $team_list[0] = [
                'team_id' => 0,
                'team_title' => 'No Team',
                'employees' => [],
                'total_team_spent_hours' => 0.00
            ];
        }

        // 4. Retrieve timesheet entries
        $offset = convert_seconds_to_time_format(get_timezone_offset());

        $where = " WHERE pt.deleted = 0 AND pt.status = 'logged' ";
        $where .= " AND DATE(ADDTIME(pt.start_time, '$offset')) BETWEEN '$start_date' AND '$end_date' ";
        
        if ($user_id) {
            $where .= " AND pt.user_id = " . (int)$user_id;
        }
        if ($project_id) {
            $where .= " AND pt.project_id = " . (int)$project_id;
        }
        if ($task_id) {
            $where .= " AND pt.task_id = " . (int)$task_id;
        }

        $sql = "SELECT 
                    pt.id AS log_id,
                    pt.user_id,
                    CONCAT(u.first_name, ' ', u.last_name) AS employee_name,
                    pt.project_id,
                    p.title AS project_title,
                    pt.task_id,
                    t.title AS task_title,
                    t.estimated_time AS task_estimated_time,
                    pt.start_time AS start_time_utc,
                    pt.end_time AS end_time_utc,
                    pt.note,
                    ADDTIME(pt.start_time, '$offset') AS local_start_time,
                    ADDTIME(pt.end_time, '$offset') AS local_end_time,
                    DATE(ADDTIME(pt.start_time, '$offset')) AS local_date,
                    ROUND(
                        IF(pt.end_time > pt.start_time, 
                           TIMESTAMPDIFF(SECOND, pt.start_time, pt.end_time), 
                           ROUND(pt.hours * 3600)
                        ) / 3600, 2
                    ) AS hours_spent
                FROM $project_time_table pt
                JOIN $users_table u ON u.id = pt.user_id
                LEFT JOIN $projects_table p ON p.id = pt.project_id
                LEFT JOIN $tasks_table t ON t.id = pt.task_id
                $where
                ORDER BY employee_name ASC, local_date DESC, pt.start_time DESC";

        $logs = $db->query($sql)->getResult();

        // 5. Structure data: employee -> logs (flat array)
        $employee_logs = [];
        foreach ($logs as $log) {
            $uid = (int)$log->user_id;
            
            if (!isset($employee_logs[$uid])) {
                $employee_logs[$uid] = [
                    'employee_id' => $uid,
                    'employee_name' => $log->employee_name,
                    'logs' => [],
                    'total_spent_hours' => 0.00
                ];
            }

            $log_item = [
                'log_id' => (int)$log->log_id,
                'date' => $log->local_date,
                'project_id' => (int)$log->project_id,
                'project_title' => $log->project_title ?? '',
                'task_id' => $log->task_id ? (int)$log->task_id : null,
                'task_title' => $log->task_title ?? '',
                'task_estimated_time' => $log->task_estimated_time ? (float)$log->task_estimated_time : null,
                'start_time' => $log->local_start_time,
                'end_time' => $log->local_end_time,
                'hours_spent' => (float)$log->hours_spent,
                'note' => strip_tags($log->note ?? '')
            ];

            $employee_logs[$uid]['logs'][] = $log_item;
            $employee_logs[$uid]['total_spent_hours'] += $log_item['hours_spent'];
        }

        // 6. Nest employees into teams
        foreach ($employee_logs as $uid => $emp_data) {
            $tids = isset($user_teams[$uid]) ? $user_teams[$uid] : [0];
            
            foreach ($tids as $tid) {
                if (isset($team_list[$tid])) {
                    $team_list[$tid]['employees'][] = $emp_data;
                    $team_list[$tid]['total_team_spent_hours'] += $emp_data['total_spent_hours'];
                }
            }
        }

        // 7. Format final response
        $final_teams = [];
        foreach ($team_list as $tid => $team_data) {
            if ($tid === 0 && empty($team_data['employees'])) {
                continue;
            }
            
            $team_data['total_team_spent_hours'] = round($team_data['total_team_spent_hours'], 2);
            foreach ($team_data['employees'] as &$emp) {
                $emp['total_spent_hours'] = round($emp['total_spent_hours'], 2);
            }
            
            $final_teams[] = $team_data;
        }

        return $this->response->setJSON([
            'success' => true,
            'teams' => $final_teams
        ]);
    }

    public function get_team_assigned_tasks()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($this->request->getMethod() === 'options') {
            exit;
        }

        $db = \Config\Database::connect();
        $users_model = model("App\Models\Users_model");
        
        // 1. Authenticate (Session or Token)
        $login_user_id = $users_model->login_user_id();
        $is_authenticated = false;
        $login_user = null;

        if ($login_user_id) {
            $login_user = $users_model->get_access_info($login_user_id);
            if ($login_user && $login_user->id && $login_user->user_type === "staff") {
                if ($login_user->permissions) {
                    $permissions = unserialize($login_user->permissions);
                    $login_user->permissions = is_array($permissions) ? $permissions : array();
                } else {
                    $login_user->permissions = array();
                }
                
                $perm = get_array_value($login_user->permissions, "custom_reports");
                if ($login_user->is_admin || $perm === "all" || $perm === "own") {
                    $is_authenticated = true;
                }
            }
        }

        // If not authenticated via session, check API token (SSO_SECRET_KEY)
        if (!$is_authenticated) {
            $token = $this->request->getGet('token');
            if (!$token) {
                $auth_header = $this->request->getServer('HTTP_AUTHORIZATION');
                if ($auth_header && preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                    $token = $matches[1];
                }
            }

            $sso_key = env('SSO_SECRET_KEY');
            if ($token && $sso_key && $token === $sso_key) {
                $is_authenticated = true;
            }
        }

        if (!$is_authenticated) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Unauthorized access.'
            ]);
        }

        // 2. Read parameters
        $team_id = $this->request->getGet('team_id');
        $project_id = $this->request->getGet('project_id');
        $status_id = $this->request->getGet('status_id');
        $exclude_status_id = $this->request->getGet('exclude_status_id');
        $priority_id = $this->request->getGet('priority_id');
        $start_date = $this->request->getGet('start_date');
        $end_date = $this->request->getGet('end_date');

        // Apply permission filters if logged in with limited permissions
        $custom_reports_permission = $login_user ? get_array_value($login_user->permissions, "custom_reports") : "all";
        if ($custom_reports_permission === "own") {
            $user_id = $login_user->id;
        } else {
            $user_id = $this->request->getGet('user_id') ?: $this->request->getGet('member_id');
        }

        if ($start_date) $start_date = date('Y-m-d', strtotime($start_date));
        if ($end_date) $end_date = date('Y-m-d', strtotime($end_date));

        // 3. Retrieve teams and construct mapping
        $team_table = $db->prefixTable('team');
        $users_table = $db->prefixTable('users');
        $tasks_table = $db->prefixTable('tasks');
        $projects_table = $db->prefixTable('projects');
        $task_status_table = $db->prefixTable('task_status');
        $task_priority_table = $db->prefixTable('task_priority');

        $team_query = "SELECT id, title, members FROM $team_table WHERE deleted = 0";
        if ($team_id) {
            $team_query .= " AND id = " . (int)$team_id;
        }
        $team_query .= " ORDER BY title ASC";
        $teams = $db->query($team_query)->getResult();

        $user_teams = [];
        $team_list = [];
        
        foreach ($teams as $team) {
            $team_list[$team->id] = [
                'team_id' => (int)$team->id,
                'team_title' => $team->title,
                'employees' => [],
                'total_team_assigned_tasks' => 0,
                'total_team_estimated_hours' => 0.00
            ];
            
            $member_ids = array_filter(array_map('trim', explode(',', $team->members ?? '')));
            foreach ($member_ids as $mid) {
                $mid = (int)$mid;
                if ($mid) {
                    $user_teams[$mid][] = (int)$team->id;
                }
            }
        }

        // Include virtual "No Team" if no team filter is selected
        $has_no_team_filter = empty($team_id);
        if ($has_no_team_filter) {
            $team_list[0] = [
                'team_id' => 0,
                'team_title' => 'No Team',
                'employees' => [],
                'total_team_assigned_tasks' => 0,
                'total_team_estimated_hours' => 0.00
            ];
        }

        // 4. Retrieve assigned tasks entries
        $where = " WHERE t.deleted = 0 ";
        if ($user_id) {
            $where .= " AND t.assigned_to = " . (int)$user_id;
        } else {
            $where .= " AND t.assigned_to > 0 ";
        }
        if ($project_id) {
            $where .= " AND t.project_id = " . (int)$project_id;
        }
        if ($status_id) {
            $where .= " AND t.status_id = " . (int)$status_id;
        }
        if ($exclude_status_id) {
            $where .= " AND t.status_id != " . (int)$exclude_status_id;
        }
        if ($priority_id) {
            $where .= " AND t.priority_id = " . (int)$priority_id;
        }
        if ($start_date) {
            $where .= " AND t.start_date >= '$start_date' ";
        }
        if ($end_date) {
            $where .= " AND t.deadline <= '$end_date' ";
        }

        $sql = "SELECT 
                    t.id AS task_id,
                    t.title AS task_title,
                    t.project_id,
                    p.title AS project_title,
                    t.assigned_to,
                    CONCAT(u.first_name, ' ', u.last_name) AS employee_name,
                    t.status_id,
                    ts.title AS task_status_title,
                    ts.key_name AS task_status_key,
                    t.priority_id,
                    tp.title AS task_priority_title,
                    t.start_date,
                    t.deadline,
                    t.estimated_time,
                    t.points
                FROM $tasks_table t
                JOIN $users_table u ON u.id = t.assigned_to
                LEFT JOIN $projects_table p ON p.id = t.project_id
                LEFT JOIN $task_status_table ts ON ts.id = t.status_id
                LEFT JOIN $task_priority_table tp ON tp.id = t.priority_id
                $where
                ORDER BY employee_name ASC, t.deadline ASC, t.id DESC";

        $tasks_data = $db->query($sql)->getResult();

        // 5. Structure data: employee -> tasks list
        $employee_tasks = [];
        foreach ($tasks_data as $task) {
            $uid = (int)$task->assigned_to;
            
            if (!isset($employee_tasks[$uid])) {
                $employee_tasks[$uid] = [
                    'employee_id' => $uid,
                    'employee_name' => $task->employee_name,
                    'tasks' => [],
                    'total_assigned_tasks' => 0,
                    'total_estimated_hours' => 0.00
                ];
            }

            $task_item = [
                'task_id' => (int)$task->task_id,
                'task_title' => $task->task_title,
                'project_id' => (int)$task->project_id,
                'project_title' => $task->project_title ?? '',
                'status_id' => (int)$task->status_id,
                'status_title' => $task->task_status_title ?? '',
                'status_key' => $task->task_status_key ?? '',
                'priority_id' => $task->priority_id ? (int)$task->priority_id : null,
                'priority_title' => $task->task_priority_title ?? '',
                'start_date' => $task->start_date,
                'deadline' => $task->deadline,
                'estimated_time' => $task->estimated_time ? (float)$task->estimated_time : 0.00,
                'points' => (int)$task->points
            ];

            $employee_tasks[$uid]['tasks'][] = $task_item;
            $employee_tasks[$uid]['total_assigned_tasks']++;
            $employee_tasks[$uid]['total_estimated_hours'] += $task_item['estimated_time'];
        }

        // 6. Nest employees into teams
        foreach ($employee_tasks as $uid => $emp_data) {
            $tids = isset($user_teams[$uid]) ? $user_teams[$uid] : [0];
            
            foreach ($tids as $tid) {
                if (isset($team_list[$tid])) {
                    $team_list[$tid]['employees'][] = $emp_data;
                    $team_list[$tid]['total_team_assigned_tasks'] += $emp_data['total_assigned_tasks'];
                    $team_list[$tid]['total_team_estimated_hours'] += $emp_data['total_estimated_hours'];
                }
            }
        }

        // 7. Format final response
        $final_teams = [];
        foreach ($team_list as $tid => $team_data) {
            if ($tid === 0 && empty($team_data['employees'])) {
                continue;
            }
            
            $team_data['total_team_estimated_hours'] = round($team_data['total_team_estimated_hours'], 2);
            foreach ($team_data['employees'] as &$emp) {
                $emp['total_estimated_hours'] = round($emp['total_estimated_hours'], 2);
            }
            
            $final_teams[] = $team_data;
        }

        return $this->response->setJSON([
            'success' => true,
            'teams' => $final_teams
        ]);
    }
}
