<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Leads_api extends Controller {

    protected $Users_model;
    protected $Clients_model;
    protected $Lead_status_model;
    protected $Notes_model;

    function __construct() {
        helper(array('url', 'file', 'form', 'language', 'general', 'date_time', 'app_files', 'currency', 'reports'));
        $this->Users_model = model("App\Models\Users_model");
        $this->Clients_model = model("App\Models\Clients_model");
        $this->Lead_status_model = model("App\Models\Lead_status_model");
        $this->Notes_model = model("App\Models\Notes_model");
        
        $Settings_model = model("App\Models\Settings_model");
        $settings = $Settings_model->get_all_required_settings()->getResult();
        foreach ($settings as $setting) {
            config('Rise')->app_settings_array[$setting->setting_name] = $setting->setting_value;
        }
    }

    function save() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($this->request->getMethod() === 'options') {
            exit;
        }

        try {
            $rules = [
                "owner_email" => "required|valid_email",
                "client_name" => "required"
            ];
            
            if (!$this->validate($rules)) {
                 echo json_encode(array("success" => false, 'message' => $this->validator->getErrors()));
                 exit();
            }

            $owner_email = $this->request->getPost('owner_email');
            $owner_user = $this->Users_model->get_one_where(array("email" => $owner_email, "deleted" => 0, "status" => "active", "user_type" => "staff"));

            if (!$owner_user->id) {
                echo json_encode(array("success" => false, 'message' => 'Owner not found.'));
                exit();
            }

            $owner_id = $owner_user->id;

            // Bypass CURL for notifications
            $rise_config = config('Rise');
            $rise_config->app_settings_array['log_direct_notifications'] = "1";
            
            // Fields
            $company_name = $this->request->getPost('client_name');
            $contact_person = $this->request->getPost('contact_person');
            $email = $this->request->getPost('contact_email');
            $phone = $this->request->getPost('contact_phone');
            $job_title = $this->request->getPost('contact_job_title');
            $remarks = $this->request->getPost('remarks');
            $feedback = $this->request->getPost('feedback');
            $note_content = trim(($remarks ? "Remarks: " . $remarks . "\n" : "") . ($feedback ? "Feedback: " . $feedback : ""));

            $first_name = "";
            $last_name = "";
            if ($contact_person) {
                $parts = explode(" ", $contact_person, 2);
                $first_name = $parts[0];
                $last_name = isset($parts[1]) ? $parts[1] : "";
            }

            // Lead Source ID handling
            $lead_source_id = $this->request->getPost("lead_source_id");
            if (empty($lead_source_id)) {
                $lead_source_id = 0; 
            }

            // Lead Data
            $leads_data = array(
                "company_name" => $company_name,
                "phone" => $phone,
                "is_lead" => 1,
                "lead_status_id" => $this->Lead_status_model->get_first_status(), 
                "lead_source_id" => $lead_source_id,
                "created_date" => get_current_utc_time(),
                "owner_id" => $owner_id
            );

            // Logic for type (organization/person)
            $entity_type = $this->request->getPost('entity_type');
            if ($entity_type && strtolower($entity_type) == 'person') {
                $leads_data["type"] = "person";
                 if (!$company_name && $first_name) {
                    $leads_data["company_name"] = $first_name . " " . $last_name;
                 }
            } else {
                $leads_data["type"] = "organization";
            }

            $lead_id = $this->Clients_model->ci_save($leads_data);

            if ($lead_id) {
                $contact_id = 0;
                
                // Add Contact
                if ($first_name || $last_name || $email) {
                    $lead_contact_data = array(
                        "first_name" => $first_name ? $first_name : "",
                        "last_name" => $last_name ? $last_name : "",
                        "client_id" => $lead_id,
                        "user_type" => "lead",
                        "email" => trim($email),
                        "phone" => $phone,
                        "job_title" => $job_title,
                        "created_at" => get_current_utc_time(),
                        "is_primary_contact" => 1
                    );
                    $contact_id = $this->Users_model->ci_save($lead_contact_data);
                }

                // Add Note if content exists
                $note_id = 0;
                if ($note_content) {
                    $note_data = array(
                        "title" => "Lead Remarks",
                        "description" => $note_content,
                        "created_by" => $owner_id,
                        "created_at" => get_current_utc_time(),
                        "client_id" => $lead_id,
                        "is_public" => 0 // Only visible to team members involved
                    );
                    $note_id = $this->Notes_model->ci_save($note_data);
                }

                log_notification("lead_created", array("lead_id" => $lead_id), "0"); // 0 for system or external

                echo json_encode(array("success" => true, 'message' => 'Created', 'id' => $lead_id, 'contact_id' => $contact_id, 'note_id' => $note_id));
            } else {
                echo json_encode(array("success" => false, 'message' => 'Failed to save lead'));
            }

        } catch (\Throwable $e) {
            echo json_encode(array("success" => false, 'message' => 'Exception: ' . $e->getMessage()));
        }
    }
}
