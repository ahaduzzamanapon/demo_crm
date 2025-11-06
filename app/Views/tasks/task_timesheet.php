<?php foreach ($task_timesheet as $timesheet) { ?>
    <div id="timesheet-row-<?php echo $timesheet->id; ?>" class="d-flex mt15 mb15 timesheet-row">
        <div class="flex-shrink-0">
            <span class="avatar avatar-xs">
                <img src="<?php echo get_avatar($timesheet->logged_by_avatar); ?>" alt="..." />
            </span>
        </div>
        <div class="w-100 ps-2">
            <div class="float-start">
                <div><?php echo format_to_date($timesheet->start_time); ?></div>
                <?php if (!$timesheet->hours) { ?>
                    <small><?php echo format_to_time($timesheet->start_time) . " to " . format_to_time($timesheet->end_time); ?></small>
                <?php } ?>
            </div>
            <div class="float-end">
                <strong><?php echo convert_seconds_to_time_format($timesheet->hours ? (round(($timesheet->hours * 60), 0) * 60) : abs(strtotime($timesheet->end_time) - strtotime($timesheet->start_time))) ?></strong>
                <?php if ($is_admin || $timesheet->user_id == $member_id) { ?>
                    <a href="#" class="task-timesheet-delete-link ms-2" data-id="<?php echo $timesheet->id; ?>" title="<?php echo app_lang('delete_timelog'); ?>"><i data-feather="x" class="icon-16"></i></a>
                <?php } ?>
            </div>
        </div>
    </div>
<?php } ?>

<script type="text/javascript">
$(document).ready(function () {
    $('body').on('click', '.task-timesheet-delete-link', function (e) {
        e.preventDefault();
        var $link = $(this);
        var timesheetId = $link.data('id');

        $('#confirmationModal').modal('show');
        $('#confirmDeleteButton').off('click').on('click', function () {
            $('#confirmationModal').modal('hide');
            appLoader.show();
            $.ajax({
                url: '<?php echo get_uri("projects/delete_timelog"); ?>',
                type: 'POST',
                dataType: 'json',
                data: { id: timesheetId },
                success: function (result) {
                    if (result.success) {
                        $('#timesheet-row-' + timesheetId).fadeOut(function () {
                            $(this).remove();
                        });
                    }
                    appLoader.hide();
                }
            });
        });
    });
});
</script>
