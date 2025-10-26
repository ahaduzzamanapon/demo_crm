<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "google_meet_integration";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="card">

                <ul id="google_meet_integration-settings-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a  role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#google_meet_integration-settings-tab"> <?php echo app_lang('google_meet_integration'); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("google_meet_integration_settings/other_settings/"); ?>" data-bs-target="#google_meet_integration-other-settings-tab"><?php echo app_lang('google_meet_integration_other_settings'); ?></a></li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="google_meet_integration-settings-tab">

                        <div class="card mb0">

                            <?php echo form_open(get_uri("google_meet_integration_settings/save_google_meet_integration_settings"), array("id" => "google_meet_integration-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>

                            <div class="card-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="integrate_google_meet" class=" col-md-3"><?php echo app_lang('google_meet_integration_integrate_google_meet'); ?></label>

                                        <div class="col-md-9">
                                            <?php
                                            echo form_checkbox("integrate_google_meet", "1", get_google_meet_integration_setting("integrate_google_meet") ? true : false, "id='integrate_google_meet' class='form-check-input'");
                                            ?> 
                                        </div>
                                    </div>
                                </div>

                                <div class="clearfix integrate-with-google-meet-details-section <?php echo get_google_meet_integration_setting("integrate_google_meet") ? "" : "hide" ?>">
                                    <div class="form-group">
                                        <div class="row">
                                            <label class=" col-md-12">
                                                <?php echo app_lang("get_your_app_credentials_from_here") . " " . anchor("https://console.developers.google.com", "Google API Console", array("target" => "_blank")); ?>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="google_client_id" class=" col-md-3"><?php echo app_lang('google_client_id'); ?></label>
                                            <div class=" col-md-9">
                                                <?php
                                                echo form_input(array(
                                                    "id" => "google_client_id",
                                                    "name" => "google_client_id",
                                                    "value" => get_google_meet_integration_setting("google_client_id"),
                                                    "class" => "form-control",
                                                    "placeholder" => app_lang('google_client_id'),
                                                    "data-rule-required" => true,
                                                    "data-msg-required" => app_lang("field_required"),
                                                ));
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="google_client_secret" class=" col-md-3"><?php echo app_lang('google_client_secret'); ?></label>
                                            <div class=" col-md-9">
                                                <?php
                                                echo form_input(array(
                                                    "id" => "google_client_secret",
                                                    "name" => "google_client_secret",
                                                    "value" => get_google_meet_integration_setting('google_client_secret'),
                                                    "class" => "form-control",
                                                    "placeholder" => app_lang('google_client_secret'),
                                                    "data-rule-required" => true,
                                                    "data-msg-required" => app_lang("field_required"),
                                                ));
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="redirect_uri" class=" col-md-3"><i data-feather="alert-triangle" class="icon-16 text-warning"></i> <?php echo app_lang('remember_to_add_this_url_in_authorized_redirect_uri'); ?></label>
                                            <div class=" col-md-9">
                                                <?php
                                                echo "<pre class='mt5'>" . get_uri("google_meet_integration_settings/save_access_token_of_meet") . "</pre>"
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="status" class=" col-md-3"><?php echo app_lang('status'); ?></label>
                                            <div class=" col-md-9">
                                                <?php if (get_google_meet_integration_setting('google_meet_authorized')) { ?>
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
                                <button id="save-button" type="submit" class="btn btn-primary <?php echo get_google_meet_integration_setting("integrate_google_meet") ? "hide" : "" ?>"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                                <button id="save-and-authorize-button" type="submit" class="btn btn-primary ml5 <?php echo get_google_meet_integration_setting("integrate_google_meet") ? "" : "hide" ?>"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save_and_authorize'); ?></button>
                            </div>

                        </div>

                    </div>

                    <div role="tabpanel" class="tab-pane fade" id="google_meet_integration-other-settings-tab"></div>

                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    "use strict";

    $(document).ready(function () {
        var $saveAndAuthorizeBtn = $("#save-and-authorize-button"),
                $saveBtn = $("#save-button"),
                $meetDetailsArea = $(".integrate-with-google-meet-details-section");

        $("#google_meet_integration-settings-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                appAlert.success(result.message, {duration: 10000});

                //if google meet is enabled, redirect to authorization system
                if ($saveBtn.hasClass("hide")) {
                    window.location.href = "<?php echo_uri('google_meet_integration_settings/authorize_meet'); ?>";
                }
            }
        });

        //show/hide google calendar details area
        $("#integrate_google_meet").click(function () {
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