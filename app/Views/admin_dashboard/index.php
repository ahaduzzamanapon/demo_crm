<div id="page-content" class="page-wrapper clearfix">
    <div class="card">
        <div class="page-title clearfix">
            <h1>
                <?php echo app_lang('admin_dashboard'); ?>
            </h1>
        </div>
        <div class="card-body">

            <!-- Summary Stats Row -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 text-center p-3"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                        <i data-feather="users" class="mb-2" style="width:36px;height:36px;"></i>
                        <h6 class="mb-1 text-white-50">
                            <?php echo app_lang('team_members'); ?>
                        </h6>
                        <h2 class="mb-0 fw-bold">
                            <?php echo $total_team_members; ?>
                        </h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 text-center p-3"
                        style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #fff;">
                        <i data-feather="command" class="mb-2" style="width:36px;height:36px;"></i>
                        <h6 class="mb-1 text-white-50">
                            <?php echo app_lang('projects'); ?>
                        </h6>
                        <h2 class="mb-0 fw-bold">
                            <?php echo $total_projects; ?>
                        </h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 text-center p-3"
                        style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #fff;">
                        <i data-feather="check-circle" class="mb-2" style="width:36px;height:36px;"></i>
                        <h6 class="mb-1 text-white-50">
                            <?php echo app_lang('tasks'); ?>
                        </h6>
                        <h2 class="mb-0 fw-bold">
                            <?php echo $total_tasks; ?>
                        </h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 text-center p-3"
                        style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: #fff;">
                        <i data-feather="briefcase" class="mb-2" style="width:36px;height:36px;"></i>
                        <h6 class="mb-1 text-white-50">
                            <?php echo app_lang('clients'); ?>
                        </h6>
                        <h2 class="mb-0 fw-bold">
                            <?php echo $total_clients; ?>
                        </h2>
                    </div>
                </div>
            </div>

            <!-- Quick Links Row -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i data-feather="grid" class="icon-16 mr5"></i>
                                <?php echo app_lang('quick_links'); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2 col-sm-4 col-6 text-center">
                                    <a href="<?php echo get_uri('team_members'); ?>"
                                        class="btn btn-outline-primary w-100 py-3">
                                        <i data-feather="users" class="d-block mb-1 mx-auto"></i>
                                        <?php echo app_lang('team_members'); ?>
                                    </a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 text-center">
                                    <a href="<?php echo get_uri('projects/all_projects'); ?>"
                                        class="btn btn-outline-primary w-100 py-3">
                                        <i data-feather="command" class="d-block mb-1 mx-auto"></i>
                                        <?php echo app_lang('projects'); ?>
                                    </a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 text-center">
                                    <a href="<?php echo get_uri('tasks/all_tasks'); ?>"
                                        class="btn btn-outline-primary w-100 py-3">
                                        <i data-feather="check-circle" class="d-block mb-1 mx-auto"></i>
                                        <?php echo app_lang('tasks'); ?>
                                    </a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 text-center">
                                    <a href="<?php echo get_uri('clients'); ?>"
                                        class="btn btn-outline-primary w-100 py-3">
                                        <i data-feather="briefcase" class="d-block mb-1 mx-auto"></i>
                                        <?php echo app_lang('clients'); ?>
                                    </a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 text-center">
                                    <a href="<?php echo get_uri('invoices'); ?>"
                                        class="btn btn-outline-primary w-100 py-3">
                                        <i data-feather="file-text" class="d-block mb-1 mx-auto"></i>
                                        <?php echo app_lang('invoices'); ?>
                                    </a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 text-center">
                                    <a href="<?php echo get_uri('settings/general'); ?>"
                                        class="btn btn-outline-primary w-100 py-3">
                                        <i data-feather="settings" class="d-block mb-1 mx-auto"></i>
                                        <?php echo app_lang('settings'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        feather.replace();
    });
</script>