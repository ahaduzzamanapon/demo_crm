<div class="modal-header">
    <h5 class="modal-title" id="ajaxModalTitle"><i data-feather="list" class="icon-16"></i> <?php echo $modal_title; ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body clearfix">
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php echo app_lang('id'); ?></th>
                    <th><?php echo app_lang('title'); ?></th>
                    <th><?php echo app_lang('project'); ?></th>
                    <th><?php echo app_lang('assigned_to'); ?></th>
                    <th><?php echo app_lang('status'); ?></th>
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
                        <span class="avatar avatar-xs mr10">
                            <img src="<?php echo get_avatar($task->assigned_to_avatar); ?>" alt="..." />
                        </span>
                        <?php echo htmlspecialchars($task->assigned_to_user); ?>
                    </td>
                    <td>
                        <span class="badge" style="background-color: <?php echo $task->status_color; ?>;">
                            <?php echo htmlspecialchars($task->status_title); ?>
                        </span>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($tasks)) { ?>
                <tr><td colspan="5" class="text-center text-muted">No tasks found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
</div>
