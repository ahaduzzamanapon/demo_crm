<?php
$buckets = [
    '1-2'  => ['label' => '1–2 Days',        'bg' => '#00bcd4'],
    '3-4'  => ['label' => '3–4 Days',        'bg' => '#00bcd4'],
    '5-6'  => ['label' => '5–6 Days',        'bg' => '#00acc1'],
    '7-8'  => ['label' => '7–8 Days',        'bg' => '#00acc1'],
    '9-10' => ['label' => '9–10 Days',       'bg' => '#0288d1'],
    '11+'  => ['label' => 'Due (11+ Days)',   'bg' => '#2196f3'],
    'od5'  => ['label' => 'Overdue ≤5 Days', 'bg' => '#ff5722'],
    'od5+' => ['label' => 'Overdue >5 Days', 'bg' => '#b71c1c'],
    'none' => ['label' => 'No Deadline',     'bg' => '#9e9e9e'],
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Task Completion Aging Report</title>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; margin: 0; }
        .aging-wrap { padding: 8px 10px; }

        /* ── Scrollable wrapper — same pattern as effort-table-wrap ── */
        .aging-scroll {
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 100px);
            position: relative;
            cursor: grab;
            user-select: none;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }

        /* ── Table base ── */
        .aging-table {
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12px;
            white-space: nowrap;
        }
        .aging-table th, .aging-table td {
            border-right: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            padding: 5px 8px;
            vertical-align: middle;
        }
        .aging-table thead tr:first-child th { border-top: 1px solid #cbd5e1; }
        .aging-table th:first-child, .aging-table td:first-child { border-left: 1px solid #cbd5e1; }

        /* ── All headers: dark navy base ── */
        .aging-table th { background: #1e3a8a; color: #fff; font-weight: 600; }

        /* ── Sticky header rows (top) ── */
        .aging-table thead tr:nth-child(1) th { position: sticky; top: 0;    z-index: 4; }
        .aging-table thead tr:nth-child(2) th { position: sticky; top: 34px; z-index: 4; } /* overridden by JS */

        /* Non-sticky bucket headers stay below sticky-left columns */
        .aging-table thead tr:nth-child(1) th:not(.sc) { z-index: 1; }
        .aging-table thead tr:nth-child(2) th:not(.sc) { z-index: 1; }

        /* Corner cells: both X and Y sticky — highest z-index */
        .aging-table thead .sc { z-index: 100; }

        /* ── Sticky column base ── */
        .aging-table .sc { position: sticky; z-index: 2; }

        /* Team column */
        .aging-table .sc-team {
            left: 0;
            min-width: 65px; width: 65px;
            text-align: center;
            font-weight: 700;
            word-break: break-word; white-space: normal;
        }
        /* Project column */
        .aging-table .sc-proj {
            left: 65px;
            min-width: 120px; width: 120px;
            text-align: left;
            white-space: normal; word-break: break-word;
            box-shadow: 3px 0 6px -2px rgba(0,0,0,.15);
        }

        /* Header sticky cols */
        .aging-table thead .sc-team,
        .aging-table thead .sc-proj  { background: #1e3a8a; color: #fff; }

        /* Body sticky cols */
        .aging-table tbody td.sc-team { background: #e8eaf6; }
        .aging-table tbody td.sc-proj { background: #f0f4ff; }

        /* ── Bucket column widths ── */
        .bucket-task     { min-width: 140px; text-align: left; vertical-align: top; white-space: normal; word-break: break-word; }
        .bucket-deadline { min-width: 70px;  text-align: center; vertical-align: top; white-space: nowrap; }

        /* ── Zebra striping ── */
        .aging-table tbody tr:nth-child(odd)  td { background: #f8faff; }
        .aging-table tbody tr:nth-child(even) td { background: #eef3ff; }
        .aging-table tbody tr:nth-child(odd)  td.sc-team { background: #e8eaf6; }
        .aging-table tbody tr:nth-child(even) td.sc-team { background: #dde1f5; }
        .aging-table tbody tr:nth-child(odd)  td.sc-proj { background: #f0f4ff; }
        .aging-table tbody tr:nth-child(even) td.sc-proj { background: #e6edff; }

        /* No deadline: tint data cells only */
        .no-deadline td:not(.sc-team):not(.sc-proj) { background: #fee2e2 !important; }

        /* Hover */
        .aging-table tbody tr { cursor: pointer; }
        .aging-table tbody tr:hover td { filter: brightness(0.96); }

        /* ── Links ── */
        .task-link    { color: #1e3a8a; font-weight: 500; text-decoration: none; display: block; }
        .task-link:hover { text-decoration: underline; }
        .project-link { color: #1e3a8a; font-weight: 600; text-decoration: none; display: block; }
        .project-link:hover { text-decoration: underline; }

        .deadline-val { color: #64748b; font-size: 11px; display: block; }

        /* ── Title area ── */
        .aging-title    { text-align:center; color:#1e3a8a; font-weight:700; font-size:14px; margin-bottom:2px; }
        .aging-subtitle { text-align:center; color:#64748b; font-size:11px; margin-bottom:8px; }

        /* Continuation cells (no top border = looks merged) */
        .aging-table tbody td.sc-cont { border-top-color: transparent !important; }

        @media print {
            .aging-scroll { overflow: visible; max-height: none; }
            .aging-table .sc { position: static; }
            .aging-table { border-collapse: collapse; }
        }
    </style>
</head>
<body>
<div class="aging-wrap">

    <div style="position:relative; margin-bottom:8px;">
        <div class="aging-title">📋 Task Completion Aging Report</div>
        <div class="aging-subtitle">as of <?php echo date('d M Y'); ?></div>
        <button onclick="exportAgingExcel()" style="position:absolute;right:0;top:0;padding:4px 14px;background:#1d6f42;color:#fff;border:none;border-radius:4px;font-size:12px;cursor:pointer;">⬇ Export Excel</button>
    </div>

    <?php if (empty($aging_tree)): ?>
        <p style="color:#6b7280; text-align:center; padding:30px;">No tasks found.</p>
    <?php else: ?>
    <div class="aging-scroll" id="agingScrollWrap">
    <table class="aging-table" id="agingTable">
        <thead>
            <tr>
                <th class="sc sc-team" rowspan="2">Team</th>
                <th class="sc sc-proj" rowspan="2">Project</th>
                <?php foreach ($buckets as $key => $b): ?>
                <th colspan="2" style="background:<?php echo $b['bg']; ?>;color:#fff;text-align:center;">
                    <?php echo $b['label']; ?>
                </th>
                <?php endforeach; ?>
            </tr>
            <tr id="agingSubRow">
                <?php foreach ($buckets as $key => $b): ?>
                <th style="background:<?php echo $b['bg']; ?>;color:#fff;font-weight:400;font-size:10px;text-align:center;min-width:140px;">Task</th>
                <th style="background:<?php echo $b['bg']; ?>;color:#fff;font-weight:400;font-size:10px;text-align:center;min-width:70px;">Deadline</th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($aging_tree as $tid => $team):
            $first_team = true;
            foreach ($team['projects'] as $pid => $proj):
                $first_proj = true;
                $tasks = !empty($proj['tasks']) ? $proj['tasks'] : [null]; // at least one row

                foreach ($tasks as $task):
                    $no_dl  = ($task && (empty($task->deadline) || $task->deadline === '0000-00-00'));
                    $rowcls = $no_dl ? ' class="no-deadline"' : '';
                    echo '<tr' . $rowcls . '>';

                    // Team cell — every row, name only on first, no top-border on rest
                    $team_cls = 'sc sc-team' . ($first_team ? '' : ' sc-cont');
                    echo '<td class="' . $team_cls . '">' . ($first_team ? htmlspecialchars($team['name']) : '') . '</td>';

                    // Project cell — every row, name only on first of this project
                    $proj_cls = 'sc sc-proj' . ($first_proj ? '' : ' sc-cont');
                    if ($first_proj) {
                        echo '<td class="' . $proj_cls . '" title="' . htmlspecialchars($proj['name']) . '">';
                        echo '<a class="project-link" href="#" data-project-id="' . $pid . '" data-team-id="' . $tid . '">' . htmlspecialchars($proj['name']) . '</a>';
                        echo '</td>';
                    } else {
                        echo '<td class="' . $proj_cls . '"></td>';
                    }

                    $first_team = false;
                    $first_proj = false;

                    if ($task === null) {
                        // Empty project — blank bucket cells
                        foreach ($buckets as $k => $b) { echo '<td class="bucket-task"></td><td class="bucket-deadline"></td>'; }
                    } else {
                        $is_no_dl = (empty($task->deadline) || $task->deadline === '0000-00-00');
                        foreach ($buckets as $bkey => $b):
                            if ($task->bucket === $bkey):
                                echo '<td class="bucket-task">';
                                echo '<a class="task-link" href="#" data-task-id="' . $task->task_id . '">' . htmlspecialchars($task->task_title) . '</a>';
                                echo '<small style="color:#555;display:block;">' . htmlspecialchars($task->assigned_to_name) . '</small>';
                                echo '</td>';
                                echo '<td class="bucket-deadline">';
                                if (!$is_no_dl) {
                                    $d      = (int)$task->days_remaining;
                                    $dcolor = $d < 0 ? '#dc2626' : ($d <= 3 ? '#f59e0b' : '#16a34a');
                                    echo '<span class="deadline-val">' . date('d M', strtotime($task->deadline)) . '</span>';
                                    echo '<small style="color:' . $dcolor . ';font-weight:600;">' . ($d >= 0 ? '+' : '') . $d . 'd</small>';
                                } else {
                                    echo '<span style="color:#dc2626;font-size:10px;">No deadline</span>';
                                }
                                echo '</td>';
                            else:
                                echo '<td class="bucket-task"></td><td class="bucket-deadline"></td>';
                            endif;
                        endforeach;
                    }

                    echo '</tr>';
                endforeach; // tasks
            endforeach; // projects
        endforeach; // teams ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>

</div>
<script>
(function () {
    var par = window.parent;

    /* ── Fix sticky sub-header top offset ── */
    var row1 = document.querySelector('#agingTable thead tr:nth-child(1)');
    if (row1) {
        var h = row1.getBoundingClientRect().height;
        document.querySelectorAll('#agingSubRow th').forEach(function (th) {
            th.style.top = h + 'px';
        });
    }

    /* ── Grab-to-scroll (same as effort table) ── */
    var el = document.getElementById('agingScrollWrap');
    if (el) {
        var isDown = false, startX, startY, scrollLeft, scrollTop, moved = false;
        el.addEventListener('mousedown', function (e) {
            isDown = true; moved = false;
            el.style.cursor = 'grabbing';
            startX     = e.pageX - el.offsetLeft;
            startY     = e.pageY - el.offsetTop;
            scrollLeft = el.scrollLeft;
            scrollTop  = el.scrollTop;
        });
        el.addEventListener('mouseleave', function () { isDown = false; el.style.cursor = 'grab'; });
        el.addEventListener('mouseup',    function () { isDown = false; el.style.cursor = 'grab'; });
        el.addEventListener('mousemove',  function (e) {
            if (!isDown) return;
            e.preventDefault();
            moved = true;
            el.scrollLeft = scrollLeft - (e.pageX - el.offsetLeft - startX) * 1.5;
            el.scrollTop  = scrollTop  - (e.pageY - el.offsetTop  - startY) * 1.5;
        });
        /* Prevent link clicks when dragging */
        el.addEventListener('click', function (e) {
            if (moved) e.stopPropagation();
        }, true);
    }

    /* ── Task click → CRM task modal ── */
    document.querySelectorAll('.task-link').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            if (!moved && typeof par.openCrmTaskModal === 'function') {
                par.openCrmTaskModal(this.getAttribute('data-task-id'));
            }
        });
    });

    /* ── Project click → Project Effort modal ── */
    document.querySelectorAll('.project-link').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            if (!moved && typeof par.openCrmProjectEffortModal === 'function') {
                par.openCrmProjectEffortModal(
                    this.getAttribute('data-project-id'),
                    this.getAttribute('data-team-id')
                );
            }
        });
    });
})();

function exportAgingExcel() {
    var wb  = XLSX.utils.book_new();
    var tbl = document.getElementById('agingTable').cloneNode(true);
    tbl.querySelectorAll('a').forEach(function (a) {
        a.replaceWith(document.createTextNode(a.textContent));
    });
    var ws = XLSX.utils.table_to_sheet(tbl, { raw: false });
    XLSX.utils.book_append_sheet(wb, ws, 'Aging Report');
    XLSX.writeFile(wb, 'Task_Aging_Report_<?php echo date('Y-m-d'); ?>.xlsx');
}
</script>
</body>
</html>
