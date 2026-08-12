<div id="page-content" class="page-wrapper clearfix">
    <div class="card">
        <div class="page-title clearfix" style="border-bottom: 1px solid #e2e8f0; padding: 20px 24px;">
            <h1 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0; display: inline-flex; align-items: center; gap: 8px;">
                <i data-feather="clock" class="icon-20 text-indigo-500" style="color: #6366f1;"></i>
                Bulk Log Time
            </h1>
        </div>
        <div class="card-body" style="padding: 24px;">
            <!-- Date Selection Row -->
            <div class="row mb-4" style="margin-bottom: 24px;">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="bulk_log_date" class="strong" style="font-size: 13px; color: #475569; display: block; margin-bottom: 8px;">Select Date</label>
                        <div style="position: relative;">
                            <input type="text" id="bulk_log_date" name="bulk_log_date" class="form-control" autocomplete="off" placeholder="YYYY-MM-DD" style="border-radius: 8px; border: 1px solid #cbd5e1; padding: 10px 14px; font-size: 13px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State Placeholder -->
            <div id="bulk-log-placeholder" class="text-center" style="padding: 60px 20px; border: 2px dashed #e2e8f0; border-radius: 12px; background: #fafafa; margin-top: 10px;">
                <i data-feather="calendar" style="width: 48px; height: 48px; stroke: #94a3b8; margin-bottom: 16px;"></i>
                <h4 style="font-size: 16px; font-weight: 600; color: #475569; margin: 0 0 6px;">No Date Selected</h4>
                <p style="font-size: 13px; color: #94a3b8; margin: 0;">Please select a date above to load existing logs or start logging time.</p>
            </div>

            <!-- Content Area (Hidden initially) -->
            <div id="bulk-log-content" style="display: none; margin-top: 10px;">
                <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <table class="table bulk-log-table" style="margin-bottom: 0; width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="width: 25%; padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;"><?php echo app_lang('project'); ?> *</th>
                                <th style="width: 25%; padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;"><?php echo app_lang('task'); ?></th>
                                <th style="width: 13%; padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;"><?php echo app_lang('start_time'); ?> *</th>
                                <th style="width: 13%; padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;"><?php echo app_lang('end_time'); ?> *</th>
                                <th style="width: 10%; padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; text-align: center;">Duration</th>
                                <th style="width: 10%; padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;"><?php echo app_lang('note'); ?></th>
                                <th style="width: 4%; padding: 12px 16px; text-align: center;"></th>
                            </tr>
                        </thead>
                        <tbody id="bulk-log-rows">
                            <!-- Populated dynamically -->
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8fafc; border-top: 2px solid #e2e8f0; border-bottom: none;">
                                <td colspan="4" style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #1e293b; text-align: right;">Total Duration:</td>
                                <td id="bulk-log-total-duration" style="padding: 14px 16px; font-size: 14px; font-weight: 800; color: #6366f1; text-align: center;">0h 0m</td>
                                <td colspan="2" style="background: #f8fafc;"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Footer buttons wrapper -->
                <div class="clearfix" style="margin-top: 20px;">
                    <button type="button" class="btn btn-default" id="btn-add-more-row" style="border-radius: 8px; font-size: 13px; font-weight: 600; padding: 8px 16px; border-color: #cbd5e1; color: #475569; background: #fff;">
                        <i data-feather="plus-circle" class="icon-16 mr5" style="margin-right: 5px;"></i> Add More
                    </button>
                    <button type="button" class="btn btn-primary float-end" id="btn-save-bulk-logs" style="border-radius: 8px; font-size: 13px; font-weight: 600; padding: 8px 20px; background: #6366f1; border-color: #6366f1; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);">
                        <i data-feather="check-circle" class="icon-16 mr5" style="margin-right: 5px;"></i> Save All
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bulk-log-row-item td {
        padding: 10px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .bulk-log-row-item:last-child td {
        border-bottom: none;
    }
    .bulk-log-row-item td input {
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 13px !important;
    }
    .bulk-log-row-item td input:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
    }
    .bulk-log-row-item.table-danger td {
        background-color: #fef2f2 !important;
    }
    .bulk-log-row-item.table-danger td input {
        border-color: #fca5a5 !important;
    }
    .select2-container .select2-choice {
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        height: 36px !important;
        line-height: 34px !important;
    }
</style>

<script type="text/javascript">
    $(document).ready(function () {
        feather.replace();

        // Project dropdown database source
        var projectsDropdownData = <?php
            $projects_json = [];
            foreach ($projects_dropdown as $id => $title) {
                if ($id !== "") {
                    $projects_json[] = ["id" => $id, "text" => $title];
                }
            }
            echo json_encode($projects_json);
        ?>;

        setDatePicker("#bulk_log_date", {
            onSelect: function(dateText) {
                loadLogsForDate(dateText);
            },
            onChangeMonthYear: function() {
                // Keep default picker functionality
            }
        });

        // Listen directly to manual change events
        $("#bulk_log_date").on("change", function() {
            var date = $(this).val();
            if (date) {
                loadLogsForDate(date);
            }
        });

        var rowCounter = 0;

        function loadLogsForDate(date) {
            appLoader.show();
            appAjaxRequest({
                url: "<?php echo get_uri('bulk_log_time/get_logs'); ?>",
                type: "POST",
                data: { date: date },
                dataType: "json",
                success: function(result) {
                    appLoader.hide();
                    if (result.success) {
                        $("#bulk-log-placeholder").hide();
                        $("#bulk-log-content").show();
                        $("#bulk-log-rows").html("");
                        rowCounter = 0;

                        if (result.logs && result.logs.length > 0) {
                            result.logs.forEach(function(log) {
                                appendRow(log);
                            });
                        } else {
                            // Add a default blank row if no logs exist
                            appendRow();
                        }
                        calculateGrandTotal();
                    } else {
                        appAlert.error(result.message);
                    }
                },
                error: function() {
                    appLoader.hide();
                    appAlert.error("An error occurred while loading logs.");
                }
            });
        }
        function appendRow(logData) {
            rowCounter++;
            var rowId = "bulk_log_row_" + rowCounter;

            var html = '<tr id="' + rowId + '" class="bulk-log-row-item" data-log-id="' + (logData ? logData.id : '') + '">';
            
            // Project
            html += '<td><select class="form-control project-select select-field" style="width: 100%;">';
            html += '<option value="">- Project -</option>';
            projectsDropdownData.forEach(function(item) {
                var selected = (logData && logData.project_id == item.id) ? ' selected' : '';
                html += '<option value="' + item.id + '"' + selected + '>' + item.text + '</option>';
            });
            html += '</select></td>';
            
            // Task
            html += '<td><select class="form-control task-select select-field" style="width: 100%;">';
            html += '<option value="">- Task -</option>';
            if (logData && logData.tasks_list) {
                logData.tasks_list.forEach(function(item) {
                    var selected = (item.id == logData.task_id) ? ' selected' : '';
                    html += '<option value="' + item.id + '"' + selected + '>' + item.text + '</option>';
                });
            }
            html += '</select></td>';
            
            // Start Time
            var startTimeVal = logData ? logData.start_time : '';
            html += '<td><input type="text" class="form-control start-time-input timepicker" value="' + startTimeVal + '" placeholder="Start Time" autocomplete="off" style="padding: 8px 12px; font-size:13px; height:36px;" /></td>';
            // End Time
            var endTimeVal = logData ? logData.end_time : '';
            html += '<td><input type="text" class="form-control end-time-input timepicker" value="' + endTimeVal + '" placeholder="End Time" autocomplete="off" style="padding: 8px 12px; font-size:13px; height:36px;" /></td>';
            // Duration
            html += '<td class="duration-display font-bold text-center" style="vertical-align: middle; font-size: 13px; color: #475569;">0h 0m</td>';
            // Note
            var noteVal = logData ? logData.note : '';
            html += '<td><input type="text" class="form-control note-input" value="' + noteVal + '" placeholder="Note" style="padding: 8px 12px; font-size:13px; height:36px;" /></td>';
            // Action
            html += '<td class="text-center" style="vertical-align: middle;"><button type="button" class="btn btn-sm btn-link text-danger btn-remove-row" style="padding:4px;" title="Remove"><i data-feather="x" class="icon-16"></i></button></td>';

            html += '</tr>';

            $("#bulk-log-rows").append(html);

            var projectInput = $("#" + rowId + " .project-select");
            var taskInput = $("#" + rowId + " .task-select");

            projectInput.select2();
            taskInput.select2();

            // Bind change handler after load
            projectInput.on("change", function() {
                var projectId = $(this).val();
                taskInput.html('<option value="">- Task -</option>').val("").trigger("change");
                if (projectId) {
                    appAjaxRequest({
                        url: "<?php echo get_uri('bulk_log_time/get_project_tasks'); ?>/" + projectId,
                        dataType: "json",
                        success: function(result) {
                            if (result.success && result.tasks) {
                                result.tasks.forEach(function(task) {
                                    taskInput.append('<option value="' + task.id + '">' + task.text + '</option>');
                                });
                            }
                            taskInput.select2();
                        }
                    });
                } else {
                    taskInput.select2();
                }
            });

            setTimePicker("#" + rowId + " .timepicker");

            // Bind calculation changes
            $("#" + rowId + " .timepicker").on("change", function() {
                calculateRowDuration(rowId);
                calculateGrandTotal();
            });

            if (logData) {
                calculateRowDuration(rowId);
            }

            feather.replace();
        }

        function calculateRowDuration(rowId) {
            var row = $("#" + rowId);
            var startVal = row.find(".start-time-input").val();
            var endVal = row.find(".end-time-input").val();
            var durationCell = row.find(".duration-display");

            if (startVal && endVal) {
                var startMin = timeToMinutes(startVal);
                var endMin = timeToMinutes(endVal);

                var diff = endMin - startMin;
                if (diff < 0) {
                    diff += 1440; // Cross midnight
                }

                durationCell.text(formatMinutes(diff)).data("minutes", diff);
            } else {
                durationCell.text("0h 0m").data("minutes", 0);
            }
        }

        function calculateGrandTotal() {
            var totalMin = 0;
            $(".duration-display").each(function() {
                var min = $(this).data("minutes");
                if (min) {
                    totalMin += parseInt(min, 10);
                }
            });
            $("#bulk-log-total-duration").text(formatMinutes(totalMin));
        }

        function timeToMinutes(timeStr) {
            if (!timeStr) return 0;
            var is12Hour = /am|pm/i.test(timeStr);
            var cleanTime = timeStr.replace(/am|pm/i, '').trim();
            var parts = cleanTime.split(':');
            if (parts.length < 2) return 0;

            var hours = parseInt(parts[0], 10);
            var minutes = parseInt(parts[1], 10);
            if (isNaN(hours) || isNaN(minutes)) return 0;

            if (is12Hour) {
                var isPm = /pm/i.test(timeStr);
                if (isPm && hours < 12) {
                    hours += 12;
                } else if (!isPm && hours === 12) {
                    hours = 0;
                }
            }
            return hours * 60 + minutes;
        }

        function formatMinutes(totalMinutes) {
            if (totalMinutes <= 0) return "0h 0m";
            var hours = Math.floor(totalMinutes / 60);
            var minutes = totalMinutes % 60;
            return hours + "h " + minutes + "m";
        }

        // Add row click handler
        $("#btn-add-more-row").on("click", function() {
            appendRow();
            calculateGrandTotal();
        });

        // Remove row click handler
        $(document).on("click", ".btn-remove-row", function() {
            var row = $(this).closest("tr");
            row.remove();
            calculateGrandTotal();
        });
        // Save All handler
        $("#btn-save-bulk-logs").on("click", function() {
            var logs = [];
            var hasError = false;
            var rows = $(".bulk-log-row-item");

            if (rows.length === 0) {
                appAlert.error("Please add at least one row to save.");
                return;
            }

            rows.each(function() {
                var row = $(this);
                var id = row.data("log-id");
                var projectId = row.find("select.project-select").val();
                var taskId = row.find("select.task-select").val();
                var startTime = row.find("input.start-time-input").val();
                var endTime = row.find("input.end-time-input").val();
                var note = row.find("input.note-input").val();

                if (!projectId || !startTime || !endTime) {
                    hasError = true;
                    row.addClass("table-danger");
                } else {
                    row.removeClass("table-danger");
                }

                logs.push({
                    id: id,
                    project_id: projectId,
                    task_id: taskId,
                    start_time: startTime,
                    end_time: endTime,
                    note: note
                });
            });

            if (hasError) {
                appAlert.error("Please fill in all required fields (Project, Start Time, End Time) marked in red.");
                return;
            }

            var date = $("#bulk_log_date").val();
            if (!date) {
                appAlert.error("Please select a date.");
                return;
            }

            appLoader.show();
            appAjaxRequest({
                url: "<?php echo get_uri('bulk_log_time/save'); ?>",
                type: "POST",
                data: {
                    date: date,
                    logs: logs
                },
                dataType: "json",
                success: function(result) {
                    appLoader.hide();
                    if (result.success) {
                        appAlert.success(result.message, {duration: 5000});
                        loadLogsForDate(date);
                    } else {
                        appAlert.error(result.message);
                    }
                },
                error: function() {
                    appLoader.hide();
                    appAlert.error("An error occurred while saving logs.");
                }
            });
        });
    });
</script>
