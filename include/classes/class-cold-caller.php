<?php

use mikehaertl\pdftk\Pdf;

if(!class_exists('ColdCaller')){

    class ColdCaller extends GamFunctions{

        function __construct(){

            add_action("wp_ajax_calculate_cold_caller_performance",array($this,'calculate_cold_caller_performance'));
            add_action("wp_ajax_nopriv_calculate_cold_caller_performance",array($this,'calculate_cold_caller_performance'));

            add_action("wp_ajax_delete_lead",array($this,'delete_lead'));
            add_action("wp_ajax_nopriv_delete_lead",array($this,'delete_lead'));

            add_action("wp_ajax_check_for_cold_caller_username",array($this,'check_for_cold_caller_username'));
            add_action("wp_ajax_nopriv_check_for_cold_caller_username",array($this,'check_for_cold_caller_username'));

            add_action("wp_ajax_link_invoice_to_cold_caller_lead",array($this,'link_invoice_to_cold_caller_lead'));
            add_action("wp_ajax_nopriv_link_invoice_to_cold_caller_lead",array($this,'link_invoice_to_cold_caller_lead'));

            add_action("wp_ajax_cold_caller_login",array($this,'cold_caller_login'));
            add_action("wp_ajax_nopriv_cold_caller_login",array($this,'cold_caller_login'));

            add_action("admin_post_create_cold_caller",array($this,'create_cold_caller'));
            add_action("admin_post_nopriv_create_cold_caller",array($this,'create_cold_caller'));        

            add_action("admin_post_create_cold_caller_lead",array($this,'create_cold_caller_lead'));
            add_action("admin_post_nopriv_create_cold_caller_lead",array($this,'create_cold_caller_lead'));
            
            add_action("admin_post_logout_cold_caller",array($this,'logout_cold_caller'));
            add_action("admin_post_nopriv_logout_cold_caller",array($this,'logout_cold_caller'));
            
            add_action("template_redirect",array($this,"check_for_page_access"));
            
            add_action("wp_ajax_check_for_username_wp",array($this,'check_for_username_wp'));
            add_action("wp_ajax_nopriv_check_for_username_wp",array($this,'check_for_username_wp'));

            add_action("wp_ajax_check_for_email",array($this,'check_for_email'));
            add_action("wp_ajax_nopriv_check_for_email",array($this,'check_for_email'));
            
            add_action( 'admin_post_update_cold_caller_data', array($this,'update_cold_caller_data'));
            add_action( 'admin_post_nopriv_update_cold_caller_data', array($this,'update_cold_caller_data'));
            
            add_action( 'wp_ajax_fire_cold_caller', array($this,'fire_cold_caller'));
            add_action( 'wp_ajax_nopriv_fire_cold_caller', array($this,'fire_cold_caller'));

            add_action( 'admin_post_update_cold_caller_payment_method', array($this,'update_cold_caller_payment_method'));
            add_action( 'admin_post_nopriv_update_cold_caller_payment_method', array($this,'update_cold_caller_payment_method'));

            add_action( 'admin_post_verify_cold_caller', array($this,'verify_cold_caller'));
            add_action( 'admin_post_nopriv_verify_cold_caller', array($this,'verify_cold_caller'));

            add_action( 'wp_ajax_cc_get_refreshed_commission', array($this,'cc_get_refreshed_commission'));
            add_action( 'wp_ajax_nopriv_cc_get_refreshed_commission', array($this,'cc_get_refreshed_commission'));

            add_action( 'wp_ajax_rehire_cold_caller', array($this,'rehire_cold_caller'));
            add_action( 'wp_ajax_nopriv_rehire_cold_caller', array($this,'rehire_cold_caller'));

            add_action( 'wp_ajax_delete_cold_caller_ac', array($this,'delete_cold_caller_ac'));
            add_action( 'wp_ajax_nopriv_delete_cold_caller_ac', array($this,'delete_cold_caller_ac'));
            
            add_action("wp_ajax_insert_cold_caller_edit_code",array($this,"insert_cold_caller_edit_code"));
            add_action("wp_ajax_nopriv_insert_cold_caller_edit_code",array($this,"insert_cold_caller_edit_code"));
            
            add_action("wp_ajax_verify_cold_caller_edit_code",array($this,"verify_cold_caller_edit_code"));
            add_action("wp_ajax_nopriv_verify_cold_caller_edit_code",array($this,"verify_cold_caller_edit_code"));
            
            add_action("admin_post_cancel_cold_caller_form_edit",array($this,'cancel_cold_caller_form_edit'));
            add_action("admin_post_nopriv_cancel_cold_caller_form_edit",array($this,'cancel_cold_caller_form_edit'));
            
            add_action( 'admin_post_nopriv_edit_cold_caller', array($this,'edit_cold_caller') );
            add_action( 'admin_post_edit_cold_caller', array($this,'edit_cold_caller') );
            
            add_action( 'admin_post_update_cold_caller_password', array($this,'update_cold_caller_password'));
            add_action( 'admin_post_nopriv_update_cold_caller_password', array($this,'update_cold_caller_password'));

            add_action( 'admin_post_temp_update_fw9_form_fields', array($this,'temp_update_fw9_form_fields'));
            add_action( 'admin_post_nopriv_temp_update_fw9_form_fields', array($this,'temp_update_fw9_form_fields'));

            add_action("wp_ajax_cold_caller_signup_code",array($this,"cold_caller_signup_code"));
            add_action("wp_ajax_nopriv_cold_caller_signup_code",array($this,"cold_caller_signup_code"));

            add_action("admin_post_cc_create_cc_type",array($this,"cc_create_cc_type"));
            add_action("admin_post_nopriv_cc_create_cc_type",array($this,"cc_create_cc_type"));
        }

        public function cc_create_cc_type(){
            global $wpdb;

            $this->verify_nonce_field('cc_create_cc_type');

            $page_url = esc_url_raw($_POST['page_url']);

            if(empty($_POST['title'])) $this->sendErrorMessage($page_url);

            $title = $this->sanitizeEscape($_POST['title']);

            $data = ['name' => $title];

            $response = $wpdb->insert($wpdb->prefix."cold_caller_types", $data);
            if(!$response) $this->sendErrorMessage($page_url);

            $message = "Cold caller type created successfully";
            $this->setFlashMessage($message, 'success');

            wp_redirect($page_url);
        }

        public function cold_caller_signup_code(){
            global $wpdb;

            if(empty($_POST['name'])) $this->response('error', $this->err_msg);

            $name = sanitize_text_field($_POST['name']);

            $data=[
                'name'  =>  $name,
                'type'  =>  'cold_caller_signup',
                'code'  =>  mt_rand(100000, 999999)
            ];

            $res = $wpdb->insert($wpdb->prefix.'technician_codes',$data);

            if(!$res) $this->response('error', $this->err_msg);

            $this->response('success', 'code generated', ['db_id' => $wpdb->insert_id]);
        }    

        public function temp_update_fw9_form_fields(){
            global $wpdb;

            $cold_caller_id = $this->getLoggedInColdCallerId();

            $page_url = $_POST['page_url'];

            $data = [
                'address'                   =>  $_POST['address'],
                'city_state_zipcode'        =>  $_POST['city_state_zipcode'],
                'social_security_number'    =>  $_POST['social_security_number'],
            ];


            $res = $wpdb->update($wpdb->prefix."cold_callers", $data, ['id' => $cold_caller_id]);

            if(!$res) $this->sendErrorMessage($page_url);

            // generate fw9 form pdf
            $pdf_path = $this->generateW9Pdf($cold_caller_id);

            // update the pdf path in database

            $res = $wpdb->update($wpdb->prefix."cold_callers", ['w9_pdf_path' => $pdf_path], ['id' => $cold_caller_id]);

            if(!$res) $this->sendErrorMessage($page_url);

            $message = "fw9 document generated and saved in system successfully";
            $this->setFlashMessage($message, 'success');

            wp_redirect($page_url);
        }

        public function verify_cold_caller(){
            global $wpdb;

            $this->verify_nonce_field('verify_cold_caller');
            
            $this->beginTransaction();

            $page_url = esc_url_raw($_POST['page_url']);

            if(empty($_POST['cold_caller_id'])) $this->sendErrorMessage($page_url);
            if(empty($_POST['branch_id'])) $this->sendErrorMessage($page_url);

            $cold_caller_id = $this->sanitizeEscape($_POST['cold_caller_id']);
            $branch_id = $this->sanitizeEscape($_POST['branch_id']);

            // first try to verify in wp_employee table 
            $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);
            if(!$employee_id) $this->rollBackTransaction($page_url);

            $response = (new Employee\Employee)->verifyEmployee($employee_id);
            if(!$response) $this->rollBackTransaction($page_url);

            // now try to update in wp_cold_callers table
            $application_data = [
                'application_status'    =>  'verified',
                'branch_id'             =>  $branch_id,
            ];
            if(!$this->updateColdCaller($cold_caller_id, $application_data)) $this->rollBackTransaction($page_url);
            
            $response = (new OfficeTasks)->linkBriaKey($employee_id);
            if(!$response) $this->rollBackTransaction($page_url);
            
            $this->commitTransaction();
            
            $message = "Cold caller application verified successfully";
            $this->setFlashMessage($message, "success");
            
            // redirect back to cold callers page
            wp_redirect(admin_url('admin.php?page=cold-caller'));
        }

        public function delete_cold_caller_ac(){
            global $wpdb;

            $this->beginTransaction();

            $this->verify_nonce_field('delete_cold_caller_ac');

            if(empty($_POST['cold_caller_id'])) $this->response('error');

            $cold_caller_id = esc_sql($_POST['cold_caller_id']);

            $response = $this->deleteColdCallerAccount($cold_caller_id);
            if(!$response){
                $wpdb->query('ROLLBACK');
                $this->response('error');                
            }

            $this->commitTransaction();

            $this->response('success','Cold Caller Ac deleted successfully');
        }

        public function deleteColdCallerAccount( int $cold_caller_id){
            global $wpdb;

            $application_status = $this->getColdCallerById($cold_caller_id, ['application_status']);
            if(!$application_status) return false;

            if((int)$application_status->application_status != 'pending') return false;

            // DELETE COLD CALLER ACCOUNT ROW
            $response = $wpdb->delete($wpdb->prefix."cold_callers", ['id' => $cold_caller_id]);
            if(!$response) return false;

            // IF NO RECORD IN EMPLOYEE TABLE THEN RETURN TRUE
            $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);
            if(!$employee_id) return true;

            // IF UNABLE TO DELETE , RETURN FALSE 
            $response = (new Employee\Employee)->deleteAccount($employee_id);
            if(!$response) return false;

            return true;
        }

        public function verifyColdCaller(int $cold_caller_id){
            $data = ['application_status' => 'verified'];
            return $this->updateColdCaller($cold_caller_id, $data);
        }

        public function rehire_cold_caller(){
            global $wpdb;

            // first verify nonce field
            $this->verify_nonce_field('rehire_cold_caller');

            if(empty($_POST['cold_caller_id'])) $this->response('error');

            $cold_caller_id = sanitize_text_field(esc_sql($_POST['cold_caller_id']));
            $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);

            $response = (new Employee\Employee)->verifyEmployee($employee_id);
            if(!$response) $this->response('error');

            $response = $this->verifyColdCaller($cold_caller_id);
            if(!$response) $this->response('error');

            $this->response('success','Cold Caller Re-Hired Successfully');
        }

        /*
            This method updates the cold caller payment type in system
            Return Type : JSON Response
            Date : 2021-07-15
            Developer : Gourav Punani
        */
        public function update_cold_caller_payment_method(){
            global $wpdb;

            $this->verify_nonce_field('update_cold_caller_payment_method');

            $status = true;

            if(isset($_POST['payment_type']) && !empty($_POST['payment_type']) && isset($_POST['cold_caller_id']) && !empty($_POST['cold_caller_id'])){

                $payment_type = esc_html($_POST['payment_type']);
                $cold_caller_id = esc_html($_POST['cold_caller_id']);

                if($payment_type == "$40_per_lead"){
                    // update to $40 per lead
                    $data = [
                        'payment_type'  =>  $payment_type,
                        'total_hours'   =>  '',
                        'pay_per_hour'  =>  ''
                    ];
                    $status = $wpdb->update($wpdb->prefix."cold_callers", $data, ['id' => $cold_caller_id]);

                }
                elseif($payment_type == "by_total_hours"){

                    if(isset($_POST['total_hours_worked']) && isset($_POST['pay_per_hour']) && !empty($_POST['total_hours_worked']) && !empty($_POST['pay_per_hour'])){

                        $total_hours_worked = esc_html($_POST['total_hours_worked']);
                        $pay_per_hour = esc_html($_POST['pay_per_hour']);

                        $data = [
                            'payment_type'  =>  $payment_type,
                            'total_hours'   =>  $total_hours_worked,
                            'pay_per_hour'  =>  $pay_per_hour
                        ];

                        $status = $wpdb->update($wpdb->prefix."cold_callers", $data, ['id' => $cold_caller_id]);
                    }

                }
                else{
                    $status = false;
                }

            }
            else{
                $status = false;
            }

            if($status){
                $message = "Cold caller payment structure updated successfully";
                $this->setFlashMessage($message,"success");
            }
            else{
                $message = "Something went wrong, please try again later";
                $this->setFlashMessage($message,"danger");
            }

            wp_redirect($_POST['page_url']);

        }

        /*
        This Method return all cold callers in system
        Return Type : Array
        Date : 2021-07-06
        Developer : Gourav Punani
        */
        public function getAllColdCallers( array $columns=[], bool $fired = true){
            global $wpdb;

            $columns = count($columns) > 0 ? implode (',', $columns ) : '*';

            $conditions = [];

            if($fired) $conditions[] = " application_status = 'verified' ";

            $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

            return $wpdb->get_results("
                select $columns 
                from {$wpdb->prefix}cold_callers
                $conditions
            ");
        }

        public function fireColdCaller(int $cold_caller_id){
            $cold_caller_data = ['application_status' => 'fired'];

            // change status to fired
            return $this->updateColdCaller($cold_caller_id, $cold_caller_data);
        }
        
        public function fire_cold_caller(){
            global $wpdb;
            
            // first verify nonce field
            $this->verify_nonce_field('fire_cold_caller');

            if(empty($_POST['cold_caller_id'])) $this->response('error');
            
            $cold_caller_id = $this->sanitizeEscape($_POST['cold_caller_id']);

            // first try to fire from wp_employee table
            $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);
            if(!$employee_id) $this->response('error');
            $response = (new Employee\Employee)->fireEmployee($employee_id);
            if(!$response) $this->response('error');
            
            // now try to fire from wp_cold_callers table
            if(!$this->fireColdCaller($cold_caller_id)) $this->response('error');

            $this->removeColdCallerHook($employee_id);

            $this->response('success','Cold Caller Fired Successfully');
        }

        public function updateColdCaller(int $cold_caller_id, array $data){
            global $wpdb;
            return $wpdb->update($wpdb->prefix."cold_callers", $data, ['id' => $cold_caller_id]);
        }

        public function removeColdCallerHook(int $employee_id){
            global $wpdb;

            // unlink bria license key
            (new Bria)->unlinkLicenseKey($employee_id);
        }
        
        public function update_cold_caller_data(){
            global $wpdb;

            $this->verify_nonce_field('update_cold_caller_data');

            $page_url = esc_url_raw($_POST['page_url']);

            if(
                empty($_POST['cold_caller_id']) ||
                empty($_POST['name']) ||
                empty($_POST['email']) ||
                empty($_POST['phone_no']) ||
                empty($_POST['address']) ||
                empty($_POST['city_state_zipcode']) ||
                empty($_POST['social_security_number']) ||
                empty($_POST['branch_id']) ||
                empty($_POST['skype']) ||
                empty($_POST['company_email'])            
            ) $this->sendErrorMessage($page_url);

            $cold_caller_id = $this->sanitizeEscape($_POST['cold_caller_id']);
            $name = $this->sanitizeEscape($_POST['name']);
            $email = $this->sanitizeEscape($_POST['email']);
            $phone_no = $this->sanitizeEscape($_POST['phone_no']);
            $address = $this->sanitizeEscape($_POST['address']);
            $city_state_zipcode = $this->sanitizeEscape($_POST['city_state_zipcode']);
            $social_security_number = $this->sanitizeEscape($_POST['social_security_number']);
            $branch_id = $this->sanitizeEscape($_POST['branch_id']);
            $skype = $this->sanitizeEscape($_POST['skype']);
            $company_email = $this->sanitizeEscape($_POST['company_email']);

            $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);

            $cold_caller_data = [
                'name'                      => $name,
                'email'                     => $email,
                'phone_no'                  => $phone_no,
                'address'                   => $address,
                'city_state_zipcode'        => $city_state_zipcode,
                'social_security_number'    => $social_security_number,
                'branch_id'                 => $branch_id,
                'skype'                     => $skype,
                'company_email'             => $company_email            
            ];

            $employee_update_data = [
                'name'                  =>  $name,
                'email'                 =>  $email,
                'phone_no'              =>  $phone_no,
                'address'               =>  $address,
            ];

            if(current_user_can('administrator')){
                if(
                    empty($_POST['status']) ||
                    empty($_POST['application_status'])
                ) $this->sendErrorMessage($page_url);

                $status = $this->sanitizeEscape($_POST['status']);
                $application_status = $this->sanitizeEscape($_POST['application_status']);

                $cold_caller_data['status'] = $status;
                $cold_caller_data['application_status'] = $application_status;

                $employee_update_data['status'] = $status == "active" ? '1' : '0';
                $employee_update_data['application_status'] = $application_status;

                if(!empty($_FILES['driving_license']['name'])){
                    $upload = $this->uploadSingleFile($_FILES['driving_license']);
                    $cold_caller_data['cold_caller_docs'] = $upload['url'];
                }
            }

            $response = $wpdb->update($wpdb->prefix."cold_callers", $cold_caller_data, ['id' => $cold_caller_id]);

            if(!$response) $this->sendErrorMessage($page_url);

            $response = (new Employee\Employee)->updateEmployee($employee_id, $employee_update_data);

            if(!$response) $this->sendErrorMessage($page_url);

            $message="Cold Caller data updated successfully";
            $this->setFlashMessage($message,'success');

            wp_redirect($page_url);
        }
        
        public function check_for_username_wp(){

            global $wpdb;
            
            $res=$wpdb->get_var("select count(*) from {$wpdb->prefix}cold_callers where username='{$_POST['username']}'");

            if($res){
                echo "false";
            }
            else{
                echo "true";
            }

            wp_die();

        }

        public function check_for_email(){

            global $wpdb;

            $res=$wpdb->get_var("select count(*) from {$wpdb->prefix}cold_callers where email='{$_POST['email']}'");

            if($res){
                echo "false";
            }
            else{
                echo "true";
            }

            wp_die();

        }

        public function dashboardUrl(){
            return site_url()."/cold-caller-dashboard/?view=attendance";
        }

        public function check_for_page_access(){

            // if not logged in then redirect to login page 
            if(is_page(['cold-caller-dashboard'])){
                if(!isset($_SESSION['cold_caller_id'])){
                    wp_redirect(site_url()."/cold-caller-login/");
                }
            }

            // if logged in and trying to access login page, then redirect to dashboard page
            if(is_page(['cold-caller-login'])){
                if(isset($_SESSION['cold_caller_id'])){
                    wp_redirect(site_url()."/cold-caller-dashboard/?view=attendance");
                }
            }

        }

        public function calculate_cold_caller_performance(){
            global $wpdb;

            $this->verify_nonce_field('calculate_cold_caller_performance');

            $from_date = $to_date = "";
            $conditions=[];

            if(
                empty($_POST['cold_caller_id']) ||
                empty($_POST['date_type'])
            ) $this->response('error');
            
            $cold_caller_id = $this->sanitizeEscape($_POST['cold_caller_id']);
            $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);
            if(empty($employee_id)) pdie("Linked employee ref not found, please contact developer for this issue");
            $date_type = $this->sanitizeEscape($_POST['date_type']);

            if($date_type == "date_range"){

                if(empty($_POST['from_date']) || empty($_POST['to_date'])) $this->response('error');

                $from_date = $this->sanitizeEscape($_POST['from_date']);
                $to_date = $this->sanitizeEscape($_POST['to_date']);

            }
            elseif($date_type == "year_month"){

                if(empty($_POST['year']) || empty($_POST['month'])) $this->response('error');

                $year = $this->sanitizeEscape($_POST['year']);
                $month = $this->sanitizeEscape($_POST['month']);

                $from_date=date('Y-m-d',strtotime($year."-".$month."-01"));
                $to_date=date('Y-m-t',strtotime($year."-".$month."-01"));
            }
            elseif($date_type == "all_time"){
                $cold_caller = $this->getColdCallerById($cold_caller_id, ['created_at']);
                $from_date = $cold_caller->created_at;
                $to_date = date('Y-m-d');
            }

            $leads_condition = [];
            $leads_condition[] = " cold_caller_id = '$cold_caller_id'";

            // leads
            $leads = $this->getLeads($leads_condition);
            $total_leads = count($leads);

            $leads_condition[] = " DATE(date) >= '$from_date' ";
            $leads_condition[] = " DATE(date) <= '$to_date' ";
            $leads_in_date_range = $this->getLeads($leads_condition);
            $leads_in_date_range_count = count($leads_in_date_range);

            $no_document_lead = [];
            $invoices = [];
            $residential_quotes = [];
            $commercial_quotes = [];
            $invoice_columns = ['client_name', 'address', 'email', 'date', 'total_amount', 'phone_no', 'callrail_id'];
            $res_quote_fields = ['clientName', 'clientAddress', 'clientPhn', 'clientEmail', 'date', 'total_cost', 'lead_status'];
            $comm_quote_fields = ['client_name', 'client_address', 'client_phone', 'clientEmail', 'date', 'initial_cost', 'lead_status'];

            foreach($leads as $key => $lead){
                
                $lead_found = false;

                // invoices
                $invoice_conditions = [];
                $invoice_conditions[] = " DATE(date) >= '$from_date' ";
                $invoice_conditions[] = " DATE(date) <= '$to_date' ";

                if(empty($lead->address) && empty($lead->address) && empty($lead->phone)) continue;

                $comparison_fields = [];

                if(!empty($lead->address)) $comparison_fields[] = " address = '$lead->address' ";
                if(!empty($lead->phone)) $comparison_fields[] = " phone_no = '$lead->phone' ";

                $comparison_fields = "(".implode(' or ', $comparison_fields).")";

                $invoice_conditions[] = " $comparison_fields ";

                $temp_invoices = (new Invoice)->getInvoices($invoice_conditions, $invoice_columns);

                if(is_array($temp_invoices) && count($temp_invoices) > 0) $lead_found = true; 

                foreach($temp_invoices as $temp_invoice){
                    $invoices[] = $temp_invoice;
                }

                // residential quotes
                $quote_condition = [];
                $quote_condition[] = " DATE(date) >= '$from_date' ";
                $quote_condition[] = " DATE(date) <= '$to_date' ";

                $comparison_fields = [];
                if(!empty($lead->address)) $comparison_fields[] = " clientAddress = '$lead->address' ";
                if(!empty($lead->phone)) $comparison_fields[] = " clientPhn = '$lead->phone' ";

                $comparison_fields = "(".implode(' or ', $comparison_fields).")";

                $quote_condition[] = " $comparison_fields ";                

                $temp_quotes = (new Quote)->getResidentialQuotes($quote_condition, $res_quote_fields);

                if(is_array($temp_quotes) && count($temp_quotes) > 0) $lead_found = true;

                foreach($temp_quotes as $temp_quote){
                    $residential_quotes[] = $temp_quote;
                }

                // commercial quotes
                $quote_condition = [];
                $quote_condition[] = " DATE(date) >= '$from_date' ";
                $quote_condition[] = " DATE(date) <= '$to_date' ";

                $comparison_fields = [];
                if(!empty($lead->address)) $comparison_fields[] = " client_address = '$lead->address' ";
                if(!empty($lead->phone)) $comparison_fields[] = " client_phone = '$lead->phone' ";

                $comparison_fields = "(".implode(' or ', $comparison_fields).")";

                $quote_condition[] = " $comparison_fields ";

                $temp_quotes = (new Quote)->getCommercialQuotes($quote_condition, $comm_quote_fields);
                if(is_array($temp_quotes) && count($temp_quotes) > 0) $lead_found= true;

                foreach($temp_quotes as $temp_quote){
                    $commercial_quotes[] = $temp_quote;
                }
                
                if(!$lead_found){
                    $no_document_lead[] = $lead;
                }
            }

            $total_invoices = count($invoices);

            $total_quotes = count($residential_quotes) + count($commercial_quotes);

            // weekly revenue
            $cost_vs_revenue = [];
            $total_payment = $total_recurring_revenue = $total_new_revenue = $total_revenue = 0;
            $weeks = $this->getWeeksBetweenDateRanges($from_date, $to_date);
            foreach($weeks as $key => $week){
                list($last_sunday, $this_saturday) = $this->weekRange($week);

                $week_payment = $wpdb->get_var("
                    select amount_paid
                    from {$wpdb->prefix}payments
                    where employee_id = '$employee_id'
                    and week = '$week'
                    and payment_status = 'paid'
                ");

                if(!empty($week_payment)) $total_payment += $week_payment;

                $week_revenue = $recurring_revenue = $new_revenue = 0;

                foreach($invoices as $invoice){
                    
                    $invoice_date = strtotime($invoice->date);
                    $start_date = strtotime($last_sunday);
                    $end_date = strtotime($this_saturday);

                    if($invoice_date >= $start_date && $invoice_date <= $end_date){
                        $week_revenue += $invoice->total_amount;
                        $total_revenue += $week_revenue;
                    }

                    if($invoice_date >= $start_date && $invoice_date <= $end_date && $invoice->callrail_id == "reoccuring_customer"){
                        $recurring_revenue += $invoice->total_amount;
                        $total_recurring_revenue += $recurring_revenue;
                    }
                    elseif($invoice_date >= $start_date && $invoice_date <= $end_date && $invoice->callrail_id != "reoccuring_customer"){
                        $new_revenue += $invoice->total_amount;
                        $total_new_revenue += $new_revenue;

                    }
                }

                $cost_vs_revenue[$key]['week'] = $week;
                $cost_vs_revenue[$key]['week_label'] = date('d M Y', strtotime($last_sunday))." To ".date('d M Y', strtotime($this_saturday)) ;
                $cost_vs_revenue[$key]['cost'] = empty($week_payment) ? 'Not paid yet' : $week_payment;
                $cost_vs_revenue[$key]['revenue'] = $week_revenue;
                $cost_vs_revenue[$key]['recurring_revenue'] = $recurring_revenue;
                $cost_vs_revenue[$key]['new_revenue'] = $new_revenue;
            }


            $performance_html = "
                <table class='table table-striped table-hover'>
                    <caption> Cost Vs Revenue Summary </caption>
                    <thead>
                        <th>Week</th>
                        <th>Recurring Revenue</th>
                        <th>New Revenue</th>
                        <th>Revenue</th>
                        <th>Pay/Cost</t>
                    </thead>
                    <tbody>
            ";

            foreach($cost_vs_revenue as $summary){
                $performance_html .= "
                    <tr>
                        <td>{$summary['week_label']}</td>
                        <td>\${$summary['recurring_revenue']}</td>
                        <td>\${$summary['new_revenue']}</td>
                        <td>\${$summary['revenue']}</td>
                        <td>{$summary['cost']}</td>
                    </tr>
                ";
            }

            $performance_html .= "
                    <tr>
                        <th>Summary</th>
                        <th>\$$total_recurring_revenue</th>
                        <th>\$$total_new_revenue</th>
                        <th>\$$total_revenue</th>
                        <th>\$$total_payment</th>
                    </tr>

            ";

            if($total_revenue >= $total_payment){
                $performance_html .= "
                    <tr>
                        <th class='text-right text-success' colspan='4'>Total Profit</th>
                        <th>\$".($total_revenue - $total_payment)."</th>
                    </tr>
                ";
            }
            else{
                $performance_html .= "
                    <tr>
                        <th class='text-right text-danger' colspan='4'>Total Loss</th>
                        <th>\$".($total_payment - $total_revenue)."</th>
                    </tr>
                ";
            }

            $performance_html .= "
                    </tbody>
                </table>            
            ";

            $performance_html .= "
                <table class='table table-striped table-hover'>
                    <caption>Leads</caption>
                    <tbody>
                        <tr>
                            <th>Total Leads</th>
                            <td>$total_leads</td>
                            <th>Date Range Leads</th>
                            <td>$leads_in_date_range_count</td>
                        </tr>
                    </tbody>
                </table>

                <table class='table table-striped table-hover'>
                    <caption>Invoices Summary</caption>
                    <tbody>
                        <tr>
                            <th>Total Linked Invoices </th>
                            <td>$total_invoices</td>
                            <th>Total Invoice Revenue</th>
                            <td>$$total_revenue</td>
                        </tr>
                    </tbody>
                </table>

                <table class='table table-striped table-hover'>
                    <caption>Quote Summary</caption>
                    <tbody>
                        <tr>
                            <th>Total Linked Quotes</th>
                            <td>$total_quotes</td>
                        </tr>
                        <tr>
                            <th>Total Residential Quotes</th>
                            <td>".count($residential_quotes)."</td>
                            <th>Total Commercial Quotes</th>
                            <td>".count($commercial_quotes)."</td>
                    </tbody>
                </table>                

            <div class='panel-group'>
                <div class='panel panel-default'>
                    <div class='panel-heading'>
                        <h4 class='panel-title'>
                            <a data-toggle='collapse' href='#collapse1'><span><i class='fa fa-arrow-down'></i></span> Invoices Listing ($total_invoices records)</a>
                        </h4>
                    </div>
                    <div id='collapse1' class='panel-collapse collapse'>
                        <div class='panel-body'>
                            <table class='table table-striped table-hover'>
                                <caption>Invoice Listing</caption>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th>Phone</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>                        
            ";

            foreach($invoices as $invoice){
                $performance_html .= "
                    <tr>
                        <td>$invoice->client_name</td>
                        <td>$invoice->email</td>
                        <td>$invoice->address</td>
                        <td>$invoice->phone_no</td>
                        <td>$$invoice->total_amount</td>
                        <td>$invoice->date</td>
                    </tr>
                ";
            }

            $performance_html .= "
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class='panel-group'>
                    <div class='panel panel-default'>
                        <div class='panel-heading'>
                            <h4 class='panel-title'>
                                <a data-toggle='collapse' href='#collapse2'><span><i class='fa fa-arrow-down'></i></span> Residential Quotes Listing (".count($residential_quotes)." Records)</a>
                            </h4>
                        </div>
                        <div id='collapse2' class='panel-collapse collapse'>
                            <div class='panel-body'>
                                <table class='table table-striped table-hover'>
                                    <caption>Residential Quotes Listing</caption>
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Address</th>
                                            <th>Phone</th>
                                            <th>Amount</th>
                                            <th>Lead Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            ";

            foreach($residential_quotes as $quote){
                $performance_html .= "
                    <tr>
                        <td>$quote->clientName</td>
                        <td>$quote->clientEmail</td>
                        <td>$quote->clientAddress</td>
                        <td>$quote->clientPhn</td>
                        <td>$$quote->total_cost</td>
                        <td>$quote->lead_status</td>
                        <td>$quote->date</td>
                    </tr>
                ";
            }

            $performance_html .= "
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='panel-group'>
                    <div class='panel panel-default'>
                        <div class='panel-heading'>
                            <h4 class='panel-title'>
                                <a data-toggle='collapse' href='#collapse3'><span><i class='fa fa-arrow-down'></i></span> Commercial Quotes Listing (".count($commercial_quotes)." Records)</a>
                            </h4>
                        </div>
                        <div id='collapse3' class='panel-collapse collapse'>
                            <div class='panel-body'>
                                <table class='table table-striped table-hover'>
                                    <caption>Commercial Quotes Listing</caption>
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Address</th>
                                            <th>Phone</th>
                                            <th>Initial Cost</th>
                                            <th>Lead Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            ";

            foreach($commercial_quotes as $quote){
                $performance_html .= "
                    <tr>
                        <td>$quote->client_name</td>
                        <td>$quote->clientEmail</td>
                        <td>$quote->client_address</td>
                        <td>$quote->client_phone</td>
                        <td>$$quote->initial_cost</td>
                        <td>$quote->lead_status</td>
                        <td>$quote->date</td>
                    </tr>
                ";
            }

            $performance_html .= "
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='panel-group'>
                    <div class='panel panel-default'>
                        <div class='panel-heading'>
                            <h4 class='panel-title'>
                                <a data-toggle='collapse' href='#collapse4'><span><i class='fa fa-arrow-down'></i></span> No Document/Form Lead (".count($no_document_lead)." Records)</a>
                            </h4>
                        </div>
                        <div id='collapse4' class='panel-collapse collapse'>
                            <div class='panel-body'>
                                <table class='table table-striped table-hover'>
                                    <caption>No Document/Form Lead (no invoice/quote found for these leads)</caption>
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Address</th>
                                            <th>Phone No.</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            ";

            foreach($no_document_lead as $lead){
                $performance_html .= "
                    <tr>
                        <td>$lead->name</td>
                        <td>$lead->email</td>
                        <td>$lead->address</td>
                        <td>$lead->phone</td>
                        <td>$lead->date</td>
                    </tr>
                ";
            }

            $performance_html .= "
                    </tbody>
                </table>
            ";

            echo $performance_html;wp_die();
        }

        public function getLeads( array $conditions, array $columns = []){
            global $wpdb;

            $conditions = $this->generate_query($conditions);
            $columns = count($columns) > 0 ? implode(',', $columns) : '*';

            return $wpdb->get_results("
                select $columns
                from {$wpdb->prefix}leads
                $conditions
            ");
        }

        public function delete_lead(){
            global $wpdb;

            if(isset($_POST['lead_id']) && !empty($_POST['lead_id'])){
                $res=$wpdb->delete($wpdb->prefix."leads",['id'=>$_POST['lead_id']]);
                if($res){
                    $this->response('success','Lead deleted successfully');
                }
                else{
                    $this->response('error','Something went wrong, please try again later');
                }
            }
            else{
                $this->response('error','Something went wrong, please try again later');
            }

        }

        public function check_for_cold_caller_username(){

            global $wpdb;
            
            $res=$wpdb->get_var("select count(*) from {$wpdb->prefix}cold_callers where username='{$_POST['username']}'");

            if($res){
                echo "false";
            }
            else{
                echo "true";
            }

            wp_die();

        } 

        public function cold_caller_login(){
            global $wpdb;

            $this->verify_nonce_field('cold_caller_login');

            if(empty($_POST['username']) || empty($_POST['password'])) $this->response('error','username or passowrd are not set');
            
            $username = sanitize_text_field($_POST['username']);
            $password = $_POST['password'];

            $user_details=$wpdb->get_row("
                select id,password
                from {$wpdb->prefix}cold_callers 
                where (username='$username' or email = '$username')
                and application_status = 'verified'
            ");

            if(!$user_details) $this->response('error','No user found with the given details');

            if(!password_verify($password, $user_details->password)) $this->response('error','incorrect password');

            $this->doLogin($user_details->id);

            $this->response('success');
        }

        public function doLogin(int $cold_caller_id){
            global $wpdb;
            $_SESSION['cold_caller_id'] = $cold_caller_id;

            $_SESSION['employee_id'] = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);

            $_SESSION['redirect_to_attendance'] = 1;
            
            return $this;
        }

        public function link_invoice_to_cold_caller_lead(){
            global $wpdb;
            $this->verify_nonce_field('link_invoice_to_cold_caller_lead');
            if(isset($_POST['invoice_no']) && !empty($_POST['invoice_no']) && isset($_POST['lead_id']) && !empty($_POST['lead_id'])){
                $data=[
                    'invoice_no'    =>  $_POST['invoice_no']
                ];

                $res=$wpdb->update($wpdb->prefix."leads",$data,['id'=>$_POST['lead_id']]);

                if($res){
                    $this->response('success','Invoice linked to lead successfully');
                }
                else{
                    $this->response('error','Something went wrong while linking lead, please try again later');
                }

            }
            else{
                $this->response('error','fields are not set or empty value');
            }


        }


        public function create_cold_caller_lead(){
            global $wpdb;

            $this->verify_nonce_field('create_cold_caller_lead');

            $page_url = esc_url_raw($_POST['page_url']);

            if(
                empty($_POST['lead_type']) ||
                empty($_POST['establishment_name']) ||
                empty($_POST['name']) ||
                empty($_POST['phone']) ||
                empty($_POST['address']) ||
                empty($_POST['time']) ||
                empty($_POST['day']) ||
                empty($_POST['notes'])
            ) $this->sendErrorMessage($page_url);

            $cold_caller_id = $this->getLoggedInColdCallerId();

            $data=[
                'cold_caller_id'        =>  $cold_caller_id,
                'lead_type'             =>  $this->sanitizeEscape($_POST['lead_type']) ,
                'establishment_name'    =>  $this->sanitizeEscape($_POST['establishment_name']) ,
                'name'                  =>  $this->sanitizeEscape($_POST['name']) ,
                'phone'                 =>  $this->sanitizeEscape($_POST['phone']) ,
                'address'               =>  $this->sanitizeEscape($_POST['address']) ,
                'time'       		    =>  $this->sanitizeEscape($_POST['time']) ,
                'day'       		    =>  $this->sanitizeEscape($_POST['day']) ,
                'notes'       		    =>  $this->sanitizeEscape($_POST['notes'], 'textarea') ,
                'date'                  =>  date('Y-m-d'),
            ];

            if(!empty($_POST['email'])) $data['email'] = sanitize_email($_POST['email']);

            $response = $this->createLead($data);
            if(!$response) $this->sendErrorMessage($page_url);

            $message = "Lead created successfully";
            $this->setFlashMessage($message, 'success');

            wp_redirect($page_url);
        }

        public function createLead(array $data){
            global $wpdb;
            return $wpdb->insert($wpdb->prefix."leads",$data);
        }

        public function createColdcaller(array $data){
            global $wpdb;

            $data['created_at'] = date('Y-m-d h:i:s');
            $data['updated_at'] = date('Y-m-d h:i:s');

            $response = $wpdb->insert($wpdb->prefix."cold_callers", $data);

            return $response === false ? false : $wpdb->insert_id;
        }

        public function create_cold_caller(){
            global $wpdb;

            $this->beginTransaction();

            $page_url = esc_url_raw($_POST['page_url']);

            // verify for nonce field first
            (new GamFunctions)->verify_nonce_field('create_cold_caller');

            if(
                empty($_POST['email']) ||
                empty($_POST['password']) ||
                empty($_POST['name']) ||
                empty($_POST['phone']) ||
                empty($_POST['address']) ||
                empty($_POST['city_state_zipcode']) ||
                empty($_POST['social_security_number'])   
            ) $this->sendErrorMessage($page_url);

            $username = (new Employee\Employee)->generateUsername($_POST['name']);
            $password = password_hash($_POST['password'],PASSWORD_DEFAULT);

            $email = sanitize_text_field($_POST['email']);
            $name = sanitize_text_field($_POST['name']);
            $phone = sanitize_text_field($_POST['phone']);
            $address = sanitize_text_field($_POST['address']);
            $city_state_zipcode = sanitize_text_field($_POST['city_state_zipcode']);
            $social_security_number = sanitize_text_field($_POST['social_security_number']);

            $data=[
                'username'                  =>  $username,
                'password'                  =>  $password,
                'email'                     =>  $email,
                'name'                      =>  $name,
                'phone_no'                  =>  $phone,
                'date'                      =>  date('Y-m-d'),
                'status'                    =>  'active',
                'address'                   =>  $address,
                'city_state_zipcode'        =>  $city_state_zipcode,
                'social_security_number'    =>  $social_security_number,
                'application_status'        =>  'pending' //pending verification
            ];

            if(!empty($_FILES['doc_proof']['name'])){
                $upload = $this->uploadSingleFile($_FILES['doc_proof']);
                if($upload) $data['cold_caller_docs'] = $upload['url'];
            }

            $cold_caller_id = $this->createColdcaller($data);
            if(!$cold_caller_id) $this->rollBackTransaction($page_url);

            $employee_data = [
                'employee_ref_id'       =>  $cold_caller_id,
                'username'              =>  $username,
                'password'              =>  $password,
                'name'                  =>  $name,
                'email'                 =>  $email,
                'address'               =>  $address,
                'phone_no'              =>  $phone,
                'role_id'               =>  '2',
            ];

            $employee_id = (new Employee\Employee)->createEmployee($employee_data);
            if(!$employee_id) $this->rollBackTransaction($page_url);

            // generate w9 pdf
            $w9_pdf_path = $this->generateW9Pdf($cold_caller_id);
            if(!$w9_pdf_path) $this->rollBackTransaction($page_url);

            $update_data = ['w9_pdf_path' => $w9_pdf_path];
            $response = $this->updateColdCaller($cold_caller_id, $update_data);
            if(!$response) $this->rollBackTransaction($page_url);

            $wpdb->query('COMMIT');

            $message = "Form submitted successfully, you'll receive update on your profile from office soon.";
            $this->setFlashMessage($message,'success');

            wp_redirect($page_url);
        }

        public function generateW9Pdf( int $cold_caller_id ){
            global $wpdb;

            $cold_caller = $this->getColdCallerById($cold_caller_id);
            if(!$cold_caller) return null;

            // load pdftk php sdk from vendor 
            self::loadVendor();
            
            $upload_dir=wp_upload_dir();
            
            // generate fw9 pdf
            $input_file_path=get_template_directory()."/assets/pdf/cold-caller/fw9.pdf";
            $path="/pdf/cold-caller/fw9/";
            $directory_path=$upload_dir['basedir'].$path;
            $db_saving_path=$path.date('Y/m/d/').date('his')."_".$this->quickRandom(6).".pdf";
            $this->genreate_saving_directory($directory_path);
            $output_file_path=$upload_dir['basedir'].$db_saving_path;

            
            $pdf_data = [
                'cc-name'   =>  $cold_caller->name,
                'cc-address'    =>  $cold_caller->address,
                'cc-city-state-zip' =>  $cold_caller->city_state_zipcode,
                'cc-date'   =>  date('Y-m-d'),
                'cc-signature'  =>  strtoupper($cold_caller->name)
            ];

            $security_number = str_split($cold_caller->social_security_number);

            foreach($security_number as $key => $number){
                $pdf_data["cc-ss-".($key+1)] = $number;
            }
            
            // Fill form with data array
            try{
                $pdf = new Pdf($input_file_path);
                $result = $pdf->fillForm($pdf_data)
                    ->needAppearances()
                    ->flatten()
                    ->saveAs($output_file_path);

                return $db_saving_path;
            }
            catch(Exception $e){
                return '';
            }
        }
        
        public function logout_cold_caller(){
            global $wpdb;

            $edit_id = $_POST['close_time'];
            $data = [
                'close_time' => date('h:i:s'),
            ];
            // pdie($data);
            if(!empty($data)) {
                $where = [ 'id' => $edit_id ];
                $wpdb->update( $wpdb->prefix . 'attendance', $data, $where );
            }
    

            // unset the session id for coldcaller
            unset($_SESSION['cold_caller_id']);
            wp_redirect(site_url()."/cold-caller-login");
        }

        public function getColdCallers(array $conditions = [], array $columns = [], bool $single =  false){
            global $wpdb;
            
            $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';
            $columns = count($columns) > 0 ? implode(',', $columns) : '*';

            $method = $single ? "get_row" : "get_results";

            return $wpdb->$method("
                select $columns
                from {$wpdb->prefix}cold_callers
                $conditions
            ");
        }

        public function getPendingColdCallerApplications(array $columns = [])
        {
            global $wpdb;

            $columns = count($columns) > 0 ? implode(',', $columns) : '*';

            return $wpdb->get_results("
                select $columns
                from {$wpdb->prefix}cold_callers
                where application_status = 'pending'
            ");
        }

        public function getColdCallerById( int $cold_caller_id, array $columns = []){
            global $wpdb;

            if(count($columns) > 0){
                $columns = implode(',',$columns);
            }
            else{
                $columns = "*";
            }

            return $wpdb->get_row("
                select $columns 
                from {$wpdb->prefix}cold_callers
                where id='$cold_caller_id'
            ");
        }

        /*
            This Method returns the total count of leads in a week for a cold caller
            Date : 2021-07-15
            Return Type : Integer (Total leads)
        */
        public function get_leads_count( int $cold_caller_id, string $week): int{
            global $wpdb;

            $start_date = date('Y-m-d',strtotime('this Monday',strtotime($week)));
            $end_date = date('Y-m-d',strtotime('next Sunday',strtotime($week)));

            return (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}leads where cold_caller_id='$cold_caller_id' and date(date) >= '$start_date' and date(date) <= '$end_date' ");
        }

        /*
            This method calculates cold caller payment for week and add it to the cold caller payments table
            Return Type : Bool
            Date : 2021-07-15
        */
        public function generate_cold_caller_pay(int $cold_caller_id, string $week, string $type='create'){

            global $wpdb;

            $cold_caller_data = $this->getColdCallerById($cold_caller_id);
            $commission = 0;
            $payment_type = $cold_caller_data->payment_type;
            $total_hours = $pay_per_hour = "";

            if($cold_caller_data->payment_type == "$40_per_lead"){
                
                // get total leads
                $total_leads = $this->get_leads_count($cold_caller_id, $week);

                // calculate total commission
                $commission = (int) $total_leads * 40;

            }
            else if ($cold_caller_data->payment_type == "by_total_hours"){

                // calculated commission = total hours worked x pay per hour
                $commission = (int) ((int)$cold_caller_data->total_hours * (int)$cold_caller_data->pay_per_hour);
                $total_hours = (int)$cold_caller_data->total_hours;
                $pay_per_hour = (int)$cold_caller_data->pay_per_hour;

            }

            $commission_data = [
                'cold_caller_id'        =>  $cold_caller_id,
                'calculated_commission' =>  $commission,
                'week'                  =>  $week,
                'payment_status'        =>  'pending',
                'payment_type'          =>  $payment_type,
                'total_hours'           =>  $total_hours,
                'pay_per_hour'          =>  $pay_per_hour,
                'date_created'          =>  date('Y-m-d')
            ];

            // on refresh return commission amount else return insert status
            if($type == "refresh"){
                $where_data = [
                    'cold_caller_id'    =>  $cold_caller_id,
                    'week'              =>  $week
                ];
                $wpdb->update($wpdb->prefix."cold_caller_payments", $commission_data, $where_data);
                return $commission;
            }
            else{
                return $wpdb->insert($wpdb->prefix."cold_caller_payments", $commission_data);
            }

        }

        public function cc_get_refreshed_commission(){
            $this->verify_nonce_field('cc_get_refreshed_commission');
            if(isset($_POST['cold_caller_id']) && !empty($_POST['cold_caller_id']) && isset($_POST['week']) && !empty($_POST['week'])){
                $cold_caller_id = esc_html($_POST['cold_caller_id']);
                $week = esc_html($_POST['week']);

                // Refresh the cold caller pay with latest pay structure
                $commission = $this->generate_cold_caller_pay($cold_caller_id, $week, 'refresh');

                $this->response('success','cold caller commission refreshed', ['commission' => $commission]);
            }
            else{
                $this->response('success','Something went wrong, please try again later');
            }
        }

        public function get_lead_sources(){
            global $wpdb;
            return $wpdb->get_results("select * from {$wpdb->prefix}lead_source order by name asc");
        }

        public function getColdCallersTypes(){
            global $wpdb;
            return $wpdb->get_results("
                select *
                from {$wpdb->prefix}cold_caller_types
            ");
        }
        
        public function insert_cold_caller_edit_code(){
            global $wpdb;
            
            $data=[
                'name'  =>  $_POST['name'],
                'type'  =>  $_POST['type'],
                'code'  =>  mt_rand(100000, 999999)
            ];
            
            $res=$wpdb->insert($wpdb->prefix.'technician_codes',$data);

            if($res){
                echo json_encode([
                    'status'=>'success',
                    'code'	=>'200',
                    'db_id'    =>  $wpdb->insert_id
                ]);
            }
            else{
                echo json_encode([
                    'status'=>'error',
                    'code'	=>'403'
                ]);
            }
            wp_die();
        }
        
        public function verify_cold_caller_edit_code(){
            global $wpdb;

            if(isset($_POST['code']) && !empty($_POST['code'])){
                $res=$wpdb->get_row("select id,code from {$wpdb->prefix}technician_codes where id='{$_POST['db_id']}'");

                if($_POST['code']==$res->code){
                    // try to delete the entry from db 
                    try{
                        $wpdb->delete($wpdb->prefix."technician_codes",['id'=>$res->id]);
                    }
                    catch(Exception $e){
                        unset($e);
                    }
        
                    // set the session for the id so we can edit the form by id
                    $_SESSION[$_POST['type'].'_editable']=[
                        'status'=>'true',
                        'id'    => $_POST['id']
                    ];
        
                    if(isset($_POST['mode']) && $_POST['mode']=="validation"){
                        echo "true";
                    }
                    else{
                        $this->response('success','code matched');
                    }
        
                }
                else{
                    if(isset($_POST['mode']) && $_POST['mode']=="validation"){
                        echo "false";
                    }
                    else{
                        $this->response('error','code did not matched');
                    }
                }
            }
            else{

                if(isset($_POST['mode']) && $_POST['mode']=="validation"){
                    echo "false";
                }
                else{
                    $this->response('error','code did not matched');
                }
            }
            wp_die();
        }
        
        public function cancel_cold_caller_form_edit(){
            //unset the session for editing form and redirect to same page

            unset($_SESSION['caller_editable']);
            wp_redirect($_POST['page_url']); 
        }
        
        public function edit_cold_caller(){
            global $wpdb;

            $this->verify_nonce_field('edit_cold_caller');

            $cold_caller_details=[
                'name'			        =>	$_POST['name'],
                'email'			        =>	$_POST['email'],
                'phone_no'		        =>	$_POST['phone'],
                'branch_id'		        =>	$_POST['branch_id'],
            ];

            $response = $wpdb->update($wpdb->prefix.'cold_callers', $cold_caller_details, ['id' => $_POST['cold_caller_id']]);
            
            if($response){
                $message="Cold Caller Profile updated successfully";
                $this->setFlashMessage($message,'success');
            }
            else{
                $message="Something went wrong, please try again later";
                $this->setFlashMessage($message,'danger');
            }
            unset($_SESSION['caller_editable']);
            wp_redirect($_POST['page_url']);
        }

        public function getLoginPageUrl(){
            return site_url()."/cold-caller-login";
        }

        public function isLoggedIn(){
            if(!empty($_SESSION['cold_caller_id'])) return true;
            return false;
        }

        public function getLoggedInColdCallerId(){

            if(!isset($_SESSION['cold_caller_id']) || empty($_SESSION['cold_caller_id'])){
                $login_page_url = $this->getLoginPageUrl();
                wp_redirect($login_page_url);
                exit;
            }

            return $_SESSION['cold_caller_id'];
        }
        
        public function update_cold_caller_password(){


            global $wpdb;
            $this->verify_nonce_field('update_cold_caller_password');
            $res = false;

            if(isset($_POST['cold_caller_id']) && !empty($_POST['cold_caller_id'])){

                $cold_caller_id = esc_html($_POST['cold_caller_id']);

                $cold_caller_password=[
                    'password'      =>  password_hash($_POST['password'],PASSWORD_DEFAULT),
                ];
                $res = $wpdb->update($wpdb->prefix."cold_callers",$cold_caller_password,['id' => $cold_caller_id]);
            }
            else{
                $res=false;
            }

            if($res){
                $message="Cold Caller password updated successfully";
                $this->setFlashMessage($message,'success');
            }
            else{
                $message="Something went wrong, please try again later";
                $this->setFlashMessage($message,'danger');
            }

            wp_redirect($_POST['page_url']);
        }
        
    }

    new ColdCaller();
}

class ColdCallerRoles extends ColdCaller{
    function __construct(){

		add_action("admin_post_create_cold_caller_role",array($this,'create_cold_caller_role'));
        add_action("admin_post_nopriv_create_cold_caller_role",array($this,'create_cold_caller_role'));

		add_action("admin_post_edit_cold_caller_role",array($this,'edit_cold_caller_role'));
        add_action("admin_post_nopriv_edit_cold_caller_role",array($this,'edit_cold_caller_role'));
        
		add_action("admin_post_assign_cold_caller_role",array($this,'assign_cold_caller_role'));
        add_action("admin_post_nopriv_assign_cold_caller_role",array($this,'assign_cold_caller_role'));

		add_action("wp_ajax_delete_cold_caller_role",array($this,'delete_cold_caller_role'));
        add_action("wp_ajax_nopriv_delete_cold_caller_role",array($this,'delete_cold_caller_role'));

		add_action("wp_ajax_unassign_cold_caller_role",array($this,'unassign_cold_caller_role'));
        add_action("wp_ajax_nopriv_unassign_cold_caller_role",array($this,'unassign_cold_caller_role'));

    }

    public function assign_cold_caller_role(){
        global $wpdb;

        $this->verify_nonce_field('assign_cold_caller_role');

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['role_id'])) $this->sendErrorMessage($page_url);
        if(empty($_POST['cold_caller_id'])) $this->sendErrorMessage($page_url);

        $role_id = $this->sanitizeEscape($_POST['role_id']);
        $cold_caller_id = $this->sanitizeEscape($_POST['cold_caller_id']);

        // first check if there is available count for assigning role
        if(!$this->isAvailableToAssign($role_id)) $this->sendErrorMessage($page_url, 'role is already assigned to max cold callers');

        // check if already assigned to this cold caller
        if($this->isAlreadyAssigned($role_id, $cold_caller_id)) $this->sendErrorMessage($page_url, 'Role already assigned to this cold caller');

        if(!$this->assignRole($role_id, $cold_caller_id)) $this->sendErrorMessage($page_url);

        $message = "Role assigned to cold caller successfully";
        $this->setFlashMessage($message, "success");

        wp_redirect($page_url);
    }

    public function getLinkedColdCallers(int $role_id){
        global $wpdb;

        return $wpdb->get_col("
            select CRR.cold_caller_id
            from {$wpdb->prefix}cc_role_relation CRR
            where role_id = '$role_id'
        ");
    }

    public function getAssignedRoles(){
        global $wpdb;

        return $wpdb->get_results("
            select CRR.role_id, concat(B.location_name, ' ', CCT.name) as role_name
            from {$wpdb->prefix}cc_role_relation CRR

            left join {$wpdb->prefix}cc_role_meta CRM
            on CRR.role_id = CRM.id

            left join {$wpdb->prefix}cold_caller_types CCT
            on CRM.role_id = CCT.id

            left join {$wpdb->prefix}branches B
            on B.id = CRM.branch_id

            group by CRR.role_id
        ");

    }

    public function isAlreadyAssigned( int $record_id, int $cold_caller_id){
        global $wpdb;

        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}cc_role_relation
            where role_id = '$record_id'
            and cold_caller_id = '$cold_caller_id'
        ");
    }

    public function isAvailableToAssign( int $record_id ){
        global $wpdb;
        
        $role = $this->getRole($record_id, ['count']);

        if(!$role) return false;

        $assigned_count = $this->getAssignedRolesCount($record_id);

        if($assigned_count == 0) return true;

        if($role->count - $assigned_count <= 0) return false;

        return true;
    }

    public function getAssignedRolesCount( int $role_id ){
        global $wpdb;
        return (int) $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}cc_role_relation
            where role_id = '$role_id'
        ");
    }
    
    public function unassign_cold_caller_role(){
        global $wpdb;

        $this->verify_nonce_field('unassign_cold_caller_role');

        if(empty($_POST['record_id'])) $this->response('error');

        $record_id = $this->sanitizeEscape($_POST['record_id']);

        if(!$this->unlinkRole($record_id)) $this->response('error');

        $this->response('success');
    }

    public function delete_cold_caller_role(){
        global $wpdb;

        $this->verify_nonce_field('delete_cold_caller_role');

        if(empty($_POST['role_id'])) $this->response('error');

        $role_id = $this->sanitizeEscape($_POST['role_id']);

        // delete assign cold caller records
        $this->deleteAssignedRolesRecords($role_id);

        if(!$this->deleteRole($role_id)) $this->response('error');

        $this->response('success', 'Role deleted successfully');
    }

    public function deleteAssignedRolesRecords(int $role_id){
        global $wpdb;
        return $wpdb->delete($wpdb->prefix."cc_role_relation", ['role_id' => $role_id]);
    }

    public function edit_cold_caller_role(){
        global $wpdb;

        $this->verify_nonce_field('edit_cold_caller_role');

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['record_id'])) $this->sendErrorMessage($page_url);
        if(empty($_POST['role_id'])) $this->sendErrorMessage($page_url);
        if(empty($_POST['branch_id'])) $this->sendErrorMessage($page_url);
        if(empty($_POST['count'])) $this->sendErrorMessage($page_url);

        $record_id = $this->sanitizeEscape($_POST['record_id']);
        $role_id = $this->sanitizeEscape($_POST['role_id']);
        $branch_id = $this->sanitizeEscape($_POST['branch_id']);
        $count = $this->sanitizeEscape($_POST['count']);

        $data = [
            'role_id'   =>  $role_id,
            'branch_id' =>  $branch_id,
            'count'     =>  $count
        ];

        if(!$this->editRole($record_id, $data)) $this->sendErrorMessage($page_url);

        $message = "Role record updated successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function create_cold_caller_role(){
        global $wpdb;

        $this->verify_nonce_field('create_cold_caller_role');

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['role_id'])) $this->sendErrorMessage($page_url);
        if(empty($_POST['branch_id'])) $this->sendErrorMessage($page_url);
        if(empty($_POST['count'])) $this->sendErrorMessage($page_url);

        $role_id = $this->sanitizeEscape($_POST['role_id']);
        $branch_id = $this->sanitizeEscape($_POST['branch_id']);
        $count = $this->sanitizeEscape($_POST['count']);

        if($this->isRoleExist($role_id, $branch_id)) $this->sendErrorMessage($page_url, 'Role with branch already exist');

        $data = [
            'role_id'   =>  $role_id,
            'branch_id' =>  $branch_id,
            'count'     =>  $count
        ];

        if(!$this->createRole($data)) $this->sendErrorMessage($page_url);

        $message = "Role Created Successfully";
        $this->setFlashMessage($message, "success");

        wp_redirect($page_url);
    }

    public function getRole( int $role_id , array $columns = []){
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns
            from {$wpdb->prefix}cc_role_meta
            where id = '$role_id'
        ");
    }

    public function createRole(array $data){
        global $wpdb;

        $data['created_at'] = date('Y-m-d h:i:s');
        $data['updated_at'] = date('Y-m-d h:i:s');

        return $wpdb->insert($wpdb->prefix."cc_role_meta", $data);
    }

    public function editRole(  int $record_id,  array $data ){
        global $wpdb;

        $data['updated_at'] = date('Y-m-d h:i:s');

        return $wpdb->update($wpdb->prefix."cc_role_meta", $data, ['id' => $record_id]);
    }

    public function deleteRole( int $record_id ){
        global $wpdb;
        return $wpdb->delete($wpdb->prefix."cc_role_meta", ['id' => $record_id]);
    }

    public function isRoleExist(int $role_id, int $branch_id){
        global $wpdb;

        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}cc_role_meta
            where role_id = '$role_id'
            and branch_id = '$branch_id'
        ");
    }

    public function assignRole( int $record_id, int $cold_caller_id){
        global $wpdb;

        $data = [
            'role_id'           =>  $record_id,
            'cold_caller_id'    =>  $cold_caller_id,
            'created_at'        =>  date('Y-m-d h:i:s'),
            'updated_at'        =>  date('Y-m-d h:i:s'),
        ];

        return $wpdb->insert($wpdb->prefix."cc_role_relation", $data);
    }

    public function unlinkRole( int $record_id){
        global $wpdb;
        return $wpdb->delete($wpdb->prefix."cc_role_relation", ['id' => $record_id]);
    }

    public function getColdCallerRoles(int $employee_id){
        global $wpdb;

        return $wpdb->get_results("
            select concat(B.location_name, ' ', CCT.name) as role_name
            from {$wpdb->prefix}cc_role_relation CRR

            left join {$wpdb->prefix}cc_role_meta CRM
            on CRR.role_id = CRM.id

            left join {$wpdb->prefix}cold_caller_types CCT
            on CRM.role_id = CCT.id

            left join {$wpdb->prefix}branches B
            on B.id = CRM.branch_id

            where CRR.cold_caller_id = '$employee_id'
        ");
    }

    public function getAvailableRoles(){
        global $wpdb;

        return $wpdb->get_results("

            select CRM.*, B.location_name, CCT.name, (
                select count(*)
                from {$wpdb->prefix}cc_role_relation CRR
                where CRR.role_id = CRM.id
            ) as assigned_count
            from {$wpdb->prefix}cc_role_meta CRM
        
            left join {$wpdb->prefix}cold_caller_types CCT
            on CRM.role_id = CCT.id
        
            left join {$wpdb->prefix}branches B
            on CRM.branch_id = B.id
        
            where CRM.count - (
                select count(*)
                from {$wpdb->prefix}cc_role_relation CRR
                where CRR.role_id = CRM.id
            ) > 0
        ");
    }

    public function markAsActive(int $cold_caller_id){

        $data['status'] = "active";
        $response = $this->updateColdCaller($cold_caller_id, $data);
        if($response === false) return false;

        $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);

        $employee_data['status'] = 1;
        $response = (new Employee\Employee)->updateEmployee($employee_id, $employee_data);
        if($response === false) return false;

        return true;
    }

    public function markasInactive(int $cold_caller_id){
        $data['status'] = "inactive";
        $response = $this->updateColdCaller($cold_caller_id, $data);
        if($response === false) return false;

        $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);

        $employee_data['status'] = 0;
        $response = (new Employee\Employee)->updateEmployee($employee_id, $employee_data);
        if($response === false) return false;

        return true;
    }

    public function doesColdCalllerHaveRealtorRole(int $cold_caller_id){
        global $wpdb;

        $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);
        if(!$employee_id) return false;

        // get cold caller roles
        $roles = $this->getColdCallerRoles($employee_id);
        if(!$roles) return false;

        // check in all roles if any role have realtor as keyword
        foreach($roles as $role){
            if(stripos($role->role_name, 'Realtors') !== false) return true;
        }

        return false;
    }

}

new ColdCallerRoles();