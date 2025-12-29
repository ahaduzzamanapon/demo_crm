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
                                            <button type="button" class="btn btn-default" onclick="printReport()"> <i data-feather="printer" class="icon-16"></i> <?php echo app_lang('print'); ?></button>
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
                <li class="nav-item"><a class="nav-link" href="#resource-utilization-report" data-bs-toggle="tab"><i data-feather="pie-chart" class="icon-16"></i> <?php echo app_lang('resource_utilization_report'); ?></a></li>
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
                                                <?php echo $sl; ?></td>
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
                    <div class="table-responsive">
                        <style>
                            #user-time-log-report-table,
                            #user-time-log-report-table th,
                            #user-time-log-report-table td {
                                border: 1px solid #000 !important;
                                /* black border */
                            }

                            #user-time-log-report-table {
                                border-collapse: collapse !important;
                                /* ensures borders join cleanly */
                                text-align: center;
                            }

                            #user-time-log-report-table th,
                            #user-time-log-report-table td {
                                vertical-align: middle;
                                padding: 6px 10px;
                            }
                        </style>

                        <style>
                            #user-time-log-report-table {
                                width: 100%;
                                text-align: center;
                            }

                            #user-time-log-report-table th,
                            #user-time-log-report-table td {
                                text-align: center;
                                vertical-align: middle;
                            }
                        </style>

                        <?php
                        $groupedData = [];

                        // Group data by Member + Project
                        foreach ($user_time_log_report_data as $item) {
                            $key = $item->member_name . '_' . $item->project_name;
                            $groupedData[$key][] = $item;
                        }
                        ?>

<table id="user-time-log-report-table" class="table table-striped table-hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th><?php echo app_lang('sl'); ?></th>
            <th><?php echo app_lang('member'); ?></th>
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
        $sl = 1;

        foreach ($groupedData as $groupKey => $items) {

            // Group time spent by task
            $taskSummary = [];
            $userTotals = [
                'estimated_hr_s' => 0,
                'spent_seconds' => 0,
                'remaining_seconds' => 0
            ];

            foreach ($items as $item) {
                $task_name = $item->task_name ?: '-';
                $estimated_hr = $item->task_estimated_time ? $item->task_estimated_time : 0;
                $spent_seconds = $item->spent_seconds ? $item->spent_seconds : 0;

                if (!isset($taskSummary[$task_name])) {
                    $taskSummary[$task_name] = [
                        'member_name' => $item->member_name,
                        'project_name' => $item->project_name,
                        'estimated_hr' => $estimated_hr,
                        'total_spent_seconds' => 0,
                        'logs' => []
                    ];
                }

                // Accumulate spent time
                $taskSummary[$task_name]['total_spent_seconds'] += $spent_seconds;

                // Keep logs for display
                $taskSummary[$task_name]['logs'][] = [
                    'datetime' => format_to_datetime($item->work_start_time) . " to " . format_to_datetime($item->work_end_time),
                    'spent_seconds' => $spent_seconds
                ];
            }

            // Calculate total rows for this user (for rowspan)
            $memberRowCount = 0;
            foreach ($taskSummary as $task) {
                $memberRowCount += count($task['logs']);
            }

            $printedMember = false;

            // Output each task
            foreach ($taskSummary as $task_name => $task) {
                $estimated_hr_s = $task['estimated_hr'] * 3600;
                $remaining_seconds = $estimated_hr_s - $task['total_spent_seconds'];

                // Add to per-user totals
                $userTotals['estimated_hr_s'] += $estimated_hr_s;
                $userTotals['spent_seconds'] += $task['total_spent_seconds'];
                $userTotals['remaining_seconds'] += $remaining_seconds;

                $remaining_hr = convert_seconds_to_time_format($remaining_seconds);
                $spent_hr = convert_seconds_to_time_format($task['total_spent_seconds']);

                if ($remaining_seconds < 0) {
                    $remaining_hr .= '&nbsp;(+)';
                }

                $rowCount = count($task['logs']);
                $firstRow = true;

                foreach ($task['logs'] as $log) { ?>
                    <tr>
                        <?php if (!$printedMember) { ?>
                            <td rowspan="<?php echo $memberRowCount; ?>"><?php echo $sl; ?></td>
                            <td rowspan="<?php echo $memberRowCount; ?>"><?php echo $task['member_name']; ?></td>
                            <?php $printedMember = true; ?>
                        <?php } ?>

                        <?php if ($firstRow) { ?>
                            <td rowspan="<?php echo $rowCount; ?>"><?php echo $task['project_name']; ?></td>
                        <?php } ?>

                        <td><?php echo $log['datetime']; ?></td>
                        <td><?php echo $task_name; ?></td>

                        <?php if ($firstRow) { ?>
                            <td rowspan="<?php echo $rowCount; ?>"><?php echo $task['estimated_hr']; ?></td>
                            <td rowspan="<?php echo $rowCount; ?>"><?php echo $spent_hr; ?></td>
                            <td rowspan="<?php echo $rowCount; ?>"><?php echo $remaining_hr; ?></td>
                        <?php } ?>
                    </tr>
                <?php
                    $firstRow = false;
                }
            }

            // --- Per-user total row ---
            $total_estimated_hr = convert_seconds_to_time_format($userTotals['estimated_hr_s']);
            $total_spent_hr = convert_seconds_to_time_format($userTotals['spent_seconds']);
            $total_remaining_hr = convert_seconds_to_time_format($userTotals['remaining_seconds']);
            ?>
            <tr style="font-weight:bold; background:#f2f2f2;">
                <td colspan="5" class="text-end">Total for <?php echo $task['project_name']; ?>:</td>
                <td><?php echo $total_estimated_hr; ?></td>
                <td><?php echo $total_spent_hr; ?></td>
                <td><?php echo $total_remaining_hr; ?></td>
            </tr>
            <?php
            $sl++;
        }
        ?>
    </tbody>
</table>




                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="resource-utilization-report">
                    <div class="table-responsive">
                        <table id="resource-utilization-report-table" class="display table table-striped table-hover" cellspacing="0" width="100%">
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
                                            <td><?php echo round($hours_worked, 2); ?></td>
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
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#project-table").appTable({source: 'data/projects.json'});
        $(".select2").select2();
    });

    function printReport() {
        var reportContainer = document.querySelector('.tab-pane.fade.active.show').innerHTML;
        var originalContent = document.body.innerHTML;
        document.body.innerHTML = `
            <div class="container-fluid" style="margin-top: 20px; margin-bottom: 20px;">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <img src="http://demo-crm.mysoftheaven.com/files/system/_file68f10a6ad331e-site-logo.png" alt="logo" width="200px">
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
</script>

