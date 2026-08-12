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
        $this->Lead_status_model = model("App\Models\Lead_status_model");
        $this->Notes_model = model("App\Models\Notes_model");
        $this->Events_model = model("App\Models\Events_model");
        
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

                // log_notification("lead_created", array("lead_id" => $lead_id), "0"); // 0 for system or external

                echo json_encode(array("success" => true, 'message' => 'Created', 'id' => $lead_id, 'contact_id' => $contact_id, 'note_id' => $note_id));
            } else {
                echo json_encode(array("success" => false, 'message' => 'Failed to save lead'));
            }

        } catch (\Throwable $e) {
            echo json_encode(array("success" => false, 'message' => 'Exception: ' . $e->getMessage()));
        }
    }

    function get_daily_schedules() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($this->request->getMethod() === 'options') {
            exit;
        }

        try {
            $rules = [
                "owner_email" => "required|valid_email"
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

            // Get events for today
            $today = get_my_local_time("Y-m-d"); 
            $options = [
                'user_id' => $owner_user->id,
                'start_date' => $today,
                'end_date' => $today,
                'type' => 'event' // Only events, not tasks
            ];
            
            // Re-using get_details but it doesn't support "has_lead_id". 
            // We just fetch all user's events for today and filter.
            $events_raw = $this->Events_model->get_details($options)->getResult();
            
            $schedules = [];
            foreach($events_raw as $event) {
                // Filter by Date (Strictly Today) to fix model issue returning all events
                // Check if Today is between Start and End date of the event
                if ($event->start_date > $today || ($event->end_date && $event->end_date < $today)) {
                    continue;
                }

                // Check if event is linked to a lead explicitly OR linked to a client that IS a lead
                $is_linked_to_lead = !empty($event->lead_id);
                $is_client_lead = (!empty($event->client_id) && isset($event->is_lead) && $event->is_lead == 1);

                if($is_linked_to_lead || $is_client_lead) {
                    $target_lead_id = $is_linked_to_lead ? $event->lead_id : $event->client_id;
                    
                    $schedules[] = [
                        'id' => $event->id,
                        'title' => $event->title,
                        'lead_id' => $target_lead_id,
                        'start_time' => $event->start_time,
                        'end_time' => $event->end_time,
                        'client_name' => $event->company_name ? $event->company_name : 'Lead #' . $target_lead_id,
                        'description' => $event->description
                    ];
                }
            }

            // Also fetch Leads created today? User said "schedule list". 
            // Typically means planned events. Sticking to events.

            // Fetch Lead Statuses for the dropdown
            $statuses = $this->Lead_status_model->get_details()->getResult();
            $status_list = [];
            foreach($statuses as $st) {
                $status_list[] = ['id' => $st->id, 'title' => $st->title];
            }

            echo json_encode(array("success" => true, 'schedules' => $schedules, 'statuses' => $status_list));

        } catch (\Throwable $e) {
            echo json_encode(array("success" => false, 'message' => 'Exception: ' . $e->getMessage()));
        }
    }

    function update_lead_status() {
         header('Access-Control-Allow-Origin: *');
         header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
         header('Access-Control-Allow-Headers: Content-Type, Authorization');

         if ($this->request->getMethod() === 'options') {
             exit;
         }

         try {
             $rules = [
                 "lead_id" => "required",
                 "status" => "required"
             ];
             
             if (!$this->validate($rules)) {
                  echo json_encode(array("success" => false, 'message' => $this->validator->getErrors()));
                  exit();
             }
             
             $lead_id = $this->request->getPost('lead_id');
             $status_id = $this->request->getPost('status'); // Assuming ID is passed
             $remarks = $this->request->getPost('remarks');
             $feedback = $this->request->getPost('feedback'); 
             $owner_email = $this->request->getPost('owner_email');
             $company_name = $this->request->getPost('client_name');
             $contact_person = $this->request->getPost('contact_person');
             $email = $this->request->getPost('contact_email');
             $phone = $this->request->getPost('contact_phone');
             $job_title = $this->request->getPost('contact_job_title');

             // Lookup lead owner if owner_email is provided
             $owner_id = 0;
             if ($owner_email) {
                 $owner_user = $this->Users_model->get_one_where(array("email" => $owner_email, "deleted" => 0, "status" => "active", "user_type" => "staff"));
                 if ($owner_user->id) {
                     $owner_id = $owner_user->id;
                 }
             }

             // Update Lead Status and Details
             $lead_data = [];

             // Check if status_id is a valid lead status in CRM
             if ($status_id) {
                 $status_exists = $this->Lead_status_model->get_one($status_id);
                 if ($status_exists->id) {
                     $lead_data['lead_status_id'] = $status_id;
                 }
             }

             if ($company_name !== null && trim($company_name) !== "") {
                 $lead_data["company_name"] = $company_name;
             }
             if ($phone !== null && trim($phone) !== "") {
                 $lead_data["phone"] = $phone;
             }
             if ($owner_id) {
                 $lead_data["owner_id"] = $owner_id;
             }

             if (!empty($lead_data)) {
                 $this->Clients_model->ci_save($lead_data, $lead_id);
             }

             // Parse contact person's name
             $first_name = "";
             $last_name = "";
             if ($contact_person !== null && trim($contact_person) !== "") {
                 $parts = explode(" ", $contact_person, 2);
                 $first_name = $parts[0];
                 $last_name = isset($parts[1]) ? $parts[1] : "";
             }

             // Update or create Lead primary contact details
             $primary_contact_id = $this->Clients_model->get_primary_contact($lead_id);
             if ($primary_contact_id) {
                 $contact_data = array();
                 if ($contact_person !== null && trim($contact_person) !== "") {
                     $contact_data["first_name"] = $first_name;
                     $contact_data["last_name"] = $last_name;
                 }
                 if ($email !== null && trim($email) !== "") {
                     $contact_data["email"] = trim($email);
                 }
                 if ($phone !== null && trim($phone) !== "") {
                     $contact_data["phone"] = $phone;
                 }
                 if ($job_title !== null && trim($job_title) !== "") {
                     $contact_data["job_title"] = $job_title;
                 }

                 if (!empty($contact_data)) {
                     $this->Users_model->ci_save($contact_data, $primary_contact_id);
                 }
             } else {
                 if (($contact_person !== null && trim($contact_person) !== "") || 
                     ($email !== null && trim($email) !== "") || 
                     ($phone !== null && trim($phone) !== "") || 
                     ($job_title !== null && trim($job_title) !== "")) {
                     $contact_data = array(
                         "first_name" => $first_name ? $first_name : "",
                         "last_name" => $last_name ? $last_name : "",
                         "client_id" => $lead_id,
                         "user_type" => "lead",
                         "email" => $email ? trim($email) : "",
                         "phone" => $phone ? $phone : "",
                         "job_title" => $job_title ? $job_title : "",
                         "created_at" => get_current_utc_time(),
                         "is_primary_contact" => 1
                     );
                     $this->Users_model->ci_save($contact_data);
                 }
             }
             
             // Add Note
             if ($remarks || $feedback) {
                 $note_content = trim(($remarks ? "Meeting Remarks: " . $remarks . "\n" : "") . ($feedback ? "Feedback: " . $feedback : ""));
                 
                 $creator_id = $owner_id;
                 if (!$creator_id && $owner_email) {
                     $owner = $this->Users_model->get_one_where(array("email" => $owner_email));
                     if ($owner->id) {
                         $creator_id = $owner->id;
                     }
                 }

                 $note_data = array(
                     "title" => "Meeting Feedback",
                     "description" => $note_content,
                     "created_by" => $creator_id,
                     "created_at" => get_current_utc_time(),
                     "client_id" => $lead_id,
                     "is_public" => 0
                 );
                 $this->Notes_model->ci_save($note_data);
             }
             
             echo json_encode(array("success" => true, 'message' => 'Lead status updated'));

         } catch (\Throwable $e) {
             echo json_encode(array("success" => false, 'message' => 'Exception: ' . $e->getMessage()));
         }
    }
}
