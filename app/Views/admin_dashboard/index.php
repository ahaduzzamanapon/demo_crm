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
                        <div class="admin-tab-inner" id="pp-tab-inner">

                            <!-- Loading -->
                            <div id="pp-loading" class="pp-loading-state">
                                <div class="pp-spinner"></div>
                                <p>Loading project data…</p>
                            </div>

                            <!-- Content area -->
                            <div id="pp-content" style="display:none;">
                                <div class="pp-header-row">
                                    <div>
                                        <h3 class="pp-main-title">Project Progress Overview</h3>
                                        <p class="pp-subtitle">Per-project task completion with time inconsistency alerts</p>
                                    </div>
                                    <div class="pp-legend">
                                        <span class="pp-leg-item"><span class="pp-leg-dot" style="background:#22c55e;"></span>Done</span>
                                        <span class="pp-leg-item"><span class="pp-leg-dot" style="background:#3b82f6;"></span>In Dev</span>
                                        <span class="pp-leg-item"><span class="pp-leg-dot" style="background:#a78bfa;"></span>QA</span>
                                        <span class="pp-leg-item"><span class="pp-leg-dot" style="background:#e2e8f0;"></span>Remaining</span>
                                        <span class="pp-leg-item"><span class="pp-leg-dot" style="background:#ef4444;"></span>Inconsistency</span>
                                    </div>
                                </div>

                                <!-- Search bar -->
                                <div class="tab-search-bar">
                                    <div class="tab-search-wrap">
                                        <svg class="tab-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                        <input type="text" id="pp-search" class="tab-search-input" placeholder="Search projects…" autocomplete="off">
                                        <button id="pp-search-clear" class="tab-search-clear" style="display:none;" title="Clear">&times;</button>
                                    </div>
                                    <span id="pp-count" class="tab-search-count"></span>
                                </div>

                                <div id="pp-list"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Resource Utilization Report -->
                    <div class="tab-pane fade" id="resource-utilization" role="tabpanel" aria-labelledby="tab-resource-utilization">
                        <div class="admin-tab-inner" id="ru-tab-inner">

                            <!-- Loading -->
                            <div id="ru-loading" class="ru-loading-state">
                                <div class="ru-spinner"></div>
                                <p>Loading resource data…</p>
                            </div>

                            <!-- Content -->
                            <div id="ru-content" style="display:none;">
                                <div class="ru-header-row">
                                    <div>
                                        <h3 class="ru-main-title">Resource Utilization Report</h3>
                                        <p class="ru-subtitle">Estimated vs. actual time spent per member per project</p>
                                    </div>
                                    <div class="ru-legend">
                                        <span class="ru-leg"><span style="color:#16a34a;font-weight:700;">●</span> On track</span>
                                        <span class="ru-leg"><span style="color:#f59e0b;font-weight:700;">●</span> Near limit</span>
                                        <span class="ru-leg"><span style="color:#ef4444;font-weight:700;">●</span> Over budget</span>
                                    </div>
                                </div>

                                <!-- Search bar -->
                                <div class="tab-search-bar">
                                    <div class="tab-search-wrap">
                                        <svg class="tab-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                        <input type="text" id="ru-search" class="tab-search-input" placeholder="Search projects…" autocomplete="off">
                                        <button id="ru-search-clear" class="tab-search-clear" style="display:none;" title="Clear">&times;</button>
                                    </div>
                                    <span id="ru-count" class="tab-search-count"></span>
                                </div>

                                <div id="ru-list"></div>
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

/* ════════════════════════════════════════════════
   PROJECT PROGRESS TAB
   ════════════════════════════════════════════════ */
/* Loading */
.pp-loading-state { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:60px 20px; color:#94a3b8; }
.pp-spinner { width:38px; height:38px; border:4px solid #e2e8f0; border-top-color:#6366f1; border-radius:50%; animation:pp-spin 0.8s linear infinite; margin-bottom:12px; }
@keyframes pp-spin { to { transform: rotate(360deg); } }

/* Header */
.pp-header-row { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; padding:20px 24px 12px; border-bottom:1px solid #e2e8f0; }
.pp-main-title  { font-size:17px; font-weight:700; color:#1e293b; margin:0; }
.pp-subtitle    { font-size:12px; color:#94a3b8; margin:3px 0 0; }
.pp-legend { display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
.pp-leg-item { display:flex; align-items:center; gap:5px; font-size:11.5px; color:#64748b; font-weight:500; }
.pp-leg-dot  { width:11px; height:11px; border-radius:3px; flex-shrink:0; }

/* Project list — 3 cards per row */
#pp-list { padding:16px 24px; display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }

/* Project Card */
.pp-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; transition:box-shadow 0.2s; }
.pp-card:hover { box-shadow:0 4px 18px rgba(0,0,0,0.07); }
.pp-card.pp-inconsistent { border-color:#fca5a5; box-shadow:0 0 0 2px rgba(239,68,68,0.12); }
.pp-card.pp-overdue      { border-color:#fed7aa; }

.pp-card-header { display:flex; justify-content:space-between; align-items:center; padding:13px 18px 8px; flex-wrap:wrap; gap:8px; }
.pp-project-name { font-size:14px; font-weight:700; color:#1e293b; }
.pp-project-name a { color:inherit; text-decoration:none; }
.pp-project-name a:hover { color:#6366f1; }

.pp-badges { display:flex; gap:6px; flex-wrap:wrap; align-items:center; }
.pp-badge { font-size:10.5px; font-weight:600; padding:2px 9px; border-radius:20px; }
.pp-badge-deadline   { background:#f1f5f9; color:#475569; }
.pp-badge-overdue    { background:#fee2e2; color:#dc2626; }
.pp-badge-status     { background:#ede9fe; color:#7c3aed; }
.pp-badge-inconsist  { background:#ef4444; color:#fff; animation:pp-pulse 1.4s ease-in-out infinite; }
@keyframes pp-pulse  { 0%,100%{opacity:1} 50%{opacity:0.65} }
.pp-badge-rd         { background:#fef3c7; color:#b45309; font-size:10px; }

/* Stacked progress bar */
.pp-bar-wrap { padding:4px 18px 10px; }
.pp-bar      { height:18px; border-radius:9px; background:#f1f5f9; overflow:hidden; display:flex; border:1px solid #e2e8f0; }
.pp-bar-seg  { height:100%; transition:width 0.5s ease; position:relative; }
.pp-bar-seg:first-child { border-radius:9px 0 0 9px; }
.pp-bar-seg:last-child  { border-radius:0 9px 9px 0; }
.pp-bar-seg-done  { background:linear-gradient(90deg,#16a34a,#22c55e); }
.pp-bar-seg-dev   { background:linear-gradient(90deg,#2563eb,#3b82f6); }
.pp-bar-seg-qa    { background:linear-gradient(90deg,#7c3aed,#a78bfa); }
.pp-bar-seg-rem   { background:#e2e8f0; }
.pp-bar-seg-inc   { background:linear-gradient(90deg,#dc2626,#ef4444); animation:pp-pulse 1.4s ease-in-out infinite; }

/* Tooltip on hover */
.pp-bar-seg[title] { cursor:help; }

/* Task chips row */
.pp-chips { display:flex; flex-wrap:wrap; gap:6px; padding:0 18px 13px; }
.pp-chip  { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; }
.pp-chip-total  { background:#f1f5f9; color:#475569; }
.pp-chip-done   { background:#dcfce7; color:#16a34a; }
.pp-chip-dev    { background:#dbeafe; color:#2563eb; }
.pp-chip-qa     { background:#ede9fe; color:#7c3aed; }
.pp-chip-rem    { background:#f1f5f9; color:#64748b; }
.pp-chip-rh     { background:#fef9c3; color:#92400e; }

/* Inconsistency detail row */
.pp-inconsist-row { margin:0 18px 12px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:8px 12px; font-size:11.5px; color:#dc2626; display:flex; align-items:center; gap:8px; }
.pp-inconsist-row svg { flex-shrink:0; }

@media (max-width:1100px) { #pp-list { grid-template-columns:repeat(2,1fr); } }
@media (max-width:700px)  { #pp-list { grid-template-columns:1fr; padding:12px; }
    .pp-card-header { flex-direction:column; align-items:flex-start; }
}

/* ════════════════════════════════════════════════
   RESOURCE UTILIZATION TAB
   ════════════════════════════════════════════════ */
.ru-loading-state { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:60px 20px; color:#94a3b8; }
.ru-spinner { width:38px; height:38px; border:4px solid #e2e8f0; border-top-color:#6366f1; border-radius:50%; animation:pp-spin 0.8s linear infinite; margin-bottom:12px; }

.ru-header-row { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; padding:20px 24px 14px; border-bottom:1px solid #e2e8f0; }
.ru-main-title  { font-size:17px; font-weight:700; color:#1e293b; margin:0; }
.ru-subtitle    { font-size:12px; color:#94a3b8; margin:3px 0 0; }
.ru-legend { display:flex; gap:12px; flex-wrap:wrap; align-items:center; font-size:11.5px; color:#64748b; }
.ru-leg { display:flex; align-items:center; gap:4px; }

/* Grid */
#ru-list { padding:16px 24px; display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }

/* Project card */
.ru-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; transition:box-shadow 0.2s; }
.ru-card:hover { box-shadow:0 4px 18px rgba(0,0,0,0.07); }

.ru-card-head { padding:11px 14px 8px; border-bottom:1px solid #f1f5f9; }
.ru-card-title { font-size:13px; font-weight:700; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ru-card-title a { color:inherit; text-decoration:none; }
.ru-card-title a:hover { color:#6366f1; }
.ru-card-status { font-size:10px; font-weight:600; color:#7c3aed; background:#ede9fe; padding:1px 7px; border-radius:20px; display:inline-block; margin-top:3px; }

/* Table inside card */
.ru-table { width:100%; border-collapse:collapse; font-size:11.5px; }
.ru-table th { padding:6px 10px; background:#f8fafc; color:#64748b; font-weight:700; font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid #e2e8f0; text-align:right; }
.ru-table th:first-child { text-align:left; }
.ru-table td { padding:6px 10px; border-bottom:1px solid #f1f5f9; color:#334155; vertical-align:middle; text-align:right; }
.ru-table td:first-child { text-align:left; font-weight:600; color:#1e293b; }
.ru-table tr:last-child td { border-bottom:none; }
.ru-table tr:hover td { background:#fafafa; }

/* Remaining cell color coding */
.ru-rem-ok   { color:#16a34a; font-weight:700; }
.ru-rem-warn { color:#f59e0b; font-weight:700; }
.ru-rem-over { color:#ef4444; font-weight:700; }

/* Spent bar mini */
.ru-bar-mini { height:4px; border-radius:2px; background:#e2e8f0; margin-top:3px; overflow:hidden; }
.ru-bar-mini-fill { height:100%; border-radius:2px; transition:width 0.4s; }

@media (max-width:1100px) { #ru-list { grid-template-columns:repeat(2,1fr); } }
@media (max-width:700px)  { #ru-list { grid-template-columns:1fr; padding:12px; } }

/* ── Shared Tab Search Bar ── */
.tab-search-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 24px; border-bottom:1px solid #e2e8f0; background:#fafafa; flex-wrap:wrap; }
.tab-search-wrap { display:flex; align-items:center; gap:8px; background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:6px 12px; flex:1; max-width:360px; transition:border-color .2s,box-shadow .2s; }
.tab-search-wrap:focus-within { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.12); }
.tab-search-icon { color:#94a3b8; flex-shrink:0; }
.tab-search-input { border:none; outline:none; background:transparent; font-size:13px; color:#334155; width:100%; }
.tab-search-input::placeholder { color:#cbd5e1; }
.tab-search-clear { border:none; background:none; cursor:pointer; color:#94a3b8; font-size:16px; line-height:1; padding:0 2px; transition:color .2s; }
.tab-search-clear:hover { color:#ef4444; }
.tab-search-count { font-size:11.5px; color:#94a3b8; white-space:nowrap; font-weight:500; }

/* Hidden card when filtered out */
.pp-card.pp-hidden, .ru-card.pp-hidden { display:none; }
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
.perf-util-fill.overtime {
    background: linear-gradient(90deg, #f59e0b, #ef4444);
}
.perf-util-pct { font-size: 11.5px; font-weight: 700; color: #334155; min-width: 38px; text-align: right; }
.perf-util-pct.overtime { color: #d97706; }
.perf-missing-row .perf-util-pct { color: #e2e8f0; }
.perf-comment-col { font-size: 11px; }
.perf-badge-missing { background: #fee2e2; color: #dc2626; border-radius: 20px; padding: 2px 8px; font-weight: 600; white-space: nowrap; }
.perf-badge-leave   { background: #fef3c7; color: #b45309; border-radius: 20px; padding: 2px 8px; font-weight: 600; white-space: nowrap; }
.perf-badge-ml      { background: #ede9fe; color: #7c3aed; border-radius: 20px; padding: 2px 8px; font-weight: 600; white-space: nowrap; }
.perf-badge-admin-leave { background: #dcfce7; color: #16a34a; border-radius: 20px; padding: 2px 8px; font-weight: 600; white-space: nowrap; }
/* ── Override Action Buttons ── */
.perf-action-group { display: inline-flex; gap: 5px; flex-wrap: wrap; align-items: center; }
.perf-action-btn {
    border: none;
    border-radius: 5px;
    padding: 3px 10px;
    font-size: 10.5px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.1s;
    white-space: nowrap;
}
.perf-action-btn:hover { opacity: 0.82; transform: translateY(-1px); }
.perf-btn-leave   { background: #dcfce7; color: #15803d; }
.perf-btn-missing { background: #fee2e2; color: #dc2626; }
.perf-btn-loading { opacity: 0.5; pointer-events: none; }
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
    // Expose for markPerfOverride to call after saving
    window._loadPerfReportFn = function() { loadPerfReport(); };

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
        // Make report_date available in onclick handlers
        window.currentReportDate = data.report_date;
        var currentReportDate = data.report_date;

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

                if (m.comment === 'on leave (admin)') {
                    commentHtml = '<span class="perf-badge-admin-leave">on leave (admin)</span>';
                } else if (m.comment === 'on leave') {
                    commentHtml = '<span class="perf-badge-leave">on leave</span>';
                } else if (m.comment === 'missing log + leave') {
                    // Has leave record but still no log — show action buttons
                    commentHtml = '<span class="perf-badge-ml">missing log + leave</span>';
                    if (!m.override) {
                        commentHtml += ' <span class="perf-action-group">' +
                            '<button class="perf-action-btn perf-btn-leave" onclick="markPerfOverride(' + m.user_id + ',\'' + currentReportDate + '\',\'leave\')">&#10003; On Leave</button>' +
                            '<button class="perf-action-btn perf-btn-missing" onclick="markPerfOverride(' + m.user_id + ',\'' + currentReportDate + '\',\'missing\')">&#10007; Missing</button>' +
                            '</span>';
                    }
                } else if (m.comment === 'missing log') {
                    commentHtml = '<span class="perf-badge-missing">missing log</span>';
                    if (!m.override) {
                        commentHtml += ' <span class="perf-action-group">' +
                            '<button class="perf-action-btn perf-btn-leave" onclick="markPerfOverride(' + m.user_id + ',\'' + currentReportDate + '\',\'leave\')">&#10003; On Leave</button>' +
                            '<button class="perf-action-btn perf-btn-missing" onclick="markPerfOverride(' + m.user_id + ',\'' + currentReportDate + '\',\'missing\')">&#10007; Missing</button>' +
                            '</span>';
                    }
                }

                var utilBarW    = Math.min(m.util_pct, 100);
                var isOvertime  = m.util_pct > 100;
                var fillClass   = isOvertime ? ' overtime' : '';
                var pctClass    = isOvertime ? ' overtime' : '';
                var pctLabel    = m.util_pct + '%' + (isOvertime ? ' ▲' : '');
                // Convert decimal hours → "Xh Ym"  e.g. 7.65 → "7h 39m"
                var hRaw        = m.hours > 0 ? m.hours : 0;
                var hInt        = Math.floor(hRaw);
                var hMin        = Math.round((hRaw - hInt) * 60);
                var hoursStr    = m.hours > 0 ? (hInt + 'h ' + hMin + 'm') : '0h 0m';

                out += '<tr class="' + rowClass + '">';
                out += '<td class="perf-sl-col">' + (idx + 1) + '</td>';
                out += '<td class="perf-name-col">' + escHtml(m.name) + '</td>';
                out += '<td class="perf-hours-col">' + hoursStr + '</td>';
                out += '<td class="perf-util-col"><div class="perf-util-bar-wrap"><div class="perf-util-bar"><div class="perf-util-fill' + fillClass + '" style="width:' + utilBarW + '%"></div></div><span class="perf-util-pct' + pctClass + '">' + pctLabel + '</span></div></td>';
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
// Global override handler — called by inline onclick buttons in the performance table
function markPerfOverride(userId, reportDate, overrideType) {
    document.querySelectorAll('.perf-action-btn').forEach(function(b) {
        b.classList.add('perf-btn-loading');
    });

    $.ajax({
        url: '<?php echo get_uri("admin_dashboard/mark_perf_override"); ?>',
        type: 'POST',
        data: {
            user_id:       userId,
            report_date:   reportDate,
            override_type: overrideType
        },
        dataType: 'json',
        success: function(res) {
            if (res && res.success) {
                if (typeof window._loadPerfReportFn === 'function') window._loadPerfReportFn();
                if (typeof window._loadBpdFn === 'function')        window._loadBpdFn();
            }
        },
        error: function() {
            alert('Failed to save. Please try again.');
            document.querySelectorAll('.perf-action-btn').forEach(function(b) {
                b.classList.remove('perf-btn-loading');
            });
        }
    });
}
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

    // Expose for markPerfOverride to call after saving
    window._loadBpdFn = function() { loadBestPerformedDays(); };

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

<script type="text/javascript">
(function() {
    var ppLoaded = false;

    function loadProjectProgress() {
        var loading = document.getElementById('pp-loading');
        var content = document.getElementById('pp-content');
        if (!loading || !content) return;
        loading.style.display = 'flex';
        content.style.display = 'none';

        $.ajax({
            url: '<?php echo get_uri("admin_dashboard/get_project_progress"); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                loading.style.display = 'none';
                content.style.display = 'block';
                renderProjectProgress(data.projects || []);
            },
            error: function() {
                loading.innerHTML = '<p style="color:#ef4444;padding:20px;">Failed to load. Please refresh.</p>';
            }
        });
    }

    function renderProjectProgress(projects) {
        var list = document.getElementById('pp-list');
        if (!projects || projects.length === 0) {
            list.innerHTML = '<div style="padding:40px;text-align:center;color:#94a3b8;">No active projects with deadlines found.</div>';
            return;
        }
        var html = '';
        projects.forEach(function(p) {
            var cardCls = 'pp-card' + (p.is_inconsistent ? ' pp-inconsistent' : (p.is_overdue ? ' pp-overdue' : ''));
            var dlBadge = !p.deadline
                ? '<span class="pp-badge" style="background:#f1f5f9;color:#94a3b8;">No deadline</span>'
                : (p.is_overdue
                    ? '<span class="pp-badge pp-badge-overdue">&#9888; Overdue</span>'
                    : '<span class="pp-badge pp-badge-deadline">&#128197; ' + esc(p.deadline) + '</span>');
            var rdBadge = (p.RD === null || p.RD === undefined)
                ? ''
                : (p.is_overdue
                    ? '<span class="pp-badge pp-badge-overdue">Past due</span>'
                    : '<span class="pp-badge pp-badge-rd">' + p.RD + ' day' + (p.RD!=1?'s':'') + ' left</span>');
            var incBadge = p.is_inconsistent ? '<span class="pp-badge pp-badge-inconsist">&#128308; Inconsistency</span>' : '';
            var stBadge  = p.status_label ? '<span class="pp-badge pp-badge-status">' + esc(p.status_label) + '</span>' : '';

            // Stacked bar — overdue: remaining turns red; inconsistent: remaining pulses red
            var barBg = p.is_overdue ? 'background:#fee2e2;' : '';
            var bar = '<div class="pp-bar" style="' + barBg + '">';
            if (p.done_pct > 0) bar += '<div class="pp-bar-seg pp-bar-seg-done" style="width:' + p.done_pct + '%;" title="Done: ' + p.done_pct + '%"></div>';
            if (p.dev_pct  > 0) bar += '<div class="pp-bar-seg pp-bar-seg-dev"  style="width:' + p.dev_pct  + '%;" title="Dev: '  + p.dev_pct  + '%"></div>';
            if (p.qa_pct   > 0) bar += '<div class="pp-bar-seg pp-bar-seg-qa"   style="width:' + p.qa_pct   + '%;" title="QA: '   + p.qa_pct   + '%"></div>';
            if (p.remaining_pct > 0) {
                var remSeg = p.is_inconsistent ? 'pp-bar-seg-inc' : (p.is_overdue ? 'pp-bar-seg-inc' : 'pp-bar-seg-rem');
                bar += '<div class="pp-bar-seg ' + remSeg + '" style="width:' + p.remaining_pct + '%;" title="Remaining: ' + p.remaining_pct + '%"></div>';
            }
            bar += '</div>';

            var pctRow = '<div style="display:flex;gap:10px;font-size:10.5px;margin-top:5px;flex-wrap:wrap;">'
                + '<span style="color:#16a34a;font-weight:600;">Done ' + p.done_pct + '%</span>'
                + '<span style="color:#2563eb;font-weight:600;">Dev ' + p.dev_pct + '%</span>'
                + '<span style="color:#7c3aed;font-weight:600;">QA ' + p.qa_pct + '%</span>'
                + '<span style="color:#94a3b8;">Rem ' + p.remaining_pct + '%</span>'
                + '</div>';

            var chips = '<div class="pp-chips">'
                + '<span class="pp-chip pp-chip-total">T: ' + p.T + '</span>'
                + '<span class="pp-chip pp-chip-done">Done: ' + p.Dq + '</span>'
                + '<span class="pp-chip pp-chip-dev">Dev: ' + p.Dp + '</span>'
                + '<span class="pp-chip pp-chip-qa">QA: ' + p.Qp + '</span>'
                + '<span class="pp-chip pp-chip-rem">Left: ' + p.RT + '</span>'
                + (p.avg_est_h > 0 ? '<span class="pp-chip pp-chip-rh">RH ' + p.RH + 'h / AH ' + p.AH + 'h</span>' : '')
                + '</div>';

            var incRow = '';
            if (p.is_inconsistent) {
                incRow = '<div class="pp-inconsist-row">'
                    + '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
                    + '<strong>Inconsistency:</strong>&nbsp;' + p.RT + ' task(s) need ~' + p.RH + 'h but only ' + p.AH + 'h available (' + p.RD + ' days x 8h).'
                    + '</div>';
            }

            html += '<div class="' + cardCls + '" data-title="' + esc(p.project_title) + '">'
                + '<div class="pp-card-header">'
                + '<div class="pp-project-name"><a href="<?php echo get_uri("projects/view/"); ?>' + p.project_id + '" target="_blank">' + esc(p.project_title) + '</a></div>'
                + '<div class="pp-badges">' + stBadge + dlBadge + rdBadge + incBadge + '</div>'
                + '</div>'
                + '<div class="pp-bar-wrap">' + bar + pctRow + '</div>'
                + chips + incRow
                + '</div>';
        });
        list.innerHTML = html;
        if (typeof window['_searchRefresh_pp-search'] === 'function') window['_searchRefresh_pp-search']();
    }

    function esc(str) {
        return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadProjectProgress();
        var tab = document.getElementById('tab-project-progress');
        if (tab) {
            tab.addEventListener('shown.bs.tab', function() {
                if (ppLoaded) return;
                ppLoaded = true;
                loadProjectProgress();
            });
        }
    });
})();
</script>

<script type="text/javascript">
(function() {
    var ruLoaded = false;

    function loadResourceUtilization() {
        var loading = document.getElementById('ru-loading');
        var content = document.getElementById('ru-content');
        if (!loading || !content) return;
        loading.style.display = 'flex';
        content.style.display = 'none';

        $.ajax({
            url: '<?php echo get_uri("admin_dashboard/get_resource_utilization"); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                loading.style.display = 'none';
                content.style.display = 'block';
                renderResourceUtilization(data.projects || []);
            },
            error: function() {
                loading.innerHTML = '<p style="color:#ef4444;padding:20px;">Failed to load resource data. Please refresh.</p>';
            }
        });
    }

    function renderResourceUtilization(projects) {
        var list = document.getElementById('ru-list');
        if (!projects || projects.length === 0) {
            list.innerHTML = '<div style="padding:40px;text-align:center;color:#94a3b8;grid-column:1/-1;">No project resource data found.</div>';
            return;
        }

        var html = '';
        projects.forEach(function(proj) {
            var rows = '';
            proj.members.forEach(function(m) {
                var remCls = 'ru-rem-ok';
                if (m.remaining < 0) remCls = 'ru-rem-over';
                else if (m.est > 0 && (m.spent / m.est) >= 0.8) remCls = 'ru-rem-warn';

                // Mini progress bar: spent/est ratio capped at 100%
                var pct = m.est > 0 ? Math.min(100, Math.round((m.spent / m.est) * 100)) : 0;
                var barColor = remCls === 'ru-rem-over' ? '#ef4444' : (remCls === 'ru-rem-warn' ? '#f59e0b' : '#22c55e');

                var remLabel = m.remaining < 0
                    ? '<span class="' + remCls + '">' + m.remaining + 'h <small>(over)</small></span>'
                    : '<span class="' + remCls + '">' + m.remaining + 'h</span>';

                rows += '<tr>'
                    + '<td title="' + esc(m.name) + '">' + esc(m.name) + '</td>'
                    + '<td>' + m.est + 'h</td>'
                    + '<td style="color:#3b82f6;font-weight:600;">'
                    +   m.spent + 'h'
                    +   '<div class="ru-bar-mini"><div class="ru-bar-mini-fill" style="width:' + pct + '%;background:' + barColor + ';"></div></div>'
                    + '</td>'
                    + '<td>' + remLabel + '</td>'
                    + '</tr>';
            });

            html += '<div class="ru-card" data-title="' + esc(proj.project_title) + '">'
                + '<div class="ru-card-head">'
                +   '<div class="ru-card-title" title="' + esc(proj.project_title) + '"><a href="<?php echo get_uri("projects/view/"); ?>' + proj.project_id + '" target="_blank">' + esc(proj.project_title) + '</a></div>'
                +   (proj.status_label ? '<span class="ru-card-status">' + esc(proj.status_label) + '</span>' : '')
                + '</div>'
                + '<table class="ru-table">'
                +   '<thead><tr>'
                +     '<th>Member</th>'
                +     '<th>Est. Hr</th>'
                +     '<th>Spent</th>'
                +     '<th>Remaining</th>'
                +   '</tr></thead>'
                +   '<tbody>' + rows + '</tbody>'
                + '</table>'
                + '</div>';
        });

        list.innerHTML = html;
        if (typeof window['_searchRefresh_ru-search'] === 'function') window['_searchRefresh_ru-search']();
    }

    function esc(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    document.addEventListener('DOMContentLoaded', function() {
        var tabLink = document.getElementById('tab-resource-utilization');
        if (tabLink) {
            tabLink.addEventListener('shown.bs.tab', function() {
                if (ruLoaded) return;
                ruLoaded = true;
                loadResourceUtilization();
            });
        }
    });
})();
</script>

<script type="text/javascript">
/* ── Shared live-search for Project Progress & Resource Utilization tabs ── */
(function() {
    function initTabSearch(inputId, clearId, listId, countId, cardClass) {
        var input = document.getElementById(inputId);
        var clearBtn = document.getElementById(clearId);
        var countEl = document.getElementById(countId);
        if (!input) return;

        function doFilter() {
            var q = input.value.trim().toLowerCase();
            var list = document.getElementById(listId);
            if (!list) return;
            var cards = list.querySelectorAll('.' + cardClass);
            var visible = 0;
            cards.forEach(function(card) {
                var title = (card.getAttribute('data-title') || '').toLowerCase();
                var show = !q || title.indexOf(q) !== -1;
                card.classList.toggle('pp-hidden', !show);
                if (show) visible++;
            });
            if (clearBtn) clearBtn.style.display = q ? 'inline' : 'none';
            if (countEl) {
                countEl.textContent = q
                    ? visible + ' of ' + cards.length + ' project' + (cards.length !== 1 ? 's' : '')
                    : cards.length + ' project' + (cards.length !== 1 ? 's' : '');
            }
        }

        input.addEventListener('input', doFilter);

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                input.value = '';
                doFilter();
                input.focus();
            });
        }

        // Expose so render functions can call it after populating cards
        window['_searchRefresh_' + inputId] = doFilter;
    }

    document.addEventListener('DOMContentLoaded', function() {
        initTabSearch('pp-search', 'pp-search-clear', 'pp-list', 'pp-count', 'pp-card');
        initTabSearch('ru-search', 'ru-search-clear', 'ru-list', 'ru-count', 'ru-card');
    });
})();
</script>
