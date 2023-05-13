<?php

class OfficeStaff extends Employee\Employee {

    private $role_id = 3;
    private $error_message = "Something went wrong, please try again later";
    private $auth_err_msg = "Unable to authenticate";

    function __construct(){
        add_action("admin_post_create_office_staff", array($this, "create_office_staff"));
        add_action("admin_post_nopriv_create_office_staff", array($this, "create_office_staff"));
        
        add_action("admin_post_update_office_staff_ac", array($this, "update_office_staff_ac"));
        add_action("admin_post_nopriv_update_office_staff_ac", array($this, "update_office_staff_ac"));
    }

    public function create_office_staff(){
        $this->verify_nonce_field('create_office_staff');

        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['name']) ||
            empty($_POST['email']) ||
            empty($_POST['address']) ||
            empty($_POST['phone_no']) ||
            empty($_POST['password']) ||
            empty($_POST['role'])
        ) $this->sendErrorMessage($page_url);

        $username = $this->generateUsername($_POST['name']);
        $password = password_hash($_POST['password'],PASSWORD_DEFAULT);

        $name = $this->sanitizeEscape($_POST['name']);
        $email = $this->sanitizeEscape($_POST['email']);
        $address = $this->sanitizeEscape($_POST['address']);
        $phone_no = $this->sanitizeEscape($_POST['phone_no']);
        $role = $this->sanitizeEscape($_POST['role']);
        

        // create employee record in wp_office_staff table first
        $office_data = ['role' => $role];
        $office_staff_id = $this->createOfficeStaff($office_data);
        
        if(!$office_staff_id) $this->sendErrorMessage($page_url);

        // create employee record as well for office staff
        $employee_data = [
            'employee_ref_id'       =>  $office_staff_id,
            'name'                  =>  $name,
            'email'                 =>  $email,
            'address'               =>  $address,
            'phone_no'               =>  $phone_no,
            'role_id'               =>  $this->role_id,
            'username'              =>  $username,
            'password'              =>  $password,
        ];

        // if office filled the form , then it's verified automatically
        $data['application_status'] = current_user_can('administrator') ? 'verified' : 'pending';

        if(!$this->createEmployee($employee_data)) $this->sendErrorMessage($page_url);

        $message = "Office staff member created successfully";
        $this->setFlashMessage($message, "success");

        wp_redirect($page_url);
    }

    public function createOfficeStaff(array $data){
        global $wpdb;

        $data['created_at'] = date('Y-m-d h:i:s');
        $data['updated_at'] = date('Y-m-d h:i:s');

        $wpdb->insert($wpdb->prefix."office_staff", $data);

        return $wpdb->insert_id;
    }

    public function update_office_staff_ac(){
        global $wpdb;

        $this->verify_nonce_field('update_office_staff_ac');

        $page_url = $_POST['page_url'];

        if(!isset($_POST['account_id']) || empty($_POST['account_id'])) $this->sendErrorMessage($page_url);
        
        $account_id = $_POST['account_id'];

        $data = [
            'name'      =>  esc_html($_POST['name']),
            'email'     =>  esc_html($_POST['email']),
            'address'   =>  esc_html($_POST['address']),
            'phone_no'      =>  esc_html($_POST['phone_no']),
            'status'      =>  esc_html($_POST['status']),
            'branch_id'      =>  esc_html($_POST['branch_id']),
            'application_status'      =>  esc_html($_POST['application_status']),
        ];

        $response = $this->updateEmployee($account_id, $data);

        if(!$response) $this->sendErrorMessage($page_url);

        $message = "Office staff member account updated successfully";
        $this->setFlashMessage($message, "success");

        wp_redirect($page_url);
    }

    public function getStaffMemberById(int $member_id){
        global $wpdb;
        return $this->getEmployee($member_id);
    }

    public function getStaffMembers(){
        global $wpdb;
        return $this->getAllEmployees(['office_staff']);
    }

}

new OfficeStaff();