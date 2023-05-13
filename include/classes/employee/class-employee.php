<?php

namespace Employee;

class Employee extends \GamFunctions{

    private $auth_session_name = 'employee_id';
    private $auth_err_msg = "Unable to authenticate";    
    
    function __construct(){
        $this->debug = constant('WP_DEBUG');

        add_action("template_redirect",array($this,"check_for_authentication"));

        add_action("wp_ajax_employee_login", array($this, "employee_login"));
        add_action("wp_ajax_nopriv_employee_login", array($this, "employee_login"));

        add_action("wp_ajax_verify_employee_account", array($this, "verify_employee_account"));
        add_action("wp_ajax_nopriv_verify_employee_account", array($this, "verify_employee_account"));

        add_action("admin_post_logout_employee", array($this, "logout_employee"));
        add_action("admin_post_nopriv_logout_employee", array($this, "logout_employee"));

        add_action("wp_ajax_fire_employee", array($this, "fire_employee"));
        add_action("wp_ajax_nopriv_fire_employee", array($this, "fire_employee"));
		
		add_action("wp_ajax_delete_employee_account",array($this,"delete_employee_account"));
        add_action("wp_ajax_nopriv_delete_employee_account",array($this,"delete_employee_account"));

		add_action("wp_ajax_check_if_employee_email_exist",array($this,"check_if_employee_email_exist"));
        add_action("wp_ajax_nopriv_check_if_employee_email_exist",array($this,"check_if_employee_email_exist"));

        add_action('wp_ajax_get_employees_for_notice',array($this,'get_employees_for_notice'));
        add_action('wp_ajax_nopriv_get_employees_for_notice',array($this,'get_employees_for_notice'));

        add_action('wp_ajax_is_email_exist',array($this,'is_email_exist'));
        add_action('wp_ajax_nopriv_is_email_exist',array($this,'is_email_exist'));

        add_action('wp_ajax_emp_delete_notice',array($this,'emp_delete_notice'));
        add_action('wp_ajax_nopriv_emp_delete_notice',array($this,'emp_delete_notice'));

        add_action("admin_post_verify_door_to_door", array($this, "verify_door_to_door"));
        add_action("admin_post_nopriv_verify_door_to_door", array($this, "verify_door_to_door"));

        add_action("admin_post_verify_office_staff", array($this, "verify_office_staff"));
        add_action("admin_post_nopriv_verify_office_staff", array($this, "verify_office_staff"));

        add_action("wp_ajax_update_client_invoice_situation_status", array($this, "update_client_invoice_status"));
        add_action("wp_ajax_nopriv_update_client_invoice_situation_status", array($this, "update_client_invoice_status"));

    }

    public function update_client_invoice_status(){
        global $wpdb;

        $this->verify_nonce_field('update_client_invoice_situation_status');

        if(empty($_POST['client_id'])) $this->response('error');
        if(empty($_POST['client_status'])) $this->response('error');

        $client_id = $this->sanitizeEscape($_POST['client_id']);
        $client_status = $this->sanitizeEscape($_POST['client_status']);

        $data = [
            'tbl' => 'unsatisfied_clients',
            'where' => $client_id
        ];
        $update_data = [
            'satisfaction_status' => $client_status
        ];
        $response = (new \GamFunctions)->updateRecordInDbTable($data, $update_data);
        if(!$response) $this->response('error', 'opps ! Error on updating status');

        $this->response('success', 'Client status updated successfully.');
    }

    public function emp_delete_notice(){
        global $wpdb;

        $this->verify_nonce_field('emp_delete_notice');

        if(empty($_POST['notice_id'])) $this->response('error');

        $notice_id = $this->sanitizeEscape($_POST['notice_id']);

        $notice = $this->getEmployeeNoticeById($notice_id);
        if(!$notice) $this->response('error');

        if($notice->type == "single"){
            $response = $wpdb->delete($wpdb->prefix."employee_notice", ['notice_id' => $notice_id]);
            if(!$response) $this->response('error');
        }

        $response = $wpdb->delete($wpdb->prefix."notice", ['id' => $notice_id]);
        if(!$response) $this->response('error');

        $this->response('success', 'Employee Notice Deleted Successfully');
    }

    public function getEmployeeNoticeById(int $notice_id){
        global $wpdb;
        return $wpdb->get_row("select * from {$wpdb->prefix}notice where id = '$notice_id'");
    }

    public function is_email_exist(){

        $this->verify_nonce_field('is_email_exist');

        if(empty($_POST['email'])){
            echo "false";
            wp_die();
        }

        $email = sanitize_email($_POST['email']);

        if($this->isEmailExist($email)){
            echo "false";
            wp_die();
        }

        echo "true";
        wp_die();
    }

    public function check_if_employee_email_exist(){
        global $wpdb;

        if(empty($_POST['email'])){
            echo "true";
            wp_die();            
        }
        
        $email = sanitize_email($_POST['email']);

        $res=$wpdb->get_var("
            select count(*) 
            from {$wpdb->prefix}employees 
            where email='$email'
        ");

        echo ($res) ? "false" : "true"; wp_die();
    }


    public function fire_employee(){

        $this->verify_nonce_field('fire_employee');

        if(empty($_POST['employee_id'])) $this->response('error');
        if(empty($_POST['fire_reason'])) $this->response('error');

        $employee_id = filter_var($_POST['employee_id'], FILTER_SANITIZE_STRING);
        $fire_reason = filter_var($_POST['fire_reason'], FILTER_SANITIZE_STRING);

        if(!$this->fireEmployee($employee_id, $fire_reason)) $this->response('error');

        $this->response('success', 'Employee Fired successfully');
    }

    public function verify_employee_account(){

        $this->verify_nonce_field('verify_employee_account');

        if(empty($_POST['employee_id'])) $this->response('error');

        $employee_id = filter_var($_POST['employee_id'], FILTER_SANITIZE_STRING);

        if(!$this->verifyEmployee($employee_id)) $this->response('error');

        $this->response('success', 'Account verified successfully');
    }

    public function fireEmployee(int $employee_id, string $fire_reason = ''){
        global $wpdb;

        $update_data = [
            'application_status'        => 'fired',
            'application_status_reason' =>  $fire_reason
        ];

        $response = $wpdb->update($wpdb->prefix."employees", $update_data, ['id' => $employee_id]);
        if($response === false) return false;
        if($response === 0) return true;
        return $response;
    }

    public function verifyEmployee(int $employee_id){
        global $wpdb;
        return $this->updateEmployee($employee_id, ['application_status' => 'verified']);
    }

    public function check_for_authentication(){

        if(!is_page('employee-dashboard')) return true;
        
        if($this->isEmployeeLoggedIn()) return true;

        wp_redirect($this->loginPageUrl());
    }

    public function logout_employee(){
        global $wpdb;
        // first verify nonce field
        $this->verify_nonce_field('logout_employee');
        
        $edit_id = $_POST['user_id'];
        $data = [
            'close_time' => date('h:i:s'),
        ];
        
        if(!empty($data)) {
            $where = [ 'id' => $edit_id];
            $wpdb->update( $wpdb->prefix . 'attendance', $data, $where);
        }
        // logout 
        $this->logout();

        // redirect to login page
        wp_redirect($this->loginPageUrl());
    }

    public function logout(){
        unset($_SESSION['employee']);
        unset($_SESSION['employee_id']);
        unset($_SESSION['redirect_to_attendance']);
        return $this;
    }

    public function verify_office_staff(){
        global $wpdb;

        $this->verify_nonce_field('verify_office_staff');

        if(isset($_POST['employee_id']) && !empty($_POST['employee_id'])){
            $employee_id = $_POST['employee_id'];
            $branch_id = $_POST['branch_id'];
            $application_status = $_POST['application_status'];

            $update_data = [
                'application_status' => $application_status,
                'branch_id'   => $branch_id
            ];

            $response = $wpdb->update($wpdb->prefix."employees", $update_data, ['id' => $employee_id]);
            if($response == 1){
                $message="Branch updated successfully";
                $this->setFlashMessage($message,'success');    
            }else{
                $message="Something Went wrong, please try again later";
                $this->setFlashMessage($message,'warning');    
            }

        }else{
            $message="Something Went wrong, please try again later";
            $this->setFlashMessage($message,'warning');
        }
        wp_redirect($_POST['page_url']);

    }

    public function verify_door_to_door(){
        global $wpdb;

        $this->verify_nonce_field('verify_door_to_door');

        if(isset($_POST['employee_id']) && !empty($_POST['employee_id'])){
            $employee_id = $_POST['employee_id'];
            $branch_id = $_POST['branch_id'];
            $application_status = $_POST['application_status'];

            $update_data = [
                'application_status' => $application_status,
                'branch_id'   => $branch_id
            ];

            $response = $wpdb->update($wpdb->prefix."employees", $update_data, ['id' => $employee_id]);
            if($response == 1){
                $message="Branch updated successfully";
                $this->setFlashMessage($message,'success');    
            }else{
                $message="Something Went wrong, please try again later";
                $this->setFlashMessage($message,'warning');    
            }

        }else{
            $message="Something Went wrong, please try again later";
            $this->setFlashMessage($message,'warning');
        }
        wp_redirect($_POST['page_url']);

    }

    public function employee_login(){
        global $wpdb;

        $required_fields = [
            'username',
            'password',
            'role'            
        ];

        foreach($required_fields as $field){
            if(empty($_POST[$field])) $this->response('error', $field."is required");
        }

        $username = $this->sanitizeEscape($_POST['username']);
        $password = $this->sanitizeEscape($_POST['password']);
        $slug = $this->sanitizeEscape($_POST['role']);        

        $type_id = $this->getEmployeeTypeIdBySlug($slug);
        if(!$type_id) $this->response('error', $this->auth_err_msg);
        
        if(!$this->login($username, $password, $type_id)) $this->response('error', $this->auth_err_msg);
        
        $this->response('success', 'successfully authenticated');
    }

    public function getEmployee(int $employee_id, array $columns = []){
        global $wpdb;

        $conditions = [];
        $conditions[] = " id = '$employee_id'";

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';        
        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns
            from {$wpdb->prefix}employees E
            $conditions
        ");
    }

    public function getEmployeeTypes( array $roles = []){
        global $wpdb;

        $conditions = [];

        if(count($roles) > 0){
            $roles = join("','", $roles);
            $conditions[] = " slug IN ('$roles')";
        }

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

        return $wpdb->get_results("
            select *
            from {$wpdb->prefix}employees_types
            $conditions
        ");
    }

    public function getEmployeeTypeSlug(int $employee_id){
        global $wpdb;

        return $wpdb->get_var("
            select ER.slug
            from {$wpdb->prefix}employees_types ER
            left join {$wpdb->prefix}employees E
            on ER.id = E.role_id
            where E.id = '$employee_id'
        ");
    }

    public function getEmployeeTypeIdBySlug( string $slug ){
        global $wpdb;
        return $wpdb->get_var("
            select id
            from {$wpdb->prefix}employees_types
            where slug = '$slug'
        ");
    }

    public function getAllEmployees( array $roles = [], $isFired = true){
        global $wpdb;

        $conditions = [];

        if(count($roles) > 0){
            $roles = join("','", $roles);
            $conditions[] = " ER.slug IN ('$roles')";
        }

        if(!empty($role)) $conditions[] = " ER.slug = '$role'";
        if($isFired == true) $conditions[] = " E.application_status = 'verified'";

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';
        
        return $wpdb->get_results("
            select E.*
            from {$wpdb->prefix}employees E
            left join {$wpdb->prefix}employees_types ER
            on E.role_id = ER.id
            $conditions
        ");
    }

    public function createEmployee( array $data ){
        global $wpdb;

        if(empty($data['email'])) return false;

        if($this->isEmailExist($data['email'])) return false;

        if(!isset($data['status'])) $data['status'] = 1;

        if(!isset($data['application_status']) || empty($data['application_status']))
            $data['application_status'] = 'pending';

        if(empty($data['password'])) return false;

        $response = $wpdb->insert($wpdb->prefix."employees", $data);
        if(!$response) return false;

        return $wpdb->insert_id;
    }

    public function updateEmployee( int $employee_id, array $data){
        global $wpdb;

        $data['updated_at'] = date('Y-m-d h:i:s');

        $response = $wpdb->update($wpdb->prefix."employees", $data, ['id' => $employee_id]);

        return $response === false ? false : true;
    }

    public function login(string $username, string $password, int $role_id): bool{
        global $wpdb;

        $username = sanitize_text_field($username);
        $role_id = sanitize_text_field($role_id);

        // get employee by username or email
        $employee = $wpdb->get_row("
            select id, password
            from {$wpdb->prefix}employees
            where (username = '$username' or email = '$username')
            and role_id = '$role_id'
        ");
        
        // if no user found then return false
        if(!$employee) return false;

        // compare password
        if(!password_verify($password, $employee->password)) return false;
        
        if($role_id == "3" && !$this->markAttendance($employee->id)) return false;
        
        // set as logged in
        $this->setAsLoggedIn($employee->id);

        return true;
    }

    public function setAsLoggedIn(int $employee_id){
        $type_slug = $this->getEmployeeTypeSlug($employee_id);
        $_SESSION['employee']['slug'] = $type_slug;
        $_SESSION['employee']['id'] = $employee_id;
        $_SESSION['employee_id'] = $employee_id;
        $_SESSION['redirect_to_attendance'] = 1;
        return $this;
    }

    public function isEmployeeLoggedIn(): bool{
        if(!isset($_SESSION['employee']['id']) || empty($_SESSION['employee']['id'])) return false;
        return true;
    }

    public function loginPageUrl(){
        return site_url()."/employees-login";
    }

    public function isLoggedIn(){
        if(!isset($_SESSION['employee_id']) || empty($_SESSION['employee_id'])) return false;
        return true;
    }

    public function __getLoggedInEmployeeId(){
        if($this->isLoggedIn()) return $_SESSION['employee_id'];
        return wp_redirect($this->loginPageUrl());
    }

    public function signup( array $data){

    }

    public function generateUsername( string $name ){

        $name = filter_var($name, FILTER_SANITIZE_STRING);

        $name = $this->genereateSlug($name);

        if(!$this->checkUsernameExist($name)) return $name;             
        
        // generate a new random username
        $username = $name.mt_rand(0,1000);

        return $this->generateUsername($username);
    }

    public function isEmailExist(string $email){
        global $wpdb;
        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}employees
            where email = '$email'
        ");
    }

    public function checkUsernameExist( string $username): bool{
        global $wpdb;

        $count = $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}employees
            where username = '$username'
        ");

        return $count > 0 ? true : false;
    }

    public function getLoggedInEmployee(){
        global $wpdb;

        $employee_id = $this->__getLoggedInEmployeeId();
        return $this->getEmployee($employee_id);
    }
	
	public function delete_employee_account(){

        // verify nonce field first
        $this->verify_nonce_field('delete_employee_account');

        if(empty($_POST['employee_id'])) $this->response('error');

        $employee_id = esc_html($_POST['employee_id']);

        $response = $this->deleteAccount($employee_id);

        if(!$response) $this->response('error');

        $this->response('success','Employee account deleted successfully');
    }

	public function deleteAccount(int $employee_id): bool{
	    global $wpdb;

        // first verify if this applicaiton is pending or not
        $employee_data = $this->getEmployee($employee_id, ['application_status']);
        
        if(!$employee_data) return false;
        if($employee_data->application_status != "pending") return false;

		// delete application from database
		return $wpdb->delete($wpdb->prefix."employees", ['id' => $employee_id]);
    }

    public function dashboardUrl(){
        return site_url()."/employee-dashboard";
    }

    public function get_employees_for_notice(){
        global $wpdb;
        
		$this->verify_nonce_field('get_employees_for_notice');

        $employees = $this->getAllEmployees();

        if(count($employees) <=0 ) wp_die();

        $html_data = "";
        $html_data .= "<label for='employees'>Select Employee</label>";
        $html_data .= "<select name='employee_id' id='employee_id' class='form-control select2-field' required><option value=>Select</option>";
            foreach($employees as $emp):
        $html_data .=  "<option value='".$emp->id."'>".$emp->name."</option>";
            endforeach;
        $html_data .="</select>";

        echo $html_data;
        wp_die();
    }

    public function getEmployeeIdByRefId( int $employee_ref_id, int $employee_type_id ){
        global $wpdb;
        
        return $wpdb->get_var("
            select id
            from {$wpdb->prefix}employees
            where employee_ref_id = '$employee_ref_id'
            and role_id = '$employee_type_id'
        ");
    }

    public function getReferenceIdByEmployeeId(int $employee_id){
        global $wpdb;

        return $wpdb->get_var("
            select employee_ref_id
            from {$wpdb->prefix}employees
            where id = '$employee_id'
        ");
    }

    public function markAttendance(int $employee_id){
        global $wpdb;

            $current_date = date('Y-m-d');

            $check_exists = $wpdb->get_row("
                SELECT id 
                FROM {$wpdb->prefix}attendance 
                WHERE employee_id = '$employee_id' 
                AND DATE(created_at) = '$current_date'
            ");

            if($check_exists) return true;

            // if(date('H') >= 9) {
            //     $data = [
            //         "employee_id"       =>  $employee_id,
            //         "start_time"        =>  date('h:i:s'),
            //         "close_time"        =>  null,
            //         "attendance_date"   =>  $current_date,
            //     ];

            //     $response = $wpdb->insert($wpdb->prefix."attendance", $data);
            //     if(!$response) return false;
            // }

            return true;
    }

}

class Attendence extends Employee{

    public function setStartTime(){

    }

    public function setEndTime(){

    }



}

new Employee();

if(!is_admin()) return;

class EmployeeAdmin extends Employee{

    function __construct(){
        add_action("admin_post_admin_dashboard_login", array($this, "admin_dashboard_login"));
        add_action("admin_post_nopriv_admin_dashboard_login", array($this, "admin_dashboard_login"));
    }

    public function admin_dashboard_login(){
        global $wpdb;

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['employee_id'])) $this->sendErrorMessage($page_url);

        $employee_id = sanitize_text_field($_POST['employee_id']);

        $this->makeLogin($employee_id);
    }

    public function makeLogin(int $employee_id){

        $employee = $this->getEmployee($employee_id, ['role_id', 'employee_ref_id']);

        if($employee->role_id == 1) $this->technicianLogin($employee->employee_ref_id);
        elseif($employee->role_id == 2) $this->coldCallerLogin($employee->employee_ref_id);
        elseif($employee->role_id == 3 || $employee->role_id == 4) $this->employeeLogin($employee_id);

        return $this;
    }

    public function employeeLogin(int $employee_id){
        // first logout any old employee seession logged in
        $this->logout();

        // make logged in as new employee id
        $this->setAsLoggedIn($employee_id);

        wp_redirect($this->dashboardUrl());
    }

    public function technicianLogin(int $technician_id){
        // first logout old technician if any logged in 
        (new \Technician_details)->logout();

        // make logged in with technician id
        (new \Technician_details)->loginTechnician($technician_id);

        wp_redirect((new \Technician_details)->dashboardUrl());
    }

    public function coldCallerLogin(int $cold_caller_id){
        (new \ColdCaller)->logout_cold_caller();

        (new \ColdCaller)->doLogin($cold_caller_id);
    
        wp_redirect((new \ColdCaller)->dashboardUrl());
    }
}

new EmployeeAdmin();