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
        return $this->template->rander("admin_dashboard/index");
    }

}

/* End of file Admin_dashboard.php */
/* Location: ./app/Controllers/Admin_dashboard.php */
