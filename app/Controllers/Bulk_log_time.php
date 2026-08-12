<?php

namespace App\Controllers;

class Bulk_log_time extends Security_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->access_only_team_members();

        $this->Tasks_model = model('App\Models\Tasks_model');
        $this->Task_status_model = model('App\Models\Task_status_model');
        $this->Timesheets_model = model('App\Models\Timesheets_model');
        $this->Projects_model = model('App\Models\Projects_model');
    }

    public function index()
    {
        $view_data["projects_dropdown"] = $this->_get_projects_dropdown();
        return $this->template->rander("bulk_log_time/index", $view_data);
    }

    public function get_project_tasks($project_id = 0)
    {
        validate_numeric_value($project_id);
        if (!$project_id) {
            return $this->response->setJSON(array("success" => false, "tasks" => array()));
        }

        // Find the Done status ID
        $done_status = $this->Task_status_model->get_one_where(array("key_name" => "done", "deleted" => 0));
        $done_status_id = $done_status->id ? $done_status->id : 3;

        // Fetch tasks for the project excluding Done
        $options = array(
            "project_id" => $project_id,
            "exclude_status_id" => $done_status_id
        );

        // If the user can only see assigned tasks, restrict to them
        $show_assigned_tasks_only_user_id = $this->show_assigned_tasks_only_user_id();
        if (!$show_assigned_tasks_only_user_id) {
            $timesheet_manage_permission = get_array_value($this->login_user->permissions, "timesheet_manage_permission");
            if (!$timesheet_manage_permission || $timesheet_manage_permission === "own") {
                $show_assigned_tasks_only_user_id = $this->login_user->id;
            }
        }
        if ($show_assigned_tasks_only_user_id) {
            $options["show_assigned_tasks_only_user_id"] = $show_assigned_tasks_only_user_id;
        }

        $tasks = $this->Tasks_model->get_details($options)->getResult();

        $tasks_list = array();
        foreach ($tasks as $task) {
            $tasks_list[] = array(
                "id" => $task->id,
                "text" => $task->id . " - " . $task->title
            );
        }

        return $this->response->setJSON(array("success" => true, "tasks" => $tasks_list));
    }

    private function _convert_custom_date_to_database_date($date_str)
    {
        if (!$date_str) {
            return "";
        }
        $system_format = get_setting("date_format");
        $d = \DateTime::createFromFormat($system_format, $date_str);
        if ($d && $d->format($system_format) === $date_str) {
            return $d->format("Y-m-d");
        }
        $timestamp = strtotime($date_str);
        if ($timestamp) {
            return date("Y-m-d", $timestamp);
        }
        return $date_str;
    }

    public function get_logs()
    {
        $selected_date = $this->request->getPost("date");
        if (!$selected_date) {
            return $this->response->setJSON(array("success" => false, "message" => "Date is required"));
        }
        $selected_date = $this->_convert_custom_date_to_database_date($selected_date);

        $options = array(
            "user_id" => $this->login_user->id,
            "start_date" => $selected_date,
            "end_date" => $selected_date
        );

        $logs = $this->Timesheets_model->get_details($options)->getResult();
        $time_format_24_hours = get_setting("time_format") == "24_hours" ? true : false;

        $result_logs = array();
        foreach ($logs as $log) {
            // Convert times from UTC to Local
            $local_start = convert_date_utc_to_local($log->start_time);
            $local_end = convert_date_utc_to_local($log->end_time);

            if ($time_format_24_hours) {
                $start_time = date("H:i", strtotime($local_start));
                $end_time = date("H:i", strtotime($local_end));
            } else {
                $start_time = convert_time_to_12hours_format(date("H:i:s", strtotime($local_start)));
                $end_time = convert_time_to_12hours_format(date("H:i:s", strtotime($local_end)));
            }

            // Get project tasks list (excluding Done, but ensuring current task is included)
            $done_status = $this->Task_status_model->get_one_where(array("key_name" => "done", "deleted" => 0));
            $done_status_id = $done_status->id ? $done_status->id : 3;

            $task_options = array(
                "project_id" => $log->project_id,
                "exclude_status_id" => $done_status_id
            );

            $show_assigned_tasks_only_user_id = $this->show_assigned_tasks_only_user_id();
            if (!$show_assigned_tasks_only_user_id) {
                $timesheet_manage_permission = get_array_value($this->login_user->permissions, "timesheet_manage_permission");
                if (!$timesheet_manage_permission || $timesheet_manage_permission === "own") {
                    $show_assigned_tasks_only_user_id = $this->login_user->id;
                }
            }
            if ($show_assigned_tasks_only_user_id) {
                $task_options["show_assigned_tasks_only_user_id"] = $show_assigned_tasks_only_user_id;
            }

            $tasks = $this->Tasks_model->get_details($task_options)->getResult();

            $has_current_task = false;
            $tasks_list = array();
            foreach ($tasks as $t) {
                $tasks_list[] = array("id" => $t->id, "text" => $t->id . " - " . $t->title);
                if ($t->id == $log->task_id) {
                    $has_current_task = true;
                }
            }

            if ($log->task_id && !$has_current_task) {
                $current_task = $this->Tasks_model->get_one($log->task_id);
                if ($current_task->id) {
                    $tasks_list[] = array("id" => $current_task->id, "text" => $current_task->id . " - " . $current_task->title);
                }
            }

            $result_logs[] = array(
                "id" => $log->id,
                "project_id" => $log->project_id,
                "task_id" => $log->task_id,
                "start_time" => $start_time,
                "end_time" => $end_time,
                "note" => $log->note,
                "tasks_list" => $tasks_list
            );
        }

        return $this->response->setJSON(array("success" => true, "logs" => $result_logs));
    }

    public function save()
    {
        $selected_date = $this->request->getPost("date");
        $logs_data = $this->request->getPost("logs");

        if (!$selected_date) {
            return $this->response->setJSON(array("success" => false, "message" => "Date is required"));
        }
        $selected_date = $this->_convert_custom_date_to_database_date($selected_date);

        // Find existing log IDs for this user on this date
        $options = array(
            "user_id" => $this->login_user->id,
            "start_date" => $selected_date,
            "end_date" => $selected_date
        );
        $existing_logs = $this->Timesheets_model->get_details($options)->getResult();
        $existing_ids = array_column($existing_logs, "id");

        $active_ids = array();
        if (is_array($logs_data)) {
            foreach ($logs_data as $log) {
                $id = get_array_value($log, "id");
                if ($id && in_array($id, $existing_ids)) {
                    $active_ids[] = (int)$id;
                }
            }
        }

        // Delete logs that were removed
        $to_delete = array_diff($existing_ids, $active_ids);
        foreach ($to_delete as $del_id) {
            $this->Timesheets_model->delete($del_id);
        }

        // Insert or Update active logs
        if (is_array($logs_data)) {
            foreach ($logs_data as $log) {
                $id = get_array_value($log, "id") ? (int)get_array_value($log, "id") : 0;
                $project_id = get_array_value($log, "project_id");
                $task_id = get_array_value($log, "task_id") ? (int)get_array_value($log, "task_id") : 0;
                $start_time = get_array_value($log, "start_time");
                $end_time = get_array_value($log, "end_time");
                $note = get_array_value($log, "note");

                if (!$project_id || !$start_time || !$end_time) {
                    continue; // Skip invalid rows
                }

                // Convert local times to 24-hours format if system uses 12-hour format
                if (get_setting("time_format") != "24_hours") {
                    $start_time = convert_time_to_24hours_format($start_time);
                    $end_time = convert_time_to_24hours_format($end_time);
                }

                $start_date_time = convert_date_local_to_utc($selected_date . " " . $start_time);
                $end_date_time = convert_date_local_to_utc($selected_date . " " . $end_time);

                // Calculate hours
                $seconds = strtotime($end_date_time) - strtotime($start_date_time);
                if ($seconds < 0) {
                    // Cross midnight: add 24 hours
                    $seconds += 86400;
                    $end_date_time = date("Y-m-d H:i:s", strtotime($end_date_time) + 86400);
                }
                $hours = round($seconds / 3600, 4);

                $data = array(
                    "project_id" => $project_id,
                    "task_id" => $task_id,
                    "start_time" => $start_date_time,
                    "end_time" => $end_date_time,
                    "note" => $note ? $note : "",
                    "hours" => "",
                    "status" => "logged"
                );

                if (!$id) {
                    $data["user_id"] = $this->login_user->id;
                }

                $this->Timesheets_model->ci_save($data, $id);
            }
        }

        return $this->response->setJSON(array("success" => true, "message" => app_lang("record_saved")));
    }
}
