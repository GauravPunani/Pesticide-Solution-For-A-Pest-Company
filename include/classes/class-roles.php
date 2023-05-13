<?php

// OFFICE ROLES CLASS
class Roles extends GamFunctions{
    function __construct(){
        add_action('admin_post_edit_role',array($this,'edit_role'));
        add_action('admin_post_nopriv_edit_role',array($this,'edit_role'));

        add_action('admin_post_create_office_staff_role',array($this,'create_office_staff_role'));
        add_action('admin_post_nopriv_create_office_staff_role',array($this,'create_office_staff_role'));

        add_action('admin_post_update_office_role',array($this,'update_office_role'));
        add_action('admin_post_nopriv_update_office_role',array($this,'update_office_role'));

		add_action( 'wp_ajax_delete_office_staff_role', array($this,'delete_office_staff_role'));
        add_action( 'wp_ajax_nopriv_delete_office_staff_role', array($this,'delete_office_staff_role'));
        
		add_action( 'wp_ajax_unlink_employee_from_role', array($this,'unlink_employee_from_role'));
        add_action( 'wp_ajax_nopriv_unlink_employee_from_role', array($this,'unlink_employee_from_role'));

    }

    public function getLinkedEmployees(int $role_id){
        global $wpdb;
        return $wpdb->get_results("select * from {$wpdb->prefix}office_role_employee where role_id = '$role_id'");
    }

    public function linkEmployee(int $role_id, int $employee_id){
        global $wpdb;

        // check if not already linked
        if($this->isAlreadyLinked($role_id, $employee_id)) return true;

        $data = [
            'role_id'   =>  $role_id,
            'employee_id'   =>  $employee_id
        ];

        return $wpdb->insert($wpdb->prefix."office_role_employee", $data);
    }

    public function isAlreadyLinked(int $role_id, int $employee_id){
        global $wpdb;
        return $wpdb->get_var("
            select count(*) 
            from {$wpdb->prefix}office_role_employee 
            where role_id = '$role_id' 
            and employee_id = '$employee_id'
        ");
    }

    public function isOfficeRoleSlugExist(string $slug){
        global $wpdb;
        
        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}roles
            where slug = '$slug'
        ");
    }

    public function generateRoleSlug( string $role ){

        $slug = $this->genereateSlug($role);

        if(!$this->isOfficeRoleSlugExist($slug)) return $slug;             
        
        // generate a new random username
        $slug = $slug.mt_rand(0,1000);

        return $this->generateRoleSlug($slug);
    }

    public function createOfficeRole(array $data){
        global $wpdb;

        if(empty($data['name'])) return false;

        $data['slug'] = $this->generateRoleSlug($data['name']);

        $response = $wpdb->insert($wpdb->prefix."roles", $data );

        return $response ? $wpdb->insert_id : false;
    }

    public function create_office_staff_role(){
        global $wpdb;
        
        $this->verify_nonce_field('create_office_staff_role');

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['role_name'])) $this->sendErrorMessage($page_url);

        $data = ['name'  =>  $this->sanitizeEscape($_POST['role_name'])];

        $this->beginTransaction();

        $role_id = $this->createOfficeRole($data);
        if(!$role_id) $this->rollBackTransaction($page_url);

        if(isset($_POST['employee_ids']) && is_array($_POST['employee_ids']) && count($_POST['employee_ids']) > 0){
            $employee_ids = $_POST['employee_ids'];
            foreach($employee_ids as $employee_id){
                if(!$this->linkEmployee($role_id, $employee_id)) $this->rollBackTransaction($page_url);
            }
        }

        $this->commitTransaction();

        $message = "Office role created successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function updateRole(int $role_id, array $data){
        global $wpdb;

        $response = $wpdb->update($wpdb->prefix."roles", $data, ['id' => $role_id]);
        return $response === false ? false : true;
    }

    public function update_office_role(){
        global $wpdb;
        
        $this->verify_nonce_field('update_office_role');

        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['role_id']) ||
            empty($_POST['role_name'])
        ) $this->sendErrorMessage($page_url);

        $role_id = $this->sanitizeEscape($_POST['role_id']);
        $role_name = $this->sanitizeEscape($_POST['role_name']);

        $data = [ 'name'  =>  $role_name];
        
        $this->beginTransaction();

        $response = $this->updateRole($role_id, $data);
        if(!$response) $this->rollBackTransaction($page_url);

        if(isset($_POST['employee_ids']) && is_array($_POST['employee_ids']) && count($_POST['employee_ids']) > 0){
            $employee_ids = $_POST['employee_ids'];
            foreach($employee_ids as $employee_id){
                if(!$this->linkEmployee($role_id, $employee_id)) $this->rollBackTransaction($page_url);
            }
        }        

        $this->commitTransaction();

        $message = "Role updated successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function deleteOfficeRole(int $role_id){
        global $wpdb;

        // delete linked employees first
        $response = $this->__deleteLinkedEmployees($role_id);
        if(!$response) return [false, 'Unable to delete linked employee records'];

        $response =  $wpdb->delete($wpdb->prefix."roles",['id' => $role_id]);
        if(!$response) return [false, $wpdb->last_error];

        return [true, null];
    }

    public function __deleteLinkedEmployees(int $role_id){
        global $wpdb;
        $response = $wpdb->delete($wpdb->prefix."office_role_employee", ['role_id' => $role_id]);
        return $response === false ? false : true;
    }

    public function delete_office_staff_role(){
        global $wpdb;

        $this->verify_nonce_field('delete_office_staff_role');

        if(empty($_POST['role_id'])) $this->response('error');

        $role_id = $this->sanitizeEscape($_POST['role_id']);

        list($response, $message) = $this->deleteOfficeRole($role_id); 
        if(!$response) $this->response('error', $message);

        $this->response('success');        
    }

    public function unlinkEmployeeFromRole(int $record_id){
        global $wpdb;
        return $wpdb->delete($wpdb->prefix."office_role_employee", ['id' => $record_id]);
    }

    public function unlink_employee_from_role(){
        global $wpdb;

        $this->verify_nonce_field('unlink_employee_from_role');

        if(empty($_POST['record_id'])) $this->response('error');
        
        $record_id = $this->sanitizeEscape($_POST['record_id']);

        if(!$this->unlinkEmployeeFromRole($record_id)) $this->response('error', 'Unable to unlink employee');

        $this->response('success', 'Employee unlinked from role successfully');
    }

    public function getOfficeRoles(array $columns = []){
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}roles
        ");
    }

    public function getRoleIdBySlug(string $slug){
        global $wpdb;

        return $wpdb->get_var("
            select id
            from {$wpdb->prefix}roles
            where slug = '$slug'
        ");
    }

    public function getRoleBySlug(string $slug){
        global $wpdb;

        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}roles
            where slug = '$slug'
        ");
    }

    public function getRoles(int $employee_id){
        global $wpdb;
        return $wpdb->get_results("
            select R.name, ORE.created_at
            from {$wpdb->prefix}office_role_employee ORE

            left join {$wpdb->prefix}roles R
            on ORE.role_id = R.id

            left join {$wpdb->prefix}employees E
            on E.id = ORE.employee_id

            where ORE.employee_id = '$employee_id'
        ");
    }
}


new Roles();