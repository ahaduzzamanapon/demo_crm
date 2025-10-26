  <script src="https://unpkg.com/react@18.2.0/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18.2.0/umd/react-dom.development.js"></script>
    <script
      type="text/javascript"
      src="https://unpkg.com/@excalidraw/excalidraw@0.17.1/dist/excalidraw.development.js"
    ></script>

<style>
    .ToolIcon {
        margin-bottom: 0 !important;
    }
</style>

<div class="page-content clearfix contract-details-view pb0">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="contract-title-section">
                    <div class="page-title no-bg clearfix mb5 no-border">
                        <h1 class="pl0">
                            <span><i data-feather="clipboard" class='icon'></i></span>
                            <?php echo $model_info->title; ?>
                        </h1>

                        <?php if ($model_info->permission == "editor" || $model_info->created_by === $login_user->id) { ?>
                            <div class="title-button-group mr0">
                                <?php echo form_open(get_uri("whiteboards/save_whiteboard_content"), array("id" => "whiteboard-content-form", "class" => "general-form dashed-row", "role" => "form")); ?>

                                <input type="hidden" id="whiteboard_content" name="whiteboard_content" value="">
                                <input type="hidden" name="id" value="<?php echo $model_info->id; ?>">

                                <button class="btn btn-info text-white mb0" type="submit">
                                    <i data-feather="check-circle" class="icon-16"></i> <?php echo app_lang('save'); ?>
                                </button>

                                <?php echo form_close(); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p10 w-100">
            <div id="excalidraw"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    "use strict";

    $(document).ready(function() {
        $("#whiteboard-content-form").appForm({
            isModal: false,
            onSuccess: function(result) {
                if (result.success) {
                    appAlert.success(result.message, {
                        duration: 10000
                    });
                } else {
                    appAlert.error(result.message);
                }
            }
        });

        $("#excalidraw").height($(window).height() - 185);

        function onExcalidrawChange(elements, appState) {
            $("#whiteboard_content").val(JSON.stringify(elements));
        }

        const Excalidraw = window.ExcalidrawLib.Excalidraw;
        const ExcalidrawWithRef = React.forwardRef((props, ref) => {
            return React.createElement(Excalidraw, {
                ...props,
                forwardRef: ref
            });
        });

        const excalidrawWrapper = document.getElementById('excalidraw');
        const root = ReactDOM.createRoot(excalidrawWrapper);

        const excalidrawProps = {
            onChange: onExcalidrawChange,
            UIOptions: {
                tools: {
                    image: false,
                }
            },
            //viewModeEnabled: true, // Enable view-only mode
        };

        var color = getCookie("theme_color");
        if (color == "1E202D") {
            excalidrawProps.theme = "dark";
        }

        <?php if ($model_info->content) { ?>
            excalidrawProps.initialData = {
                elements: <?php echo json_encode(unserialize($model_info->content)); ?>
            };
        <?php } ?>

        <?php if ($model_info->permission == "viewer" && $model_info->created_by !== $login_user->id) { ?>
            excalidrawProps.viewModeEnabled = true;
        <?php } ?>

        root.render(React.createElement(ExcalidrawWithRef, excalidrawProps));

        var intervalId = setInterval(function() {
            // Check if there is any data in #excalidraw
            if ($('#excalidraw').html().trim().length > 0) {

                $(".layer-ui__wrapper__top-right").empty();
                $(".layer-ui__wrapper__footer-right").empty();
                $(".App-menu_top__left").empty();

                $(".App-toolbar__extra-tools-trigger").remove();
                $('.App-toolbar-container .App-toolbar__divider:last').remove();

                //for small screens
                $('.default-sidebar-trigger').remove();
                $('.dropdown-menu-button--mobile').remove();


                // Clear the interval to stop the loop
                clearInterval(intervalId);
            }
        }, 200); // Run every 200 milliseconds
    });
</script>