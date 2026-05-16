<div id="page-content" class="page-wrapper clearfix">


    <div class="card">
        <div class="page-title clearfix">
            <h1><?php echo app_lang('custom_reports'); ?></h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h4><?php echo app_lang('total_projects'); ?></h4>
                            <h2><?php echo $total_projects; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h4><?php echo app_lang('total_tasks'); ?></h4>
                            <h2><?php echo $total_tasks; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h4><?php echo app_lang('total_time_logged'); ?></h4>
                            <h2><?php echo $total_time_logged; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="reports-filter-form" method="get"
                                action="<?php echo get_uri('custom_reports'); ?>">
                                <div class="row g-3 align-items-end">

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="project_id"
                                                class="form-label"><?php echo app_lang('project'); ?></label>
                                            <?php
                                            echo form_dropdown("project_id", $projects_dropdown, $project_id, "class='select2 form-control select2' id='project_id'");
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="member_id"
                                                class="form-label"><?php echo app_lang('member'); ?></label>
                                            <?php
                                            echo form_dropdown("member_id", $members_dropdown, $member_id, "class='select2 form-control select2' id='member_id'");
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="task_id"
                                                class="form-label"><?php echo app_lang('task'); ?></label>
                                            <?php
                                            echo form_dropdown("task_id", $tasks_dropdown, $task_id, "class='select2 form-control select2' id='task_id'");
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 align-items-end">

                                    <div class="col-md-3">
                                        <input type="date" name="start_date" class="form-control"
                                            placeholder="<?php echo app_lang('start_date'); ?>"
                                            value="<?php echo $start_date; ?>" />
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" name="end_date" class="form-control"
                                            placeholder="<?php echo app_lang('end_date'); ?>"
                                            value="<?php echo $end_date; ?>" />
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                            <?php echo anchor(get_uri("custom_reports"), "<i data-feather='x' class='icon-16'></i> " . app_lang('clear'), array("class" => "btn btn-default")); ?>
                                            <button type="button" class="btn btn-default" onclick="printReport()"> <i
                                                    data-feather="printer" class="icon-16"></i>
                                                <?php echo app_lang('print'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" href="#project-report" data-bs-toggle="tab"><i
                            data-feather="list" class="icon-16"></i> <?php echo app_lang('project_report'); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="#time-tracking-report" data-bs-toggle="tab"><i
                            data-feather="clock" class="icon-16"></i>
                        <?php echo app_lang('time_tracking_report'); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="#user-time-log-report" data-bs-toggle="tab"><i
                            data-feather="user" class="icon-16"></i>
                        <?php echo app_lang('per_user_time_log_report'); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="#resource-utilization-report" data-bs-toggle="tab"><i
                            data-feather="pie-chart" class="icon-16"></i>
                        <?php echo app_lang('resource_utilization_report'); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="#team-wise-report" data-bs-toggle="tab"><i
                            data-feather="users" class="icon-16"></i> Team Wise Report</a></li>
                <li class="nav-item"><a class="nav-link" href="#effort-report" data-bs-toggle="tab"><i
                            data-feather="bar-chart-2" class="icon-16"></i> Project Wise Effort</a></li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade show active" id="project-report">
                    <div class="table-responsive">
                        <table id="project-report-table" class="display table table-striped table-hover" cellspacing="0"
                            width="100%">
                            <thead>
                                <tr>
                                    <th><?php echo app_lang('sl'); ?></th>
                                    <th><?php echo app_lang('project_name'); ?></th>
                                    <th><?php echo app_lang('total_tasks'); ?></th>
                                    <?php foreach ($task_statuses as $status) { ?>
                                        <th><?php echo $status->title; ?></th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($project_report_data as $key => $project) { ?>
                                    <tr>
                                        <td><?php echo $key + 1; ?></td>
                                        <td><?php echo $project->project_name; ?></td>
                                        <td><?php echo $project->total_tasks; ?></td>
                                        <?php foreach ($task_statuses as $status) {
                                            $status_count_property = "status_" . $status->id . "_count";
                                            ?>
                                            <td><?php echo $project->$status_count_property; ?></td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="time-tracking-report">
                    <div class="table-responsive">
                        <table id="time-tracking-report-table" class="display table table-striped table-hover"
                            cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th><?php echo app_lang('sl'); ?></th>
                                    <th><?php echo app_lang('assignee'); ?></th>
                                    <th><?php echo app_lang('project'); ?></th>
                                    <th><?php echo app_lang('estimated_hr'); ?></th>
                                    <th><?php echo app_lang('time_spent_hr'); ?></th>
                                    <th><?php echo app_lang('remaining_hr'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $assignee_project_counts = array();
                                foreach ($time_tracking_report_data as $item) {
                                    if (!isset($assignee_project_counts[$item->assignee_name])) {
                                        $assignee_project_counts[$item->assignee_name] = 0;
                                    }
                                    $assignee_project_counts[$item->assignee_name]++;
                                }

                                $current_assignee = "";
                                $sl = 0;
                                foreach ($time_tracking_report_data as $item) {
                                    $estimated_hr = $item->total_estimated_hr ? $item->total_estimated_hr : 0;
                                    $spent_seconds = $item->total_spent_seconds ? $item->total_spent_seconds : 0;
                                    $spent_hr = $spent_seconds / 3600;
                                    $remaining_hr = $estimated_hr - $spent_hr;
                                    ?>
                                    <tr>
                                        <?php if ($current_assignee != $item->assignee_name) {
                                            $sl++;
                                            ?>
                                            <td rowspan="<?php echo $assignee_project_counts[$item->assignee_name]; ?>">
                                                <?php echo $sl; ?>
                                            </td>
                                            <td rowspan="<?php echo $assignee_project_counts[$item->assignee_name]; ?>">
                                                <?php echo $item->assignee_name; ?>
                                            </td>
                                            <?php $current_assignee = $item->assignee_name;
                                        } ?>
                                        <td><?php echo $item->project_name; ?></td>
                                        <td><?php echo number_format($estimated_hr, 2); ?></td>
                                        <td><?php echo number_format($spent_hr, 2); ?></td>
                                        <td><?php echo number_format($remaining_hr, 2); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="user-time-log-report">
                    <?php
                    // ========== Group data: Member -> Project -> Tasks ==========
                    $employeeData = [];
                    foreach ($user_time_log_report_data as $item) {
                        $member = $item->member_name;
                        $project = $item->project_name;
                        $task_name = $item->task_name ?: '-';
                        $estimated_hr = $item->task_estimated_time ? $item->task_estimated_time : 0;
                        $spent_seconds = $item->spent_seconds ? $item->spent_seconds : 0;

                        if (!isset($employeeData[$member])) {
                            $employeeData[$member] = [];
                        }
                        if (!isset($employeeData[$member][$project])) {
                            $employeeData[$member][$project] = [];
                        }
                        if (!isset($employeeData[$member][$project][$task_name])) {
                            $employeeData[$member][$project][$task_name] = [
                                'estimated_hr' => $estimated_hr,
                                'total_spent_seconds' => 0,
                                'logs' => []
                            ];
                        }
                        $employeeData[$member][$project][$task_name]['total_spent_seconds'] += $spent_seconds;
                        $employeeData[$member][$project][$task_name]['logs'][] = [
                            'datetime' => format_to_datetime($item->work_start_time) . " to " . format_to_datetime($item->work_end_time),
                            'spent_seconds' => $spent_seconds
                        ];
                    }
                    ?>

                    <style>
                        .per-user-card {
                            margin-bottom: 24px;
                            border: 1px solid #dee2e6;
                            border-radius: 8px;
                            overflow: hidden;
                            box-shadow: 0 2px 6px rgba(0,0,0,0.07);
                        }
                        .per-user-card-header {
                            background: linear-gradient(135deg, #3c78d8 0%, #1a4fa0 100%);
                            color: #fff;
                            padding: 12px 18px;
                            display: flex;
                            align-items: center;
                            gap: 10px;
                        }
                        .per-user-card-header .employee-avatar {
                            width: 38px;
                            height: 38px;
                            border-radius: 50%;
                            background: rgba(255,255,255,0.25);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 16px;
                            font-weight: 700;
                            flex-shrink: 0;
                        }
                        .per-user-card-header .employee-name {
                            font-size: 16px;
                            font-weight: 600;
                            margin: 0;
                        }
                        .per-user-card-table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        .per-user-card-table th,
                        .per-user-card-table td {
                            border: 1px solid #dee2e6;
                            padding: 7px 10px;
                            text-align: center;
                            vertical-align: middle;
                            font-size: 13px;
                        }
                        .per-user-card-table thead tr {
                            background: #f0f4ff;
                            font-weight: 600;
                        }
                        .per-user-card-table .project-subtotal td {
                            background: #f8f9fa;
                            font-weight: 600;
                            font-style: italic;
                            color: #495057;
                        }
                        .per-user-card-table .grand-total td {
                            background: #e8f0fe;
                            font-weight: 700;
                            color: #1a4fa0;
                            border-top: 2px solid #3c78d8 !important;
                        }
                        .per-user-card-table .project-name-cell {
                            background: #f8f9fa;
                            font-weight: 600;
                            color: #333;
                        }
                        @media print {
                            .per-user-card { page-break-inside: avoid; }
                        }
                    </style>

                    <div class="mt-3">
                    <?php
                    $empSlNo = 1;
                    foreach ($employeeData as $member_name => $projects) :
                        // Calculate grand totals for this employee
                        $grand_estimated_s = 0;
                        $grand_spent_s = 0;

                        foreach ($projects as $project_name => $tasks) {
                            foreach ($tasks as $task_name => $task) {
                                $grand_estimated_s += $task['estimated_hr'] * 3600;
                                $grand_spent_s += $task['total_spent_seconds'];
                            }
                        }
                        $grand_remaining_s = $grand_estimated_s - $grand_spent_s;

                        // Avatar initials
                        $nameParts = explode(' ', $member_name);
                        $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                    ?>
                    <div class="per-user-card">
                        <!-- Card Header -->
                        <div class="per-user-card-header">
                            <div class="employee-avatar"><?php echo $initials; ?></div>
                            <div>
                                <p class="employee-name"><?php echo $empSlNo . '. ' . $member_name; ?></p>
                            </div>
                        </div>

                        <!-- Card Table -->
                        <div class="table-responsive">
                            <table class="per-user-card-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?php echo app_lang('project'); ?></th>
                                        <th><?php echo app_lang('datetime'); ?></th>
                                        <th><?php echo app_lang('task_name'); ?></th>
                                        <th><?php echo app_lang('estimated_hr'); ?></th>
                                        <th><?php echo app_lang('time_spent_hr'); ?></th>
                                        <th><?php echo app_lang('remaining_hr'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $projNo = 1;
                                foreach ($projects as $project_name => $tasks) :
                                    // Project subtotals
                                    $proj_est_s = 0;
                                    $proj_spent_s = 0;

                                    // Count total log rows in this project (for project rowspan)
                                    $projectLogRowCount = 0;
                                    foreach ($tasks as $task_name => $task) {
                                        $projectLogRowCount += count($task['logs']);
                                        $proj_est_s += $task['estimated_hr'] * 3600;
                                        $proj_spent_s += $task['total_spent_seconds'];
                                    }
                                    $proj_remaining_s = $proj_est_s - $proj_spent_s;

                                    $projectPrinted = false;
                                    $taskNo = 1;

                                    foreach ($tasks as $task_name => $task) :
                                        $est_s = $task['estimated_hr'] * 3600;
                                        $spent_s = $task['total_spent_seconds'];
                                        $remaining_s = $est_s - $spent_s;
                                        $spent_fmt = convert_seconds_to_time_format($spent_s);
                                        $remaining_fmt = convert_seconds_to_time_format($remaining_s);
                                        if ($remaining_s < 0) $remaining_fmt .= '&nbsp;(+)';
                                        $rowCount = count($task['logs']);
                                        $firstLog = true;

                                        foreach ($task['logs'] as $log) :
                                        ?>
                                        <tr>
                                            <?php if (!$projectPrinted) : ?>
                                                <td rowspan="<?php echo $projectLogRowCount; ?>" class="project-name-cell"><?php echo $projNo; ?></td>
                                                <td rowspan="<?php echo $projectLogRowCount; ?>" class="project-name-cell"><?php echo $project_name; ?></td>
                                                <?php $projectPrinted = true; ?>
                                            <?php endif; ?>

                                            <td><?php echo $log['datetime']; ?></td>
                                            <td><?php echo $task_name; ?></td>

                                            <?php if ($firstLog) : ?>
                                                <td rowspan="<?php echo $rowCount; ?>"><?php echo $task['estimated_hr']; ?></td>
                                                <td rowspan="<?php echo $rowCount; ?>"><?php echo $spent_fmt; ?></td>
                                                <td rowspan="<?php echo $rowCount; ?>"><?php echo $remaining_fmt; ?></td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php
                                        $firstLog = false;
                                        endforeach;
                                        $taskNo++;
                                    endforeach;
                                    ?>
                                    <!-- Project Subtotal Row -->
                                    <tr class="project-subtotal">
                                        <td colspan="4" class="text-end">
                                            Total for <strong><?php echo $project_name; ?></strong>:
                                        </td>
                                        <td><?php echo convert_seconds_to_time_format($proj_est_s); ?></td>
                                        <td><?php echo convert_seconds_to_time_format($proj_spent_s); ?></td>
                                        <td><?php echo convert_seconds_to_time_format($proj_remaining_s); ?></td>
                                    </tr>
                                    <?php
                                    $projNo++;
                                endforeach;
                                ?>
                                </tbody>
                                <!-- Grand Total Footer -->
                                <tfoot>
                                    <tr class="grand-total">
                                        <td colspan="4" class="text-end">
                                            🏁 Grand Total for <strong><?php echo $member_name; ?></strong>:
                                        </td>
                                        <td><?php echo convert_seconds_to_time_format($grand_estimated_s); ?></td>
                                        <td><?php echo convert_seconds_to_time_format($grand_spent_s); ?></td>
                                        <td><?php echo convert_seconds_to_time_format($grand_remaining_s); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <?php
                    $empSlNo++;
                    endforeach;
                    ?>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="resource-utilization-report">
                    <div class="table-responsive">
                        <table id="resource-utilization-report-table" class="display table table-striped table-hover"
                            cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th><?php echo app_lang('resource'); ?></th>
                                    <th><?php echo app_lang('designation'); ?></th>
                                    <?php foreach ($date_range as $date) { ?>
                                        <th><?php echo $date->format("d M"); ?></th>
                                    <?php } ?>
                                    <th><?php echo app_lang('leave'); ?></th>
                                    <th><?php echo app_lang('availability'); ?></th>
                                    <th><?php echo app_lang('utilization'); ?></th>
                                    <th><?php echo app_lang('utilization_rate'); ?> (%)</th>
                                    <th><?php echo app_lang('capacity_loss'); ?> (%)</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resource_utilization_data as $data) { ?>
                                    <tr>
                                        <td><?php echo $data['user_name']; ?></td>
                                        <td><?php echo $data['designation']; ?></td>
                                        <?php foreach ($date_range as $date) {
                                            $log_date = $date->format('Y-m-d');
                                            $hours_worked = isset($data['daily_logs'][$log_date]) ? $data['daily_logs'][$log_date] : 0;
                                            ?>
                                            <td><?php echo $hours_worked; ?></td>
                                        <?php } ?>
                                        <td><?php echo $data['total_leave']; ?></td>
                                        <td><?php echo $data['availability']; ?></td>
                                        <td><?php echo $data['utilization']; ?></td>
                                        <td><?php echo $data['utilization_rate']; ?></td>
                                        <td><?php echo $data['capacity_loss']; ?></td>

                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- =================== TEAM WISE REPORT TAB =================== -->
                <div role="tabpanel" class="tab-pane fade" id="team-wise-report">

                    <style>
                        .team-report-card {
                            margin-bottom: 20px;
                            border: 1px solid #dee2e6;
                            border-radius: 10px;
                            overflow: hidden;
                            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
                        }
                        .team-report-header {
                            background: linear-gradient(135deg, #1a4fa0 0%, #2563eb 100%);
                            color: #fff;
                            padding: 12px 18px;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            cursor: pointer;
                        }
                        .team-report-header .team-title {
                            font-size: 15px;
                            font-weight: 700;
                            display: flex;
                            align-items: center;
                            gap: 10px;
                        }
                        .team-report-header .team-badges {
                            display: flex;
                            gap: 8px;
                            flex-wrap: wrap;
                        }
                        .team-badge {
                            background: rgba(255,255,255,0.18);
                            border: 1px solid rgba(255,255,255,0.35);
                            border-radius: 20px;
                            padding: 2px 12px;
                            font-size: 12px;
                            font-weight: 600;
                            white-space: nowrap;
                        }
                        .team-badge.badge-created  { background: rgba(34,197,94,0.25); border-color: rgba(34,197,94,0.5); }
                        .team-badge.badge-updated  { background: rgba(234,179,8,0.25);  border-color: rgba(234,179,8,0.5); }
                        .team-badge.badge-logs     { background: rgba(239,68,68,0.25);  border-color: rgba(239,68,68,0.5); }
                        .team-report-table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        .team-report-table th,
                        .team-report-table td {
                            border: 1px solid #e2e8f0;
                            padding: 8px 12px;
                            text-align: center;
                            font-size: 13px;
                            vertical-align: middle;
                        }
                        .team-report-table th {
                            background: #f0f4ff;
                            font-weight: 600;
                            color: #374151;
                        }
                        .team-report-table td:first-child,
                        .team-report-table th:first-child { text-align: left; }
                        .team-report-table .member-name-cell { font-weight: 500; color: #1e293b; }
                        .team-report-table .total-row td {
                            background: #dbeafe;
                            font-weight: 700;
                            color: #1e3a8a;
                            border-top: 2px solid #2563eb;
                        }
                        .badge-num {
                            display: inline-block;
                            min-width: 28px;
                            background: #2563eb;
                            color: #fff;
                            border-radius: 12px;
                            padding: 1px 7px;
                            font-size: 12px;
                            font-weight: 700;
                        }
                        .badge-num.green  { background: #16a34a; }
                        .badge-num.yellow { background: #ca8a04; }
                        .badge-num.red    { background: #dc2626; }
                        @media print { .team-report-card { page-break-inside: avoid; } }
                    </style>

                    <div class="mt-3">
                        <p class="text-muted mb-3" style="font-size:13px;">
                            <i data-feather="calendar" class="icon-14"></i>
                            Report Period: <strong><?php echo $team_report_date_label; ?></strong>
                            &nbsp;|&nbsp; Showing tasks <strong>created</strong>, <strong>updated</strong>, or with <strong>activity logs</strong> within the selected date range.
                        </p>

                    <?php if (empty($team_wise_report)): ?>
                        <div class="alert alert-info">No teams found.</div>
                    <?php else: ?>

                    <?php foreach ($team_wise_report as $team): ?>
                    <div class="team-report-card">
                        <div class="team-report-header" data-bs-toggle="collapse" data-bs-target="#team-collapse-<?php echo $team['team_id']; ?>">
                            <div class="team-title">
                                <i data-feather="users" class="icon-16"></i>
                                <?php echo htmlspecialchars($team['team_name']); ?>
                                <small style="font-weight:400; opacity:.8;">(<?php echo count($team['projects']); ?> projects)</small>
                            </div>
                            <div class="team-badges">
                                <span class="team-badge">Total: <?php echo $team['overall_tasks']; ?> / <?php echo $team['total_tasks']; ?></span>
                                <?php foreach ($team_task_statuses as $ts): ?>
                                    <span class="team-badge"><?php echo htmlspecialchars($ts->title); ?>: <?php echo ($team['overall_status_totals'][$ts->id] ?? 0) . ' / ' . ($team['status_totals'][$ts->id] ?? 0); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div id="team-collapse-<?php echo $team['team_id']; ?>" class="collapse show">
                            <div class="table-responsive">
                                <table class="team-report-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Project Name</th>
                                            <th>Total Tasks <small style="font-weight:400;display:block;opacity:.75;">(Overall / Active)</small></th>
                                            <?php foreach ($team_task_statuses as $ts): ?>
                                                <th style="min-width:90px;">
                                                    <?php echo htmlspecialchars($ts->title); ?>
                                                    <small style="font-weight:400;display:block;opacity:.75;">(Overall / Active)</small>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($team['projects'])): ?>
                                        <tr><td colspan="<?php echo 3 + count($team_task_statuses); ?>" class="text-center text-muted">No projects found for this team.</td></tr>
                                    <?php else: ?>
                                    <?php $pSlNo = 1; foreach ($team['projects'] as $proj): ?>
                                        <tr>
                                            <td><?php echo $pSlNo++; ?></td>
                                            <td class="member-name-cell"><?php echo htmlspecialchars($proj->project_name); ?></td>
                                            <td class="text-center">
                                                <span class="badge-num" style="cursor:pointer;" data-act="ajax-modal" data-action-url="<?php echo get_uri('custom_reports/team_wise_tasks_modal'); ?>" data-post-team_id="<?php echo $team['team_id']; ?>" data-post-project_id="<?php echo $proj->project_id; ?>" data-post-status_id="" data-post-start_date="<?php echo $start_date; ?>" data-post-end_date="<?php echo $end_date; ?>" data-post-type="overall" title="Overall tasks in project (team members)"><?php echo $proj->overall_tasks; ?></span>
                                                <br>
                                                <span class="badge-num" style="cursor:pointer; background:#0ea5e9;" data-act="ajax-modal" data-action-url="<?php echo get_uri('custom_reports/team_wise_tasks_modal'); ?>" data-post-team_id="<?php echo $team['team_id']; ?>" data-post-project_id="<?php echo $proj->project_id; ?>" data-post-status_id="" data-post-start_date="<?php echo $start_date; ?>" data-post-end_date="<?php echo $end_date; ?>" data-post-type="active" title="Active in date range"><?php echo $proj->total_tasks; ?></span>
                                            </td>
                                            <?php foreach ($team_task_statuses as $ts):
                                                $col = "status_{$ts->id}_count";
                                                $overall_col = "overall_status_{$ts->id}_count"; ?>
                                                <td class="text-center">
                                                    <span class="badge-num" style="cursor:pointer;" data-act="ajax-modal" data-action-url="<?php echo get_uri('custom_reports/team_wise_tasks_modal'); ?>" data-post-team_id="<?php echo $team['team_id']; ?>" data-post-project_id="<?php echo $proj->project_id; ?>" data-post-status_id="<?php echo $ts->id; ?>" data-post-start_date="<?php echo $start_date; ?>" data-post-end_date="<?php echo $end_date; ?>" data-post-type="overall" title="Overall tasks (team members)"><?php echo $proj->$overall_col ?? 0; ?></span>
                                                    <br>
                                                    <span class="badge-num" style="cursor:pointer; background:<?php echo $ts->color ?: '#2563eb'; ?>;" data-act="ajax-modal" data-action-url="<?php echo get_uri('custom_reports/team_wise_tasks_modal'); ?>" data-post-team_id="<?php echo $team['team_id']; ?>" data-post-project_id="<?php echo $proj->project_id; ?>" data-post-status_id="<?php echo $ts->id; ?>" data-post-start_date="<?php echo $start_date; ?>" data-post-end_date="<?php echo $end_date; ?>" data-post-type="active" title="Active in date range"><?php echo $proj->$col ?? 0; ?></span>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="total-row">
                                            <td colspan="2" style="text-align:right;">🏁 Team Total:</td>
                                            <td>
                                                <span style="cursor:pointer; color:#1e3a8a; text-decoration:underline;" data-act="ajax-modal" data-action-url="<?php echo get_uri('custom_reports/team_wise_tasks_modal'); ?>" data-post-team_id="<?php echo $team['team_id']; ?>" data-post-project_id="" data-post-status_id="" data-post-start_date="<?php echo $start_date; ?>" data-post-end_date="<?php echo $end_date; ?>" data-post-type="overall"><?php echo $team['overall_tasks']; ?></span> / 
                                                <span style="cursor:pointer; color:#1e3a8a; text-decoration:underline;" data-act="ajax-modal" data-action-url="<?php echo get_uri('custom_reports/team_wise_tasks_modal'); ?>" data-post-team_id="<?php echo $team['team_id']; ?>" data-post-project_id="" data-post-status_id="" data-post-start_date="<?php echo $start_date; ?>" data-post-end_date="<?php echo $end_date; ?>" data-post-type="active"><?php echo $team['total_tasks']; ?></span>
                                            </td>
                                            <?php foreach ($team_task_statuses as $ts):
                                                $bg = $ts->color ?: '#2563eb';
                                            ?>
                                                <td>
                                                    <span style="cursor:pointer; color:#1e3a8a; text-decoration:underline;" data-act="ajax-modal" data-action-url="<?php echo get_uri('custom_reports/team_wise_tasks_modal'); ?>" data-post-team_id="<?php echo $team['team_id']; ?>" data-post-project_id="" data-post-status_id="<?php echo $ts->id; ?>" data-post-start_date="<?php echo $start_date; ?>" data-post-end_date="<?php echo $end_date; ?>" data-post-type="overall"><?php echo ($team['overall_status_totals'][$ts->id] ?? 0); ?></span> / 
                                                    <span style="cursor:pointer; color:#1e3a8a; text-decoration:underline;" data-act="ajax-modal" data-action-url="<?php echo get_uri('custom_reports/team_wise_tasks_modal'); ?>" data-post-team_id="<?php echo $team['team_id']; ?>" data-post-project_id="" data-post-status_id="<?php echo $ts->id; ?>" data-post-start_date="<?php echo $start_date; ?>" data-post-end_date="<?php echo $end_date; ?>" data-post-type="active"><?php echo ($team['status_totals'][$ts->id] ?? 0); ?></span>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </div>
                </div>

                <!-- =================== PROJECT WISE EFFORT REPORT TAB =================== -->
                <div role="tabpanel" class="tab-pane fade" id="effort-report">
                    <style>
                        .effort-table-wrap { overflow-x: auto; }
                        .effort-table-wrap { overflow-x: auto; overflow-y: auto; max-height: calc(100vh - 200px); position: relative; cursor: grab; user-select: none; }
                        .effort-table {
                            border-collapse: separate;
                            border-spacing: 0;
                            font-size: 12px;
                            white-space: nowrap;
                        }
                        .effort-table th, .effort-table td {
                            border-right: 1px solid #cbd5e1;
                            border-bottom: 1px solid #cbd5e1;
                            padding: 5px 8px;
                            text-align: center;
                            vertical-align: middle;
                        }
                        .effort-table thead tr:first-child th { border-top: 1px solid #cbd5e1; }
                        .effort-table th:first-child, .effort-table td:first-child { border-left: 1px solid #cbd5e1; }
                        .effort-table th { background: #1e3a8a; color:#fff; font-weight:600; }
                        .effort-table .th-sub { background: #2563eb; color:#fff; }
                        .effort-table .neg     { color: #dc2626; font-weight:600; }
                        .effort-table .pos     { color: #16a34a; font-weight:600; }
                        .effort-table .total-row td { background:#dbeafe; font-weight:700; color:#1e3a8a; border-top:2px solid #2563eb; }
                        .effort-table .util-row td  { background:#fef9c3; font-weight:600; color:#92400e; }
                        .effort-table .member-col   { background:#f0f4ff; }
                        /* Sticky column styles */
                        .effort-table .sc { position: sticky; z-index: 2; }
                        /* Sticky header rows (Y-axis) - sticks within the scrollable wrap */
                        .effort-table thead tr:nth-child(1) th { position: sticky; top: 0;    z-index: 4; }
                        .effort-table thead tr:nth-child(2) th { position: sticky; top: 34px; z-index: 4; }
                        /* Non-sticky member name headers stay below fixed columns */
                        .effort-table thead tr:nth-child(1) th:not(.sc) { z-index: 1; }
                        .effort-table thead tr:nth-child(2) th:not(.sc) { z-index: 1; }
                        /* Corner cells: both X and Y sticky — must be above member name headers */
                        .effort-table thead .sc { z-index: 100; }
                        .effort-table .sc-1  { left: 0;     min-width: 32px;  background: #1e3a8a; }
                        .effort-table .sc-2  { left: 32px;  min-width: 170px; background: #1e3a8a; text-align: left; white-space: normal; word-break: break-word; }
                        .effort-table .sc-3  { left: 202px; min-width: 44px;  background: #1e3a8a; }
                        .effort-table .sc-4  { left: 246px; min-width: 88px;  background: #1e3a8a; }
                        .effort-table .sc-eff{ left: 334px; min-width: 252px; background: #1e3a8a; }
                        .effort-table .sc-5  { left: 334px; min-width: 84px;  background: #2563eb; }
                        .effort-table .sc-6  { left: 418px; min-width: 84px;  background: #2563eb; }
                        .effort-table .sc-7  { left: 502px; min-width: 84px;  background: #2563eb; }
                        /* Data/footer rows sticky cells get white-ish bg */
                        .effort-table tbody td.sc, .effort-table tfoot td.sc {
                            background: #fff;
                        }
                        .effort-table .total-row td.sc { background: #dbeafe; }
                        .effort-table .util-row  td.sc { background: #fef9c3; }
                        .effort-table .sc-shadow { box-shadow: 3px 0 6px -2px rgba(0,0,0,0.15); }
                        /* Zebra striping */
                        .effort-table tbody tr:nth-child(odd)  td { background: #f8faff; }
                        .effort-table tbody tr:nth-child(even) td { background: #eef3ff; }
                        .effort-table tbody tr:nth-child(odd)  td.sc { background: #f8faff; }
                        .effort-table tbody tr:nth-child(even) td.sc { background: #eef3ff; }
                        .effort-table tbody tr:nth-child(odd)  td.member-col { background: #f0f4ff; }
                        .effort-table tbody tr:nth-child(even) td.member-col { background: #e6edff; }
                        /* Selected row highlight */
                        .effort-table tbody tr.row-selected td { background: #fef08a !important; }
                        .effort-table tbody tr.row-selected td.sc { background: #fef08a !important; }
                        .effort-table tbody tr { cursor: pointer; }
                        .effort-table tbody tr:hover td { filter: brightness(0.96); }
                        .effort-title {
                            text-align:center;
                            color:#dc2626;
                            font-weight:700;
                            font-size:14px;
                            margin-bottom:4px;
                        }
                        .effort-subtitle {
                            text-align:center;
                            color:#dc2626;
                            font-size:12px;
                            margin-bottom:12px;
                        }
                        @media print {
                            .effort-table-wrap { overflow: visible; }
                            .effort-table { font-size:10px; border-collapse: collapse; }
                            .effort-table .sc { position: static; }
                        }
                    </style>

                    <?php
                    // Pre-compute totals
                    $eff_total_est       = 0;
                    $eff_total_preceding = 0;
                    $eff_total_current   = 0;
                    $eff_member_totals   = []; // user_id => total hours
                    foreach ($effort_staff as $es) { $eff_member_totals[$es->id] = 0; }

                    foreach ($effort_projects as $ep) {
                        $eff_total_est       += (float)$ep->estimated_hours;
                        $eff_total_preceding += (float)$ep->preceding_hours;
                        $eff_total_current   += (float)$ep->current_hours;
                        foreach ($effort_staff as $es) {
                            $mh = $effort_member_hours[$ep->project_id][$es->id] ?? 0;
                            $eff_member_totals[$es->id] += $mh;
                        }
                    }
                    $eff_total_due = $eff_total_est - ($eff_total_preceding + $eff_total_current);
                    $total_capacity = count($effort_staff) * $effort_working_days * 8; // total capacity in hours
                    ?>

                    <div class="mt-3">
                        <div class="effort-title">Project Wise Effort</div>
                        <div class="effort-subtitle"><?php echo $effort_date_label; ?> &nbsp;|&nbsp; Working Days: <strong><?php echo $effort_working_days; ?></strong> &nbsp;|&nbsp; Capacity per member: <strong><?php echo $effort_working_days * 8; ?> hrs</strong></div>

                        <div class="effort-table-wrap" id="effort-table-wrap">
                            <table class="effort-table">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="sc sc-1">#</th>
                                        <th rowspan="2" class="sc sc-2">Project Name</th>
                                        <th rowspan="2" class="sc sc-3">Type</th>
                                        <th rowspan="2" class="sc sc-4">Due Efforts</th>
                                        <th colspan="3" class="sc sc-eff">Efforts</th>
                                        <?php foreach ($effort_staff as $es):
                                            if (($eff_member_totals[$es->id] ?? 0) == 0) continue; ?>
                                            <th rowspan="2"><?php echo htmlspecialchars($es->first_name); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <th class="sc sc-5 th-sub">Estimated</th>
                                        <th class="sc sc-6 th-sub">Preceding</th>
                                        <th class="sc sc-7 th-sub sc-shadow">Current</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $pNo = 1; foreach ($effort_projects as $ep): ?>
                                    <?php
                                        $est  = (float)$ep->estimated_hours;
                                        $prec = (float)$ep->preceding_hours;
                                        $curr = (float)$ep->current_hours;
                                        $due  = $est - ($prec + $curr);
                                        $type_label = $ep->project_type === 'client_project' ? 'C' : 'I';
                                    ?>
                                    <tr>
                                        <td class="sc sc-1"><?php echo $pNo++; ?></td>
                                        <td class="sc sc-2"><?php echo htmlspecialchars($ep->project_name); ?></td>
                                        <td class="sc sc-3"><?php echo $type_label; ?></td>
                                        <td class="sc sc-4 <?php echo $due < 0 ? 'neg' : 'pos'; ?>">
                                            <?php echo $due < 0 ? '(' . number_format(abs($due), 2) . ')' : number_format($due, 2); ?>
                                        </td>
                                        <td class="sc sc-5"><?php echo $est > 0 ? number_format($est, 2) : '-'; ?></td>
                                        <td class="sc sc-6"><?php echo $prec > 0 ? number_format($prec, 2) : '-'; ?></td>
                                        <td class="sc sc-7 sc-shadow"><?php echo $curr > 0 ? number_format($curr, 2) : '-'; ?></td>
                                        <?php foreach ($effort_staff as $es):
                                            if (($eff_member_totals[$es->id] ?? 0) == 0) continue;
                                            $mh = $effort_member_hours[$ep->project_id][$es->id] ?? 0; ?>
                                            <td class="member-col"><?php echo $mh > 0 ? number_format($mh, 2) : '-'; ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <!-- Total Row -->
                                    <tr class="total-row">
                                        <td class="sc sc-1" style="text-align:left;">Total</td>
                                        <td class="sc sc-2" style="text-align:left;"></td>
                                        <td class="sc sc-3"></td>
                                        <td class="sc sc-4 <?php echo $eff_total_due < 0 ? 'neg' : 'pos'; ?>">
                                            <?php echo $eff_total_due < 0 ? '(' . number_format(abs($eff_total_due), 2) . ')' : number_format($eff_total_due, 2); ?>
                                        </td>
                                        <td class="sc sc-5"><?php echo number_format($eff_total_est, 2); ?></td>
                                        <td class="sc sc-6"><?php echo number_format($eff_total_preceding, 2); ?></td>
                                        <td class="sc sc-7 sc-shadow"><?php echo number_format($eff_total_current, 2); ?></td>
                                        <?php foreach ($effort_staff as $es):
                                            if (($eff_member_totals[$es->id] ?? 0) == 0) continue; ?>
                                            <td class="member-col"><?php echo number_format($eff_member_totals[$es->id], 2); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <!-- Utilization Row -->
                                    <tr class="util-row">
                                        <td class="sc sc-1" colspan="1" style="text-align:left;">Util (%)</td>
                                        <td class="sc sc-2" style="text-align:left;"><strong>[<?php echo $effort_working_days * 8; ?> hrs]</strong></td>
                                        <td class="sc sc-3"></td>
                                        <td class="sc sc-4"></td>
                                        <td class="sc sc-5"></td>
                                        <td class="sc sc-6"></td>
                                        <td class="sc sc-7 sc-shadow"><?php echo $total_capacity > 0 ? number_format(($eff_total_current / $total_capacity) * 100, 2) : '-'; ?>%</td>
                                        <?php foreach ($effort_staff as $es):
                                            if (($eff_member_totals[$es->id] ?? 0) == 0) continue;
                                            $cap = $effort_working_days * 8;
                                            $util = $cap > 0 ? ($eff_member_totals[$es->id] / $cap) * 100 : 0;
                                        ?>
                                            <td class="member-col"><?php echo number_format($util, 2); ?>%</td>
                                        <?php endforeach; ?>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Legend -->
                        <div class="mt-3" style="display:inline-block; border:1px solid #cbd5e1; padding:10px 16px; border-radius:6px; font-size:12px;">
                            <strong>Project Type Legend:</strong><br>
                            C &ndash; Client Project<br>
                            I &ndash; Internal Project
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#project-table").appTable({ source: 'data/projects.json' });
        $(".select2").select2();
    });

    function printReport() {
        var reportContainer = document.querySelector('.tab-pane.fade.active.show').innerHTML;
        var originalContent = document.body.innerHTML;
        document.body.innerHTML = `
            <div class="container-fluid" style="margin-top: 20px; margin-bottom: 20px;">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <img src="http://crm.mysoftheaven.com/files/system/_file68f10a6ad331e-site-logo.png" alt="logo" width="200px">
                        <h1>Mysoftheaven BD Ltd.</h1>
                    </div>
                </div>
            </div>
            <hr>
        ` + `
            <div style="margin: 20px;">
        ` + reportContainer + `
            </div>
        `;
        window.print();
        document.body.innerHTML = originalContent;
    }
    // Drag-to-scroll on effort table
    (function() {
        var el = document.getElementById('effort-table-wrap');
        if (!el) return;
        var isDown = false, startX, scrollLeft, moved = false;
        el.addEventListener('mousedown', function(e) {
            isDown = true; moved = false;
            el.style.cursor = 'grabbing';
            startX = e.pageX - el.offsetLeft;
            scrollLeft = el.scrollLeft;
        });
        el.addEventListener('mouseleave', function() { isDown = false; el.style.cursor = 'grab'; });
        el.addEventListener('mouseup',    function() { isDown = false; el.style.cursor = 'grab'; });
        el.addEventListener('mousemove',  function(e) {
            if (!isDown) return;
            e.preventDefault();
            moved = true;
            var x    = e.pageX - el.offsetLeft;
            var walk = (x - startX) * 1.5;
            el.scrollLeft = scrollLeft - walk;
        });
        // Row click-to-select (only if not dragging)
        el.addEventListener('click', function(e) {
            if (moved) return;
            var tr = e.target.closest('tbody tr');
            if (!tr) return;
            var wasSelected = tr.classList.contains('row-selected');
            el.querySelectorAll('tbody tr').forEach(function(r) { r.classList.remove('row-selected'); });
            if (!wasSelected) tr.classList.add('row-selected');
        });
    })();

    // Drag-to-scroll on effort table (already handles both X drag and row click)
    // Navbar offset no longer needed - table scrolls internally within max-height wrap
</script>