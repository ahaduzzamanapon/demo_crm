<li>
    <span data-feather="key" class="icon-14 ml-20"></span>
    <h5><?php echo app_lang("whiteboards_can_manage_whiteboards"); ?></h5>
    <div>
        <?php
        $whiteboards = get_array_value($permissions, "whiteboards");
        if (is_null($whiteboards)) {
            $whiteboards = "";
        }

        echo form_radio(array(
            "id" => "whiteboards_no",
            "name" => "whiteboards_permission",
            "value" => "",
            "class" => "form-check-input"
                ), $whiteboards, ($whiteboards === "") ? true : false);
        ?>
        <label for="whiteboards_no"><?php echo app_lang("no"); ?> </label>
    </div>
    <div>
        <?php
        echo form_radio(array(
            "id" => "whiteboards_yes",
            "name" => "whiteboards_permission",
            "value" => "all",
            "class" => "form-check-input"
                ), $whiteboards, ($whiteboards === "all") ? true : false);
        ?>
        <label for="whiteboards_yes"><?php echo app_lang("yes"); ?></label>
    </div>
</li>