<script>
(function () {
    var dlg = document.querySelector('#agingBucketModal .modal-dialog');
    if (dlg) dlg.classList.add('modal-xl');
})();
</script>

<div class="modal-header" style="background:#1e3a8a; color:#fff;">
    <h5 class="modal-title">
        <i data-feather="list" class="icon-16"></i> <?php echo $modal_title; ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-2">
    <div class="table-responsive" style="max-height:65vh; overflow-y:auto;">
        <table class="table table-sm table-striped table-bordered mb-0" style="font-size:13px; border-collapse:separate; border-spacing:0;">
            <thead class="table-dark" style="position:sticky; top:0; z-index:5;">
                <tr>
                    <th style="min-width:40px;">ID</th>
                    <th style="min-width:200px;">Title</th>
                    <th style="min-width:150px;">Assigned To</th>
                    <th style="min-width:150px;">Collaborators</th>
                    <th class="text-center" style="min-width:80px;">Estimated<br><small style="font-weight:400;opacity:.75;">(hrs)</small></th>
                    <th class="text-center" style="min-width:80px;">Logged<br><small style="font-weight:400;opacity:.75;">(hrs)</small></th>
                    <th class="text-center" style="min-width:90px;">Deadline</th>
                    <th class="text-center" style="min-width:90px;">Days Left</th>
                    <th class="text-center" style="min-width:90px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?php echo $task->task_id; ?></td>
                    <td>
                        <a href="#"
                           class="aging-task-view-link text-decoration-none fw-semibold"
                           data-task-id="<?php echo $task->task_id; ?>"
                           data-action-url="<?php echo get_uri('tasks/view'); ?>">
                            <?php echo htmlspecialchars($task->task_title); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($task->assigned_to_name); ?></td>
                    <td style="font-size:12px; color:#475569;">
                        <?php echo !empty($task->collaborator_names) ? htmlspecialchars($task->collaborator_names) : '<span class="text-muted">—</span>'; ?>
                    </td>
                    <td class="text-center">
                        <?php echo $task->estimated_time ? clean_hours_format($task->estimated_time) : '—'; ?>
                    </td>
                    <td class="text-center">
                        <?php
                        $logged = (float)($task->logged_hours ?? 0);
                        $est    = (float)($task->estimated_time ?? 0);
                        $color  = ($est > 0 && $logged > $est) ? 'color:#dc2626;font-weight:600;' : '';
                        echo $logged > 0 ? "<span style='$color'>" . clean_hours_format($logged) . '</span>' : '—';
                        ?>
                    </td>
                    <td class="text-center">
                        <?php echo (!empty($task->deadline) && $task->deadline !== '0000-00-00')
                            ? date('d M Y', strtotime($task->deadline))
                            : '<span class="text-danger">No deadline</span>'; ?>
                    </td>
                    <td class="text-center">
                        <?php
                        if (!empty($task->deadline) && $task->deadline !== '0000-00-00') {
                            $d      = (int)$task->days_remaining;
                            $dcolor = $d < 0 ? '#dc2626' : ($d <= 3 ? '#f59e0b' : '#16a34a');
                            echo "<span style='color:$dcolor;font-weight:600;'>" . ($d >= 0 ? '+' : '') . $d . 'd</span>';
                        } else {
                            echo '—';
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <span class="badge" style="background-color:<?php echo $task->status_color; ?>;">
                            <?php echo htmlspecialchars($task->status_title); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($tasks)): ?>
                <tr><td colspan="9" class="text-center text-muted py-3">No tasks found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">✕ Close</button>
</div>

<script>
(function () {
    document.querySelectorAll('.aging-task-view-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var taskId = this.getAttribute('data-task-id');
            var url    = this.getAttribute('data-action-url');

            /* Close this bucket modal, then open task modal in parent */
            var bucketModal = document.getElementById('agingBucketModal');
            if (bucketModal) {
                var inst = bootstrap.Modal.getInstance(bucketModal);
                if (inst) inst.hide();
            }
            if (typeof window.openCrmTaskModal === 'function') {
                window.openCrmTaskModal(taskId);
            }
        });
    });
})();
</script>
