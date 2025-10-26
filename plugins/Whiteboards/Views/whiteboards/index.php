<div id="page-content" class="page-wrapper clearfix">
    <div class="card">
        <div class="page-title clearfix">
            <h1> <?php echo app_lang('whiteboards'); ?></h1>
            <div class="title-button-group">
                <?php
                $can_manage_whiteboards = can_manage_whiteboards();
                if ($can_manage_whiteboards) {
                    echo modal_anchor(get_uri("whiteboards/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('whiteboards_add_whiteboard'), array("class" => "btn btn-default", "title" => app_lang('whiteboards_add_whiteboard')));
                }
                ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="whiteboards-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    "use strict";
    
    $(document).ready(function () {
        var actionVisibility = false;
<?php if ($can_manage_whiteboards) { ?>
            actionVisibility = true;
<?php } ?>

        $("#whiteboards-table").appTable({
            source: '<?php echo_uri("whiteboards/list_data") ?>',
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