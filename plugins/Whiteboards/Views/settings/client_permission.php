<div class="form-group">
    <div class="row">
        <label for="client_can_access_whiteboards" class="col-md-2 col-xs-8 col-sm-4"><?php echo app_lang('whiteboards_client_can_access_whiteboards'); ?></label>
        <div class="col-md-10 col-xs-4 col-sm-8">
            <?php
            echo form_checkbox("client_can_access_whiteboards", "1", get_whiteboards_setting("client_can_access_whiteboards") ? true : false, "id='client_can_access_whiteboards' class='form-check-input ml15'");
            ?>
        </div>
    </div>
</div>