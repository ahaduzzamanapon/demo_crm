<?php echo form_open(get_uri("google_meet_integration_settings/save_other_settings"), array("id" => "google_meet_integration-other-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>

<div class="card mb0">

    <div class="card-body">

        <div class="form-group">
            <div class="row">
                <label for="google_meet_integration_users" class=" col-md-2"><?php echo app_lang('google_meet_integration_who_can_manage_meetings'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('google_meet_integration_users_help_message'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "google_meet_integration_users",
                        "name" => "google_meet_integration_users",
                        "value" => get_google_meet_integration_setting("google_meet_integration_users"),
                        "class" => "form-control",
                        "placeholder" => app_lang('team_members')
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="client_can_access_meetings" class="col-md-2 col-xs-8 col-sm-4"><?php echo app_lang('google_meet_integration_client_can_access_meetings'); ?></label>
                <div class="col-md-10 col-xs-4 col-sm-8">
                    <?php
                    echo form_checkbox("client_can_access_meetings", "1", get_google_meet_integration_setting("client_can_access_meetings") ? true : false, "id='client_can_access_meetings' class='form-check-input'");
                    ?>
                </div>
            </div>
        </div>

    </div>

    <div class="card-footer">
        <button id="save-button" type="submit" class="btn btn-primary"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save'); ?></button>
    </div>

</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    "use strict";

    $(document).ready(function () {
        $("#google_meet_integration-other-settings-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                appAlert.success(result.message, {duration: 10000});
            }
        });

        $("#google_meet_integration_users").select2({
            multiple: true,
            data: <?php echo ($members_dropdown); ?>
        });

        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>