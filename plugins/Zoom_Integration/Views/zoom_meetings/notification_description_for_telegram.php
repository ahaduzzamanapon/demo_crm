<?php

if ($notification->plugin_zoom_meeting_id) {
    $Zoom_meetings_model = new \Zoom_Integration\Models\Zoom_meetings_model();
    $meeting_info = $Zoom_meetings_model->get_one($notification->plugin_zoom_meeting_id);
    echo "\n<b>" . app_lang("zoom_integration_topic") . ":</b> " . $meeting_info->title;
    echo "\n<b>" . app_lang("zoom_integration_meeting_time") . ":</b> " . format_to_datetime($meeting_info->start_time);
}
