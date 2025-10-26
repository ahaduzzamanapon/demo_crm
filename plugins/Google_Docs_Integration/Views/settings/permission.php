<li>
    <span data-feather="key" class="icon-14 ml-20"></span>
    <h5><?php echo app_lang("google_docs_integration_can_manage_google_docs"); ?></h5>
    <div>
        <?php
        $google_docs = get_array_value($permissions, "google_docs");
        if (is_null($google_docs)) {
            $google_docs = "";
        }

        echo form_radio(array(
            "id" => "google_docs_no",
            "name" => "google_docs_permission",
            "value" => "",
            "class" => "form-check-input"
                ), $google_docs, ($google_docs === "") ? true : false);
        ?>
        <label for="google_docs_no"><?php echo app_lang("no"); ?> </label>
    </div>
    <div>
        <?php
        echo form_radio(array(
            "id" => "google_docs_yes",
            "name" => "google_docs_permission",
            "value" => "all",
            "class" => "form-check-input"
                ), $google_docs, ($google_docs === "all") ? true : false);
        ?>
        <label for="google_docs_yes"><?php echo app_lang("yes"); ?></label>
    </div>
</li>