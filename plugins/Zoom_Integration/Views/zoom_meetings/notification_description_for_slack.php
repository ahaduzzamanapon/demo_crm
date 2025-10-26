<?php

if ($notification->plugin_zoom_meeting_id) {
    $Zoom_meetings_model = new \Zoom_Integration\Models\Zoom_meetings_model();
    $meeting_info = $Zoom_meetings_model->get_one($notification->plugin_zoom_meeting_id);
    echo "\n*" . app_lang("zoom_integration_topic") . ":* " . $meeting_info->title;
    echo "\n*" . app_lang("zoom_integration_meeting_time") . ":* " . format_to_datetime($meeting_info->start_time);
}
