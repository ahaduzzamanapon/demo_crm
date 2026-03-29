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

        // All active (non-deleted) projects
        $projects = $db->query(
            "SELECT p.id, p.title, p.deadline, p.start_date,
                    ps.title AS status_label
             FROM $projects_table p
             LEFT JOIN $project_status_table ps ON ps.id = p.status_id
             WHERE p.deleted=0
             ORDER BY p.deadline IS NULL ASC, p.deadline ASC"
        )->getResult();

        $today  = new \DateTime('today');
        $output = [];

        foreach ($projects as $proj) {
            $pid = (int)$proj->id;

            // Task counts per status with avg estimated_time
            $task_rows = $db->query(
                "SELECT status_id,
                        COUNT(*) AS cnt,
                        AVG(NULLIF(estimated_time, 0)) AS avg_est
                 FROM $tasks_table
                 WHERE deleted=0 AND project_id=$pid AND parent_task_id=0
                 GROUP BY status_id"
            )->getResult();

            $T  = 0; $Dq = 0; $Dp = 0; $Qp = 0;
            $weighted_est = 0; $est_count = 0;

            foreach ($task_rows as $tr) {
                $cnt = (int)$tr->cnt;
                $T  += $cnt;
                $key = $status_map[(int)$tr->status_id] ?? '';

                if ($tr->avg_est > 0) {
                    $weighted_est += $tr->avg_est * $cnt;
                    $est_count    += $cnt;
                }

                if (in_array($key, ['done', 'completed', 'closed', 'qa_completed'])) {
                    $Dq += $cnt;
                } elseif (in_array($key, ['in_progress', 'development', 'dev_in_progress', 'doing'])) {
                    $Dp += $cnt;
                } elseif (strpos($key, 'qa') !== false || $key === 'testing' || $key === 'review') {
                    $Qp += $cnt;
                }
            }

            if ($T === 0) continue;

            $H = ($est_count > 0) ? ($weighted_est / $est_count) : 1;

            $done_pct      = round(($Dq / $T) * 100, 1);
            $dev_pct       = round(($Dp / $T) * 100, 1);
            $qa_pct        = round(($Qp / $T) * 100, 1);
            $remaining_pct = max(0, 100 - $done_pct - $dev_pct - $qa_pct);

            // Time inconsistency logic (only if deadline set)
            $deadline_raw = $proj->deadline ?? '';
            $has_deadline = (!empty($deadline_raw) && $deadline_raw !== '0000-00-00');
            $RT           = $T - $Dq;   // remaining tasks

            $deadline_dt     = null;
            $RD              = null;
            $is_overdue      = false;
            $is_inconsistent = false;

            if ($has_deadline) {
                $deadline_dt     = new \DateTime($deadline_raw);
                $diff            = $today->diff($deadline_dt);
                $RD              = ($deadline_dt >= $today) ? (int)$diff->days : 0;
                $is_overdue      = ($deadline_dt < $today);
                $is_inconsistent = (!$is_overdue && $RD <= 2 && ($RT * $H) > ($RD * 8));
            }

            $RH = round($RT * $H, 1);
            $AH = $has_deadline ? ($RD * 8) : null;

            $output[] = [
                'project_id'      => $pid,
                'project_title'   => $proj->title,
                'deadline'        => $proj->deadline,
                'status_label'    => $proj->status_label ?? 'Active',
                'T'               => $T,
                'Dq'              => $Dq,
                'Dp'              => $Dp,
                'Qp'              => $Qp,
                'RT'              => $RT,
                'RD'              => $RD,
                'RH'              => $RH,
                'AH'              => $AH,
                'done_pct'        => $done_pct,
                'dev_pct'         => $dev_pct,
                'qa_pct'          => $qa_pct,
                'remaining_pct'   => $remaining_pct,
                'is_inconsistent' => $is_inconsistent,
                'is_overdue'      => $is_overdue,
                'avg_est_h'       => round($H, 2),
            ];
        }

        return $this->response->setJSON([
            'projects' => $output,
            'total'    => count($output),
        ]);
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

        // 4. All active projects (ordered by title)
        $projects = $db->query(
            "SELECT p.id, p.title, ps.title AS status_label
             FROM $projects_table p
             LEFT JOIN $ps_table ps ON ps.id = p.status_id
             WHERE p.deleted = 0
             ORDER BY p.title ASC"
        )->getResult();

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

                $members[] = [
                    'user_id'   => $uid,
                    'name'      => $user_map[$uid] ?? "User #$uid",
                    'est'       => $est,
                    'spent'     => $spent,
                    'remaining' => $rem,
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

            // Team performance = avg util_pct of members who logged hours
            // On weekends: members with no log are simply absent (not "missing"),
            // so only count members who actually logged to keep the score fair.
            $perf_members = array_filter($members_data, function($m) use ($is_working_day) {
                if ($m['override'] === 'leave') return false;         // excused leave
                if (!$is_working_day && !$m['has_log']) return false; // weekend no-show → skip
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
