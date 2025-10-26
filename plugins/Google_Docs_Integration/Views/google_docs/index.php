<div id="page-content" class="page-wrapper clearfix">
    <div class="card">
        <div class="page-title clearfix">
            <h1> <?php echo app_lang('google_docs'); ?></h1>
            <div class="title-button-group">
                <?php
                $can_manage_google_docs_integration = can_manage_google_docs_integration();
                if ($can_manage_google_docs_integration) {
                    echo modal_anchor(get_uri("google_docs/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('google_docs_integration_add_document'), array("class" => "btn btn-default", "title" => app_lang('google_docs_integration_add_document')));
                }
                ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="google-docs-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    "use strict";
    
    $(document).ready(function () {
        var actionVisibility = false;
<?php if ($can_manage_google_docs_integration) { ?>
            actionVisibility = true;
<?php } ?>

        $("#google-docs-table").appTable({
            source: '<?php echo_uri("google_docs/list_data") ?>',
            order: [[0, 'desc']],
            columns: [
                {title: '<?php echo app_lang("title"); ?>', "class": "w300"},
                {title: '<?php echo app_lang("description"); ?>', "class": "w300"},
                {title: '<?php echo app_lang("created_by"); ?>', "class": "w200"},
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w100", visible: actionVisibility}
            ]
        });
    });
</script>