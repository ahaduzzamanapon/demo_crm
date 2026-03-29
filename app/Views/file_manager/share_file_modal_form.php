<?php
// Build currently shared member IDs array
$currently_shared = array();
if (!empty($file_info->shared_with)) {
    // format: :3::7: → extract numbers
    preg_match_all('/\d+/', $file_info->shared_with, $matches);
    $currently_shared = $matches[0];
}
$file_display_name = short_file_name(remove_file_prefix($file_info->file_name));
?>
<?php echo form_open(get_uri("file_manager/save_share_file"), array("id" => "share-file-form", "class" => "general-form", "role" => "form")); ?>
<input type="hidden" name="id" value="<?php echo $file_info->id; ?>" />

<div class="modal-body clearfix">
    <div class="container-fluid">

        <div class="mb15">
            <div class="d-flex align-items-center gap-2 p10 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;">
                <i data-feather="file" class="icon-20 text-primary"></i>
                <span class="font-14 text-dark" style="word-break:break-all;"><?php echo $file_display_name; ?></span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label fw-600"><?php echo app_lang('share_with'); ?></label>
            <p class="text-off font-12 mb10"><?php echo app_lang('select_team_members_to_share'); ?></p>

            <div style="max-height:280px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:8px;">
                <?php foreach ($team_members as $member): ?>
                    <?php if ($member->id == $file_info->uploaded_by) continue; // skip owner ?>
                    <label class="d-flex align-items-center gap-2 p10 share-member-row"
                           style="cursor:pointer;border-bottom:1px solid #f1f5f9;margin:0;"
                           for="sm_<?php echo $member->id; ?>">
                        <input type="checkbox"
                               id="sm_<?php echo $member->id; ?>"
                               name="shared_members[]"
                               value="<?php echo $member->id; ?>"
                               <?php echo in_array($member->id, $currently_shared) ? 'checked' : ''; ?>
                               style="width:16px;height:16px;accent-color:#6366f1;">
                        <span class="font-13 text-dark"><?php echo $member->user_name; ?></span>
                    </label>
                <?php endforeach; ?>
                <?php if (count($team_members) <= 1): ?>
                    <p class="text-off text-center p15 font-13"><?php echo app_lang('no_team_members_found'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt10 p10 rounded" style="background:#eff6ff;border:1px solid #bfdbfe;font-size:12px;color:#1d4ed8;">
            <i data-feather="info" class="icon-14 mr5"></i>
            <?php echo app_lang('shared_file_note') ?: 'Shared members can view and download this file.'; ?>
        </div>

    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal">
        <span data-feather="x" class="icon-16"></span> <?php echo app_lang('cancel'); ?>
    </button>
    <button type="submit" class="btn btn-primary">
        <span data-feather="share-2" class="icon-16"></span> <?php echo app_lang('save'); ?>
    </button>
</div>
<?php echo form_close(); ?>

<style>
.share-member-row:hover { background: #f8fafc; }
.share-member-row:last-child { border-bottom: none !important; }
</style>

<script type="text/javascript">
$(document).ready(function () {
    $("#share-file-form").appForm({
        onSuccess: function (result) {
            $("#app-modal").modal("hide");
            appAlert.success(result.message, { duration: 3000 });
        }
    });
    feather.replace();
});
</script>
