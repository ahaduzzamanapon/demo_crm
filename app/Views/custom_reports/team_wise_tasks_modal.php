<script>
/* Widen this modal to xl on load */
document.addEventListener('DOMContentLoaded', function () {
    var dlg = document.querySelector('#ajaxModal .modal-dialog');
    if (dlg) { dlg.classList.add('modal-xl'); }
});
(function () {
    var dlg = document.querySelector('#ajaxModal .modal-dialog');
    if (dlg) { dlg.classList.add('modal-xl'); }
})();
</script>
<div class="modal-header">
    <h5 class="modal-title" id="ajaxModalTitle"><i data-feather="list" class="icon-16"></i> <?php echo $modal_title; ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body clearfix">
    <div class="table-responsive">
        <table class="table table-sm table-striped table-bordered" style="font-size:13px;">
            <thead class="table-dark">
                <tr>
                    <th style="min-width:40px;"><?php echo app_lang('id'); ?></th>
                    <th style="min-width:180px;"><?php echo app_lang('title'); ?></th>
                    <th style="min-width:120px;"><?php echo app_lang('project'); ?></th>
                    <th style="min-width:160px;"><?php echo app_lang('assigned_to'); ?></th>
                    <th class="text-center" style="min-width:80px;">Estimated<br><small style="font-weight:400;opacity:.75;">(hrs)</small></th>
                    <th class="text-center" style="min-width:80px;">Logged<br><small style="font-weight:400;opacity:.75;">(hrs)</small></th>
                    <th class="text-center" style="min-width:90px;">Due Date</th>
                    <th class="text-center" style="min-width:90px;"><?php echo app_lang('status'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($tasks as $task) { ?>
                <tr>
                    <td><?php echo $task->id; ?></td>
                    <td>
                        <a href="#" data-action-url="<?php echo get_uri("tasks/view"); ?>" data-post-id="<?php echo $task->id; ?>" data-act="ajax-modal" data-title="<?php echo app_lang('task_info'); ?>" data-bs-dismiss="modal">
                            <?php echo htmlspecialchars($task->title); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($task->project_title); ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <span class="avatar avatar-xs">
                                <img src="<?php echo get_avatar($task->assigned_to_avatar); ?>" alt="..." />
                            </span>
                            <span><?php echo htmlspecialchars($task->assigned_to_user); ?></span>
                        </div>
                        <?php if (!empty($task->collaborator_list)): ?>
                            <div class="mt-1" style="font-size:11px; color:#64748b;">
                                <small><strong>Collaborators:</strong></small><br>
                                <?php
                                $collabs = explode('||', $task->collaborator_list);
                                foreach ($collabs as $collab):
                                    $parts = explode('::', $collab);
                                    $cName  = $parts[0] ?? '';
                                    $cImage = $parts[1] ?? '';
                                ?>
                                <span class="d-inline-flex align-items-center gap-1 me-1">
                                    <span class="avatar avatar-xs" style="width:16px;height:16px;">
                                        <img src="<?php echo get_avatar($cImage); ?>" alt="..." style="width:16px;height:16px;" />
                                    </span>
                                    <span><?php echo htmlspecialchars($cName); ?></span>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php echo $task->estimated_time ? number_format($task->estimated_time, 2) : '-'; ?>
                    </td>
                    <td class="text-center">
                        <?php
                        $logged = (float)($task->logged_hours ?? 0);
                        $est    = (float)($task->estimated_time ?? 0);
                        $color  = '';
                        if ($est > 0 && $logged > $est) $color = 'color:#dc2626;font-weight:600;'; // over estimate
                        echo $logged > 0 ? "<span style='$color'>" . number_format($logged, 2) . "</span>" : '-';
                        ?>
                    </td>
                    <td class="text-center">
                        <?php echo $task->deadline ? date('d M Y', strtotime($task->deadline)) : '-'; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge" style="background-color: <?php echo $task->status_color; ?>;">
                            <?php echo htmlspecialchars($task->status_title); ?>
                        </span>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($tasks)) { ?>
                <tr><td colspan="8" class="text-center text-muted">No tasks found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
</div>
