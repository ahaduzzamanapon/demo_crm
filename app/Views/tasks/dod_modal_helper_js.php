<script type="text/javascript">
    /**
     * openDodModal — opens the Definition of Done form in the CRM's native ajaxModal.
     * Call this whenever { require_dod: true } is returned from the backend.
     * MUST be called with require_dod checked BEFORE success, because both can be true.
     */
    window.openDodModal = window.openDodModal || function(taskId, onSuccessCallback) {
        var dodUrl = '<?php echo get_uri("tasks/dod_modal"); ?>';

        // Show the native CRM modal immediately (same pattern as data-act=ajax-modal)
        $("#ajaxModalTitle").html("Definition of Done — Submit to QA");
        $("#ajaxModalContent").html($("#ajaxModalOriginalContent").html());
        $("#ajaxModalContent").find(".original-modal-body").removeClass("original-modal-body").addClass("modal-body");
        $("#ajaxModal").find(".modal-dialog").removeClass("mini-modal modal-fullscreen custom-bg-modal");
        $("#ajaxModal").find(".modal-dialog").addClass("custom-modal-lg");
        $("#ajaxModal").modal("show");

        // Load the DoD form HTML into the modal
        $.ajax({
            url: dodUrl,
            type: "POST",
            data: { task_id: taskId, ajaxModal: 1 },
            cache: false,
            success: function(response) {
                $("#ajaxModalContent").html(response);
                feather.replace();

                // Register appFormHook — fires after dod_form submits successfully
                if (typeof onSuccessCallback === 'function') {
                    registerAppFormHook("dod_form", function(postData, result) {
                        onSuccessCallback(result);
                    });
                }
            },
            error: function() {
                $("#ajaxModalContent").find(".modal-body").html(
                    '<p class="text-danger p15">Could not load the DoD form. Please try again.</p>'
                );
            }
        });
    };
</script>
