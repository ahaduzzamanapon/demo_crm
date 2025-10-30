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

            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" href="#project-report" data-bs-toggle="tab"><i data-feather="list" class="icon-16"></i> <?php echo app_lang('project_report'); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="#time-tracking-report" data-bs-toggle="tab"><i data-feather="clock" class="icon-16"></i> <?php echo app_lang('time_tracking_report'); ?></a></li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade show active" id="project-report">
                    <div class="table-responsive">
                        <table id="project-report-table" class="display table table-striped table-hover" cellspacing="0" width="100%">
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
                        <table id="time-tracking-report-table" class="display table table-striped table-hover" cellspacing="0" width="100%">
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
            </div>
        </div>
    </div>
</div>
