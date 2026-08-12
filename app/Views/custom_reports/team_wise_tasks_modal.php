<script>
/* Widen this modal to xl on load */
(function () {
    var dlg = document.querySelector('#ajaxModal .modal-dialog');
    if (dlg) { dlg.classList.add('modal-xl'); }

    /* Build second modal on body (not inside #ajaxModal) so Bootstrap stacks it correctly */
    if (!document.getElementById('projectEffortModal')) {
        var m = document.createElement('div');
        m.id = 'projectEffortModal';
        m.className = 'modal fade';
        m.setAttribute('tabindex', '-1');
        m.style.zIndex = '1080';
        m.style.overflowY = 'auto'; /* fix: body has overflow:hidden from first modal */
        m.innerHTML = '<div class="modal-dialog modal-xl" style="margin-top:40px;">'
            + '<div class="modal-content" style="max-height:85vh;overflow-y:auto;"><div id="projectEffortModalBody">'
            + '<div class="modal-body text-center p-5">'
            + '<div class="spinner-border text-primary" role="status"></div></div>'
            + '</div></div></div>';
        document.body.appendChild(m);

        m.addEventListener('hidden.bs.modal', function () {
            /* restore #ajaxModal backdrop z-index after second modal closes */
            var bd = document.querySelector('.modal-backdrop');
            if (bd) bd.style.zIndex = '';
        });
    }
})();
</script>

<div class="modal-header">
    <h5 class="modal-title" id="ajaxModalTitle">
        <i data-feather="list" class="icon-16"></i> <?php echo $modal_title; ?>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body clearfix">
    <div class="table-responsive" style="max-height:65vh; overflow-y:auto;">
        <table class="table table-sm table-striped table-bordered" style="font-size:13px; border-collapse:separate; border-spacing:0;">
            <thead class="table-dark" style="position:sticky; top:0; z-index:5;">
                <tr>
                    <th style="min-width:40px;"><?php echo app_lang('id'); ?></th>
                    <th style="min-width:180px;"><?php echo app_lang('title'); ?></th>
                    <th style="min-width:140px;"><?php echo app_lang('project'); ?></th>
                    <th style="min-width:160px;"><?php echo app_lang('assigned_to'); ?></th>
                    <th class="text-center" style="min-width:80px;">Estimated<br><small style="font-weight:400;opacity:.75;">(hrs)</small></th>
                    <th class="text-center" style="min-width:80px;">Logged<br><small style="font-weight:400;opacity:.75;">(hrs)</small></th>
                    <th class="text-center" style="min-width:90px;">Deadline</th>
                    <th class="text-center" style="min-width:90px;"><?php echo app_lang('status'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($tasks as $task) { ?>
                <tr>
                    <td><?php echo $task->id; ?></td>
                    <td>
                        <a href="#"
                           class="task-view-link"
                           data-action-url="<?php echo get_uri('tasks/view'); ?>"
                           data-post-id="<?php echo $task->id; ?>">
                            <?php echo htmlspecialchars($task->title); ?>
                        </a>
                    </td>
                    <td>
                        <a href="#"
                           class="project-effort-link text-decoration-none fw-semibold"
                           data-project-id="<?php echo $task->project_id ?? ''; ?>"
                           data-project-title="<?php echo htmlspecialchars($task->project_title); ?>"
                           data-start-date="<?php echo $start_date; ?>"
                           data-end-date="<?php echo $end_date; ?>"
                           title="View effort for this project">
                            <?php echo htmlspecialchars($task->project_title); ?>
                            <i data-feather="bar-chart-2" style="width:12px;height:12px;vertical-align:middle;margin-left:3px;"></i>
                        </a>
                    </td>
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
                        <?php echo $task->estimated_time ? clean_hours_format($task->estimated_time) : '-'; ?>
                    </td>
                    <td class="text-center">
                        <?php
                        $logged = (float)($task->logged_hours ?? 0);
                        $est    = (float)($task->estimated_time ?? 0);
                        $color  = ($est > 0 && $logged > $est) ? 'color:#dc2626;font-weight:600;' : '';
                        echo $logged > 0 ? "<span style='$color'>" . clean_hours_format($logged) . "</span>" : '-';
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
    <button type="button" class="btn btn-default" data-bs-dismiss="modal">
        <span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?>
    </button>
</div>

<script>
(function () {
    var modalEl = document.getElementById('projectEffortModal');
    if (!modalEl) return;

    /* When second modal opens, push its backdrop above the first modal dialog */
    modalEl.addEventListener('shown.bs.modal', function () {
        var backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length >= 2) {
            backdrops[backdrops.length - 1].style.zIndex = '1075';
        }
    });

    /* Task title click — wait for #ajaxModal to fully hide before re-opening */
    document.querySelectorAll('.task-view-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation(); /* prevent CRM's data-act delegation */
            var url    = this.getAttribute('data-action-url');
            var taskId = this.getAttribute('data-post-id');
            var ajaxEl = document.getElementById('ajaxModal');

            function loadTask() {
                $('#ajaxModalContent').html($('#ajaxModalOriginalContent').html());
                $('#ajaxModalContent .original-modal-body')
                    .removeClass('original-modal-body').addClass('modal-body');
                $('#ajaxModal').modal('show');
                $.ajax({
                    url: url,
                    data: { ajaxModal: 1, id: taskId },
                    type: 'POST',
                    cache: false,
                    success: function (html) {
                        $('#ajaxModalContent').html(html);
                        feather.replace();
                    }
                });
            }

            /* If #ajaxModal is visible, hide it first then load */
            if (ajaxEl.classList.contains('show')) {
                ajaxEl.addEventListener('hidden.bs.modal', function onHide() {
                    ajaxEl.removeEventListener('hidden.bs.modal', onHide);
                    loadTask();
                });
                bootstrap.Modal.getInstance(ajaxEl).hide();
            } else {
                loadTask();
            }
        });
    });

    /* Project effort link click */
    document.querySelectorAll('.project-effort-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var projectId = this.dataset.projectId;
            var startDate = this.dataset.startDate;
            var endDate   = this.dataset.endDate;
            var bodyEl    = document.getElementById('projectEffortModalBody');

            /* Reset to spinner */
            bodyEl.innerHTML = '<div class="modal-body text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';

            /* Show second modal */
            var modal2 = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: true, keyboard: true });
            modal2.show();

            /* AJAX POST */
            var fd = new FormData();
            fd.append('project_id', projectId);
            fd.append('start_date', startDate);
            fd.append('end_date',   endDate);

            fetch('<?php echo get_uri('custom_reports/project_effort_quick_modal'); ?>', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                bodyEl.innerHTML = html;
                if (typeof feather !== 'undefined') feather.replace();
            })
            .catch(function () {
                bodyEl.innerHTML = '<div class="modal-body text-danger p-4">Failed to load project effort data.</div>';
            });
        });
    });
})();
</script>
