<?php
if ($model_info->estimated_time > 0) {
    $log_time = modal_anchor(get_uri("projects/stop_timer_modal_form/" . $project_id), "<i data-feather='clock' class='icon-16 mr5'></i> " . app_lang('log_time'), array("class" => "btn btn-primary", "title" => app_lang('log_time'), "data-post-task_id" => $model_info->id));
    echo $log_time;
} else {
    echo "<button type='button' class='btn btn-primary' onclick='alert(\"Please add an estimated time to log time. Please edit the task.\")'><i data-feather='clock' class='icon-16 mr5'></i> " . app_lang('log_time') . "</button>";
}
?>