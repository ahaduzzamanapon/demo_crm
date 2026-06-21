<?php

namespace App\Controllers;

class Admin_dashboard extends Security_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->access_only_team_members();
        if (!($this->login_user->is_admin || get_array_value($this->login_user->permissions, "can_access_admin_dashboard"))) {
            app_redirect("forbidden");
        }
    }

    public function index()
    {
        return $this->template->rander("admin_dashboard/index");
    }

    // ─── Billable Chart ────────────────────────────────────────────────────────

    public function get_billable_chart_data()
    {
        $projects_model = model('App\Models\Projects_model');
        $data = $projects_model->get_billable_type_counts();

        $billable     = (int)($data->billable     ?? 0);
        $non_billable = (int)($data->non_billable ?? 0);
        $none         = (int)($data->none_type    ?? 0);

        return $this->response->setJSON([
            'billable'     => $billable,
            'non_billable' => $non_billable,
            'none'         => $none,
            'total'        => $billable + $non_billable + $none,
        ]);
    }

    // ─── Project Progress ──────────────────────────────────────────────────────

    public function get_project_progress()
    {
        $this->access_only_team_members();

        $db = \Config\Database::connect();

        $projects_table       = $db->prefixTable('projects');
        $tasks_table          = $db->prefixTable('tasks');
        $task_status_table    = $db->prefixTable('task_status');
        $project_status_table = $db->prefixTable('project_status');

        // Load all task statuses
        $statuses = $db->query(
            "SELECT id, key_name FROM $task_status_table WHERE deleted=0"
        )->getResult();
        $status_map = [];
        foreach ($statuses as $s) {
            $status_map[(int)$s->id] = strtolower(trim($s->key_name));
        }

        // All active (non-deleted) projects with "Open" status
        $projects = $db->query(
            "SELECT p.id, p.title, p.deadline, p.start_date,
                    ps.title AS status_label
             FROM $projects_table p
             LEFT JOIN $project_status_table ps ON ps.id = p.status_id
             WHERE p.deleted=0 AND ps.title='Open'
             ORDER BY p.deadline IS NULL ASC, p.deadline ASC"
        )->getResult();

        // Fetch and map label lists
        $labels_table = $db->prefixTable('labels');
        $labels_list = $db->query("SELECT id, title FROM $labels_table WHERE deleted=0 AND context='task'")->getResult();

        $qa_label_ids = [];
        foreach ($labels_list as $lbl) {
            $title_lower = strtolower(trim($lbl->title));
            if ($title_lower === 'qa') {
                $qa_label_ids[] = (int)$lbl->id;
            }
        }

        $today  = new \DateTime('today');
        $output = [];

        foreach ($projects as $proj) {
            $pid = (int)$proj->id;

            // Fetch all tasks for this project
            $tasks = $db->query(
                "SELECT status_id, estimated_time, labels
                 FROM $tasks_table
                 WHERE deleted=0 AND project_id=$pid AND parent_task_id=0"
            )->getResult();

            $T  = 0; $Dq = 0; $Dp = 0;
            $total_qa = 0; $done_qa = 0;
            $weighted_est = 0; $est_count = 0;

            foreach ($tasks as $task) {
                $task_label_ids = array_filter(explode(',', $task->labels ?? ''));
                $task_label_ids = array_map('intval', $task_label_ids);

                $is_qa_task   = !empty(array_intersect($task_label_ids, $qa_label_ids));
                $is_main_task = !$is_qa_task;

                $key = $status_map[(int)$task->status_id] ?? '';

                if ($is_main_task) {
                    $T++;
                    if ($task->estimated_time > 0) {
                        $weighted_est += (float)$task->estimated_time;
                        $est_count++;
                    }

                    if (in_array($key, ['done', 'completed', 'closed', 'qa_completed'])) {
                        $Dq++;
                    } elseif (in_array($key, ['in_progress', 'development', 'dev_in_progress', 'doing'])) {
                        $Dp++;
                    }
                }

                if ($is_qa_task) {
                    $total_qa++;
                    if (in_array($key, ['done', 'completed', 'closed', 'qa_completed'])) {
                        $done_qa++;
                    }
                }
            }

            if ($T === 0 && $total_qa === 0) continue;

            $H = ($est_count > 0) ? ($weighted_est / $est_count) : 1;

            $done_pct      = ($T > 0) ? round(($Dq / $T) * 100, 1) : 0;
            $dev_pct       = ($T > 0) ? round(($Dp / $T) * 100, 1) : 0;
            $remaining_pct = round(max(0, 100 - $done_pct - $dev_pct), 1);

            // Time inconsistency logic (only if deadline set)
            $deadline_raw = $proj->deadline ?? '';
            $has_deadline = (!empty($deadline_raw) && $deadline_raw !== '0000-00-00');
            $RT           = $T - $Dq;   // remaining tasks

            $deadline_dt     = null;
            $RD              = null;
            $is_past_due     = false;
            $is_overdue      = false;
            $is_inconsistent = false;
            $days_past       = 0;

            if ($has_deadline) {
                $deadline_dt     = new \DateTime($deadline_raw);
                $diff            = $today->diff($deadline_dt);
                if ($deadline_dt >= $today) {
                    $RD          = (int)$diff->days;
                    $is_overdue  = false;
                    $is_past_due = false;
                } else {
                    $RD          = 0;
                    $days_past   = (int)$diff->days;
                    if ($days_past > 10) {
                        $is_overdue  = true;
                        $is_past_due = false;
                    } else {
                        $is_overdue  = false;
                        $is_past_due = true;
                    }
                }
                $is_inconsistent = (!$is_overdue && !$is_past_due && $RD <= 2 && ($RT * $H) > ($RD * 8));
            }

            $RH = round($RT * $H, 1);
            $AH = $has_deadline ? ($RD * 8) : null;

            $output[] = [
                'project_id'      => $pid,
                'project_title'   => $proj->title,
                'deadline'        => $proj->deadline,
                'start_date'      => $proj->start_date,
                'status_label'    => $proj->status_label ?? 'Active',
                'T'               => $T,
                'Dq'              => $Dq,
                'Dp'              => $Dp,
                'total_qa'        => $total_qa,
                'done_qa'         => $done_qa,
                'RT'              => $RT,
                'RD'              => $RD,
                'RH'              => $RH,
                'AH'              => $AH,
                'done_pct'        => $done_pct,
                'dev_pct'         => $dev_pct,
                'qa_pct'          => 0,
                'remaining_pct'   => $remaining_pct,
                'is_inconsistent' => $is_inconsistent,
                'is_overdue'      => $is_overdue,
                'is_past_due'     => $is_past_due,
                'days_past'       => $days_past,
                'avg_est_h'       => round($H, 2),
            ];
        }

        return $this->response->setJSON([
            'projects' => $output,
            'total'    => count($output),
        ]);
    }

    public function get_project_tasks_by_category()
    {
        $this->access_only_team_members();
        $db = \Config\Database::connect();
        
        $project_id = (int)$this->request->getPost('project_id');
        $category   = $this->request->getPost('category'); // done, dev, qa, rem
        
        $tasks_table       = $db->prefixTable('tasks');
        $task_status_table = $db->prefixTable('task_status');
        
        // Fetch and map label lists
        $labels_table = $db->prefixTable('labels');
        $labels_list = $db->query("SELECT id, title FROM $labels_table WHERE deleted=0 AND context='task'")->getResult();

        $qa_label_ids = [];
        foreach ($labels_list as $lbl) {
            $title_lower = strtolower(trim($lbl->title));
            if ($title_lower === 'qa') {
                $qa_label_ids[] = (int)$lbl->id;
            }
        }

        // Only load main tasks (parent_task_id=0) matching the progress logic
        $sql = "SELECT t.id, t.title, t.labels, ts.key_name, ts.title AS status_title, ts.color 
                FROM $tasks_table t
                LEFT JOIN $task_status_table ts ON ts.id = t.status_id
                WHERE t.project_id = $project_id AND t.deleted = 0 AND t.parent_task_id = 0";
                
        $tasks = $db->query($sql)->getResult();
        
        $filtered = [];
        foreach ($tasks as $t) {
            $task_label_ids = array_filter(explode(',', $t->labels ?? ''));
            $task_label_ids = array_map('intval', $task_label_ids);

            $is_qa_task   = !empty(array_intersect($task_label_ids, $qa_label_ids));
            $is_main_task = !$is_qa_task;

            $key = strtolower(trim($t->key_name));
            
            $is_done = in_array($key, ['done', 'completed', 'closed', 'qa_completed']);
            $is_dev  = in_array($key, ['in_progress', 'development', 'dev_in_progress', 'doing']);
            
            if ($category === 'done' && $is_main_task && $is_done) {
                $filtered[] = $t;
            } elseif ($category === 'dev' && $is_main_task && $is_dev) {
                $filtered[] = $t;
            } elseif ($category === 'rem' && $is_main_task && !$is_done && !$is_dev) {
                $filtered[] = $t;
            } elseif ($category === 'total_qa' && $is_qa_task) {
                $filtered[] = $t;
            } elseif ($category === 'done_qa' && $is_qa_task && $is_done) {
                $filtered[] = $t;
            }
        }
        
        $html = '';
        if (empty($filtered)) {
            $html = '<div class="p20 text-center color-secondary">No tasks found for this category.</div>';
        } else {
            $html .= '<ul class="list-group">';
            foreach ($filtered as $t) {
                $color = $t->color ? $t->color : '#e2e8f0';
                $html .= '<li class="list-group-item d-flex justify-content-between align-items-center">';
                $html .= '<a href="#" data-act="ajax-modal" data-action-url="'.get_uri("tasks/view").'" data-post-id="'.$t->id.'" data-modal-lg="1" class="edit">'.esc($t->title).'</a>';
                $html .= '<span class="badge" style="background-color: '.$color.';">'.esc($t->status_title).'</span>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        
        return $this->response->setJSON(['success' => true, 'html' => $html]);
    }

    public function get_member_project_tasks_by_category()
    {
        $this->access_only_team_members();
        $db = \Config\Database::connect();
        
        $project_id = (int)$this->request->getPost('project_id');
        $user_id    = (int)$this->request->getPost('user_id');
        $category   = $this->request->getPost('category'); // spent, remaining
        
        $tasks_table       = $db->prefixTable('tasks');
        $task_status_table = $db->prefixTable('task_status');
        $timesheet_table   = $db->prefixTable('project_time');

        // Helper function to format hours
        $format_hours = function($hours) {
            if ($hours === null || $hours === '') return '0h 0m';
            $hVal = (float)$hours;
            $sign = $hVal < 0 ? '-' : '';
            $hVal = abs($hVal);
            $hours_int = floor($hVal);
            $mins = round(($hVal - $hours_int) * 60);
            if ($mins == 60) {
                $hours_int += 1;
                $mins = 0;
            }
            return "{$sign}{$hours_int}h {$mins}m";
        };

        $html = '';

        if ($category === 'remaining') {
            // Query all tasks assigned to the user on this project
            $sql = "SELECT t.id, t.title, t.estimated_time, ts.key_name, ts.title AS status_title, ts.color,
                           (SELECT SUM(TIMESTAMPDIFF(SECOND, pt.start_time, pt.end_time)) / 3600 
                            FROM $timesheet_table pt 
                            WHERE pt.task_id = t.id AND pt.user_id = $user_id AND pt.deleted = 0 AND pt.end_time IS NOT NULL AND pt.end_time != '0000-00-00 00:00:00') AS spent_hours
                    FROM $tasks_table t
                    LEFT JOIN $task_status_table ts ON ts.id = t.status_id
                    WHERE t.project_id = $project_id 
                      AND t.assigned_to = $user_id 
                      AND t.deleted = 0";
            
            $tasks = $db->query($sql)->getResult();
            $filtered = [];
            foreach ($tasks as $t) {
                $key = strtolower(trim($t->key_name ?? ''));
                $is_done = in_array($key, ['done', 'completed', 'closed', 'qa_completed']);
                if (!$is_done) {
                    $filtered[] = $t;
                }
            }

            if (empty($filtered)) {
                $html = '<div class="p20 text-center color-secondary">No remaining tasks found.</div>';
            } else {
                $html .= '<ul class="list-group">';
                foreach ($filtered as $t) {
                    $color = $t->color ? $t->color : '#e2e8f0';
                    $est_str = $format_hours($t->estimated_time);
                    $spent_str = $format_hours($t->spent_hours);
                    
                    $html .= '<li class="list-group-item d-flex justify-content-between align-items-center" style="gap: 12px; flex-wrap: wrap;">';
                    $html .= '<div style="flex: 1; min-width: 200px;">';
                    $html .= '  <a href="#" data-act="ajax-modal" data-action-url="'.get_uri("tasks/view").'" data-post-id="'.$t->id.'" data-modal-lg="1" class="edit" style="font-weight: 500;">'.esc($t->title).'</a>';
                    $html .= '  <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Est: ' . $est_str . ' | Spent: ' . $spent_str . '</div>';
                    $html .= '</div>';
                    $html .= '<span class="badge" style="background-color: '.$color.';">'.esc($t->status_title).'</span>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }
        } elseif ($category === 'spent') {
            // Query all timesheet logs on tasks or project level
            $sql = "SELECT pt.task_id, 
                           SUM(TIMESTAMPDIFF(SECOND, pt.start_time, pt.end_time)) / 3600 AS spent_hours,
                           t.title AS task_title, 
                           ts.title AS status_title, 
                           ts.color AS status_color
                    FROM $timesheet_table pt
                    LEFT JOIN $tasks_table t ON t.id = pt.task_id
                    LEFT JOIN $task_status_table ts ON ts.id = t.status_id
                    WHERE pt.project_id = $project_id
                      AND pt.user_id = $user_id
                      AND pt.deleted = 0
                      AND pt.end_time IS NOT NULL
                      AND pt.end_time != '0000-00-00 00:00:00'
                    GROUP BY pt.task_id
                    ORDER BY pt.task_id DESC";
            
            $logs = $db->query($sql)->getResult();

            if (empty($logs)) {
                $html = '<div class="p20 text-center color-secondary">No spent hours found.</div>';
            } else {
                $html .= '<ul class="list-group">';
                foreach ($logs as $log) {
                    $spent_str = $format_hours($log->spent_hours);
                    $html .= '<li class="list-group-item d-flex justify-content-between align-items-center" style="gap: 12px; flex-wrap: wrap;">';
                    
                    if ($log->task_id > 0 && !empty($log->task_title)) {
                        $color = $log->status_color ? $log->status_color : '#e2e8f0';
                        $html .= '<div style="flex: 1; min-width: 200px;">';
                        $html .= '  <a href="#" data-act="ajax-modal" data-action-url="'.get_uri("tasks/view").'" data-post-id="'.$log->task_id.'" data-modal-lg="1" class="edit" style="font-weight: 500;">'.esc($log->task_title).'</a>';
                        $html .= '  <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Spent: ' . $spent_str . '</div>';
                        $html .= '</div>';
                        $html .= '<span class="badge" style="background-color: '.$color.';">'.esc($log->status_title).'</span>';
                    } else {
                        $html .= '<div style="flex: 1; min-width: 200px;">';
                        $html .= '  <span style="color: #64748b; font-style: italic; font-weight: 500;">General project time (no specific task)</span>';
                        $html .= '  <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Spent: ' . $spent_str . '</div>';
                        $html .= '</div>';
                        $html .= '<span class="badge" style="background-color: #cbd5e1; color: #475569;">Logged</span>';
                    }
                    
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }
        }

        return $this->response->setJSON(['success' => true, 'html' => $html]);
    }

    // ─── Resource Utilization ──────────────────────────────────────────────────


    public function get_resource_utilization()
    {
        $this->access_only_team_members();

        $db = \Config\Database::connect();

        $projects_table  = $db->prefixTable('projects');
        $tasks_table     = $db->prefixTable('tasks');
        $timesheet_table = $db->prefixTable('project_time');
        $users_table     = $db->prefixTable('users');
        $ps_table        = $db->prefixTable('project_status');
        $task_status_table = $db->prefixTable('task_status');

        // Disable strict SQL mode for GROUP BY aggregations
        try { $db->query("SET sql_mode = ''"); } catch (\Exception $e) {}

        // 1. Estimated hours per project per assigned member (from tasks)
        $est_rows = $db->query(
            "SELECT t.project_id,
                    t.assigned_to AS user_id,
                    SUM(IFNULL(t.estimated_time, 0)) AS est_hours
             FROM $tasks_table t
             WHERE t.deleted = 0
               AND t.project_id > 0
               AND t.assigned_to > 0
             GROUP BY t.project_id, t.assigned_to"
        )->getResult();

        // 2. Time spent per project per member (from timesheets)
        $spent_rows = $db->query(
            "SELECT pt.project_id,
                    pt.user_id,
                    SUM(TIMESTAMPDIFF(SECOND, pt.start_time, pt.end_time)) / 3600 AS spent_hours
             FROM $timesheet_table pt
             WHERE pt.deleted = 0
               AND pt.end_time IS NOT NULL
               AND pt.end_time != '0000-00-00 00:00:00'
             GROUP BY pt.project_id, pt.user_id"
        )->getResult();

        // 3. User names
        $user_rows = $db->query(
            "SELECT id, CONCAT(first_name, ' ', last_name) AS full_name, image
             FROM $users_table
             WHERE deleted = 0 AND user_type = 'staff'"
        )->getResult();

        $user_map = [];
        foreach ($user_rows as $u) {
            $user_map[(int)$u->id] = $u->full_name;
        }

        // 4. All active projects with "Open" status (ordered by title)
        $projects = $db->query(
            "SELECT p.id, p.title, p.deadline AS project_deadline, ps.title AS status_label
             FROM $projects_table p
             LEFT JOIN $ps_table ps ON ps.id = p.status_id
             WHERE p.deleted = 0 AND ps.title = 'Open'
             ORDER BY p.title ASC"
        )->getResult();

        // 5. Fetch max task deadlines per project per user
        $deadline_rows = $db->query(
            "SELECT t.project_id,
                    t.assigned_to AS user_id,
                    MAX(CASE WHEN ts.key_name NOT IN ('done', 'completed', 'closed', 'qa_completed') THEN t.deadline END) AS active_task_deadline,
                    MAX(t.deadline) AS all_task_deadline
             FROM $tasks_table t
             LEFT JOIN $task_status_table ts ON ts.id = t.status_id
             WHERE t.deleted = 0
               AND t.project_id > 0
               AND t.assigned_to > 0
             GROUP BY t.project_id, t.assigned_to"
        )->getResult();

        $deadlines_map = [];
        foreach ($deadline_rows as $dr) {
            $deadlines_map[(int)$dr->project_id][(int)$dr->user_id] = [
                'active_task_deadline' => $dr->active_task_deadline,
                'all_task_deadline'    => $dr->all_task_deadline,
            ];
        }

        // Build lookup maps  project_id → user_id → est/spent
        $est_map   = [];   // [project_id][user_id] = est_hours
        $spent_map = [];   // [project_id][user_id] = spent_hours

        foreach ($est_rows as $r) {
            $est_map[(int)$r->project_id][(int)$r->user_id] = (float)$r->est_hours;
        }
        foreach ($spent_rows as $r) {
            $spent_map[(int)$r->project_id][(int)$r->user_id] = round((float)$r->spent_hours, 2);
        }

        $output = [];
        foreach ($projects as $proj) {
            $pid      = (int)$proj->id;
            $members  = [];

            // All users that appear in either est or spent for this project
            $user_ids = array_unique(array_merge(
                array_keys($est_map[$pid]   ?? []),
                array_keys($spent_map[$pid] ?? [])
            ));

            if (empty($user_ids)) continue;

            foreach ($user_ids as $uid) {
                $est   = round($est_map[$pid][$uid]   ?? 0, 2);
                $spent = round($spent_map[$pid][$uid] ?? 0, 2);
                $rem   = round($est - $spent, 2);

                // Determine engage_up_to date
                $active_task_deadline = $deadlines_map[$pid][$uid]['active_task_deadline'] ?? null;
                $all_task_deadline    = $deadlines_map[$pid][$uid]['all_task_deadline']    ?? null;
                $project_deadline     = $proj->project_deadline ?? null;

                $engage_date = null;
                if (!empty($active_task_deadline) && $active_task_deadline !== '0000-00-00 00:00:00' && $active_task_deadline !== '0000-00-00') {
                    $engage_date = $active_task_deadline;
                } elseif (!empty($all_task_deadline) && $all_task_deadline !== '0000-00-00 00:00:00' && $all_task_deadline !== '0000-00-00') {
                    $engage_date = $all_task_deadline;
                } elseif (!empty($project_deadline) && $project_deadline !== '0000-00-00 00:00:00' && $project_deadline !== '0000-00-00') {
                    $engage_date = $project_deadline;
                }

                $engage_str = '-';
                if ($engage_date) {
                    $clean_date = date('Y-m-d', strtotime($engage_date));
                    if ($clean_date !== '1970-01-01' && $clean_date !== '0000-00-00') {
                        $engage_str = format_to_date($engage_date, false);
                    }
                }

                $members[] = [
                    'user_id'      => $uid,
                    'name'         => $user_map[$uid] ?? "User #$uid",
                    'est'          => $est,
                    'spent'        => $spent,
                    'remaining'    => $rem,
                    'engage_up_to' => $engage_str,
                ];
            }

            // Sort by name
            usort($members, fn($a, $b) => strcmp($a['name'], $b['name']));

            $output[] = [
                'project_id'   => $pid,
                'project_title'=> $proj->title,
                'status_label' => $proj->status_label ?? 'Active',
                'members'      => $members,
            ];
        }

        return $this->response->setJSON([
            'projects' => $output,
            'total'    => count($output),
        ]);
    }

    public function get_staff_projects()
    {
        $this->access_only_team_members();
        $db = \Config\Database::connect();

        $projects_table        = $db->prefixTable('projects');
        $project_members_table = $db->prefixTable('project_members');
        $project_status_table  = $db->prefixTable('project_status');
        $users_table           = $db->prefixTable('users');
        $tasks_table           = $db->prefixTable('tasks');
        $task_status_table     = $db->prefixTable('task_status');

        $today = date('Y-m-d');

        // 1. Group by project_id (All Assignments)
        $sql = "SELECT pm.project_id, pm.user_id, p.title AS project_title, p.deadline AS project_deadline,
                       CONCAT(u.first_name, ' ', u.last_name) AS member_name,
                       u.job_title AS designation,
                       (SELECT MAX(t.deadline) 
                        FROM $tasks_table t 
                        WHERE t.project_id = pm.project_id 
                          AND t.assigned_to = pm.user_id 
                          AND t.deleted = 0 
                          AND t.status_id NOT IN (SELECT ts.id FROM $task_status_table ts WHERE ts.key_name IN ('done', 'completed', 'closed', 'qa_completed'))) AS active_task_deadline,
                       (SELECT MAX(t.deadline) 
                        FROM $tasks_table t 
                        WHERE t.project_id = pm.project_id 
                          AND t.assigned_to = pm.user_id 
                          AND t.deleted = 0) AS all_task_deadline
                FROM $project_members_table pm
                JOIN $projects_table p ON p.id = pm.project_id
                JOIN $project_status_table ps ON ps.id = p.status_id
                JOIN $users_table u ON u.id = pm.user_id
                WHERE pm.deleted = 0 
                  AND p.deleted = 0 
                  AND u.deleted = 0 
                  AND u.user_type = 'staff' 
                  AND ps.title = 'Open'
                ORDER BY p.title ASC, member_name ASC";

        $rows = $db->query($sql)->getResult();

        // Group by project_id
        $projects_map = [];
        foreach ($rows as $row) {
            $pid = (int)$row->project_id;
            if (!isset($projects_map[$pid])) {
                $projects_map[$pid] = [
                    'project_id'    => $pid,
                    'project_title' => $row->project_title,
                    'members'       => []
                ];
            }

            // Determine engage_up_to date
            $engage_date = null;
            if (!empty($row->active_task_deadline) && $row->active_task_deadline !== '0000-00-00 00:00:00' && $row->active_task_deadline !== '0000-00-00') {
                $engage_date = $row->active_task_deadline;
            } elseif (!empty($row->all_task_deadline) && $row->all_task_deadline !== '0000-00-00 00:00:00' && $row->all_task_deadline !== '0000-00-00') {
                $engage_date = $row->all_task_deadline;
            } elseif (!empty($row->project_deadline) && $row->project_deadline !== '0000-00-00 00:00:00' && $row->project_deadline !== '0000-00-00') {
                $engage_date = $row->project_deadline;
            }

            $engage_str = '-';
            if ($engage_date) {
                $clean_date = date('Y-m-d', strtotime($engage_date));
                if ($clean_date !== '1970-01-01' && $clean_date !== '0000-00-00') {
                    $engage_str = format_to_date($engage_date, false);
                }
            }

            $projects_map[$pid]['members'][] = [
                'user_id'      => (int)$row->user_id,
                'name'         => $row->member_name,
                'designation'  => !empty($row->designation) ? $row->designation : '-',
                'engage_up_to' => $engage_str
            ];
        }

        // 2. Fetch Active and Inactive resources (grouped by staff)
        $resources_sql = "SELECT u.id AS user_id, 
                               CONCAT(u.first_name, ' ', u.last_name) AS member_name, 
                               u.job_title AS designation,
                               COUNT(DISTINCT CASE WHEN (t.id IS NOT NULL AND ts.key_name NOT IN ('done', 'completed', 'closed', 'qa_completed') AND (t.deadline IS NULL OR t.deadline >= '$today')) THEN t.id END) AS active_tasks_count,
                               MAX(CASE WHEN (t.id IS NOT NULL AND ts.key_name NOT IN ('done', 'completed', 'closed', 'qa_completed') AND (t.deadline IS NOT NULL AND t.deadline != '0000-00-00 00:00:00' AND t.deadline >= '$today')) THEN t.deadline END) AS active_engage_up_to,
                               GROUP_CONCAT(DISTINCT CASE WHEN (t.id IS NOT NULL AND ts.key_name NOT IN ('done', 'completed', 'closed', 'qa_completed') AND (t.deadline IS NULL OR t.deadline >= '$today')) THEN p.title END SEPARATOR ', ') AS active_projects,
                               (SELECT GROUP_CONCAT(DISTINCT p2.title SEPARATOR ', ') 
                                FROM $project_members_table pm2
                                JOIN $projects_table p2 ON p2.id = pm2.project_id
                                JOIN $project_status_table ps2 ON ps2.id = p2.status_id
                                WHERE pm2.deleted = 0 AND p2.deleted = 0 AND ps2.title = 'Open' AND pm2.user_id = u.id) AS member_projects
                        FROM $users_table u
                        LEFT JOIN $tasks_table t ON t.assigned_to = u.id AND t.deleted = 0 AND t.project_id IN (
                            SELECT p3.id FROM $projects_table p3 
                            JOIN $project_status_table ps3 ON ps3.id = p3.status_id 
                            WHERE p3.deleted = 0 AND ps3.title = 'Open'
                        )
                        LEFT JOIN $task_status_table ts ON ts.id = t.status_id
                        LEFT JOIN $projects_table p ON p.id = t.project_id
                        WHERE u.deleted = 0 
                          AND u.user_type = 'staff' 
                          AND u.status = 'active'
                        GROUP BY u.id
                        ORDER BY member_name ASC";

        $resource_rows = $db->query($resources_sql)->getResult();

        $active_resources = [];
        $inactive_resources = [];

        foreach ($resource_rows as $row) {
            $active_count = (int)$row->active_tasks_count;

            if ($active_count > 0) {
                $engage_str = '-';
                if ($row->active_engage_up_to) {
                    $clean_date = date('Y-m-d', strtotime($row->active_engage_up_to));
                    if ($clean_date !== '1970-01-01' && $clean_date !== '0000-00-00') {
                        $engage_str = format_to_date($row->active_engage_up_to, false);
                    }
                }

                $active_resources[] = [
                    'user_id'            => (int)$row->user_id,
                    'name'               => $row->member_name,
                    'designation'        => !empty($row->designation) ? $row->designation : '-',
                    'active_tasks_count' => $active_count,
                    'engage_up_to'       => $engage_str,
                    'projects'           => !empty($row->active_projects) ? $row->active_projects : '-'
                ];
            } else {
                $inactive_resources[] = [
                    'user_id'     => (int)$row->user_id,
                    'name'        => $row->member_name,
                    'designation' => !empty($row->designation) ? $row->designation : '-',
                    'projects'    => !empty($row->member_projects) ? $row->member_projects : '-'
                ];
            }
        }

        return $this->response->setJSON([
            'projects'           => array_values($projects_map),
            'active_resources'   => $active_resources,
            'inactive_resources' => $inactive_resources,
            'total'              => count($projects_map)
        ]);
    }

    public function get_member_active_tasks()
    {
        $this->access_only_team_members();
        $db = \Config\Database::connect();
        
        $user_id = (int)$this->request->getPost('user_id');
        
        $tasks_table        = $db->prefixTable('tasks');
        $task_status_table  = $db->prefixTable('task_status');
        $projects_table     = $db->prefixTable('projects');
        $project_status_table = $db->prefixTable('project_status');
        
        $today = date('Y-m-d');
        
        $sql = "SELECT t.id, t.title, t.estimated_time, t.deadline, 
                       ts.title AS status_title, ts.color, p.title AS project_title
                FROM $tasks_table t
                JOIN $projects_table p ON p.id = t.project_id
                JOIN $project_status_table ps ON ps.id = p.status_id
                LEFT JOIN $task_status_table ts ON ts.id = t.status_id
                WHERE t.deleted = 0 
                  AND t.assigned_to = $user_id 
                  AND p.deleted = 0 
                  AND ps.title = 'Open'
                  AND ts.key_name NOT IN ('done', 'completed', 'closed', 'qa_completed')
                  AND (t.deadline IS NULL OR t.deadline >= '$today')
                ORDER BY p.title ASC, t.deadline IS NULL ASC, t.deadline ASC";
                
        $tasks = $db->query($sql)->getResult();
        
        $format_hours = function($hours) {
            if ($hours === null || $hours === '') return '0h 0m';
            $hVal = (float)$hours;
            $sign = $hVal < 0 ? '-' : '';
            $hVal = abs($hVal);
            $hours_int = floor($hVal);
            $mins = round(($hVal - $hours_int) * 60);
            if ($mins == 60) {
                $hours_int += 1;
                $mins = 0;
            }
            return "{$sign}{$hours_int}h {$mins}m";
        };
        
        $html = '';
        if (empty($tasks)) {
            $html = '<div class="p20 text-center color-secondary">No active tasks found.</div>';
        } else {
            $html .= '<ul class="list-group">';
            foreach ($tasks as $t) {
                $color = $t->color ? $t->color : '#e2e8f0';
                $est_str = $format_hours($t->estimated_time);
                $deadline_str = !empty($t->deadline) && $t->deadline !== '0000-00-00' && $t->deadline !== '0000-00-00 00:00:00' ? format_to_date($t->deadline, false) : 'No deadline';
                
                $html .= '<li class="list-group-item d-flex justify-content-between align-items-center" style="gap: 12px; flex-wrap: wrap;">';
                $html .= '<div style="flex: 1; min-width: 200px;">';
                $html .= '  <div style="font-size: 10px; color: #6366f1; font-weight: 600; text-transform: uppercase; margin-bottom: 2px;">'.esc($t->project_title).'</div>';
                $html .= '  <a href="#" data-act="ajax-modal" data-action-url="'.get_uri("tasks/view").'" data-post-id="'.$t->id.'" data-modal-lg="1" class="edit" style="font-weight: 500; font-size:12.5px;">'.esc($t->title).'</a>';
                $html .= '  <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Est: ' . $est_str . ' | Deadline: ' . $deadline_str . '</div>';
                $html .= '</div>';
                $html .= '<span class="badge" style="background-color: '.$color.';">'.esc($t->status_title).'</span>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        
        return $this->response->setJSON(['success' => true, 'html' => $html]);
    }

    // ─── Helper: ensure override table exists ──────────────────────────────────

    private function ensureOverrideTable($db)
    {
        $tbl = $db->prefixTable('perf_leave_overrides');
        $db->query("CREATE TABLE IF NOT EXISTS $tbl (
            `id`            int(11)      NOT NULL AUTO_INCREMENT,
            `user_id`       int(11)      NOT NULL,
            `report_date`   date         NOT NULL,
            `override_type` varchar(20)  NOT NULL DEFAULT 'leave',
            `created_by`    int(11)      NOT NULL DEFAULT 0,
            `created_at`    timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_date` (`user_id`, `report_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }

    // ─── Mark override (leave / missing) ──────────────────────────────────────

    public function mark_perf_override()
    {
        $this->access_only_team_members();

        $user_id     = (int)$this->request->getPost('user_id');
        $report_date = preg_replace('/[^0-9\-]/', '', $this->request->getPost('report_date') ?? '');
        $raw_type    = $this->request->getPost('override_type');
        $otype       = ($raw_type === 'leave') ? 'leave' : 'missing';
        $by          = (int)$this->login_user->id;

        if (!$user_id || !$report_date) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid input.']);
        }

        $db  = \Config\Database::connect();
        $this->ensureOverrideTable($db);
        $tbl = $db->prefixTable('perf_leave_overrides');

        $db->query("INSERT INTO $tbl (user_id, report_date, override_type, created_by)
                    VALUES ($user_id, '$report_date', '$otype', $by)
                    ON DUPLICATE KEY UPDATE override_type='$otype', created_by=$by, created_at=NOW()");

        return $this->response->setJSON(['success' => true]);
    }

    // ─── Employee Performance Report ───────────────────────────────────────────

    public function get_employee_performance_report()
    {
        $this->access_only_team_members();

        $report_date = $this->request->getGet('report_date');
        if (!$report_date) $report_date = date('Y-m-d');
        $report_date = preg_replace('/[^0-9\-]/', '', $report_date);

        $db = \Config\Database::connect();
        $this->ensureOverrideTable($db);

        $users_table     = $db->prefixTable('users');
        $team_table      = $db->prefixTable('team');
        $timesheet_table = $db->prefixTable('project_time');
        $leave_table     = $db->prefixTable('leave_applications');
        $override_table  = $db->prefixTable('perf_leave_overrides');

        // 1. All teams
        $teams = $db->query(
            "SELECT id, title, members FROM $team_table WHERE deleted=0 ORDER BY title ASC"
        )->getResult();

        // 2. Timesheet hours for the date
        $ts_map = [];
        foreach ($db->query(
            "SELECT user_id,
                    ROUND((COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time))),0) +
                           COALESCE(SUM(ROUND((hours * 60), 0) * 60),0)) / 3600, 2) AS total_hours
             FROM $timesheet_table
             WHERE deleted=0 AND status != 'open'
               AND DATE(start_time) = '$report_date'
             GROUP BY user_id"
        )->getResult() as $row) {
            $ts_map[$row->user_id] = (float)$row->total_hours;
        }

        // 3. Approved leave covering this date
        $leave_map = [];
        foreach ($db->query(
            "SELECT applicant_id, SUM(total_days) AS leave_days
             FROM $leave_table
             WHERE deleted=0 AND status='approved'
               AND '$report_date' BETWEEN start_date AND end_date
             GROUP BY applicant_id"
        )->getResult() as $row) {
            $leave_map[$row->applicant_id] = (float)$row->leave_days;
        }

        // 4. Admin overrides for this date
        $override_map = [];
        foreach ($db->query(
            "SELECT user_id, override_type
             FROM $override_table
             WHERE report_date = '$report_date'"
        )->getResult() as $row) {
            $override_map[$row->user_id] = $row->override_type;
        }

        // 5. Active staff names
        $user_map = [];
        foreach ($db->query(
            "SELECT id, CONCAT(first_name, ' ', last_name) AS full_name
             FROM $users_table
             WHERE deleted=0 AND user_type='staff' AND status='active'"
        )->getResult() as $u) {
            $user_map[$u->id] = $u->full_name;
        }

        // 6. Working day check
        $day_of_week    = (int)(new \DateTime($report_date))->format('N');
        $is_working_day = ($day_of_week <= 5) ? 1 : 0;
        $expected_hours = $is_working_day ? 8 : 0;

        // 7. Build team output
        $output_teams = [];
        foreach ($teams as $team) {
            $member_ids = array_filter(array_map('trim', explode(',', $team->members ?? '')));
            if (empty($member_ids)) continue;

            $members_data = [];
            $log_found    = 0;
            $missing_log  = 0;

            foreach ($member_ids as $uid) {
                $uid      = (int)$uid;
                if (!$uid) continue;

                $name     = $user_map[$uid]      ?? "User #$uid";
                $hours    = $ts_map[$uid]        ?? 0;
                $leave    = $leave_map[$uid]     ?? 0;
                $override = $override_map[$uid]  ?? null;   // 'leave' | 'missing' | null

                // Performance calculation:
                // - Working day: use expected_hours (8h)
                // - Weekend/holiday: if someone actually logged time, still measure
                //   against 8h standard so progress bars & team score are meaningful
                $calc_base = ($expected_hours > 0) ? $expected_hours : 8;
                $util_pct = ($hours > 0)
                            ? round(($hours / $calc_base) * 100, 1)
                            : 0;

                $has_log = ($hours > 0) ? 1 : 0;
                $comment = '';

                if ($has_log) {
                    // Has actual log
                    if ($leave > 0) $comment = 'on leave';
                    $log_found++;
                } elseif ($override === 'leave') {
                    // Admin marked as leave — excused
                    $comment = 'on leave (admin)';
                } elseif ($override === 'missing') {
                    // Admin confirmed missing
                    $comment = 'missing log';
                    $missing_log++;
                } else {
                    // No log, no override — always show badge so admin can classify
                    if ($leave > 0)  $comment = 'missing log + leave';
                    else             $comment = 'missing log';
                    // Only count toward missing score on actual working days
                    if ($is_working_day) $missing_log++;
                }

                $members_data[] = [
                    'user_id'   => $uid,
                    'name'      => $name,
                    'hours'     => $hours,
                    'util_pct'  => $util_pct,
                    'leave'     => $leave,
                    'has_log'   => $has_log,
                    'comment'   => $comment,
                    'override'  => $override,   // passed to frontend
                ];
            }

            // Team performance rules:
            // - "missing log"          → util=0, INCLUDED (penalises team score)
            // - "on leave" (any type)  → EXCLUDED (absence excused, doesn't penalise)
            // - logged hours           → actual util included
            $perf_members = array_filter($members_data, function($m) {
                $c = $m['comment'];
                // Exclude all leave variants
                if ($m['override'] === 'leave')   return false; // admin-marked leave
                if ($c === 'on leave')             return false; // has real leave record + log
                if ($c === 'on leave (admin)')     return false; // admin override
                // Missing log + leave → treat as missing (still penalises)
                // Include logged members and plain missing log members
                return true;
            });
            $count     = count($perf_members);
            $team_perf = ($count > 0)
                         ? round(array_sum(array_column($perf_members, 'util_pct')) / $count, 1)
                         : 0;

            $output_teams[] = [
                'team_id'   => $team->id,
                'team_name' => $team->title,
                'members'   => $members_data,
                'team_perf' => $team_perf,
                'log_found' => $log_found,
                'missing'   => $missing_log,
            ];
        }

        return $this->response->setJSON([
            'teams'          => $output_teams,
            'report_date'    => $report_date,
            'is_working_day' => $is_working_day,
            'expected_hours' => $expected_hours,
        ]);
    }

    // ─── Best Performed Days ───────────────────────────────────────────────────

    public function get_best_performed_days()
    {
        $this->access_only_team_members();

        $year  = (int)($this->request->getGet('year')  ?: date('Y'));
        $month = (int)($this->request->getGet('month') ?: date('m'));

        $month_start = sprintf('%04d-%02d-01', $year, $month);
        $month_end   = date('Y-m-t', strtotime($month_start));

        $db = \Config\Database::connect();
        $team_table      = $db->prefixTable('team');
        $timesheet_table = $db->prefixTable('project_time');

        $teams = $db->query(
            "SELECT id, title, members FROM $team_table WHERE deleted=0 ORDER BY title ASC"
        )->getResult();

        $palette = [
            '#4361ee', '#e63946', '#2ec4b6', '#f4a261',
            '#8338ec', '#06d6a0', '#fb5607', '#3a86ff',
        ];

        $result = [];
        $i = 0;
        foreach ($teams as $team) {
            $member_ids = array_filter(array_map('trim', explode(',', $team->members ?? '')));
            if (empty($member_ids)) continue;

            $ids_str = implode(',', array_map('intval', $member_ids));

            $sql = "SELECT COUNT(DISTINCT DATE(start_time)) AS day_count
                    FROM $timesheet_table
                    WHERE deleted=0
                      AND status != 'open'
                      AND user_id IN ($ids_str)
                      AND DATE(start_time) BETWEEN '$month_start' AND '$month_end'";

            $row = $db->query($sql)->getRow();
            $result[] = [
                'team_name' => strtoupper($team->title),
                'day_count' => $row ? (int)$row->day_count : 0,
                'color'     => $palette[$i % count($palette)],
            ];
            $i++;
        }

        return $this->response->setJSON([
            'teams'       => $result,
            'month_label' => date('F Y', strtotime($month_start)),
        ]);
    }

}

/* End of file Admin_dashboard.php */
/* Location: ./app/Controllers/Admin_dashboard.php */
