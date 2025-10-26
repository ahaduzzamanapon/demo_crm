<style type="text/css">
    #google-docs-content {
        max-width: 700px;
        margin: auto;
    }

    .google-docs-title {
        background: #f9fbfd;
        padding: 10px 10px 5px 60px;
    }
</style>

<div class="font-16 google-docs-title">
    <i data-feather='file-text' class='icon-16'></i> <?php echo $model_info->title; ?>
</div>

<div id="google-docs-iframe-wrapper">
    <iframe width="100%" id="google-docs-iframe" src="https://docs.google.com/document/d/<?php echo $model_info->google_document_id; ?>/edit?usp=sharing&widget=true&rm=embedded"></iframe>
</div>

<script type="text/javascript">
    "use strict";

    $(document).ready(function() {
        window.GoogleDocsRefreshPageAfterUpdate = true;

        //set iframe height
        $("#google-docs-iframe").height($(window).height() - 110);
    });
</script>