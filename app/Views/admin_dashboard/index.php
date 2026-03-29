<div id="page-content" class="page-wrapper clearfix">
    <div class="card">
        <div class="page-title clearfix">
            <h1>
                <?php echo app_lang('admin_dashboard'); ?>
            </h1>
        </div>
        <div class="card-body p0">

            <!-- Admin Dashboard Tabs -->
            <div class="admin-dashboard-tabs-wrapper">
                <ul class="nav admin-dash-tabs" id="adminDashboardTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="tab-project-progress" data-bs-toggle="tab" href="#project-progress" role="tab" aria-controls="project-progress" aria-selected="true">
                            <i data-feather="bar-chart-2" class="icon-16 mr5"></i>
                            Project Progress
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tab-resource-utilization" data-bs-toggle="tab" href="#resource-utilization" role="tab" aria-controls="resource-utilization" aria-selected="false">
                            <i data-feather="users" class="icon-16 mr5"></i>
                            Resource Utilization Report
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tab-billable-piechart" data-bs-toggle="tab" href="#billable-piechart" role="tab" aria-controls="billable-piechart" aria-selected="false">
                            <i data-feather="pie-chart" class="icon-16 mr5"></i>
                            Billable Pie Chart
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tab-employee-performance" data-bs-toggle="tab" href="#employee-performance" role="tab" aria-controls="employee-performance" aria-selected="false">
                            <i data-feather="trending-up" class="icon-16 mr5"></i>
                            Date Wise Employee Performance Report
                        </a>
                    </li>
                </ul>

                <div class="tab-content admin-dash-tab-content" id="adminDashboardTabContent">

                    <!-- Tab 1: Project Progress -->
                    <div class="tab-pane fade show active" id="project-progress" role="tabpanel" aria-labelledby="tab-project-progress">
                        <div class="admin-tab-inner">
                            <div class="admin-tab-placeholder">
                                <i data-feather="bar-chart-2" class="placeholder-icon"></i>
                                <h4 class="placeholder-title">Project Progress</h4>
                                <p class="placeholder-text">Project progress charts and statistics will be displayed here.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Resource Utilization Report -->
                    <div class="tab-pane fade" id="resource-utilization" role="tabpanel" aria-labelledby="tab-resource-utilization">
                        <div class="admin-tab-inner">
                            <div class="admin-tab-placeholder">
                                <i data-feather="users" class="placeholder-icon"></i>
                                <h4 class="placeholder-title">Resource Utilization Report</h4>
                                <p class="placeholder-text">Resource allocation and utilization data will be displayed here.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Billable Pie Chart -->
                    <div class="tab-pane fade" id="billable-piechart" role="tabpanel" aria-labelledby="tab-billable-piechart">
                        <div class="admin-tab-inner" id="billable-tab-inner">

                            <!-- Loading State -->
                            <div id="billable-loading" class="billable-loading-state">
                                <div class="billable-spinner"></div>
                                <p>Loading chart data…</p>
                            </div>

                            <!-- Chart Area (hidden until loaded) -->
                            <div id="billable-chart-area" style="display:none;">

                                <!-- Header row -->
                                <div class="billable-header">
                                    <div>
                                        <h3 class="billable-title">Project Billable Distribution</h3>
                                        <p class="billable-subtitle">Breakdown of all projects by billing type</p>
                                    </div>
                                    <div class="billable-total-badge">
                                        <span class="total-label">Total Projects</span>
                                        <span class="total-count" id="billable-total-count">0</span>
                                    </div>
                                </div>

                                <!-- Stat cards -->
                                <div class="billable-stat-cards">
                                    <div class="bsc-card bsc-billable">
                                        <div class="bsc-icon">
                                            <i data-feather="check-circle"></i>
                                        </div>
                                        <div class="bsc-info">
                                            <span class="bsc-count" id="stat-billable">0</span>
                                            <span class="bsc-label">Billable</span>
                                        </div>
                                        <div class="bsc-pct" id="stat-billable-pct">0%</div>
                                    </div>
                                    <div class="bsc-card bsc-nonbillable">
                                        <div class="bsc-icon">
                                            <i data-feather="x-circle"></i>
                                        </div>
                                        <div class="bsc-info">
                                            <span class="bsc-count" id="stat-nonbillable">0</span>
                                            <span class="bsc-label">Non-Billable</span>
                                        </div>
                                        <div class="bsc-pct" id="stat-nonbillable-pct">0%</div>
                                    </div>
                                    <div class="bsc-card bsc-none">
                                        <div class="bsc-icon">
                                            <i data-feather="help-circle"></i>
                                        </div>
                                        <div class="bsc-info">
                                            <span class="bsc-count" id="stat-none">0</span>
                                            <span class="bsc-label">None / Not Set</span>
                                        </div>
                                        <div class="bsc-pct" id="stat-none-pct">0%</div>
                                    </div>
                                </div>

                                <!-- Chart + Legend row -->
                                <div class="billable-chart-row">
                                    <div class="billable-chart-wrap">
                                        <canvas id="billablePieChart" width="320" height="320"></canvas>
                                        <div class="chart-center-label">
                                            <span id="chart-center-count">0</span>
                                            <span class="chart-center-sub">Projects</span>
                                        </div>
                                    </div>
                                    <div class="billable-legend">
                                        <div class="bl-item">
                                            <span class="bl-dot" style="background:#4ade80;"></span>
                                            <div class="bl-text">
                                                <span class="bl-name">Billable</span>
                                                <span class="bl-desc">Projects that are charged to a client</span>
                                            </div>
                                            <span class="bl-val" id="legend-billable">0</span>
                                        </div>
                                        <div class="bl-item">
                                            <span class="bl-dot" style="background:#f87171;"></span>
                                            <div class="bl-text">
                                                <span class="bl-name">Non-Billable</span>
                                                <span class="bl-desc">Internal or pro-bono projects</span>
                                            </div>
                                            <span class="bl-val" id="legend-nonbillable">0</span>
                                        </div>
                                        <div class="bl-item">
                                            <span class="bl-dot" style="background:#cbd5e1;"></span>
                                            <div class="bl-text">
                                                <span class="bl-name">None / Not Set</span>
                                                <span class="bl-desc">Billing type not configured</span>
                                            </div>
                                            <span class="bl-val" id="legend-none">0</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- End Chart Area -->

                        </div>
                    </div>

                    <!-- Tab 4: Date Wise Employee Performance Report -->
                    <div class="tab-pane fade" id="employee-performance" role="tabpanel" aria-labelledby="tab-employee-performance">
                        <div class="admin-tab-inner perf-tab-inner">

                            <!-- Best Performed Days of the Month widget -->
                            <div class="bpd-widget" id="bpd-widget">
                                <div class="bpd-header">Best Performed Days of the Month</div>
                                <div class="bpd-body" id="bpd-body">
                                    <div class="bpd-skeleton">
                                        <div class="bpd-sk-col"></div>
                                        <div class="bpd-sk-col"></div>
                                        <div class="bpd-sk-col"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filter bar (single date) -->
                            <div class="perf-filter-bar">
                                <div class="perf-filter-group">
                                    <label for="perf-report-date">Select Date</label>
                                    <input type="date" id="perf-report-date" class="perf-date-input" />
                                </div>
                                <button id="perf-generate-btn" class="perf-btn">
                                    <i data-feather="refresh-cw" class="perf-btn-icon"></i>
                                    Generate Report
                                </button>
                                <span id="perf-working-days-badge" class="perf-wd-badge" style="display:none;"></span>
                            </div>

                            <!-- Loading -->
                            <div id="perf-loading" class="perf-loading" style="display:none;">
                                <div class="perf-spinner"></div>
                                <span>Generating report…</span>
                            </div>

                            <!-- Report output -->
                            <div id="perf-output"></div>

                        </div>
                    </div>

                </div>
            </div>
            <!-- End Admin Dashboard Tabs -->

        </div>
    </div>
</div>

<style>
    /* ── Admin Dashboard Tab Wrapper ── */
    .admin-dashboard-tabs-wrapper {
        background: #fff;
        border-radius: 0 0 6px 6px;
    }

    /* ── Tab Nav ── */
    .admin-dash-tabs {
        display: flex;
        flex-wrap: wrap;
        border-bottom: 2px solid #e8eaf0;
        padding: 0 20px;
        gap: 0;
        background: #f8f9fc;
        border-radius: 0;
    }

    .admin-dash-tabs .nav-item {
        margin-bottom: -2px;
    }

    .admin-dash-tabs .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 14px 20px;
        font-size: 13.5px;
        font-weight: 500;
        color: #6b7a99;
        border: none;
        border-bottom: 2px solid transparent;
        background: transparent;
        border-radius: 0;
        cursor: pointer;
        transition: color 0.2s ease, border-color 0.2s ease;
        white-space: nowrap;
        text-decoration: none;
    }

    .admin-dash-tabs .nav-link:hover {
        color: #4361ee;
        background: rgba(67, 97, 238, 0.06);
        border-bottom-color: rgba(67, 97, 238, 0.3);
    }

    .admin-dash-tabs .nav-link.active {
        color: #4361ee;
        border-bottom: 2px solid #4361ee;
        background: #fff;
        font-weight: 600;
    }

    .admin-dash-tabs .nav-link svg.icon-16 {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
        margin-right: 0;
    }

    .mr5 { margin-right: 5px; }

    /* ── Tab Content ── */
    .admin-dash-tab-content {
        padding: 30px 24px;
    }

    .admin-tab-inner {
        min-height: 360px;
    }

    /* ── Placeholder State ── */
    .admin-tab-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 320px;
        text-align: center;
        color: #b0b8d1;
        user-select: none;
    }

    .admin-tab-placeholder .placeholder-icon {
        width: 52px;
        height: 52px;
        stroke: #c8d0e7;
        margin-bottom: 16px;
    }

    .admin-tab-placeholder .placeholder-title {
        font-size: 16px;
        font-weight: 600;
        color: #8a94b2;
        margin-bottom: 8px;
    }

    .admin-tab-placeholder .placeholder-text {
        font-size: 13px;
        color: #b0b8d1;
        max-width: 360px;
        line-height: 1.6;
        margin: 0;
    }

    /* ════════════════════════════
       BILLABLE PIE CHART STYLES
    ════════════════════════════ */

    /* Loading spinner */
    .billable-loading-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 360px;
        gap: 14px;
        color: #94a3b8;
        font-size: 13px;
    }

    .billable-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid #e2e8f0;
        border-top-color: #4361ee;
        border-radius: 50%;
        animation: bspin 0.7s linear infinite;
    }

    @keyframes bspin { to { transform: rotate(360deg); } }

    /* Header */
    .billable-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .billable-title {
        font-size: 17px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 4px;
    }

    .billable-subtitle {
        font-size: 12.5px;
        color: #94a3b8;
        margin: 0;
    }

    .billable-total-badge {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        background: #f1f5f9;
        border-radius: 10px;
        padding: 10px 18px;
    }

    .total-label {
        font-size: 11px;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 600;
    }

    .total-count {
        font-size: 26px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.1;
    }

    /* Stat Cards */
    .billable-stat-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 30px;
    }

    .bsc-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        border-radius: 12px;
        border: 1px solid transparent;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .bsc-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }

    .bsc-billable    { background: #f0fdf4; border-color: #bbf7d0; }
    .bsc-nonbillable { background: #fef2f2; border-color: #fecaca; }
    .bsc-none        { background: #f8fafc; border-color: #e2e8f0; }

    .bsc-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .bsc-billable    .bsc-icon { background: #dcfce7; }
    .bsc-nonbillable .bsc-icon { background: #fee2e2; }
    .bsc-none        .bsc-icon { background: #f1f5f9; }

    .bsc-billable    .bsc-icon svg { stroke: #16a34a; width:18px; height:18px; }
    .bsc-nonbillable .bsc-icon svg { stroke: #dc2626; width:18px; height:18px; }
    .bsc-none        .bsc-icon svg { stroke: #64748b; width:18px; height:18px; }

    .bsc-info {
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .bsc-count {
        font-size: 22px;
        font-weight: 800;
        line-height: 1;
        color: #1e293b;
    }

    .bsc-label {
        font-size: 11.5px;
        color: #64748b;
        margin-top: 2px;
        font-weight: 500;
    }

    .bsc-pct {
        font-size: 13px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .bsc-billable    .bsc-pct { background: #dcfce7; color: #16a34a; }
    .bsc-nonbillable .bsc-pct { background: #fee2e2; color: #dc2626; }
    .bsc-none        .bsc-pct { background: #f1f5f9; color: #64748b; }

    /* Chart Row */
    .billable-chart-row {
        display: flex;
        align-items: center;
        gap: 40px;
        flex-wrap: wrap;
    }

    /* Donut chart wrap with center label */
    .billable-chart-wrap {
        position: relative;
        width: 280px;
        height: 280px;
        flex-shrink: 0;
    }

    .billable-chart-wrap canvas {
        width: 280px !important;
        height: 280px !important;
    }

    .chart-center-label {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
    }

    #chart-center-count {
        display: block;
        font-size: 28px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1;
    }

    .chart-center-sub {
        display: block;
        font-size: 11px;
        color: #94a3b8;
        font-weight: 500;
        margin-top: 3px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    /* Legend */
    .billable-legend {
        flex: 1;
        min-width: 220px;
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .bl-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .bl-item:last-child { border-bottom: none; }

    .bl-dot {
        width: 14px;
        height: 14px;
        border-radius: 4px;
        flex-shrink: 0;
    }

    .bl-text {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .bl-name {
        font-size: 13.5px;
        font-weight: 600;
        color: #334155;
    }

    .bl-desc {
        font-size: 11.5px;
        color: #94a3b8;
        margin-top: 1px;
    }

    .bl-val {
        font-size: 16px;
        font-weight: 800;
        color: #1e293b;
        min-width: 28px;
        text-align: right;
    }

    /* ── Responsive ── */
    @media (max-width: 700px) {
        .admin-dash-tabs { padding: 0 10px; }
        .admin-dash-tabs .nav-link { padding: 12px; font-size: 12px; }
        .admin-dash-tab-content { padding: 20px 14px; }
        .billable-stat-cards { grid-template-columns: 1fr; }
        .billable-chart-row { flex-direction: column; align-items: center; }
    }

    @media (max-width: 900px) {
        .billable-stat-cards { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<script type="text/javascript">
$(document).ready(function () {
    feather.replace();

    var billableChartInstance = null;
    var billableLoaded = false;

    // Load chart when the billable tab is shown
    $('a[id="tab-billable-piechart"]').on('shown.bs.tab', function () {
        if (!billableLoaded) {
            loadBillableChart();
        }
    });

    function pct(val, total) {
        if (!total || total === 0) return '0%';
        return Math.round((val / total) * 100) + '%';
    }

    function loadBillableChart() {
        $.ajax({
            url: '<?php echo get_uri("admin_dashboard/get_billable_chart_data"); ?>',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                billableLoaded = true;

                var billable     = data.billable     || 0;
                var non_billable = data.non_billable || 0;
                var none         = data.none         || 0;
                var total        = data.total        || 0;

                // Populate stat cards
                $('#stat-billable').text(billable);
                $('#stat-nonbillable').text(non_billable);
                $('#stat-none').text(none);
                $('#stat-billable-pct').text(pct(billable, total));
                $('#stat-nonbillable-pct').text(pct(non_billable, total));
                $('#stat-none-pct').text(pct(none, total));

                // Populate total badge & center
                $('#billable-total-count').text(total);
                $('#chart-center-count').text(total);

                // Populate legend
                $('#legend-billable').text(billable);
                $('#legend-nonbillable').text(non_billable);
                $('#legend-none').text(none);

                // Hide loader, show chart area
                $('#billable-loading').hide();
                $('#billable-chart-area').show();
                feather.replace();

                // Build Chart.js donut
                var ctx = document.getElementById('billablePieChart').getContext('2d');

                // If all zero — show an empty visual
                var chartData  = (total > 0) ? [billable, non_billable, none] : [1, 1, 1];
                var chartAlpha = (total > 0) ? 1 : 0.25;

                billableChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Billable', 'Non-Billable', 'None'],
                        datasets: [{
                            data: chartData,
                            backgroundColor: [
                                'rgba(74, 222, 128, ' + chartAlpha + ')',
                                'rgba(248, 113, 113, ' + chartAlpha + ')',
                                'rgba(203, 213, 225, ' + chartAlpha + ')'
                            ],
                            borderColor: ['#fff', '#fff', '#fff'],
                            borderWidth: 4,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        cutout: '68%',
                        responsive: false,
                        animation: { animateRotate: true, duration: 800 },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                enabled: (total > 0),
                                callbacks: {
                                    label: function(context) {
                                        var val = (total > 0) ? context.parsed : 0;
                                        var p   = (total > 0) ? Math.round((val / total) * 100) : 0;
                                        return ' ' + context.label + ': ' + val + ' (' + p + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            },
            error: function () {
                $('#billable-loading').html('<p style="color:#f87171;">Failed to load chart data. Please refresh.</p>');
            }
        });
    }
});
</script>

<!-- ═══════════════════════════════════════════════
     EMPLOYEE PERFORMANCE REPORT — STYLES & SCRIPT
═══════════════════════════════════════════════ -->
<style>
/* ── Best Performed Days Widget ── */
.bpd-widget {
    display: inline-block;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 22px;
    min-width: 260px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.bpd-header {
    background: #f8fafc;
    border-bottom: 1.5px solid #e2e8f0;
    padding: 9px 16px;
    font-size: 11.5px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.bpd-body { padding: 0; }
.bpd-table {
    border-collapse: collapse;
    width: 100%;
}
.bpd-table thead th {
    padding: 8px 22px 5px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    text-align: center;
    border-right: 1px solid #f1f5f9;
    white-space: nowrap;
}
.bpd-table thead th:last-child { border-right: none; }
.bpd-table tbody td {
    padding: 10px 22px 12px;
    font-size: 22px;
    font-weight: 800;
    color: #1e293b;
    text-align: center;
    border-right: 1px solid #f1f5f9;
}
.bpd-table tbody td:last-child { border-right: none; }
/* Skeleton shimmer while loading */
.bpd-skeleton {
    display: flex;
    gap: 1px;
    padding: 12px 16px;
}
.bpd-sk-col {
    flex: 1;
    height: 36px;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
    border-radius: 4px;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Filter Bar ── */
.perf-filter-bar {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 14px;
    padding: 20px 0 24px;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 26px;
}
.perf-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.perf-filter-group label {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.perf-date-input {
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
    color: #334155;
    outline: none;
    transition: border-color 0.2s;
    min-width: 150px;
}
.perf-date-input:focus { border-color: #4361ee; }
.perf-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #4361ee;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 9px 20px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s;
}
.perf-btn:hover { background: #3451d1; transform: translateY(-1px); }
.perf-btn-icon { width: 14px; height: 14px; }
.perf-wd-badge {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #16a34a;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
/* ── Loading ── */
.perf-loading {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #94a3b8;
    font-size: 13px;
    padding: 30px 0;
}
.perf-spinner {
    width: 22px;
    height: 22px;
    border: 2.5px solid #e2e8f0;
    border-top-color: #4361ee;
    border-radius: 50%;
    animation: bspin 0.7s linear infinite;
}
/* ── Teams grid ── */
.perf-teams-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(460px, 1fr));
    gap: 24px;
}
/* ── Team card ── */
.perf-team-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: box-shadow 0.2s;
}
.perf-team-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.09); }
.perf-team-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    background: linear-gradient(135deg, #4361ee 0%, #6366f1 100%);
    color: #fff;
}
.perf-team-name {
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.perf-team-count {
    background: rgba(255,255,255,0.2);
    border-radius: 20px;
    padding: 3px 12px;
    font-size: 11.5px;
    font-weight: 600;
}
/* ── Table ── */
.perf-table-wrap { overflow-x: auto; }
.perf-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}
.perf-table th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 9px 12px;
    border-bottom: 1.5px solid #e2e8f0;
    white-space: nowrap;
    text-align: left;
}
.perf-table td {
    padding: 9px 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}
.perf-table tr:last-child td { border-bottom: none; }
.perf-table tr.perf-missing-row { background: #fff8f8; }
.perf-table tr.perf-missing-row td { color: #94a3b8; }
.perf-sl-col { color: #94a3b8; font-size: 11px; width: 32px; }
.perf-name-col { font-weight: 600; color: #1e293b; min-width: 140px; }
.perf-missing-row .perf-name-col { color: #cbd5e1; }
.perf-hours-col { font-weight: 700; color: #4361ee; text-align: right; }
.perf-missing-row .perf-hours-col { color: #e2e8f0; }
.perf-util-col { text-align: right; }
.perf-util-bar-wrap {
    display: flex;
    align-items: center;
    gap: 7px;
    justify-content: flex-end;
}
.perf-util-bar {
    width: 60px;
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}
.perf-util-fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, #4ade80, #4361ee);
    transition: width 0.6s ease;
}
.perf-util-pct { font-size: 11.5px; font-weight: 700; color: #334155; min-width: 38px; text-align: right; }
.perf-missing-row .perf-util-pct { color: #e2e8f0; }
.perf-comment-col { font-size: 11px; }
.perf-badge-missing { background: #fee2e2; color: #dc2626; border-radius: 20px; padding: 2px 8px; font-weight: 600; white-space: nowrap; }
.perf-badge-leave   { background: #fef3c7; color: #b45309; border-radius: 20px; padding: 2px 8px; font-weight: 600; white-space: nowrap; }
.perf-badge-ml      { background: #ede9fe; color: #7c3aed; border-radius: 20px; padding: 2px 8px; font-weight: 600; white-space: nowrap; }
/* ── Team Footer ── */
.perf-team-footer {
    padding: 12px 18px;
    background: #f8fafc;
    border-top: 1.5px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.perf-footer-label { font-size: 11.5px; color: #64748b; font-weight: 600; }
.perf-perf-score {
    display: flex;
    align-items: center;
    gap: 8px;
}
.perf-score-pill {
    background: #dc2626;
    color: #fff;
    border-radius: 6px;
    padding: 4px 12px;
    font-size: 13px;
    font-weight: 800;
    min-width: 52px;
    text-align: center;
}
.perf-score-pill.good  { background: #16a34a; }
.perf-score-pill.avg   { background: #d97706; }
.perf-footer-meta { font-size: 11px; color: #64748b; }
.perf-footer-meta span { font-weight: 700; color: #1e293b; }
.perf-miss-chip {
    background: #fee2e2;
    color: #dc2626;
    border-radius: 4px;
    padding: 2px 8px;
    font-weight: 700;
    font-size: 11px;
}
/* ── No-teams placeholder ── */
.perf-no-teams {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 60px 20px;
    color: #94a3b8;
    text-align: center;
    gap: 10px;
}
.perf-no-teams svg { width: 44px; height: 44px; stroke: #cbd5e1; }
/* ── Responsive ── */
@media (max-width: 600px) {
    .perf-teams-grid { grid-template-columns: 1fr; }
    .perf-filter-bar { flex-direction: column; align-items: flex-start; }
}
</style>

<script type="text/javascript">
(function() {
    var perfLoaded = false;

    // Set default date to today
    function setDefaultDates() {
        var now = new Date();
        var y   = now.getFullYear();
        var m   = String(now.getMonth() + 1).padStart(2, '0');
        var d   = String(now.getDate()).padStart(2, '0');
        document.getElementById('perf-report-date').value = y + '-' + m + '-' + d;
    }

    // Trigger report load when tab first shown
    document.addEventListener('DOMContentLoaded', function() {
        setDefaultDates();

        // Bootstrap tab event
        var tabLink = document.getElementById('tab-employee-performance');
        if (tabLink) {
            tabLink.addEventListener('shown.bs.tab', function() {
                if (!perfLoaded) {
                    perfLoaded = true;
                    loadPerfReport();
                }
            });
        }

        document.getElementById('perf-generate-btn').addEventListener('click', loadPerfReport);
    });

    function loadPerfReport() {
        var rdate = document.getElementById('perf-report-date').value;
        if (!rdate) { alert('Please select a date.'); return; }

        document.getElementById('perf-loading').style.display = 'flex';
        document.getElementById('perf-output').innerHTML = '';
        document.getElementById('perf-working-days-badge').style.display = 'none';

        $.ajax({
            url: '<?php echo get_uri("admin_dashboard/get_employee_performance_report"); ?>',
            type: 'GET',
            data: { report_date: rdate },
            dataType: 'json',
            success: function(data) {
                document.getElementById('perf-loading').style.display = 'none';

                // Show date info badge
                var wdBadge = document.getElementById('perf-working-days-badge');
                var dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                var d = new Date(data.report_date + 'T00:00:00');
                var dayLabel = dayNames[d.getDay()] + ', ' + data.report_date;
                wdBadge.textContent = dayLabel + (data.is_working_day ? ' · ' + data.expected_hours + 'h expected' : ' · Weekend');
                wdBadge.style.display = 'inline-flex';

                renderReport(data);
                if (typeof feather !== 'undefined') feather.replace();
            },
            error: function() {
                document.getElementById('perf-loading').style.display = 'none';
                document.getElementById('perf-output').innerHTML = '<p style="color:#f87171;padding:20px 0;">Failed to load report. Please refresh.</p>';
            }
        });
    }

    function renderReport(data) {
        var teams = data.teams;
        var out   = '';

        if (!teams || teams.length === 0) {
            out = '<div class="perf-no-teams"><i data-feather="users"></i><p>No teams found. Please create teams with members first.</p></div>';
            document.getElementById('perf-output').innerHTML = out;
            return;
        }

        out += '<div class="perf-teams-grid">';

        teams.forEach(function(team) {
            var members = team.members;
            var perfClass = team.team_perf >= 70 ? 'good' : (team.team_perf >= 40 ? 'avg' : '');

            out += '<div class="perf-team-card">';

            // Header
            out += '<div class="perf-team-header">';
            out +=   '<span class="perf-team-name">' + escHtml(team.team_name) + '</span>';
            out +=   '<span class="perf-team-count">' + members.length + ' member' + (members.length !== 1 ? 's' : '') + '</span>';
            out += '</div>';

            // Table
            out += '<div class="perf-table-wrap"><table class="perf-table">';
            out += '<thead><tr>';
            out += '<th class="perf-sl-col">#</th>';
            out += '<th>Name</th>';
            out += '<th style="text-align:right;">Hours</th>';
            out += '<th style="text-align:right;">Util %</th>';
            out += '<th>Status</th>';
            out += '</tr></thead><tbody>';

            members.forEach(function(m, idx) {
                var rowClass = m.has_log ? '' : 'perf-missing-row';
                var commentHtml = '';
                if (m.comment === 'missing log') {
                    commentHtml = '<span class="perf-badge-missing">missing log</span>';
                } else if (m.comment === 'on leave') {
                    commentHtml = '<span class="perf-badge-leave">on leave</span>';
                } else if (m.comment === 'missing log + leave') {
                    commentHtml = '<span class="perf-badge-ml">missing log + leave</span>';
                }

                var utilBarW = Math.min(m.util_pct, 100);
                var hoursStr = m.hours > 0 ? m.hours + 'h' : '0.00h';

                out += '<tr class="' + rowClass + '">';
                out += '<td class="perf-sl-col">' + (idx + 1) + '</td>';
                out += '<td class="perf-name-col">' + escHtml(m.name) + '</td>';
                out += '<td class="perf-hours-col">' + hoursStr + '</td>';
                out += '<td class="perf-util-col"><div class="perf-util-bar-wrap"><div class="perf-util-bar"><div class="perf-util-fill" style="width:' + utilBarW + '%"></div></div><span class="perf-util-pct">' + m.util_pct + '%</span></div></td>';
                out += '<td class="perf-comment-col">' + commentHtml + '</td>';
                out += '</tr>';
            });

            out += '</tbody></table></div>';

            // Footer
            out += '<div class="perf-team-footer">';
            out +=   '<div class="perf-perf-score">';
            out +=     '<span class="perf-footer-label">Overall Team Performance</span>';
            out +=     '<span class="perf-score-pill ' + perfClass + '">' + team.team_perf + '</span>';
            out +=   '</div>';
            out +=   '<div class="perf-footer-meta">';
            out +=     'Log Found: <span>' + team.log_found + '</span>&nbsp;&nbsp;';
            out +=     'Missing LOG+Leave: <span class="perf-miss-chip">' + team.missing + '</span>';
            out +=   '</div>';
            out += '</div>';

            out += '</div>'; // team card
        });

        out += '</div>'; // teams grid
        document.getElementById('perf-output').innerHTML = out;
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>

<script type="text/javascript">
(function() {
    // Read year & month from the selected date input
    function getYearMonthFromInput() {
        var val = document.getElementById('perf-report-date').value;
        if (val) {
            var parts = val.split('-');
            return { year: parseInt(parts[0], 10), month: parseInt(parts[1], 10) };
        }
        var now = new Date();
        return { year: now.getFullYear(), month: now.getMonth() + 1 };
    }

    function loadBestPerformedDays() {
        var ym = getYearMonthFromInput();
        document.getElementById('bpd-body').innerHTML =
            '<div class="bpd-skeleton"><div class="bpd-sk-col"></div><div class="bpd-sk-col"></div><div class="bpd-sk-col"></div></div>';

        $.ajax({
            url: '<?php echo get_uri("admin_dashboard/get_best_performed_days"); ?>',
            type: 'GET',
            data: { year: ym.year, month: ym.month },
            dataType: 'json',
            success: function(data) {
                var teams = data.teams;
                var body  = document.getElementById('bpd-body');
                if (!teams || teams.length === 0) {
                    body.innerHTML = '<p style="color:#94a3b8;font-size:12px;padding:10px 16px;">No team data found.</p>';
                    return;
                }

                // Update widget header with the month label
                var hdr = document.querySelector('.bpd-header');
                if (hdr) hdr.textContent = 'Best Performed Days — ' + data.month_label;

                var html = '<table class="bpd-table"><thead><tr>';
                teams.forEach(function(t) {
                    html += '<th style="color:' + t.color + ';">' + escBpd(t.team_name) + '</th>';
                });
                html += '</tr></thead><tbody><tr>';
                teams.forEach(function(t) {
                    html += '<td>' + t.day_count + '</td>';
                });
                html += '</tr></tbody></table>';
                body.innerHTML = html;
            },
            error: function() {
                document.getElementById('bpd-body').innerHTML =
                    '<p style="color:#f87171;font-size:12px;padding:10px 16px;">Failed to load.</p>';
            }
        });
    }

    function escBpd(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Load on tab first shown
        var tabLink = document.getElementById('tab-employee-performance');
        if (tabLink) {
            tabLink.addEventListener('shown.bs.tab', function() {
                loadBestPerformedDays();
            });
        }

        // Reload when Generate Report button is clicked
        var btn = document.getElementById('perf-generate-btn');
        if (btn) {
            btn.addEventListener('click', loadBestPerformedDays);
        }
    });
})();
</script>
