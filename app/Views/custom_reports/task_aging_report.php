<?php
$buckets = [
    '0-2'  => ['label' => '0–2 Days',       'bg' => '#00bcd4'],
    '3-4'  => ['label' => '3–4 Days',        'bg' => '#00bcd4'],
    '5-6'  => ['label' => '5–6 Days',        'bg' => '#00acc1'],
    '7-8'  => ['label' => '7–8 Days',        'bg' => '#00acc1'],
    '9-10' => ['label' => '9–10 Days',       'bg' => '#0288d1'],
    '11+'  => ['label' => '11+ Days',        'bg' => '#1565c0'],
    'od5'  => ['label' => 'Due <=5',         'bg' => '#ff5722'],
    'od5+' => ['label' => 'Overdue > 5',     'bg' => '#b71c1c'],
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

        .aging-table {
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12px;
            white-space: nowrap;
            width: 100%;
        }
        .aging-table th, .aging-table td {
            border-right: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            padding: 5px 8px;
            vertical-align: middle;
        }
        .aging-table thead tr:first-child th { border-top: 1px solid #cbd5e1; }
        .aging-table th:first-child, .aging-table td:first-child { border-left: 1px solid #cbd5e1; }
        .aging-table th { background: #1e3a8a; color: #fff; font-weight: 600; }

        /* Sticky header (single row) */
        .aging-table thead th { position: sticky; top: 0; z-index: 4; }
        .aging-table thead th:not(.sc) { z-index: 1; }
        .aging-table thead .sc { z-index: 100; }

        /* Sticky columns */
        .aging-table .sc { position: sticky; z-index: 2; }
        .aging-table .sc-team {
            left: 0;
            min-width: 70px; width: 70px;
            text-align: center; font-weight: 700;
            word-break: break-word; white-space: normal;
        }
        .aging-table .sc-proj {
            left: 70px;
            min-width: 150px; width: 150px; max-width: 150px;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            box-shadow: 3px 0 6px -2px rgba(0,0,0,.15);
        }

        .aging-table thead .sc-team,
        .aging-table thead .sc-proj { background: #1e3a8a; color: #fff; }

        /* Body sticky col backgrounds */
        .aging-table tbody td.sc-team { background: #e8eaf6; }
        .aging-table tbody td.sc-proj { background: #f0f4ff; font-weight: 600; }

        /* Continuation cells (no top border = looks merged with row above) */
        .aging-table tbody td.sc-cont { border-top-color: transparent !important; }

        /* Zebra */
        .aging-table tbody tr:nth-child(odd)  td { background: #f8faff; }
        .aging-table tbody tr:nth-child(even) td { background: #eef3ff; }
        .aging-table tbody tr:nth-child(odd)  td.sc-team { background: #e8eaf6; }
        .aging-table tbody tr:nth-child(even) td.sc-team { background: #dde1f5; }
        .aging-table tbody tr:nth-child(odd)  td.sc-proj { background: #f0f4ff; }
        .aging-table tbody tr:nth-child(even) td.sc-proj { background: #e6edff; }
        .aging-table tbody tr:nth-child(odd)  td.sc-cont { background: #e8eaf6; }
        .aging-table tbody tr:nth-child(even) td.sc-cont { background: #dde1f5; }

        /* Hover */
        .aging-table tbody tr { cursor: default; }
        .aging-table tbody tr:hover td { filter: brightness(0.96); }

        /* Bucket count cells */
        .bucket-count { text-align: center; min-width: 70px; }
        .count-badge {
            display: inline-block; min-width: 28px;
            padding: 2px 8px; border-radius: 12px;
            font-weight: 700; font-size: 13px;
            text-decoration: none; cursor: pointer;
        }
        .count-badge.has-tasks { background: #dc2626; color: #fff; }
        .count-badge.has-tasks:hover { background: #b91c1c; color: #fff; }
        .count-badge.no-tasks  { color: #94a3b8; background: transparent; cursor: default; font-weight: 400; }

        /* Project link */
        .project-link { color: #1e3a8a; font-weight: 600; text-decoration: none; }
        .project-link:hover { text-decoration: underline; }

        .aging-title    { text-align:center; color:#1e3a8a; font-weight:700; font-size:14px; margin-bottom:2px; }
        .aging-subtitle { text-align:center; color:#64748b; font-size:11px; margin-bottom:8px; }

        @media print {
            .aging-scroll { overflow: visible; max-height: none; }
            .aging-table .sc { position: static; }
            .aging-table { border-collapse: collapse; }
            .count-badge.has-tasks { background: #dc2626 !important; -webkit-print-color-adjust: exact; }
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
                <th class="sc sc-team">Team</th>
                <th class="sc sc-proj">Project</th>
                <?php foreach ($buckets as $key => $b): ?>
                <th class="bucket-count" style="background:<?php echo $b['bg']; ?>;color:#fff;text-align:center;min-width:70px;">
                    <?php echo $b['label']; ?>
                </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($aging_tree as $tid => $team):
            $first_team = true;
            foreach ($team['projects'] as $pid => $proj):
                // Count tasks per bucket for this project
                $counts = array_fill_keys(array_keys($buckets), 0);
                foreach ($proj['tasks'] as $task) {
                    if (isset($counts[$task->bucket])) {
                        $counts[$task->bucket]++;
                    }
                }
                ?>
                <tr>
                    <!-- Team cell: show name only on first project of team -->
                    <td class="sc sc-team<?php echo $first_team ? '' : ' sc-cont'; ?>">
                        <?php echo $first_team ? htmlspecialchars($team['name']) : ''; ?>
                    </td>
                    <!-- Project cell -->
                    <td class="sc sc-proj" title="<?php echo htmlspecialchars($proj['name']); ?>">
                        <a class="project-link" href="#"
                           data-project-id="<?php echo $pid; ?>"
                           data-team-id="<?php echo $tid; ?>">
                            <?php echo htmlspecialchars($proj['name']); ?>
                        </a>
                    </td>
                    <!-- Bucket count cells -->
                    <?php foreach ($buckets as $bkey => $b):
                        $cnt = $counts[$bkey];
                    ?>
                    <td class="bucket-count">
                        <?php if ($cnt > 0): ?>
                        <a class="count-badge count-link" href="#"
                           style="background:<?php echo $b['bg']; ?>;color:#fff;"
                           data-project-id="<?php echo $pid; ?>"
                           data-team-id="<?php echo $tid; ?>"
                           data-bucket="<?php echo $bkey; ?>"><?php echo $cnt; ?></a>
                        <?php else: ?>
                        <span class="count-badge no-tasks">0</span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php
                $first_team = false;
            endforeach;
        endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>

</div>
<script>
(function () {
    var par = window.parent;

    /* ── Grab-to-scroll ── */
    var el = document.getElementById('agingScrollWrap');
    if (el) {
        var isDown = false, startX, startY, scrollLeft, scrollTop, moved = false;
        el.addEventListener('mousedown', function (e) {
            if (e.target.closest('a')) return; /* let links fire naturally */
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
    }

    /* ── Count badge click → open bucket task modal ── */
    document.querySelectorAll('.count-link').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            if (typeof par.openAgingBucketModal === 'function') {
                par.openAgingBucketModal(
                    this.getAttribute('data-project-id'),
                    this.getAttribute('data-bucket'),
                    this.getAttribute('data-team-id')
                );
            }
        });
    });

    /* ── Project name click → effort modal ── */
    document.querySelectorAll('.project-link').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            if (typeof par.openCrmProjectEffortModal === 'function') {
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
    tbl.querySelectorAll('.no-tasks').forEach(function (s) {
        s.replaceWith(document.createTextNode('0'));
    });
    var ws = XLSX.utils.table_to_sheet(tbl, { raw: false });
    XLSX.utils.book_append_sheet(wb, ws, 'Aging Report');
    XLSX.writeFile(wb, 'Task_Aging_Report_<?php echo date('Y-m-d'); ?>.xlsx');
}
</script>
</body>
</html>
