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

    public function get_billable_chart_data()
    {
        $this->access_only_team_members();

        $projects_model = model('App\Models\Projects_model');
        $data = $projects_model->get_billable_type_counts();

        $billable     = (int)($data->billable     ?? 0);
        $non_billable = (int)($data->non_billable ?? 0);
        $none         = (int)($data->none_type    ?? 0);
        $total        = $billable + $non_billable + $none;

        return $this->response->setJSON([
            'billable'     => $billable,
            'non_billable' => $non_billable,
            'none'         => $none,
            'total'        => $total,
        ]);
    }

    public function get_employee_performance_report()
    {
        $this->access_only_team_members();

        $report_date = $this->request->getGet('report_date');
        if (!$report_date) {
            $report_date = date('Y-m-d');
        }

        // Sanitize — keep only digits and dashes
        $report_date = preg_replace('/[^0-9\-]/', '', $report_date);

        // Get DB connection
        $db = \Config\Database::connect();

        $users_table     = $db->prefixTable('users');
        $team_table      = $db->prefixTable('team');
        $timesheet_table = $db->prefixTable('project_time');
        $leave_table     = $db->prefixTable('leave_applications');

        // 1. All teams
        $teams = $db->query(
            "SELECT id, title, members FROM $team_table WHERE deleted=0 ORDER BY title ASC"
        )->getResult();

        // 2. Timesheet hours for the specific date per user
        // Use DATE(start_time) directly — times are stored in local timezone
        $ts_sql = "SELECT $timesheet_table.user_id,
                       ROUND((COALESCE(SUM(TIME_TO_SEC(TIMEDIFF($timesheet_table.end_time, $timesheet_table.start_time))),0) +
                              COALESCE(SUM(ROUND(($timesheet_table.hours * 60), 0) * 60),0)) / 3600, 2) AS total_hours
                   FROM $timesheet_table
                   WHERE $timesheet_table.deleted=0
                     AND $timesheet_table.status != 'open'
                     AND DATE($timesheet_table.start_time) = '$report_date'
                   GROUP BY $timesheet_table.user_id";
        $ts_map = [];
        foreach ($db->query($ts_sql)->getResult() as $row) {
            $ts_map[$row->user_id] = (float)$row->total_hours;
        }

        // 3. Approved leave covering this date
        $leave_sql = "SELECT applicant_id, SUM(total_days) AS leave_days
                      FROM $leave_table
                      WHERE deleted=0 AND status='approved'
                        AND '$report_date' BETWEEN start_date AND end_date
                      GROUP BY applicant_id";
        $leave_map = [];
        foreach ($db->query($leave_sql)->getResult() as $row) {
            $leave_map[$row->applicant_id] = (float)$row->leave_days;
        }

        // 4. Active staff names
        $user_map = [];
        foreach ($db->query(
            "SELECT id, CONCAT(first_name, ' ', last_name) AS full_name
             FROM $users_table
             WHERE deleted=0 AND user_type='staff' AND status='active'"
        )->getResult() as $u) {
            $user_map[$u->id] = $u->full_name;
        }

        // 5. Determine if it's a working day (Mon=1 … Fri=5)
        $day_of_week    = (int)(new \DateTime($report_date))->format('N');
        $is_working_day = ($day_of_week <= 5) ? 1 : 0;
        $expected_hours = $is_working_day ? 8 : 0;

        // 6. Build team output
        $output_teams = [];
        foreach ($teams as $team) {
            $member_ids = array_filter(array_map('trim', explode(',', $team->members ?? '')));
            if (empty($member_ids)) continue;

            $members_data = [];
            $log_found    = 0;
            $missing_log  = 0;

            foreach ($member_ids as $uid) {
                $uid   = (int)$uid;
                if (!$uid) continue;

                $name  = $user_map[$uid]  ?? "User #$uid";
                $hours = $ts_map[$uid]    ?? 0;
                $leave = $leave_map[$uid] ?? 0;

                $util_pct = ($expected_hours > 0 && $hours > 0)
                            ? round(($hours / $expected_hours) * 100, 2)
                            : 0;

                $has_log = ($hours > 0) ? 1 : 0;
                $comment = '';
                if ($is_working_day) {
                    if ($hours == 0 && $leave > 0)    $comment = 'missing log + leave';
                    elseif ($hours == 0)              $comment = 'missing log';
                    elseif ($hours > 0 && $leave > 0) $comment = 'on leave';
                }

                $members_data[] = [
                    'user_id'  => $uid,
                    'name'     => $name,
                    'hours'    => $hours,
                    'util_pct' => $util_pct,
                    'leave'    => $leave,
                    'has_log'  => $has_log,
                    'comment'  => $comment,
                ];

                if ($has_log) $log_found++;
                else          $missing_log++;
            }

            $count     = count($members_data);
            $team_perf = ($count > 0)
                         ? round(array_sum(array_column($members_data, 'util_pct')) / $count, 2)
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

            // Count distinct calendar days where ANY member logged work this month
            // Use DATE(start_time) directly — local time stored in DB
            $sql = "SELECT COUNT(DISTINCT DATE($timesheet_table.start_time)) AS day_count
                    FROM $timesheet_table
                    WHERE $timesheet_table.deleted=0
                      AND $timesheet_table.status != 'open'
                      AND $timesheet_table.user_id IN ($ids_str)
                      AND DATE($timesheet_table.start_time) BETWEEN '$month_start' AND '$month_end'";

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
