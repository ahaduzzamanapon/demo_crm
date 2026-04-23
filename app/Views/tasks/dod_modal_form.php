<?php
// dod_modal_form.php — Definition of Done checklist modal
$v = $existing_dod; // shorthand for previously saved values (may be null)

$yn_yes = function($field, $v) {
    return ($v && $v->$field === 'Yes') ? 'checked' : '';
};
$yn_no = function($field, $v) {
    return ($v && $v->$field === 'No') ? 'checked' : '';
};
?>

<div class="modal-header">
    <h5 class="modal-title">
        <i data-feather="check-square" class="icon-16 mr5"></i>
        Definition of Done — Task #<?php echo $task_id; ?>: <em><?php echo esc($task_info->title); ?></em>
    </h5>
</div>

<?php echo form_open(get_uri("tasks/save_dod"), array("id" => "dod_form", "class" => "general-form", "role" => "form")); ?>
<input type="hidden" name="task_id" value="<?php echo $task_id; ?>">

<div class="modal-body" style="max-height:78vh; overflow-y:auto; padding:0;">

    <style>
        .dod-table { width:100%; border-collapse:collapse; font-size:13px; }
        .dod-table th { background:#f8fafc; color:#374151; font-weight:600; border:1px solid #e2e8f0; padding:8px 10px; text-align:left; }
        .dod-table td { border:1px solid #e2e8f0; padding:8px 10px; vertical-align:middle; }
        .dod-table td.sl { text-align:center; width:38px; color:#6b7280; font-weight:600; }
        .dod-yn { display:flex; gap:14px; align-items:center; }
        .dod-yn label { display:flex; align-items:center; gap:5px; cursor:pointer; font-weight:500; margin:0; }
        .dod-yn input[type=radio] { accent-color:#6366f1; width:15px; height:15px; cursor:pointer; }
        .dod-note { border:1px solid #d1d5db; border-radius:5px; padding:5px 8px; width:100%; font-size:12px; resize:vertical; min-height:32px; }
        .dod-meta-row { display:flex; gap:16px; padding:14px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; flex-wrap:wrap; }
        .dod-meta-row .meta-field { flex:1; min-width:200px; }
        .dod-meta-row label { font-weight:600; font-size:12px; color:#374151; display:block; margin-bottom:4px; }
        .dod-meta-row input { border:1px solid #d1d5db; border-radius:5px; padding:6px 10px; width:100%; font-size:13px; }
        .dod-header-info { display:flex; gap:20px; background:#f1f5f9; border-bottom:1px solid #e2e8f0; padding:12px 20px; flex-wrap:wrap; font-size:12px; color:#4b5563; }
        .dod-header-info span { font-weight:600; color:#111827; }
        .dod-submitted-badge { background:#dcfce7; color:#15803d; border-radius:4px; padding:2px 8px; font-size:11px; font-weight:600; display:inline-block; margin-left:8px; }
    </style>

    <!-- Header information row -->
    <div class="dod-header-info">
        <div>Task ID: <span>#<?php echo $task_id; ?></span></div>
        <div>Task: <span><?php echo esc($task_info->title); ?></span></div>
        <?php if ($task_info->project_title): ?>
        <div>Project: <span><?php echo esc($task_info->project_title); ?></span></div>
        <?php endif; ?>
        <div>Date: <span><?php echo format_to_date(get_today_date(), false); ?></span></div>
        <?php if ($v): ?>
        <div><span class="dod-submitted-badge">✓ Previously Submitted</span></div>
        <?php endif; ?>
    </div>

    <!-- Checklist table -->
    <div style="padding:16px 20px 8px;">
        <table class="dod-table">
            <thead>
                <tr>
                    <th class="sl">SL</th>
                    <th style="min-width:220px;">Activity</th>
                    <th style="width:110px; text-align:center;">Yes / No</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $items = array(
                    array('label' => 'Unit Tests Passed',         'yes_field' => 'unit_tests_passed',     'note_field' => 'unit_tests_note'),
                    array('label' => 'Functional Requirements Met','yes_field' => 'func_req_met',          'note_field' => 'func_req_note'),
                    array('label' => 'Followed Design Architectural principles and guidelines', 'yes_field' => 'design_followed', 'note_field' => 'design_note'),
                    array('label' => 'Code Review Completed',     'yes_field' => 'code_review_completed', 'note_field' => 'code_review_note'),
                    array('label' => 'Documentation Updated',     'yes_field' => 'docs_updated',          'note_field' => 'docs_note'),
                );
                foreach ($items as $i => $item):
                    $yf = $item['yes_field'];
                    $nf = $item['note_field'];
                    $note_val = $v ? esc($v->$nf) : '';
                ?>
                <tr>
                    <td class="sl"><?php echo $i + 1; ?></td>
                    <td><?php echo $item['label']; ?></td>
                    <td>
                        <div class="dod-yn" style="justify-content:center;">
                            <label>
                                <input type="radio" name="<?php echo $yf; ?>" value="Yes" <?php echo $yn_yes($yf, $v); ?> required> Yes
                            </label>
                            <label>
                                <input type="radio" name="<?php echo $yf; ?>" value="No" <?php echo $yn_no($yf, $v); ?>> No
                            </label>
                        </div>
                    </td>
                    <td>
                        <textarea name="<?php echo $nf; ?>" class="dod-note" placeholder="Optional note..."><?php echo $note_val; ?></textarea>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Signature fields -->
    <div class="dod-meta-row">
        <div class="meta-field">
            <label>Developer Note / Comment:</label>
            <textarea name="developer_note" class="dod-note" placeholder="Any additional notes from the developer..."><?php echo $v ? esc($v->developer_note) : ''; ?></textarea>
        </div>
        <div class="meta-field">
            <label>Lead Note / Comment:</label>
            <textarea name="lead_note" class="dod-note" placeholder="Any additional notes from the team lead..."><?php echo $v ? esc($v->lead_note) : ''; ?></textarea>
        </div>
    </div>

</div><!-- /.modal-body -->

<div class="modal-footer">
    <button type="submit" class="btn btn-primary" id="dod-submit-btn">
        <i data-feather="send" class="icon-16"></i> Submit to QA
    </button>
    <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
</div>

<?php echo form_close(); ?>

<script>
$(document).ready(function() {
    $('#dod_form').appForm({
        isModal: true,
        closeModalOnSuccess: true,
        onSuccess: function(response) {
            if (response.success) {
                // Refresh task table row if present
                if (response.id) {
                    try { $("#task-table").appTable({ newData: response.data, dataId: response.id }); } catch(e) {}
                }
                // Trigger kanban reload if dragged to kanban
                window.reloadKanban = true;
                if ($("#reload-kanban-button:visible").length) {
                    setTimeout(function() { $("#reload-kanban-button").trigger("click"); }, 400);
                }
            }
        }
    });
    feather.replace();
});
</script>
