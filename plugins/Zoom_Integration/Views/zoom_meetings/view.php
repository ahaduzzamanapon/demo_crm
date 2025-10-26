<style type="text/css">
    #zoom-meeting-content{
        max-width: 700px;
        margin: auto;
    }
</style> 
<div class="box">
    <div class="box-content">
        <div id="zoom-meeting-content" class="page-wrapper clearfix mb20">

            <div class="post-content">
                <div class="post clearfix">

                    <div class="card clearfix mt15">

                        <div class="card-body">
                            <div class="clearfix mb15">
                                <div class="d-flex">
                                    <div class="w-100">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-2">
                                                <span class="avatar avatar-sm">
                                                    <img src="<?php echo get_avatar($model_info->created_by_avatar); ?>" alt="..." />
                                                </span>
                                            </div>
                                            <div class="w-100">
                                                <div class="mt5"><?php echo get_team_member_profile_link($model_info->created_by, $model_info->created_by_name, array("class" => "dark strong")); ?></div>
                                                <small><span class="text-off"><?php echo $model_info->created_by_job_title; ?></span></small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (can_manage_zoom_integration()) { ?>
                                        <div class="flex-shrink-0">
                                            <span class="float-end dropdown">
                                                <div class="text-off dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="true" >
                                                    <i data-feather="chevron-down" class="icon"></i>
                                                </div>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li role="presentation"><?php echo modal_anchor(get_uri("zoom_meetings/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('zoom_integration_edit_meeting'), array("class" => "dropdown-item", "title" => app_lang('zoom_integration_edit_meeting'), "data-post-id" => $model_info->id)); ?> </li>
                                                </ul>
                                            </span>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="col-md-12 mb15">
                                <strong class="font-16 strong"><?php echo $model_info->title; ?></strong>
                            </div>

                            <div class="col-md-12 mb15">
                                <?php echo $model_info->description ? nl2br(link_it(process_images_from_content($model_info->description))) : "-"; ?>
                            </div>

                            <div class="col-md-12 mb15">
                                <strong><?php echo app_lang("zoom_integration_meeting_time") . ": "; ?></strong> <?php echo format_to_datetime($model_info->start_time); ?>
                            </div>

                            <div class="col-md-12 mb15">
                                <strong><?php echo app_lang("zoom_integration_duration") . ": "; ?></strong> <?php echo $model_info->duration . " " . app_lang("zoom_integration_minutes"); ?>
                            </div>

                            <div class="col-md-12 mb15">
                                <strong><?php echo app_lang("zoom_integration_join_url") . ": "; ?></strong> <?php echo anchor($model_info->join_url, $model_info->join_url, array("target" => "_blank")); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    "use strict";

    $(document).ready(function () {
        window.ZoomRefreshPageAfterUpdate = true;
    });
</script>