<?php

use mikehaertl\pdftk\Pdf;

class Technician_details extends GamFunctions
{

    use GamValidation;

    private $err_msg = 'something went wrong, please try again later';

    function __construct()
    {

        add_action("wp_ajax_technician_login", array($this, "technician_login"));
        add_action("wp_ajax_nopriv_technician_login", array($this, "technician_login"));

        add_action("wp_ajax_link_uknown_spent", array($this, "link_uknown_spent"));
        add_action("wp_ajax_nopriv_link_uknown_spent", array($this, "link_uknown_spent"));

        add_action("wp_ajax_check_for_tech_session", array($this, "check_for_tech_session"));
        add_action("wp_ajax_nopriv_check_for_tech_session", array($this, "check_for_tech_session"));

        add_action("wp_ajax_unlock_ac_by_office", array($this, "unlock_ac_by_office"));
        add_action("wp_ajax_nopriv_unlock_ac_by_office", array($this, "unlock_ac_by_office"));

        add_action("admin_post_edit_technician_details", array($this, "edit_technician_details"));
        add_action("admin_post_nopriv_edit_technician_details", array($this, "edit_technician_details"));

        add_action("admin_post_change_tech_password", array($this, "change_tech_password"));
        add_action("admin_post_nopriv_change_tech_password", array($this, "change_tech_password"));

        add_action("admin_post_freeze_tech_account_by_office", array($this, "freeze_tech_account_by_office"));
        add_action("admin_post_nopriv_freeze_tech_account_by_office", array($this, "freeze_tech_account_by_office"));

        add_action("admin_post_technician_resign", array($this, "technician_resign"));
        add_action("admin_post_nopriv_technician_resign", array($this, "technician_resign"));

        add_action("wp_ajax_insert_technician_edit_code", array($this, "insert_technician_edit_code"));
        add_action("wp_ajax_nopriv_insert_technician_edit_code", array($this, "insert_technician_edit_code"));

        add_action("wp_ajax_insert_technician_signup_code", array($this, "insert_technician_signup_code"));
        add_action("wp_ajax_nopriv_insert_technician_signup_code", array($this, "insert_technician_signup_code"));

        add_action("wp_ajax_verify_technician_signup_code", array($this, "verify_technician_signup_code"));
        add_action("wp_ajax_nopriv_verify_technician_signup_code", array($this, "verify_technician_signup_code"));

        add_action("wp_ajax_verify_technician_edit_code", array($this, "verify_technician_edit_code"));
        add_action("wp_ajax_nopriv_verify_technician_edit_code", array($this, "verify_technician_edit_code"));

        add_action("template_redirect", array($this, "check_for_page_access"));

        add_action("admin_post_logout_technician", array($this, 'logout_technician'));
        add_action("admin_post_nopriv_logout_technician", array($this, 'logout_technician'));

        add_action("admin_post_cancel_technician_form_edit", array($this, 'cancel_technician_form_edit'));
        add_action("admin_post_nopriv_cancel_technician_form_edit", array($this, 'cancel_technician_form_edit'));

        add_action("admin_post_technician_signup", array($this, 'technician_signup'));
        add_action("admin_post_nopriv_technician_signup", array($this, 'technician_signup'));

        add_action("admin_post_reimbursement_proof", array($this, 'reimbursement_proof'));
        add_action("admin_post_nopriv_reimbursement_proof", array($this, 'reimbursement_proof'));

        add_action("admin_post_verify_technician_account", array($this, 'verify_technician_account'));
        add_action("admin_post_nopriv_verify_technician_account", array($this, 'verify_technician_account'));

        add_action("admin_post_reject_application", array($this, 'reject_application'));
        add_action("admin_post_nopriv_reject_application", array($this, 'reject_application'));

        add_action("admin_post_fire_technician", array($this, 'fire_technician'));
        add_action("admin_post_nopriv_fire_technician", array($this, 'fire_technician'));

        add_action("admin_post_edit_technician_profile", array($this, 'edit_technician_profile'));
        add_action("admin_post_nopriv_edit_technician_profile", array($this, 'edit_technician_profile'));

        add_action("admin_post_current_technician_contract", array($this, 'current_technician_contract'));
        add_action("admin_post_nopriv_current_technician_contract", array($this, 'current_technician_contract'));

        add_action("admin_post_tech_taxpayer_misc_contract", array($this, 'current_tech_taxpayer_misc_contract'));
        add_action("admin_post_nopriv_tech_taxpayer_misc_contract", array($this, 'current_tech_taxpayer_misc_contract'));

        add_action("admin_post_tech_salary_contract", array($this, 'current_tech_salary_contract'));
        add_action("admin_post_nopriv_tech_salary_contract", array($this, 'current_tech_salary_contract'));

        add_action("admin_post_add_new_technician_dashboard_notice", array($this, 'add_new_technician_dashboard_notice'));
        add_action("admin_post_nopriv_add_new_technician_dashboard_notice", array($this, 'add_new_technician_dashboard_notice'));

        add_action("admin_post_update_technician_dashboard_notice", array($this, 'update_technician_dashboard_notice'));
        add_action("admin_post_nopriv_update_technician_dashboard_notice", array($this, 'update_technician_dashboard_notice'));

        add_action("admin_post_update_technician_branch", array($this, 'update_technician_branch'));
        add_action("admin_post_nopriv_update_technician_branch", array($this, 'update_technician_branch'));

        add_action("admin_post_rehire_technician", array($this, 'rehire_technician'));
        add_action("admin_post_nopriv_rehire_technician", array($this, 'rehire_technician'));

        add_action("wp_ajax_hide_technician_notification", array($this, "hide_technician_notification"));
        add_action("wp_ajax_nopriv_hide_technician_notification", array($this, "hide_technician_notification"));

        add_action("wp_ajax_delete_technician_application", array($this, "delete_technician_application"));
        add_action("wp_ajax_nopriv_delete_technician_application", array($this, "delete_technician_application"));

        add_action("admin_post_quit_technician", array($this, 'quit_technician'));
        add_action("admin_post_nopriv_quit_technician", array($this, 'quit_technician'));
    }

    public function update_technician_branch()
    {
        global $wpdb;

        $this->verify_nonce_field('update_technician_branch');

        $page_url = esc_url_raw($_POST['page_url']);

        if (empty($_POST['technician_id'])) $this->sendErrorMessage($page_url);
        if (empty($_POST['branch_id'])) $this->sendErrorMessage($page_url);
        if (empty($_POST['calendar_id'])) $this->sendErrorMessage($page_url);

        $technician_id = sanitize_text_field($_POST['technician_id']);
        $branch_id = sanitize_text_field($_POST['branch_id']);
        $calendar_id = sanitize_text_field($_POST['calendar_id']);

        $data = [
            'calendar_id'   =>  $calendar_id,
            'branch_id'     =>  $branch_id
        ];

        $response = $this->updateTechnician($technician_id, $data);
        if (!$response) $this->sendErrorMessage($page_url);

        $message = "Technician branch and calendar account id updated successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
        exit;
    }

    public function edit_technician_details()
    {
        global $wpdb;

        $this->verify_nonce_field('technician_edit_form');

        $page_url = esc_url_raw($_POST['page_url']);

        $required_fields = [
            'first_name',
            'last_name',
            'email',
            'dob',
            'address',
            'social_security',
            'technician_id'
        ];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) $this->sendErrorMessage($page_url, $field . " is required");
        }

        $technician_id = $this->sanitizeEscape($_POST['technician_id']);

        $data = [
            'first_name'         =>  $this->sanitizeEscape($_POST['first_name']),
            'last_name'          =>  $this->sanitizeEscape($_POST['last_name']),
            'email'              =>  $this->sanitizeEscape($_POST['email']),
            'dob'                =>  $this->sanitizeEscape($_POST['dob']),
            'address'            =>  $this->sanitizeEscape($_POST['address']),
            'social_security'    =>  $this->sanitizeEscape($_POST['social_security']),
        ];

        $response = $this->updateTechnician($technician_id, $data);
        if (!$response) $this->sendErrorMessage($page_url);

        $message = "Technician details updated successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
        exit;
    }

    public function change_tech_password()
    {
        global $wpdb;

        $this->verify_nonce_field('change_tech_password');

        $res = false;

        $page_url = esc_url_raw($_POST['page_url']);

        if (empty($_POST['technician_id'])) $this->sendErrorMessage($page_url);
        if (empty($_POST['password'])) $this->sendErrorMessage($page_url);

        $technician_id = sanitize_text_field($_POST['technician_id']);
        $password = $_POST['password'];

        $data = ['password'  =>  password_hash($password, PASSWORD_DEFAULT)];

        $response = $this->updateTechnician($technician_id, $data);
        if (!$response) $this->sendErrorMessage($page_url);

        $message = "Password Updated Successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function link_uknown_spent()
    {
        global $wpdb;

        $this->verify_nonce_field('link_uknown_spent');

        $this->beginTransaction();

        if (empty($_POST['spent_id'])) $this->response('error');
        if (empty($_POST['campaign'])) $this->response('error');

        $spent_id = sanitize_text_field($_POST['spent_id']);
        $campaign = sanitize_text_field($_POST['campaign']);

        $spent_data = $wpdb->get_row("
            select * 
            from {$wpdb->prefix}unknown_spends 
            where id='$spent_id'
        ");

        if (!$spent_data) $this->response('error');

        $data = [
            'tracking_id'           =>  $campaign,
            'date'                  =>  $spent_data->date,
            'total_cost'            =>  $spent_data->cost,
            'total_unique_calls'    =>  '',
            'account_name'          =>  $spent_data->account,
            'date_created'          =>  $spent_data->created_at,
        ];

        $response = $wpdb->insert($wpdb->prefix . "googleads_daily_data", $data);
        if (!$response) {
            $this->rollbackCommand();
            $this->response('error');
        }

        $response = $wpdb->delete($wpdb->prefix . "unknown_spends", ['id' => $spent_data->id]);
        if (!$response) {
            $this->rollbackCommand();
            $this->response('error');
        }

        $this->commitTransaction();

        $isPendingUnknownLeads = (new AdsReport)->isPenndingGoogleUnknownLeads();
        if (!$isPendingUnknownLeads) (new OfficeTasks)->clearLinkUnknownGoogleLeads();

        $this->response('success', 'unknown spent linked to campaing successuflly');
    }

    public function technician_resign()
    {
        global $wpdb;

        // first verify nonce field 
        $this->verify_nonce_field('technician_resign');

        $page_url = esc_url_raw($_POST['page_url']);

        if (empty($_POST['resignation_reason'])) $this->sendErrorMessage($page_url);

        $resignation_reason = sanitize_textarea_field($_POST['resignation_reason']);

        $this->beginTransaction();

        $technician_id = $this->get_technician_id();

        $parking_address = '';
        if (!empty($_POST['parking_address'])) $parking_address = $this->sanitizeEscape($_POST['parking_address']);

        list($response, $message) = $this->removeTechnician($technician_id, 'resigned', $resignation_reason, $parking_address);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        $this->commitTransaction();

        $this->logout_technician();
    }

    public function rehire_technician()
    {
        global $wpdb;

        $page_url = esc_url_raw($_POST['page_url']);

        if (empty($_POST['vehicle_id'])) $this->sendErrorMessage($page_url);
        if (empty($_POST['technician_id'])) $this->sendErrorMessage($page_url);

        $vehicle_id = sanitize_text_field($_POST['vehicle_id']);
        $technician_id = sanitize_text_field($_POST['technician_id']);

        $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($technician_id, 1);

        $this->beginTransaction();

        // update fields to enable his account again 
        $data = [
            'application_status'            =>  'verified',
            'application_status_reason'     =>  'rehired by office',
            'vehicle_id'                    =>  $vehicle_id,
            'status'                        =>  1
        ];

        $response = $this->updateTechnician($technician_id, $data);
        if (!$response) $this->rollBackTransaction($page_url, "Unable to update technician account");

        unset($data['vehicle_id']);

        $response = (new Employee\Employee)->updateEmployee($employee_id, $data);
        if (!$response) $this->rollBackTransaction($page_url, "Unable to update employee account");

        $this->commitTransaction();

        $message = "Technician Re-hired Successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function hide_technician_notification()
    {
        global $wpdb;

        if (empty($_POST['notification_id'])) $this->response('error', $this->err_msg);

        $notification_id = sanitize_text_field($_POST['notification_id']);

        $res = $wpdb->update($wpdb->prefix . "technician_dashboard_notices", ['status' => 'checked'], ['id' => $notification_id]);

        if (!$res) $this->response('error', $this->err_msg);

        $this->response('success', 'Notifcation removec successfull');
    }

    public function unlock_ac_by_office()
    {
        global $wpdb;

        if (empty($_POST['technician_id'])) $this->response('error', $this->err_msg);

        $technician_id = sanitize_text_field($_POST['technician_id']);

        $where_data = [
            'technician_id' =>  $technician_id,
            'type'          =>  'account_freezed_by_office'
        ];
        $res = $wpdb->delete($wpdb->prefix . "technician_account_status", $where_data);

        if (!$res) $this->response('error', $this->err_msg);

        $message = "Account unlocked and error notice removed from technician dashboard successfully";
        $this->response('success', $message);
    }

    public function freeze_tech_account_by_office()
    {
        global $wpdb;

        $page_url = esc_url_raw($_POST['page_url']);

        if (empty($_POST['technician_id'])) $this->sendErrorMessage($page_url);

        if (empty($_POST['freeze_reason'])) $this->sendErrorMessage($page_url);
        if (empty($_POST['technician_id'])) $this->sendErrorMessage($page_url);

        $freeze_reason = sanitize_textarea_field($_POST['freeze_reason']);
        $technician_id = sanitize_text_field($_POST['technician_id']);


        $data = [
            'notice'        =>  $freeze_reason,
            'technician_id' =>  $technician_id
        ];

        $status = $this->freezeAc($data);

        if (!$status) $this->sendErrorMessage($page_url);

        $message = "Technician account freezed and reason notice pushed to his dashboard";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function freezeAc(array $data)
    {
        global $wpdb;

        $data['type'] =  'account_freezed_by_office';
        $data['level'] = 'critical';

        return (new Notices)->generateTechnicianNotice($data, true);
    }

    public function check_if_locked_by_office($tech_id)
    {
        global $wpdb;

        return $wpdb->get_var("
            select COUNT(*) 
            from {$wpdb->prefix}technician_account_status 
            where technician_id='$tech_id' 
            and type='account_freezed_by_office'
        ");
    }

    public function update_technician_dashboard_notice()
    {

        // first verify nonce field 
        $this->verify_nonce_field('update_technician_dashboard_notice');

        global $wpdb;

        $page_url = esc_url_raw($_POST['page_url']);

        if (empty($_POST['notice_id'])) $this->sendErrorMessage($page_url);
        if (empty($_POST['notice'])) $this->sendErrorMessage($page_url);
        if (empty($_POST['type'])) $this->sendErrorMessage($page_url);

        $notice_id = sanitize_textarea_field($_POST['notice_id']);
        $notice = sanitize_textarea_field($_POST['notice']);
        $type = sanitize_text_field($_POST['type']);

        $data = [
            'notice'        =>  $notice,
            'type'          =>  $type,
        ];

        $response = $wpdb->update($wpdb->prefix . "technician_dashboard_notices", $data, ['id' => $notice_id]);

        if (!$response) $this->sendErrorMessage($page_url);

        $message = "New technician notice added successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function add_new_technician_dashboard_notice()
    {

        $this->verify_nonce_field('add_new_technician_dashboard_notice');

        global $wpdb;

        $page_url = esc_url_raw($_POST['page_url']);

        if (empty($_POST['notice'])) $this->sendErrorMessage($page_url);
        if (empty($_POST['type'])) $this->sendErrorMessage($page_url);

        $notice = sanitize_textarea_field($_POST['notice']);
        $type = sanitize_text_field($_POST['type']);

        $data = [
            'notice'        =>  $notice,
            'type'          =>  $type,
        ];

        $response = $wpdb->insert($wpdb->prefix . "technician_dashboard_notices", $data);

        if (!$response) $this->sendErrorMessage($page_url);

        $message = "New technician notice added successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function current_tech_contract_details(array $data, int $employee_id)
    {
        //employee id of current logged in
        switch ($employee_id) {
            case 161:
                return [
                    'text_1vuhd' => $data['sign'],
                    'text_2rcnp' => $data['date'],
                    'text_3ykdv' => $data['name'],
                    'text_4kliq' => $data['role']
                ];
                break;
            case 74:
                return [
                    'text_1zfnl' => $data['sign'],
                    'text_2ximv' => $data['date'],
                    'text_3fyyz' => $data['name'],
                    'text_4iwzg' => $data['role']
                ];
                break;
            case 131:
                return [
                    'text_1tkoh' => $data['sign'],
                    'text_2helz' => $data['date'],
                    'text_3trlr' => $data['name'],
                    'text_4ybh' => $data['role']
                ];
                break;
            case 164:
                return [
                    'text_1eduw' => $data['sign'],
                    'text_2nftf' => $data['date'],
                    'text_3arne' => $data['name'],
                    'text_4vpde' => $data['role']
                ];
                break;
            case 64:
                return [
                    'text_1qysq' => $data['sign'],
                    'text_2nwqt' => $data['date'],
                    'text_3ybyw' => $data['name'],
                    'text_4soqx' => $data['role']
                ];
                break;
            case 177:
                return [
                    'text_1kcev' => $data['sign'],
                    'text_2ugre' => $data['date'],
                    'text_3yaev' => $data['name'],
                    'text_4kaun' => $data['role']
                ];
                break;
        }
    }

    public function current_tech_salary_contract()
    {
        global $wpdb;

        $this->verify_nonce_field('current_tech_salary_contract');

        $data = [];
        $upload_dir = wp_upload_dir();
        $technician_id = $this->get_technician_id();
        $page_url = esc_url_raw($_POST['page_url']);
        $redirect_to_profile = add_query_arg('view', 'profile', esc_url_raw(home_url('technician-dashboard/')));

        $required_fields = [
            'first_name',
            'last_name',
            'signature_data',
            'salary_contract_pdf',
            'agree_checkbox'
        ];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) $this->sendErrorMessage($page_url, $field . " is required");
        }

        $employee_data = (new Employee\Employee)->getLoggedInEmployee();
        $first_name = $this->sanitizeEscape($_POST['first_name']);
        $last_name = $this->sanitizeEscape($_POST['last_name']);
        $salary_contract_pdf = $this->sanitizeEscape($_POST['salary_contract_pdf']);
        $fullname = sprintf('%s %s', $first_name, $last_name);

        list($image_path, $image_file) = $this->save_signature($_POST["signature_data"], 'employee_salary_contract', str_replace(' ','_',$fullname));

        $data['salary_contract_sign'] = (!empty($image_path)) ? $image_path : '';
        
        // 1099 salary form data
        $pdf_data = self::current_tech_contract_details([
            'sign' => (new Bitly)->shortenLink($image_path),
            'date' => date('Y-m-d'),
            'name' => $fullname,
            'role' => (new GamFunctions)->get_employee_role($employee_data->role_id)
        ], $employee_data->employee_ref_id);

        $pdf_fw9_dir_path = [
            'pdf_path' => get_template_directory() . "/assets/pdf/technician/salary/$salary_contract_pdf",
            'pdf_save_path' => "/pdf/technician/salary/"
        ];

        // push 1099 salary contract in array to save
        list($status, $pdf_fw9_saving_path) = $this->technician_fw9_taxpayer_contract_agreement($pdf_data, $upload_dir, $pdf_fw9_dir_path);

        $data['salary_1099_contract'] = ($status) ? $pdf_fw9_saving_path : '';

        $response = $this->updateTechnician($technician_id, $data);
        if (!$response) $this->sendErrorMessage($page_url);

        // delete notice for upload 1099 salary form
        $response = (new Notices)->deleteAccountNotices($technician_id, 'contract_1099_salary_agreement');
        if (!$response) $this->sendErrorMessage($page_url);
        
        $message = "Salary contract details submited successfully.";
        $this->setFlashMessage($message, 'success');
        wp_redirect($redirect_to_profile);
    }

    public function current_tech_taxpayer_misc_contract()
    {
        global $wpdb;

        $this->verify_nonce_field('current_tech_taxpayer_misc_contract');

        $data = [];
        $upload_dir = wp_upload_dir();
        $technician_id = $this->get_technician_id();
        $page_url = esc_url_raw($_POST['page_url']);
        $redirect_to_profile = add_query_arg('view', 'profile', esc_url_raw(home_url('technician-dashboard/')));

        $required_fields = [
            'first_name',
            'last_name',
            'home_address',
            'state',
            'social_security',
            'agree_checkbox'
        ];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) $this->sendErrorMessage($page_url, $field . " is required");
        }

        $first_name = $this->sanitizeEscape($_POST['first_name']);
        $last_name = $this->sanitizeEscape($_POST['last_name']);
        $home_address = $this->sanitizeEscape($_POST['home_address']);
        $technician_state = $this->sanitizeEscape($_POST['state']);
        $social_security = $this->sanitizeEscape($_POST['social_security']);

        // w9 taxpayer and 1099 agreements
        $fullname = sprintf('%s %s', $first_name, $last_name);

        // fw9 form data
        $pdf_data = [
            'topmostSubform[0].Page1[0].f1_2[0]' => 'Gam Services USA Inc',
            'cc-name'   =>  $fullname,
            'cc-address'    =>  $home_address,
            'cc-city-state-zip' =>  $technician_state,
            'cc-date'   =>  date('Y-m-d'),
            'cc-signature'  =>  strtoupper($fullname)
        ];

        // social security number
        $security_number = str_split($social_security);
        foreach ($security_number as $key => $number) {
            $pdf_data["cc-ss-" . ($key + 1)] = $number;
        }

        $pdf_fw9_dir_path = [
            'pdf_path' => get_template_directory() . "/assets/pdf/cold-caller/fw9.pdf",
            'pdf_save_path' => "/pdf/technician/fw9/"
        ];

        // push fw9 in array to save
        list($status, $pdf_fw9_saving_path) = $this->technician_fw9_taxpayer_contract_agreement($pdf_data, $upload_dir, $pdf_fw9_dir_path);
        $data['fw9_taxpayer'] = ($status) ? $pdf_fw9_saving_path : '';

        $response = $this->updateTechnician($technician_id, $data);
        if (!$response) $this->sendErrorMessage($page_url);

        // delete notice for upload w9 & 1099 forms
        $response = (new Notices)->deleteAccountNotices($technician_id, 'taxpayer_irs_agreement_proofs');
        if (!$response) $this->sendErrorMessage($page_url);
        
        $message = "Contract details submited successfully.";
        $this->setFlashMessage($message, 'success');
        wp_redirect($redirect_to_profile);
    }

    public function current_technician_contract()
    {
        global $wpdb;

        $this->verify_nonce_field('current_tech_contract');

        $data = [];
        $upload_dir = wp_upload_dir();
        $fullname = $this->get_technician_name();
        $technician_id = $this->get_technician_id();

        // save technician signature first in system 
        $signature_path = "";

        $page_url = esc_url_raw($_POST['page_url']);

        if (empty($_POST['signature_data'])) $this->sendErrorMessage($page_url);

        $file_name = date('Y/m/d') . "/" . date('ymdhis') . ".png"; //file name
        $dir_path = $upload_dir['basedir'] . "/pdf/signatures/technician-agreement/";

        $path_for_db = "/pdf/signatures/technician-agreement/" . $file_name;

        $signature_path = $upload_dir['baseurl'] . $path_for_db;

        $this->genreate_saving_directory($dir_path);

        $imagedata = base64_decode($_POST['signature_data']);
        //path where you want to upload image
        $imagefile = $dir_path . $file_name;
        file_put_contents($imagefile, $imagedata);

        $data['signature'] = $path_for_db;

        $path_for_non_compete = $this->generate_non_compete_document($fullname, $signature_path);
        $path_for_independent_contract = $this->independent_contracter_document($fullname, $signature_path, $_POST['address'], $_POST['state']);

        $data['non_competes'] = $path_for_non_compete;
        $data['independent_contractor'] = $path_for_independent_contract;

        $response = $this->updateTechnician($technician_id, $data);
        if (!$response) $this->sendErrorMessage($page_url);

        $message = "Contract details submited successfully.";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function edit_technician_profile()
    {
        global $wpdb;

        // verify nonce first 
        $this->verify_nonce_field('technician_edit_form');

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['first_name']) ||
            empty($_POST['last_name']) ||
            empty($_POST['email']) ||
            empty($_POST['dob']) ||
            empty($_POST['address']) ||
            empty($_POST['social_security'])
        ) $this->sendErrorMessage($page_url);

        $technician_id = $this->get_technician_id();

        $data = [
            'first_name'        => $this->sanitizeEscape($_POST['first_name']),
            'last_name'         => $this->sanitizeEscape($_POST['last_name']),
            'email'             => $this->sanitizeEscape($_POST['email']),
            'dob'               => $this->sanitizeEscape($_POST['dob']),
            'address'           => $this->sanitizeEscape($_POST['address']),
            'social_security'   => $this->sanitizeEscape($_POST['social_security']),
        ];

        if (!empty($_POST['certification_id'])) {
            $data['certification_id'] = $this->sanitizeEscape($_POST['certification_id']);
        }

        // upload driver license 
        if (isset($_FILES['driver_license']) && !empty($_FILES['driver_license']['tmp_name'])) {
            $upload = $this->uploadSingleFile($_FILES['drivers_license']);
            if (!$upload) $this->sendErrorMessage($page_url, "Please upload jpg/png file for driving license");
            $data['driver_license'] = $upload['url'];
        }

        // upload pesticide license 
        if (isset($_FILES['pesticide_license']) && !empty($_FILES['pesticide_license']['tmp_name'])) {

            // upload file 
            $upload = $this->uploadSingleFile($_FILES['pesticide_license']);
            if (!$upload) $this->sendErrorMessage($page_url, "Please upload jpg/png file for driving license");
            $data['pesticide_license'] = $upload['url'];

            // remove critical notice from technician profile
            (new Notices)->deleteAccountNotices($technician_id, 'request_for_pesticide_license');
        }

        // update technician details table 
        $response = $this->updateTechnician($technician_id, $data);
        if (!$response) $this->sendErrorMessage($page_url);

        $message = "Technician information updated successfully.";
        $this->setFlashMessage($message, 'success');

        // delete the notice if they've on their dashboard
        (new Notices)->deleteAccountNotices($technician_id, 'pending_profile_information');

        wp_redirect($page_url);
    }

    public function removeTechnician(int $technician_id, string $application_staus, string $reason, string $parking_address = '')
    {

        $data = [
            'application_status'        =>  $application_staus,
            'status'                    =>  '0',
            'application_status_reason' =>  $reason
        ];

        $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($technician_id, 1);
        if (!$employee_id) return [false, "Unable to fetch employeed id"];

        $response = (new Employee\Employee)->updateEmployee($employee_id, $data);
        if (!$response) return [false, "Unable to update employee account"];

        $response = $this->updateTechnician($technician_id, $data);
        if (!$response) return [false, "Unable to update technician account"];

        $vehicle_id = (new CarCenter)->getTechnicianVehicleId($technician_id);

        if (!empty($vehicle_id)) {
            $response = (new OfficeTasks)->collectVehicleEquipments($technician_id, $vehicle_id);
            if (!$response) return [false, "Unable to create task for office to collect vehicle equipments"];

            $vehicle_data = [
                'vehicle_id' => $vehicle_id,
                'parking_address'   =>  $parking_address
            ];

            list($response, $message) = (new CarCenter)->unlinkVehicle($vehicle_data);
            if (!$response) return [false, $message];
        }

        return [true, null];
    }

    public function fireTechnician($technician_id, string $fire_reason)
    {

        $data = [
            'application_status'    =>  'fired',
            'status'                =>  '0',
            'fire_reason'           =>  $fire_reason
        ];

        return $this->updateTechnician($technician_id, $data);
    }

    public function fire_technician()
    {
        global $wpdb;

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['fire_reason']) ||
            empty($_POST['technician_id'])
        ) $this->sendErrorMessage($page_url,  "Please fill all the required fields");

        $fire_reason = $_POST['fire_reason'];
        $technician_id = $this->sanitizeEscape($_POST['technician_id']);

        $this->beginTransaction();

        $parking_address = '';
        if (!empty($_POST['parking_address'])) $parking_address = $this->sanitizeEscape($_POST['parking_address']);

        list($response, $message) = $this->removeTechnician($technician_id, 'fired', $fire_reason, $parking_address);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        $this->commitTransaction();

        $message = "Technician fired from the system and moved to fired technician tab.";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function rejectApplication(int $technician_id)
    {
        return $this->updateTechnician($technician_id, ['application_status' => 'rejected']);
    }

    public function reject_application()
    {
        global $wpdb;

        $page_url = esc_url_raw($_POST['page_url']);

        if (empty($_POST['technician_id'])) $this->sendErrorMessage($page_url);

        $technician_id = $this->sanitizeEscape($_POST['technician_id']);
        $response = $this->rejectApplication($technician_id);
        if (!$response) $this->sendErrorMessage($page_url);

        $message = "Application rejected successfully & moved to the rejected tab.";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function verify_technician_account()
    {
        global $wpdb;

        // verifiy nonce field
        $this->verify_nonce_field('verify_technician_account');

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['calendar_id']) ||
            empty($_POST['branch_id']) ||
            empty($_POST['technician_id'])
        ) $this->sendErrorMessage($page_url);

        $calendar_id = $this->sanitizeEscape($_POST['calendar_id']);
        $branch_id = $this->sanitizeEscape($_POST['branch_id']);
        $technician_id = $this->sanitizeEscape($_POST['technician_id']);

        // first verify in wp_employee table
        $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($technician_id, 1);
        if (!$employee_id) $this->sendErrorMessage($page_url);

        $wpdb->query('START TRANSACTION');

        $response = (new Employee\Employee)->verifyEmployee($employee_id);
        if (!$response) $this->rollBackTransaction($page_url, "Unable to verify employee account");

        // now verify in wp_technician_details table
        $data = [
            'calendar_id'           =>  $calendar_id,
            'application_status'    =>  'verified',
            'status'                =>  '1',
            'branch_id'             =>  $branch_id
        ];

        if (!$this->updateTechnician($technician_id, $data)) $this->rollBackTransaction($page_url, "Unable to update technician accounts status");

        $wpdb->query('COMMIT');

        $message = "Technician account verified & activated successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function reimbursement_proof()
    {
        global $wpdb;
        $page_url = esc_url_raw($_POST['page_url']);

        if (isset($_SESSION['employee']) && !empty($_SESSION['employee'])) {
            $data = [
                'employee_id'    =>  $_SESSION['employee']['id'],
                'status'           =>  'not_paid',
                'date_requested'   =>  date('Y-m-d'),
            ];
        } else {
            $data = [
                'technician_id'    =>  $this->get_technician_id(),
                'status'           =>  'not_paid',
                'date_requested'   =>  date('Y-m-d'),
            ];
        }

        $new_docs = [];

        list($response, $message) = $this->singleFileValidation($_FILES, 'docs');
        if (!$response) $this->sendErrorMessage($page_url, $message);

        if (empty($_POST['amount'])) $this->sendErrorMessage($page_url, "Amount field is requried");

        $file = $this->uploadSingleFile($_FILES['docs']);
        if (empty($file['url'])) $this->sendErrorMessage($page_url, 'Unable to upload proof file on server');

        $data['receipts'] = $file['url'];
        $data['amount'] = $_POST['amount'];

        $res = $wpdb->insert($wpdb->prefix . "reimbursement_proof", $data);
        if (!$res) $this->sendErrorMessage($page_url);

        $this->setFlashMessage('Reimbursement proof uploaded successfully', 'success');

        wp_redirect($page_url);
    }

    public function check_for_tech_session()
    {
        global $wpdb;

        $technician_id = $this->get_technician_id();

        // check if tech is not fired
        $tech_status = $wpdb->get_var("select application_status from {$wpdb->prefix}technician_details where id='$technician_id'");

        if ($tech_status == "verified") $this->response('success', 'tech is still logged in');

        unset($_SESSION['technician_id']);
        $this->response('error', 'tech is fired or he resigend');
    }

    public function is_technician_logged_in()
    {
        return !empty($_SESSION['technician_id']) ? true : false;
    }

    public function get_technician_id()
    {
        if (!empty($_SESSION['technician_id'])) return $_SESSION['technician_id'];
        wp_redirect($this->loginUrl());
        exit;
    }

    public function get_technician_name()
    {
        global $wpdb;

        $technician_id = $this->get_technician_id();

        $data = $wpdb->get_row("select first_name,last_name from {$wpdb->prefix}technician_details where id='$technician_id'");

        return $data->first_name . " " . $data->last_name;
    }

    public function loginTechnician(int $technician_id)
    {
        $_SESSION['technician_id'] = $technician_id;
        $_SESSION['employee_id'] = (new Employee\Employee)->getEmployeeIdByRefId($technician_id, 1);
        return $this;
    }

    public function logout_technician()
    {

        // verify nonce field
        $this->verify_nonce_field('logout_technician');

        // unset the session id for technician 
        session_unset();
        session_destroy();
        wp_redirect($this->loginUrl());
        exit;
    }

    public function logout()
    {
        unset($_SESSION['technician_id']);
        return $this;
    }

    public function loginUrl()
    {
        return site_url() . "/technician-login";
    }

    public function check_for_page_access()
    {

        if (!current_user_can('administrator') && $this->is_technician_logged_in()) {

            $check_for_account_status = [
                "invoice",
                "residential-quote-sheet",
                "commercial-quote-sheet",
                "termite-paperwork",
                "termite-certificate",
                "termite-graph",
                "florida-consumer-consent-form",
                "florida-wood-inspection-report",
                "npma33"
            ];

            if (is_page($check_for_account_status)) {
                global $wpdb;
                $technician_id = $this->get_technician_id();

                // check if technician have any account related notice, then he'll have to clear that first 
                $notices = $wpdb->get_results("select * from {$wpdb->prefix}technician_account_status where technician_id='$technician_id' and level='critical'");

                if (count($notices) > 0) {
                    echo "Please clear all notices first before you continue with today events :-";
                    echo "<ul>";

                    foreach ($notices as $key => $notice) {
?>
                        <li><?= $notice->notice; ?></li>
<?php
                    }
                    echo "</ul>";
                    wp_die();
                }
            }
        }

        // if contract id not found in url then redirect to login page
        $maintenance_pages = [
            'monthly-maintenance',
            'quarterly-maintenance',
            'special-maintenance',
            'commercial-maintenance-contract'
        ];
        if (is_page($maintenance_pages)  && !$this->is_technician_logged_in() && empty($_GET['contract-id'])){
            wp_redirect($this->loginUrl());
            exit;
        }

        $technician_logged_in_pages = [
            "technician-dashboard",
            "invoice",
            "residential-quote-sheet",
            "commercial-quote-sheet",
            "daily-proof-of-deposit",
            "new-york-animal-trapping",
            "technician-contract",
            "termite-paperwork",
            "termite-certificate",
            "termite-graph",
            "florida-consumer-consent-form",
            "florida-wood-inspection-report",
            "npma33"
        ];

        if (is_page($technician_logged_in_pages) && !$this->is_technician_logged_in() && !(new Employee\Employee)->getLoggedInEmployee()) {
            
            //redirect on office staff login if action found in query string
            if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'staff_invoice'){
                wp_redirect((new Employee\Employee)->loginPageUrl());
                exit;
            }

            if (!(new Maintenance)->isClientCcVerificationForm()) {
                wp_redirect($this->loginUrl());
                exit;
            }
        }

        if (is_page(['technician-contract'])) {
            $tech_data = $this->get_technician_data();
            if (
                !empty($tech_data->independent_contractor) && !empty($tech_data->non_competes)
                && !empty($tech_data->fw9_taxpayer)
            ) {
                wp_redirect($this->dashboardUrl());
                exit;
            }
        }

        // if logged in then don't show login page instead redirect to dashboard page
        if (is_page(["technician-login"]) && $this->is_technician_logged_in()) {
            wp_redirect($this->dashboardUrl());
            exit;
        }
    }

    public function technician_login()
    {
        global $wpdb;

        $this->verify_nonce_field('technician_login');

        if (empty($_POST['username'])) $this->response('error', $this->err_msg);
        if (empty($_POST['password'])) $this->response('error', $this->err_msg);

        $username = sanitize_text_field($_POST['username']);
        $password = $_POST['password'];

        $user_details = $wpdb->get_row("
            select id,password,last_logged_in
            from {$wpdb->prefix}technician_details
            where (slug='$username' or email = '$username')
            and application_status = 'verified'
        ");

        if (!$user_details) $this->response('error', 'incorrect username');

        if (!password_verify($password, $user_details->password)) $this->response('error', 'incorrect password');

        // set the session for logged in user with his id
        $_SESSION['technician_id'] = $user_details->id;
        $_SESSION['employee_id'] = (new Employee\Employee)->getEmployeeIdByRefId($user_details->id, 1);

        // before doing anything check if he's logging for the first time today 
        if ($user_details->last_logged_in != date('Y-m-d')) {

            // check for yesterday deposit if it was not sunday
            if (date('W', strtotime('-1 days')) != 0) {
                $yesterday_date = date('Y-m-d', strtotime('-1 days'));

                // first check if yesterday had atleast one cash or check invoice

                $cash_or_check_invoices = $wpdb->get_var("
                    select COUNT(*) 
                    from {$wpdb->prefix}invoices 
                    where technician_id='$user_details->id' 
                    and DATE(date)='$yesterday_date' 
                    and (payment_method='cash' or payment_method='check')
                ");

                // if they'd done atleast one cash or check invoice, then they must'd to deposit proof 
                if ($cash_or_check_invoices > 0) {

                    // first check if they'd deposited yesterday proof of deposit 
                    $yesterday_deposit = $wpdb->get_row("
                        Select *
                        from {$wpdb->prefix}daily_deposit 
                        where technician_id='$user_details->id' 
                        and DATE(date) = '$yesterday_date'
                    ");

                    // if there is atleast one deposit, then this will return true 
                    if ($yesterday_deposit) {
                        if ($yesterday_deposit->status == 'pending') {
                            // push notice to get it verified by admin
                            $notice = "You've pending deposit approval for the date " . date('d M Y', strtotime('-1 days'));
                            $data = [
                                'type'          =>  'pending_deposit_approval',
                                'date'          =>  $yesterday_date,
                                'notice'        =>    $notice,
                                'technician_id' =>  $user_details->id,
                                'level'         =>  'critical'
                            ];

                            $wpdb->insert($wpdb->prefix . "technician_account_status", $data);
                        }
                    } else {
                        // push notice in account status table for not depositing daily proof and for approval as well

                        $notice = "You've pending deposit approval for the date " . date('d M Y', strtotime('-1 days'));
                        $data = [
                            'type'          =>  'pending_deposit_approval',
                            'date'          =>  $yesterday_date,
                            'notice'        =>    $notice,
                            'technician_id' =>  $user_details->id,
                            'level'         =>  'critical'
                        ];

                        $wpdb->insert($wpdb->prefix . "technician_account_status", $data);

                        $notice = "You've not submited proof of deposit for the date " . date('d M Y', strtotime('-1 days')) . " . Please make sure you select the correct date on notes page.";
                        $data = [
                            'type'          =>  'pending_deposit',
                            'date'          =>  $yesterday_date,
                            'notice'        =>    $notice,
                            'technician_id' =>  $user_details->id,
                            'level'         =>  'critical'
                        ];

                        $wpdb->insert($wpdb->prefix . "technician_account_status", $data);
                    }
                }
            }

            // update the last logged in for today date 
            $this->updateTechnician($user_details->id, ['last_logged_in' => date('Y-m-d')]);
        }
        // check for yesterday notes
        $this->response('success', 'username & password matched');

        wp_die();
    }

    public function insert_technician_edit_code()
    {
        global $wpdb;

        $this->verify_nonce_field('insert_technician_edit_code');

        if (empty($_POST['type'])) $this->response('error', $this->err_msg);

        $type = sanitize_text_field($_POST['type']);

        $data = [
            'name'  =>  $this->get_technician_name(),
            'type'  =>  $type,
            'code'  =>  mt_rand(100000, 999999)
        ];

        switch ($type) {
            case 'invoice':
                $data['link'] = admin_url('admin.php?page=invoice') . "&invoice_id={$_POST['id']}";
                break;
            case 'residential_quote':
                $data['link'] = admin_url('admin.php?page=resdiential-quotesheet') . "&quote_id={$_POST['id']}";
                break;
            case 'commercial_quote':
                $data['link'] = admin_url('admin.php?page=commercial-quotesheet') . "&quote_id={$_POST['id']}";
                break;
            case 'monthly_maintenance':
                $data['link'] = admin_url('admin.php?page=maintenance') . "&contract_id={$_POST['id']}";
                break;
            case 'quarterly_maintenance':
                $data['link'] = admin_url('admin.php?page=maintenance') . "&contract_id={$_POST['id']}";
                break;
            case 'special_maintenance':
                $data['link'] = admin_url('admin.php?page=special-maintenance') . "&contract_id={$_POST['id']}";
                break;
            case 'commercial_maintenance':
                $data['link'] = admin_url('admin.php?page=commercial-maintenance&') . "&contract_id={$_POST['id']}";
                break;

            default:
                break;
        }


        $res = $wpdb->insert($wpdb->prefix . 'technician_codes', $data);

        if ($res) {
            $company_no = esc_attr(get_option('gam_company_phone_no'));
            echo json_encode([
                'status'    =>  'success',
                'code'        =>  '200',
                'message'   =>  'Call this office number : <a href="tel:' . $company_no . '"> ' . $company_no . '</a> to get the code. This is for office to have interaction with client in order to sell maintenance plan.',
                'db_id'     =>  $wpdb->insert_id
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'code'    => '403'
            ]);
        }
        wp_die();
    }

    public function verify_technician_edit_code()
    {
        global $wpdb;

        $this->verify_nonce_field('verify_technician_edit_code');

        if (empty($_POST['db_id'])) $this->response('error', 'CODE 1');

        $db_id = $this->sanitizeEscape($_POST['db_id']);

        if (!empty($_POST['code'])) {
            $res = $wpdb->get_row("select id,code from {$wpdb->prefix}technician_codes where id='$db_id'");
            if (!$res) $this->response('error', 'CODE 2');

            if ($_POST['code'] == $res->code) {

                // try to delete the entry from db 
                $wpdb->delete($wpdb->prefix . "technician_codes", ['id' => $res->id]);

                // set the session for the id so we can edit the form by id
                if (!empty($_POST['type'])) {
                    $_SESSION[$_POST['type'] . '_editable'] = [
                        'status' => 'true',
                    ];

                    if (!empty($_POST['id'])) $_SESSION[$_POST['type'] . "_editable"]['id'] = $_POST['id'];
                }

                if (!empty($_POST['mode']) && $_POST['mode'] == "validation") {
                    echo "true";
                } else {
                    $this->response('success', 'code matched');
                }
            } else {
                if (isset($_POST['mode']) && $_POST['mode'] == "validation") {
                    echo "false";
                } else {
                    $this->response('error', 'code did not matched');
                }
            }
        } else {
            if (empty($_POST['code']) || $_POST['mode'] != "validation") $this->response('error', 'code did not matched');

            echo "false";
        }
        wp_die();
    }

    public function cancel_technician_form_edit()
    {
        //unset the session for editing form and redirect to same page
        unset($_SESSION['invoice_editable']);
        wp_redirect(esc_url_raw($_POST['page_url']));
    }

    // insert technician signup code
    public function insert_technician_signup_code()
    {
        global $wpdb;

        $data = [
            'name'  =>  trim($_POST['name']),
            'type'  =>  $_POST['type'],
            'code'  =>  mt_rand(100000, 999999)
        ];

        $res = $wpdb->insert($wpdb->prefix . 'technician_codes', $data);

        if ($res) {
            echo json_encode([
                'status' => 'success',
                'code'  => '200',
                'db_id'    =>  $wpdb->insert_id
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'code'  => '403'
            ]);
        }
        wp_die();
    }

    // verify technician signup code
    public function verify_technician_signup_code()
    {
        global $wpdb;

        if (empty($_POST['code'])) $this->response('error', $this->err_msg);
        if (empty($_POST['db_id'])) $this->response('error', $this->err_msg);

        $code = sanitize_text_field($_POST['code']);
        $db_id = sanitize_text_field($_POST['db_id']);

        $res = $wpdb->get_row("select id,code from {$wpdb->prefix}technician_codes where id='$db_id'");

        if ($code != $res->code) $this->response('error', 'code did not matched');

        $wpdb->delete($wpdb->prefix . "technician_codes", ['id' => $res->id]);

        $_SESSION["technician_signup"] = ['status' => 'true'];

        $this->response('success', 'code matched');
    }

    public function technician_signup()
    {
        global $wpdb;

        $this->verify_nonce_field('technician_signup');

        $upload_dir = wp_upload_dir();
        $page_url = esc_url_raw($_POST['page_url']);

        $required_fields = [
            'branch_id',
            'first_name',
            'last_name',
            'date_of_birth',
            'email',
            'home_address',
            'password',
            'branch_id',
            'social_security',
            'have_pesticie_license',
        ];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) $this->sendErrorMessage($page_url, $field . " is required");
        }

        $have_pesticie_license = $this->sanitizeEscape($_POST['have_pesticie_license']);

        if ($have_pesticie_license == "yes") {
            if (
                empty($_POST['pesticide_license_no']) ||
                !isset($_FILES['pesticide_license']) || empty($_FILES['pesticide_license']['name'])
            ) $this->sendErrorMessage($page_url, "Pesticide license number and proof is required if you've pesticide license");
        }

        $branch_id = $this->sanitizeEscape($_POST['branch_id']);
        $technician_state = (new Branches)->getBranchName($branch_id);
        $first_name = $this->sanitizeEscape($_POST['first_name']);
        $last_name = $this->sanitizeEscape($_POST['last_name']);
        $date_of_birth = $this->sanitizeEscape($_POST['date_of_birth']);
        $email = $this->sanitizeEscape($_POST['email']);
        $home_address = $this->sanitizeEscape($_POST['home_address']);
        $social_security = $this->sanitizeEscape($_POST['social_security']);

        $fullname = $first_name . " " . $last_name;

        $username = (new Employee\Employee)->generateUsername($fullname);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $data = [
            'first_name'            =>  $first_name,
            'last_name'             =>  $last_name,
            'dob'                   =>  $date_of_birth,
            'slug'                  =>  $username,
            'email'                 =>  $email,
            'address'               =>  $home_address,
            'social_security'       =>  $social_security,
            'application_status'    =>  'pending',
            'state'                 =>  $technician_state,
            'password'              =>  $password,
            'branch_id'             =>  $branch_id,
        ];

        // upload driver license
        if (empty($_FILES['drivers_license']['name'])) $this->sendErrorMessage($page_url, "Please upload driving license");

        $upload = $this->uploadSingleFile($_FILES['drivers_license']);
        if (!$upload) $this->sendErrorMessage($page_url, "Please upload jpg/png file for driving license");
        $data['driver_license'] = $upload['url'];

        if ($have_pesticie_license == "yes") {
            $data['certification_id'] = $this->sanitizeEscape($_POST['pesticide_license_no']);
            $upload = $this->uploadSingleFile($_FILES['pesticide_license']);
            if (!$upload) $this->sendErrorMessage($page_url, "Please upload jpg/png file for pesticide license");
            $data['pesticide_license'] = $upload['url'];
        }

        if (empty($_POST['signature_data'])) $this->sendErrorMessage($page_url, "Please upload your sign along with details");

        $path = '/pdf/signatures/technician-agreement/';
        $signature_data = $_POST['signature_data'];

        list($signature_url, $signature_db_path) = $this->saveBase64EncodedImage($signature_data, $path);

        $data['signature'] = $signature_db_path;

        //  create agreement documents for technician via template functions
        $path_for_non_compete = $this->generate_non_compete_document($fullname, $signature_url);
        $path_for_independent_contract = $this->independent_contracter_document($fullname, $signature_url, $_POST['home_address'], $technician_state);

        $data['non_competes'] = $path_for_non_compete;
        $data['independent_contractor'] = $path_for_independent_contract;

        // w9 taxpayer and 1099 agreements
        $fullname = sprintf('%s %s', $first_name, $last_name);
        $pdf_data = [
            'topmostSubform[0].Page1[0].f1_2[0]' => 'Gam Services USA Inc',
            'cc-name'   =>  $fullname,
            'cc-address'    =>  $home_address,
            'cc-city-state-zip' =>  $technician_state,
            'cc-date'   =>  date('Y-m-d'),
            'cc-signature'  =>  strtoupper($fullname)
        ];

        // social security number
        $security_number = str_split($social_security);
        foreach ($security_number as $key => $number) {
            $pdf_data["cc-ss-" . ($key + 1)] = $number;
        }
        
        $pdf_dir_path = [
            'pdf_path' => get_template_directory() . "/assets/pdf/cold-caller/fw9.pdf",
            'pdf_save_path' => "/pdf/technician/fw9/"
        ];

        // push fw9 in array to save
        list($status, $pdf_fw9_saving_path) = $this->technician_fw9_taxpayer_contract_agreement($pdf_data, $upload_dir, $pdf_dir_path);
        $data['fw9_taxpayer'] = ($status) ? $pdf_fw9_saving_path : '';

        $wpdb->query('START TRANSACTION');

        // create technician
        $response1 = $wpdb->insert($wpdb->prefix . "technician_details", $data);
        if (!$response1) $this->rollBackTransaction($page_url, "Unable to create technician, Please try again after sometime");

        // create the technician in employees table  as well
        $technician_id = $wpdb->insert_id;

        $employee_data = [
            'employee_ref_id'       =>  $technician_id,
            'username'              =>  $username,
            'name'                  =>  $first_name . " " . $last_name,
            'email'                 =>  $email,
            'address'               =>  $home_address,
            'role_id'               =>  '1',
            'password'              =>  $password
        ];

        if (!(new Employee\Employee)->createEmployee($employee_data))
            $this->rollBackTransaction($page_url, "Unable to create employee,  Please try again after sometime.");

        $wpdb->query('COMMIT');

        // set session and redirect to technician sign up page 
        $message = "Your details are submitted successfully. We'll update you on your application status very soon.";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function technician_fw9_taxpayer_contract_agreement(array $data, array $upload_dir, array $pdf_dir_path)
    {
        $input_file_path = $pdf_dir_path['pdf_path'];
        $path = $pdf_dir_path['pdf_save_path'];

        $directory_path = $upload_dir['basedir'] . $path;
        $db_saving_path = $path . date('Y/m/d/') . date('his') . "_" . $this->quickRandom(6) . ".pdf";
        $this->genreate_saving_directory($directory_path);

        $output_file_path = $upload_dir['basedir'] . $db_saving_path;
        // load pdftk php sdk from vendor 
        self::loadVendor();

        // Fill form with data array
        $pdf = new Pdf($input_file_path);
        $result = $pdf->fillForm($data)
            ->needAppearances()
            ->flatten()
            ->saveAs($output_file_path);

        return ($result === false) ? [false, $pdf->getError()] : [true, $db_saving_path];
    }

    public function independent_contracter_document($tech_name, $signature_path, $address, $state)
    {

        $file_path = get_template_directory() . "/template/independent-contractor-agreement.html";
        $contract_document = file_get_contents($file_path);

        $contract_document = str_replace('[date]', date('d M Y'), $contract_document);
        $contract_document = str_replace('[technician]', $tech_name, $contract_document);
        $contract_document = str_replace('[address]', $address, $contract_document);
        $contract_document = str_replace('[state]', $state, $contract_document);
        $contract_document = str_replace('[signature-path]', $signature_path, $contract_document);

        $upload_dir = wp_upload_dir();

        $path_for_independent_contract = "/pdf/independent-contract/" . date('ymdhis') . ".pdf";
        $file_path = $upload_dir['basedir'] . $path_for_independent_contract;

        // load mpdf php sdk from vendor
        self::loadVendor();

        $mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
        $mpdf->WriteHTML($contract_document);
        $mpdf->Output($file_path, "F");

        return $path_for_independent_contract;
    }

    public function generate_non_compete_document($tech_name, $signature_path)
    {

        $file_path = get_template_directory() . "/template/non-compete-document.html";
        $non_compete_document = file_get_contents($file_path);

        $non_compete_document = str_replace('[date]', date('d M Y'), $non_compete_document);
        $non_compete_document = str_replace('[technician]', $tech_name, $non_compete_document);
        $non_compete_document = str_replace('[signature-path]', $signature_path, $non_compete_document);

        $upload_dir = wp_upload_dir();

        $path_for_non_compete = "/pdf/non-compete/" . date('ymdhis') . ".pdf";
        $file_path = $upload_dir['basedir'] . $path_for_non_compete;

        // load mpdf php sdk from vendor
        self::loadVendor();

        $mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
        $mpdf->WriteHTML($non_compete_document);
        $mpdf->Output($file_path, "F");

        return $path_for_non_compete;
    }

    public function dashboardUrl($str_param = '')
    {
        return get_home_url() . "/technician-dashboard" . $str_param;
    }

    public function get_technician_data(array $columns = [])
    {
        global $wpdb;

        $technician_id = $this->get_technician_id();

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns 
            from {$wpdb->prefix}technician_details 
            where id='$technician_id'
        ");
    }

    public function get_all_technicians(bool $office = false, $location = '', bool $status = true, bool $by_location = true)
    {
        global $wpdb;

        $conditions = [];

        if (!current_user_can('other_than_upstate') && $by_location == true) {
            $accessible_branches = (new Branches)->partner_accessible_branches(true);
            $accessible_branches = "'" . implode("', '", $accessible_branches) . "'";

            $conditions[] = " L.id IN ($accessible_branches)";
        }

        if ($status == true) $conditions[] = " TD.application_status = 'verified'";
        if ($office == false) $conditions[] = " TD.slug <> 'office'";

        if (!empty($location) && !is_null($location)) $conditions[] = " L.slug = '$location'";

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : "";

        return $wpdb->get_results("
            select TD.* from 
            {$wpdb->prefix}technician_details TD
            left join {$wpdb->prefix}branches L
            on TD.branch_id = L.id
            $conditions
        ");
    }

    public function getWithoutVehicleTechnicians(array $columns = [])
    {
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}technician_details TD
            where (TD.vehicle_id is null || TD.vehicle_id = '')
            and TD.application_status = 'verified'
        ");
    }

    public function getTechniciansWithVehicles(array $columns = [])
    {
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}technician_details TD
            where TD.vehicle_id != ''
            and TD.application_status = 'verified'
        ");
    }

    public function delete_technician_application()
    {

        // verify nonce field first
        $this->verify_nonce_field('delete_technician_application');

        if (empty($_POST['technician_id'])) $this->response('error', $this->err_msg);

        $technician_id = esc_html($_POST['technician_id']);

        $response = $this->deleteApplication($technician_id);

        if (!$response) $this->response('error', $this->err_msg);

        $this->response('success', 'Technician application deleted successfully');
    }

    public function getTechnicianById(int $technician_id, array $columns = [])
    {
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns 
            from {$wpdb->prefix}technician_details 
            where id='$technician_id'
        ");
    }

    public function deleteApplication(int $technician_id): bool
    {
        global $wpdb;

        // get the application data
        $application = $this->getTechnicianById($technician_id);
        $base_dir = wp_upload_dir()['basedir'];

        if (!$application) return false;

        // DELETE THE TECHNICIAN FILES

        // delete driving license
        if (!empty($application->driver_license) && $application->driver_license != null) {
            $file_path = explode('uploads', $application->driver_license)[1];
            unlink($base_dir . $file_path);
        }

        // delete pesticide license
        if (!empty($application->pesticide_license) && $application->pesticide_license != null) {
            $file_path = explode('uploads', $application->pesticide_license)[1];
            unlink($base_dir . $file_path);
        }

        // delete independent contractor file
        if (!empty($application->independent_contractor) && $application->independent_contractor != null) {
            unlink($base_dir . $application->independent_contractor);
        }

        // delete non competes file
        if (!empty($application->non_competes) && $application->non_competes != null) {
            unlink($base_dir . $application->non_competes);
        }

        // delete signature
        if (!empty($application->signature) && $application->signature != null) {
            unlink($base_dir . $application->signature);
        }

        // delete the vehicle information uploaded with application if it was technician vehicle
        $vehicle_id = (new CarCenter)->getTechnicianVehicleId($technician_id);
        $vehicle_owner = (new CarCenter)->getVehicleOwner($vehicle_id);

        // delete application from database
        return $wpdb->delete($wpdb->prefix . "technician_details", ['id' => $technician_id]);
    }

    public function getTechnicianSalaryContractById(int $technician_id, array $columns = [])
    {
        global $wpdb;
        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns 
            from {$wpdb->prefix}employee_salary_contract 
            where employee_id='$technician_id'
        ");
    }

    /*
        This method will get the calendar access token from branch based on technician
        return type : string (token_path)
    */
    public function getCalendarAccessToken(int $technician_id)
    {
        global $wpdb;

        $token_path = $wpdb->get_var("
            select GC.token_path
            from {$wpdb->prefix}google_calendars GC
            left join {$wpdb->prefix}branches B
            on GC.id = B.calendar_id
            left join {$wpdb->prefix}technician_details TD
            on TD.branch_id = B.id
            where TD.id = '$technician_id'
        ");

        return get_template_directory() . $token_path;
    }

    public function getCalendarTokenByBranchId(int $branch_id)
    {
        global $wpdb;

        $token_path = $wpdb->get_var("
            select GC.token_path
            from {$wpdb->prefix}google_calendars GC
            left join {$wpdb->prefix}branches L
            on GC.id = L.calendar_id
            where L.id = '$branch_id'
        ");

        if (empty($token_path)) return null;

        return get_template_directory() . $token_path;
    }

    public function getTechnicianCalendarId(int $technician_id)
    {
        global $wpdb;

        return $wpdb->get_var("
            select calendar_id
            from {$wpdb->prefix}technician_details
            where id = '$technician_id'
        ");
    }

    public function getTechnicianName(int $technician_id): string
    {
        global $wpdb;
        $data = $wpdb->get_row("
            select first_name, last_name 
            from {$wpdb->prefix}technician_details 
            where id = '$technician_id'
        ");

        return $data->first_name . " " . $data->last_name;
    }

    public function getTechniciansByBranchId(int $branch_id, array $columns = [], bool $fired = false)
    {
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_results("
			select $columns
			from {$wpdb->prefix}technician_details
			where branch_id='$branch_id'
		");
    }

    public function getTechnicianBranchSlug(int $technician_id)
    {
        global $wpdb;

        return $wpdb->get_var("
            select L.slug
            from {$wpdb->prefix}branches L
            left join {$wpdb->prefix}technician_details TD
            on L.id = TD.branch_id
            where TD.id = '$technician_id'
        ");
    }

    public function getTechnicianBranchId(int $technician_id)
    {
        global $wpdb;

        return $wpdb->get_var("
            select branch_id
            from {$wpdb->prefix}technician_details
            where id = '$technician_id'
        ");
    }

    public function getPendingApplications(array $columns = [])
    {
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}technician_details
            where application_status = 'pending'
        ");
    }

    public function updateTechnician(int $technician_id, array $data)
    {
        global $wpdb;

        $response =  $wpdb->update($wpdb->prefix . "technician_details", $data, ['id' => $technician_id]);
        return $response === false ? false : true;
    }

    public function isPesticideDecalPending(int $technician_id, int $branch_id, ?string $pesticide_decal = '')
    {

        if (!empty($pesticide_decal)) return false;

        $branch = (new Branches)->getBranchSlug($branch_id);
        $parent_branch = (new Branches)->getParentBranchSlug($branch_id);

        if ($branch != "ny_metro" && $parent_branch != "ny_metro") return false;

        return true;
    }

    public function editProfileUrl()
    {
        return $this->dashboardUrl() . "?view=edit-profile";
    }

    public function requestForPesticideLiceneseDetails(int $technician_id)
    {
        $editProfileUrl = $this->editProfileUrl();
        $notice = "Please upload your pesticide license details into the system by <a href='$editProfileUrl'>clicking here</a>. If you don't have one, please request the office to clear this error message.";

        $data = [
            'type'          =>  'request_for_pesticide_license',
            'level'         =>  'critical',
            'class'         =>  'error',
            'notice'        =>  $notice,
            'technician_id' =>  $technician_id
        ];

        return (new Notices)->generateTechnicianNotice($data, true);
    }

    public function createVehiclePageUrl()
    {
        return $this->dashboardUrl() . "?view=vehicle-details&cnw=true";
    }

    public function getTechnicianByVehicleId(int $vehicle_id, array $columns = [])
    {
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("select $columns from {$wpdb->prefix}technician_details where vehicle_id = '$vehicle_id'");
    }
}
new Technician_details();
