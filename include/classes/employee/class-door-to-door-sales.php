<?php

namespace Employee;

class DoorToDoorSales extends Employee{

    private $role_id = 4;

    function __construct(){
        add_action("admin_post_door_to_door_sales_signup", array($this, "door_to_door_sales_signup"));
        add_action("admin_post_nopriv_door_to_door_sales_signup", array($this, "door_to_door_sales_signup"));

        add_action("admin_post_update_door_to_door_sales_ac", array($this, "update_door_to_door_sales_ac"));
        add_action("admin_post_nopriv_update_door_to_door_sales_ac", array($this, "update_door_to_door_sales_ac"));
    }
    
    public function update_door_to_door_sales_ac(){

        $this->verify_nonce_field('update_door_to_door_sales_ac');

        $page_url = $_POST['page_url'];

        if(!isset($_POST['employee_id']) || empty($_POST['employee_id'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['name']) || empty($_POST['name'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['email']) || empty($_POST['email'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['address']) || empty($_POST['address'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['phone_no']) || empty($_POST['phone_no'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['status']) || empty($_POST['status'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['branch_id']) || empty($_POST['branch_id'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['application_status']) || empty($_POST['application_status'])) $this->sendErrorMessage($page_url);
        
        $employee_id = esc_html($_POST['employee_id']);
        $status = esc_html($_POST['status']) == 'active' ? 1 : 0;

        $data = [
            'name'      =>  esc_html($_POST['name']),
            'email'     =>  esc_html($_POST['email']),
            'address'   =>  esc_html($_POST['address']),
            'phone_no'  =>  esc_html($_POST['phone_no']),
            'status'    =>  $status,
            'application_status'  =>  esc_html($_POST['application_status']),
            'branch_id'   => esc_html($_POST['branch_id']),
            'status'    =>  $status
        ];

        if(!$this->updateEmployee($employee_id, $data)) $this->sendErrorMessage($page_url);

        $message = "Door to door sales account updated successfully";
        $this->setFlashMessage($message, 'success');
        wp_redirect($page_url);
    }

    public function door_to_door_sales_signup(){

        $this->verify_nonce_field('door_to_door_sales_signup');

        $page_url = $_POST['page_url'];

        if(
            empty($_POST['name']) ||
            empty($_POST['email']) ||
            empty($_POST['address']) ||
            empty($_POST['phone_no']) ||
            empty($_POST['password'])
        ) $this->sendErrorMessage($page_url);

        $name = $this->sanitizeEscape($_POST['name']);
        $email = $this->sanitizeEscape($_POST['email']);
        $address = $this->sanitizeEscape($_POST['address']);
        $phone_no = $this->sanitizeEscape($_POST['phone_no']);

        $password = password_hash($_POST['password'],PASSWORD_DEFAULT);

        $door_to_door_sales_id = $this->createDoorToDoorReference();
        if(!$door_to_door_sales_id) $this->sendErrorMessage($page_url);

        // create employee record as well for office staff
        $employee_data = [
            'employee_ref_id'       =>  $door_to_door_sales_id,
            'name'                  =>  $name,
            'email'                 =>  $email,
            'address'               =>  $address,
            'address'               =>  $phone_no,
            'role_id'               =>  $this->role_id,
            'username'              =>  $this->generateUsername($name),
            'password'              =>  $password,
        ];

        if(!$this->createEmployee($employee_data)) $this->sendErrorMessage($page_url);

        $message = "Your record has been submitted successfully, you'll receive update on your account from office soon. You can login to your account using the email and passowrd provided once your applicaiton is verified.";
        $this->setFlashMessage($message, "success");

        wp_redirect($page_url);
    }

    public function createDoorToDoorReference( array $data = []){
        global $wpdb;
        $data['created_at'] = date('Y-m-d h:i:s');
        $data['updated_at'] = date('Y-m-d h:i:s');

        $wpdb->insert($wpdb->prefix."door_to_door_sales", $data);

        return $wpdb->insert_id;
    }

    public function get(){
        return $this->getAllEmployees(['door_to_door_sale']);
    }
}

new DoorToDoorSales();