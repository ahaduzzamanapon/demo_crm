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
                    <form id="reports-filter-form" method="get" action="<?php echo get_uri('custom_reports'); ?>">
                        <div class="row g-3 align-items-end">

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="project_id"
                                        class="form-label"><?php echo app_lang('project'); ?></label>
                                    <?php
                                    echo form_dropdown("project_id", $projects_dropdown, $project_id, "class='form-control select2' id='project_id'");
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="member_id" class="form-label"><?php echo app_lang('member'); ?></label>
                                    <?php
                                    echo form_dropdown("member_id", $members_dropdown, $member_id, "class='form-control select2' id='member_id'");
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="task_id" class="form-label"><?php echo app_lang('task'); ?></label>
                                    <?php
                                    echo form_dropdown("task_id", $tasks_dropdown, $task_id, "class='form-control select2' id='task_id'");
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-3 d-flex gap-2" style="margin-top: 0;margin-bottom: 18px;">
                                <button type="submit" class="btn btn-primary">
                                    <i data-feather="filter" class="icon-16"></i> <?php echo app_lang('filter'); ?>
                                </button>
                                <a href="<?php echo get_uri('custom_reports'); ?>" class="btn btn-outline-danger">
                                    <i data-feather="x" class="icon-16"></i> <?php echo app_lang('clear'); ?>
                                </a>
                                 <button id="print-report-button" class="btn btn-default"><i data-feather="printer" class="icon-16"></i>
                    <?php echo app_lang('print'); ?></button>
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
                                foreach ($time_tracking_report_data as $item) {
                                    $estimated_hr = $item->total_estimated_hr ? $item->total_estimated_hr : 0;
                                    $spent_seconds = $item->total_spent_seconds ? $item->total_spent_seconds : 0;
                                    $spent_hr = $spent_seconds / 3600;
                                    $remaining_hr = $estimated_hr - $spent_hr;
                                    ?>
                                    <tr>
                                        <?php if ($current_assignee != $item->assignee_name) { ?>
                                            <td rowspan="<?php echo $assignee_project_counts[$item->assignee_name]; ?>">
                                                <?php echo $item->assignee_name; ?>
                                            </td>
                                            <?php $current_assignee = $item->assignee_name;
                                        } ?>
                                        <td><?php echo $item->project_name; ?></td>
                                        <td><?php echo round($estimated_hr, 2); ?></td>
                                        <td><?php echo round($spent_hr, 2); ?></td>
                                        <td><?php echo round($remaining_hr, 2); ?></td>
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

                        <table id="user-time-log-report-table" class="table table-striped table-hover" cellspacing="0"
                            width="100%">
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

                                    $rowCount = count($items); // for rowspan
                                    $firstRow = true;

                                    foreach ($items as $item) {

                                        $estimated_hr = $item->task_estimated_time ? $item->task_estimated_time : 0;
                                        $spent_seconds = $item->spent_seconds ? $item->spent_seconds : 0;
                                        $spent_hr = $spent_seconds / 3600;
                                        $remaining_hr = $estimated_hr - $spent_hr;
                                        ?>
                                        <tr>
                                            <?php if ($firstRow) { ?>
                                                <td rowspan="<?php echo $rowCount; ?>"><?php echo $sl; ?></td>
                                                <td rowspan="<?php echo $rowCount; ?>"><?php echo $item->member_name; ?></td>
                                                <td rowspan="<?php echo $rowCount; ?>"><?php echo $item->project_name; ?></td>
                                            <?php } ?>

                                            <td>

                                            <?php echo format_to_time($item->work_start_time) . " to " . format_to_time($item->work_end_time); ?>
                                                
                                            
                                            </td>
                                            <td><?php echo $item->task_name ? $item->task_name : "-"; ?></td>
                                            <td><?php echo round($estimated_hr, 2); ?></td>
                                            <td><?php echo round($spent_hr, 2); ?></td>
                                            <td><?php echo round($remaining_hr, 2); ?></td>
                                        </tr>
                                        <?php
                                        $firstRow = false;
                                    }
                                    $sl++;
                                } ?>
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
        $('#print-report-button').on('click', function () {
            var activeTabPaneId = $('.nav-tabs .nav-link.active').attr('href');
            var reportTitle = $('.nav-tabs .nav-link.active').text().trim();
            var $tableToPrint = $(activeTabPaneId).find('table');

            var newWin = window.open('', 'Print-Window', 'height=600,width=800');

            if (!newWin || newWin.closed || typeof newWin.closed == 'undefined') {
                alert('Popup blocker is enabled. Please allow popups for this site to print the report.');
                return;
            }

            var document_html = '<html><head><title>' + reportTitle + '</title>';
            document_html += '<style>body { font-family: sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; } </style>';
            document_html += '</head><body><h1>' + reportTitle + '</h1>';
            document_html += $tableToPrint[0].outerHTML;
            document_html += '</body></html>';

            newWin.document.open();
            newWin.document.write(document_html);
            newWin.document.close();

            setTimeout(function () {
                newWin.focus();
                newWin.print();
                newWin.close();
            }, 250);
        });

        $('.select2').select2();

        // $('#project-report-table, #time-tracking-report-table, #user-time-log-report-table').DataTable();
    });
</script>