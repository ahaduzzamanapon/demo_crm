<li>
    <span data-feather="key" class="icon-14 ml-20"></span>
    <h5><?php echo app_lang("zoom_integration_can_manage_zoom_meetings"); ?></h5>
    <div>
        <?php
        $zoom_meeting = get_array_value($permissions, "zoom_meeting");
        if (is_null($zoom_meeting)) {
            $zoom_meeting = "";
        }

        echo form_radio(array(
            "id" => "zoom_meeting_no",
            "name" => "zoom_meeting_permission",
            "value" => "",
            "class" => "form-check-input"
                ), $zoom_meeting, ($zoom_meeting === "") ? true : false);
        ?>
        <label for="zoom_meeting_no"><?php echo app_lang("no"); ?> </label>
    </div>
    <div>
        <?php
        echo form_radio(array(
            "id" => "zoom_meeting_yes",
            "name" => "zoom_meeting_permission",
            "value" => "all",
            "class" => "form-check-input"
                ), $zoom_meeting, ($zoom_meeting === "all") ? true : false);
        ?>
        <label for="zoom_meeting_yes"><?php echo app_lang("yes"); ?></label>
    </div>
</li>