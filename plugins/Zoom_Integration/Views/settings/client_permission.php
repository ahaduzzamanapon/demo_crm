<div class="form-group">
    <div class="row">
        <label for="client_can_access_zoom_meetings" class="col-md-2 col-xs-8 col-sm-4"><?php echo app_lang('zoom_integration_client_can_access_zoom_meetings'); ?></label>
        <div class="col-md-10 col-xs-4 col-sm-8">
            <?php
            echo form_checkbox("client_can_access_zoom_meetings", "1", get_zoom_integration_setting("client_can_access_zoom_meetings") ? true : false, "id='client_can_access_zoom_meetings' class='form-check-input ml15'");
            ?>
        </div>
    </div>
</div>