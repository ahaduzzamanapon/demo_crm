<div class="modal-header" style="background:#1e3a8a; color:#fff; padding:12px 16px;">
    <h5 class="modal-title" style="font-size:14px;">
        📊 <?php echo htmlspecialchars($project->title ?? ''); ?> — Project Wise Effort
        <?php if ($start_date && $end_date): ?>
        <small style="font-weight:400; font-size:11px; opacity:.8; margin-left:8px;">
            <?php echo date('d M Y', strtotime($start_date)); ?> → <?php echo date('d M Y', strtotime($end_date)); ?>
        </small>
        <?php endif; ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body p-0">

    <!-- ── Effort Summary Table (mirrors main report) ── -->
    <div class="table-responsive">
    <table class="table table-bordered table-sm mb-0" style="font-size:12px; min-width:600px;">
        <thead>
            <tr style="background:#1e3a8a; color:#fff; text-align:center;">
                <th class="text-start" style="min-width:180px;">Project Name</th>
                <th style="width:45px;">Type</th>
                <th style="width:90px;">Estimated<br><small style="opacity:.75;">(hrs)</small></th>
                <th colspan="2" style="border-bottom:none;">Logged Hours</th>
                <?php foreach ($effort_staff as $s): ?>
                <th style="min-width:70px; font-size:11px;">
                    <img src="<?php echo get_avatar($s->image); ?>" style="width:20px;height:20px;border-radius:50%;margin-bottom:2px;display:block;margin-inline:auto;"/><?php echo htmlspecialchars($s->first_name); ?>
                </th>
                <?php endforeach; ?>
            </tr>
            <tr style="background:#2d50a8; color:#fff; text-align:center;">
                <th class="text-start"></th>
                <th></th>
                <th></th>
                <th style="width:80px;">Preceding</th>
                <th style="width:80px;">Current</th>
                <?php foreach ($effort_staff as $s): ?>
                <th style="font-size:11px; font-weight:400; opacity:.8;">Current</th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?php if ($effort_row): ?>
        <tr style="text-align:center;">
            <td class="text-start fw-semibold"><?php echo htmlspecialchars($effort_row->project_name); ?></td>
            <td>
                <span class="badge" style="background:<?php echo ($effort_row->project_type === 'internal') ? '#6366f1' : '#0891b2'; ?>; font-size:10px;">
                    <?php echo strtoupper(substr($effort_row->project_type ?? 'E', 0, 1)); ?>
                </span>
            </td>
            <td><?php echo number_format($effort_row->estimated_hours, 2); ?></td>
            <td style="color:#64748b;"><?php echo number_format($effort_row->preceding_hours, 2); ?></td>
            <td>
                <strong style="color:<?php echo $effort_row->current_hours > 0 ? '#16a34a' : '#94a3b8'; ?>;">
                    <?php echo number_format($effort_row->current_hours, 2); ?>
                </strong>
            </td>
            <?php foreach ($effort_staff as $s):
                $mh = $member_hours[$s->id] ?? 0;
            ?>
            <td style="color:<?php echo $mh > 0 ? '#1e3a8a' : '#94a3b8'; ?>; font-weight:<?php echo $mh > 0 ? '600' : '400'; ?>;">
                <?php echo $mh > 0 ? number_format($mh, 2) : '—'; ?>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <!-- ── Task List ── -->
    <div class="px-3 pt-3 pb-1">
        <strong style="color:#1e3a8a; font-size:13px;">📋 Tasks (<?php echo count($tasks); ?>)</strong>
    </div>
    <div class="table-responsive px-0">
    <table class="table table-sm table-striped table-bordered mb-0" style="font-size:12px;">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Assigned To</th>
                    <th>Collaborators</th>
                <th class="text-center">Est. (hrs)</th>
                <th class="text-center">Logged (hrs)</th>
                <th class="text-center">Deadline</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($tasks)): ?>
            <tr><td colspan="8" class="text-center text-muted py-3">No tasks found.</td></tr>
        <?php endif; ?>
        <?php foreach ($tasks as $t):
            $logged = (float)($t->logged_hours ?? 0);
            $est    = (float)($t->estimated_time ?? 0);
            $over   = $est > 0 && $logged > $est;
        ?>
        <tr>
            <td><?php echo $t->id; ?></td>
            <td>
                <a href="#" data-action-url="<?php echo get_uri('tasks/view'); ?>"
                   data-post-id="<?php echo $t->id; ?>" data-act="ajax-modal"
                   data-title="<?php echo app_lang('task_info'); ?>" data-bs-dismiss="modal">
                    <?php echo htmlspecialchars($t->title); ?>
                </a>
            </td>
            <td><?php echo htmlspecialchars($t->assigned_to ?? '—'); ?></td>
            <td style="font-size:11px;">
                <?php if (!empty($t->collaborator_list)):
                    $collabs = explode('||', $t->collaborator_list);
                    foreach ($collabs as $collab):
                        $parts = explode('::', $collab);
                        $cName = $parts[0] ?? '';
                        $cImg  = $parts[1] ?? '';
                ?>
                <span class="d-inline-flex align-items-center gap-1 me-1">
                    <img src="<?php echo get_avatar($cImg); ?>" style="width:16px;height:16px;border-radius:50%;" />
                    <span><?php echo htmlspecialchars($cName); ?></span>
                </span>
                <?php endforeach; else: echo '<span class="text-muted">—</span>'; endif; ?>
            </td>
            <td class="text-center"><?php echo $est > 0 ? number_format($est, 2) : '—'; ?></td>
            <td class="text-center">
                <?php if ($logged > 0): ?>
                    <span style="<?php echo $over ? 'color:#dc2626;font-weight:700;' : ''; ?>">
                        <?php echo number_format($logged, 2); ?>
                    </span>
                <?php else: echo '—'; endif; ?>
            </td>
            <td class="text-center"><?php echo $t->deadline ? date('d M Y', strtotime($t->deadline)) : '—'; ?></td>
            <td class="text-center">
                <span class="badge" style="background:<?php echo $t->status_color ?: '#6b7280'; ?>;">
                    <?php echo htmlspecialchars($t->status_title ?? ''); ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default btn-sm" data-bs-dismiss="modal">
        <span data-feather="x" class="icon-16"></span> Close
    </button>
</div>
