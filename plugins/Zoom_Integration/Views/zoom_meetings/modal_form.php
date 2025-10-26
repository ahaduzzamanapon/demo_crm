<?php echo form_open(get_uri("zoom_meetings/save"), array("id" => "meeting-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <div class="form-group">
            <div class="row">
                <label for="title" class=" col-md-3"><?php echo app_lang('zoom_integration_topic'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "title",
                        "name" => "title",
                        "value" => $model_info->title,
                        "class" => "form-control",
                        "placeholder" => app_lang('zoom_integration_topic'),
                        "autofocus" => true,
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="description" class=" col-md-3"><?php echo app_lang('description'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_textarea(array(
                        "id" => "description",
                        "name" => "description",
                        "value" => process_images_from_content($model_info->description, false),
                        "class" => "form-control",
                        "placeholder" => app_lang('description'),
                        "data-rich-text-editor" => true
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <label for="start_date" class=" col-md-3 col-sm-3"><?php echo app_lang('start_date'); ?></label>
            <div class="col-md-4 col-sm-4 form-group">
                <?php
                $in_time = is_date_exists($model_info->start_time) ? convert_date_utc_to_local($model_info->start_time) : "";

                if ($time_format_24_hours) {
                    $in_time_value = $in_time ? date("H:i", strtotime($in_time)) : "";
                } else {
                    $in_time_value = $in_time ? convert_time_to_12hours_format(date("H:i:s", strtotime($in_time))) : "";
                }

                echo form_input(array(
                    "id" => "start_date",
                    "name" => "start_date",
                    "value" => $in_time ? date("Y-m-d", strtotime($in_time)) : "",
                    "class" => "form-control",
                    "placeholder" => app_lang('start_date'),
                    "autocomplete" => "off",
                    "data-rule-required" => true,
                    "data-msg-required" => app_lang("field_required"),
                ));
                ?>
            </div>
            <label for="in_time" class=" col-md-2 col-sm-2"><?php echo app_lang('start_time'); ?></label>
            <div class=" col-md-3 col-sm-3  form-group">
                <?php
                echo form_input(array(
                    "id" => "start_time",
                    "name" => "start_time",
                    "value" => $in_time_value,
                    "class" => "form-control",
                    "placeholder" => app_lang('start_time'),
                    "data-rule-required" => true,
                    "data-msg-required" => app_lang("field_required"),
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="duration" class=" col-md-3"><?php echo app_lang('zoom_integration_duration') . " (" . app_lang('zoom_integration_in_minutes') . ") "; ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "duration",
                        "name" => "duration",
                        "type" => "number",
                        "min" => 1,
                        "value" => $model_info->duration ? $model_info->duration : 60,
                        "class" => "form-control",
                        "placeholder" => app_lang('zoom_integration_duration'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="share_with" class=" col-md-3"><?php echo app_lang('share_with'); ?></label>
                <div class=" col-md-9">
                    <!--Team members-->
                    <div class="share-with-section mb15">
                        <div>
                            <?php
                            echo form_checkbox(array(
                                "id" => "share_with_all_team_members",
                                "name" => "share_with_team_members",
                                "value" => "all",
                                "class" => "toggle_specific form-check-input",
                                    ), $model_info->share_with_team_members ? $model_info->share_with_team_members : "", ($model_info->share_with_team_members === "all") ? true : false);
                            ?>
                            <label for="share_with_all_team_members"><?php echo app_lang("all_team_members"); ?></label>
                        </div>

                        <div class="form-group mb0">
                            <?php
                            echo form_checkbox(array(
                                "id" => "share_with_specific_team_members_radio_button",
                                "name" => "share_with_team_members",
                                "value" => "specific",
                                "class" => "toggle_specific form-check-input",
                                    ), $model_info->share_with_team_members ? $model_info->share_with_team_members : "", ($model_info->share_with_team_members && $model_info->share_with_team_members != "all") ? true : false);
                            ?>
                            <label for="share_with_specific_team_members_radio_button"><?php echo app_lang("specific_members_and_teams"); ?>:</label>
                            <div class="specific_dropdown" style="display: none;">
                                <input type="text" value="<?php echo ($model_info->share_with_team_members && $model_info->share_with_team_members != "all" ) ? $model_info->share_with_team_members : ""; ?>" name="share_with_specific_team_members" id="share_with_specific_team_members" class="w100p validate-hidden"  data-rule-required="true" data-msg-required="<?php echo app_lang('field_required'); ?>" placeholder="<?php echo app_lang('choose_members_and_or_teams'); ?>"  />
                            </div>
                        </div>
                    </div>

                    <!--Clients-->
                    <?php if (get_zoom_integration_setting("client_can_access_zoom_meetings")) { ?>
                        <div class="share-with-section">
                            <div>
                                <?php
                                echo form_checkbox(array(
                                    "id" => "share_with_all_client_contacts",
                                    "name" => "share_with_client_contacts",
                                    "value" => "all",
                                    "class" => "toggle_specific form-check-input",
                                        ), $model_info->share_with_client_contacts ? $model_info->share_with_client_contacts : "", ($model_info->share_with_client_contacts === "all") ? true : false);
                                ?>
                                <label for="share_with_all_client_contacts"><?php echo app_lang("zoom_integration_all_client_contacts"); ?></label>
                            </div>

                            <div class="form-group mb0">
                                <?php
                                echo form_checkbox(array(
                                    "id" => "share_with_specific_client_contacts_radio_button",
                                    "name" => "share_with_client_contacts",
                                    "value" => "specific",
                                    "class" => "toggle_specific form-check-input",
                                        ), $model_info->share_with_client_contacts ? $model_info->share_with_client_contacts : "", ($model_info->share_with_client_contacts && $model_info->share_with_client_contacts != "all") ? true : false);
                                ?>
                                <label for="share_with_specific_client_contacts_radio_button"><?php echo app_lang("specific_client_contacts"); ?>:</label>
                                <div class="specific_dropdown" style="display: none;">
                                    <input type="text" value="<?php echo ($model_info->share_with_client_contacts && $model_info->share_with_client_contacts != "all" ) ? $model_info->share_with_client_contacts : ""; ?>" name="share_with_specific_client_contacts" id="share_with_specific_client_contacts" class="w100p validate-hidden"  data-rule-required="true" data-msg-required="<?php echo app_lang('field_required'); ?>" placeholder="<?php echo app_lang('zoom_integration_choose_client_contacts'); ?>"  />
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="waiting_room" class=" col-md-3"><?php echo app_lang('zoom_integration_enable_waiting_room'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('zoom_integration_waiting_room_help_message'); ?>"><i data-feather="help-circle" class="icon-16"></i></span></label>
                <div class=" col-md-9">
                    <?php
                    echo form_checkbox("waiting_room", "1", $model_info->waiting_room ? true : false, "id='waiting_room' class='form-check-input'");
                    ?>
                </div>
            </div>
        </div>   

    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    "use strict";

    $(document).ready(function () {
        $("#meeting-form").appForm({
            onSuccess: function (result) {
                if (window.ZoomRefreshPageAfterUpdate) {
                    window.ZoomRefreshPageAfterUpdate = false;
                    location.reload();
                } else {
                    $("#zoom-meetings-table").appTable({newData: result.data, dataId: result.id});
                }
            }
        });

        setDatePicker("#start_date");
        setTimePicker("#start_time");

        setTimeout(function () {
            $("#title").focus();
        }, 200);

        get_specific_dropdown($("#share_with_specific_team_members"), <?php echo ($members_and_teams_dropdown); ?>);
        $("#share_with_specific_client_contacts").select2({
            multiple: true,
            data: <?php echo ($clients_dropdown); ?>
        });

        function get_specific_dropdown(container, data) {
            setTimeout(function () {
                container.select2({
                    multiple: true,
                    formatResult: teamAndMemberSelect2Format,
                    formatSelection: teamAndMemberSelect2Format,
                    data: data
                }).on('select2-open change', function (e) {
                    feather.replace();
                });

                feather.replace();
            }, 100);
        }

        $(".toggle_specific").click(function () {
            //on sections, a single field should be selected
            if ($(this).is(":checked")) {
                $(this).closest(".share-with-section").find(".toggle_specific").removeAttr("checked").closest("div").addClass("hide");
                $(this).attr("checked", "checked").closest("div").removeClass("hide");
            } else {
                $(this).closest(".share-with-section").find(".toggle_specific").closest("div").removeClass("hide");
            }

            toggle_specific_dropdown();
        });

        toggle_specific_dropdown();

        function toggle_specific_dropdown() {
            $(".specific_dropdown").hide().find("input").removeClass("validate-hidden");

            $(".toggle_specific").each(function () {
                var $element = $(this);
                if ($element.is(":checked") && $element.val() === "specific") {
                    var $dropdown = $element.closest("div").find("div.specific_dropdown");
                    $dropdown.show().find("input").addClass("validate-hidden");
                }
            });
        }

        $('[data-bs-toggle="tooltip"]').tooltip();

    });
</script>