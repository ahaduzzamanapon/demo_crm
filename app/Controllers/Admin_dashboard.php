<?php

namespace App\Controllers;

class Admin_dashboard extends Security_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->access_only_team_members();
        if (!($this->login_user->is_admin || get_array_value($this->login_user->permissions, "can_access_admin_dashboard"))) {
            app_redirect("forbidden");
        }
    }


    public function index()
    {
        $Users_model = model('App\Models\Users_model');
        $Projects_model = model('App\Models\Projects_model');
        $Tasks_model = model('App\Models\Tasks_model');
        $Clients_model = model('App\Models\Clients_model');

        $view_data["page_title"] = app_lang("admin_dashboard");
        $view_data["total_team_members"] = $Users_model->get_all_where(["user_type" => "staff", "deleted" => 0, "status" => "active"])->getNumRows();
        $view_data["total_projects"] = $Projects_model->get_details(["deleted" => 0])->getNumRows();
        $view_data["total_tasks"] = $Tasks_model->get_all_where(["deleted" => 0])->getNumRows();
        $view_data["total_clients"] = $Clients_model->get_details([])->getNumRows();

        return $this->template->rander("admin_dashboard/index", $view_data);
    }

}

/* End of file Admin_dashboard.php */
/* Location: ./app/Controllers/Admin_dashboard.php */
