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

                // Performance is only counted when there are actual hours
                $util_pct = ($expected_hours > 0 && $hours > 0)
                            ? round(($hours / $expected_hours) * 100, 2)
                            : 0;

                $has_log = ($hours > 0) ? 1 : 0;
                $comment = '';

                if ($has_log) {
                    // Has actual log
                    if ($leave > 0) $comment = 'on leave';
                    $log_found++;
                } elseif ($override === 'leave') {
                    // Admin marked as leave — excused, do NOT count as missing
                    $comment = 'on leave (admin)';
                    // log_found and missing_log both unchanged, treated as excused
                } elseif ($override === 'missing') {
                    // Admin confirmed missing — counts as missing
                    $comment = 'missing log';
                    $missing_log++;
                } elseif ($is_working_day) {
                    // No log, no override — pending classification
                    if ($leave > 0)  $comment = 'missing log + leave';
                    else             $comment = 'missing log';
                    $missing_log++;
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

            // Team performance = avg of members who actually logged OR are excused
            // Excused (leave override) counted as neutral (not 0, not positive)
            $perf_members = array_filter($members_data, function($m) {
                return $m['override'] !== 'leave';
            });
            $count     = count($perf_members);
            $team_perf = ($count > 0)
                         ? round(array_sum(array_column($perf_members, 'util_pct')) / $count, 2)
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
