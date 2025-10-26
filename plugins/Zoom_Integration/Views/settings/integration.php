<div class="card no-border clearfix mb0">
    <?php echo form_open(get_uri("zoom_integration_settings/save"), array("id" => "zoom_integration-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
    <div class="card-body">
        <div class="form-group">
            <div class="row">
                <label for="integrate_zoom" class=" col-md-2"><?php echo app_lang('zoom_integration_integrate_zoom'); ?></label>

                <div class="col-md-10">
                    <?php
                    echo form_checkbox("integrate_zoom", "1", get_zoom_integration_setting("integrate_zoom") ? true : false, "id='integrate_zoom' class='form-check-input'");
                    ?> 
                </div>
            </div>
        </div>

        <div class="clearfix integrate-with-zoom-details-section <?php echo get_zoom_integration_setting("integrate_zoom") ? "" : "hide" ?>">
            <div class="form-group">
                <div class="row">
                    <label class=" col-md-12">
                        <?php echo app_lang("get_your_app_credentials_from_here") . " " . anchor("https://marketplace.zoom.us/", "Zoom App Marketplace", array("target" => "_blank")); ?>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="zoom_account_id" class=" col-md-2"><?php echo app_lang('zoom_account_id'); ?></label>
                    <div class=" col-md-10">
                        <?php
                        echo form_input(array(
                            "id" => "zoom_account_id",
                            "name" => "zoom_account_id",
                            "value" => get_zoom_integration_setting('zoom_account_id'),
                            "class" => "form-control",
                            "placeholder" => app_lang('zoom_account_id'),
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="zoom_client_id" class=" col-md-2"><?php echo app_lang('google_drive_client_id'); ?></label>
                    <div class=" col-md-10">
                        <?php
                        echo form_input(array(
                            "id" => "zoom_client_id",
                            "name" => "zoom_client_id",
                            "value" => get_zoom_integration_setting('zoom_client_id'),
                            "class" => "form-control",
                            "placeholder" => app_lang('google_drive_client_id'),
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="zoom_client_secret" class=" col-md-2"><?php echo app_lang('google_drive_client_secret'); ?></label>
                    <div class=" col-md-10">
                        <?php
                        echo form_input(array(
                            "id" => "zoom_client_secret",
                            "name" => "zoom_client_secret",
                            "value" => get_zoom_integration_setting('zoom_client_secret'),
                            "class" => "form-control",
                            "placeholder" => app_lang('google_drive_client_secret'),
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="status" class=" col-md-2"><?php echo app_lang('status'); ?></label>
                    <div class=" col-md-10">
                        <?php if (get_zoom_integration_setting('zoom_authorized')) { ?>
                            <span class="ml5 badge bg-success"><?php echo app_lang("authorized"); ?></span>
                        <?php } else { ?>
                            <span class="ml5 badge" style="background:#F9A52D;"><?php echo app_lang("unauthorized"); ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="card-footer">
        <button id="save-button" type="submit" class="btn btn-primary <?php echo get_zoom_integration_setting("integrate_zoom") ? "hide" : "" ?>"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
        <button id="save-and-authorize-button" type="submit" class="btn btn-primary ml5 <?php echo get_zoom_integration_setting("integrate_zoom") ? "" : "hide" ?>"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save_and_authorize'); ?></button>
    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript">
    "use strict";

    $(document).ready(function () {
        var $saveAndAuthorizeBtn = $("#save-and-authorize-button"),
                $saveBtn = $("#save-button"),
                $meetDetailsArea = $(".integrate-with-zoom-details-section");

        $("#zoom_integration-settings-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                appAlert.success(result.message, {duration: 10000});

                //if zoom is enabled, redirect to authorization system
                if ($saveBtn.hasClass("hide")) {
                    window.location.href = "<?php echo_uri('zoom_integration_settings/authorize_zoom'); ?>";
                }
            }
        });

        //show/hide zoom details area
        $("#integrate_zoom").click(function () {
            if ($(this).is(":checked")) {
                $saveAndAuthorizeBtn.removeClass("hide");
                $saveBtn.addClass("hide");
                $meetDetailsArea.removeClass("hide");
            } else {
                $saveAndAuthorizeBtn.addClass("hide");
                $saveBtn.removeClass("hide");
                $meetDetailsArea.addClass("hide");
            }
        });
    });
</script>