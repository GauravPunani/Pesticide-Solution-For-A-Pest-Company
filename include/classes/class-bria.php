<?php

class Bria extends GamFunctions {

    public function isKeyAlreadyLinked($key_id){
        global $wpdb;
        return $wpdb->get_var("
            select employee_id
            from {$wpdb->prefix}bria_licenses
            where id = '$key_id'
        ");
    }

    public function unlinkLicenseKey(int $employee_id){
        global $wpdb;
        return $wpdb->update($wpdb->prefix."bria_licenses", ['employee_id' => ''], ['employee_id' => $employee_id]);
    }

    public function linkLicenseKey(int $employee_id, int $key_id){
        global $wpdb;

        // return false if key is already assigned to someone
        if($this->isKeyAlreadyLinked($key_id)) return false;

        // unlink any old key if linked
        $this->unlinkLicenseKey($employee_id);

        $data = ['employee_id' => $employee_id];

        return $wpdb->update($wpdb->prefix."bria_licenses", $data, ['id' => $key_id]);
    }

    public function getkeys(bool $onlyAvailable = false, array $columns = []){
        global $wpdb;

        $conditions = [];

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        if($onlyAvailable) $conditions[] = " (employee_id is null or employee_id = '')";

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

        return $wpdb->get_results("
            select *
            from {$wpdb->prefix}bria_licenses
            $conditions
        ");
    }

    public function getLicenseKeyById(int $key_id){
        global $wpdb;

        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}bria_licenses
            where id = '$key_id'
        ");
    }

    public function getLicenseKeyByEmployeeId(int $employee_id){
        global $wpdb;
        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}bria_licenses
            where employee_id = '$employee_id'
        ");        
    }
}

new Bria();

if(is_admin()){
    class BriaAdmin extends Bria{
        function __construct(){

            add_action("admin_post_create_bria_key", array($this, "create_bria_key"));
            add_action("admin_post_nopriv_create_bria_key", array($this, "create_bria_key"));

            add_action("admin_post_update_bria_key", array($this, "update_bria_key"));
            add_action("admin_post_nopriv_update_bria_key", array($this, "update_bria_key"));

            add_action("wp_ajax_delete_bria_key", array($this, "delete_bria_key"));
            add_action("wp_ajax_nopriv_delete_bria_key", array($this, "delete_bria_key"));

            add_action("admin_post_link_bria_key", array($this, "link_bria_key"));
            add_action("admin_post_nopriv_link_bria_key", array($this, "link_bria_key"));
        }

        public function link_bria_key(){
            global $wpdb;

            $this->verify_nonce_field('link_bria_key');

            $this->beginTransaction();

            $page_url = esc_url_raw($_POST['page_url']);

            if(empty($_POST['license_key_id'])) $this->sendErrorMessage($page_url);
            if(empty($_POST['employee_id'])) $this->sendErrorMessage($page_url);

            $license_key_id = $this->sanitizeEscape($_POST['license_key_id']);
            $employee_id = $this->sanitizeEscape($_POST['employee_id']);

            $response = $this->linkLicenseKey($employee_id, $license_key_id);
            if(!$response) $this->rollBackTransaction($page_url);

            $response = (new OfficeTasks)->clearBriaKeyTask($employee_id);
            if(!$response) $this->rollBackTransaction($page_url);

            $this->commitTransaction();

            $message = "Key linked successfully";
            $this->setFlashMessage($message, 'success');

            wp_redirect($page_url);
        }

        public function delete_bria_key(){
            global $wpdb;

            $this->verify_nonce_field('delete_bria_key');

            $page_url = esc_url_raw($_POST['page_url']);

            if(empty($_POST['key_id'])) $this->response('error');

            $key_id = $this->sanitizeEscape($_POST['key_id']);

            $response = $this->deleteKey($key_id);

            if(!$response) $this->response('error');

            $this->response('success', 'Bria key deleted successfully');

            wp_redirect($page_url);
        }

        public function update_bria_key(){
            global $wpdb;

            $this->verify_nonce_field('update_bria_key');

            $page_url = esc_url_raw($_POST['page_url']);

            if(empty($_POST['license_key_id'])) $this->sendErrorMessage($page_url);
            if(empty($_POST['key'])) $this->sendErrorMessage($page_url);
            if(empty($_POST['title'])) $this->sendErrorMessage($page_url);


            $license_key_id = $this->sanitizeEscape($_POST['license_key_id']);

            $data = [
                'key'   =>  $this->sanitizeEscape($_POST['key']),
                'title'   =>  $this->sanitizeEscape($_POST['title']),
            ];

            $response = $this->editKey($license_key_id, $data);

            if(!$response) $this->sendErrorMessage($page_url);

            $message = "Bria key updated successfully";
            $this->setFlashMessage($message, 'success');

            wp_redirect($page_url);
        }

        public function create_bria_key(){
            global $wpdb;

            $this->verify_nonce_field('create_bria_key');

            $page_url = esc_url_raw($_POST['page_url']);

            if(empty($_POST['key'])) $this->sendErrorMessage($page_url);

            $data = [
                'key'   =>  $this->sanitizeEscape($_POST['key']),
                'title'   =>  $this->sanitizeEscape($_POST['title']),
            ];

            if(!empty($_POST['employee_id'])) $data['employee_id'] = $this->sanitizeEscape($_POST['employee_id']);

            $response =  $this->createKey($data);

            if(!$response) $this->sendErrorMessage($page_url);

            $message = "Bria key created successfully";
            $this->setFlashMessage($message, 'success');

            wp_redirect($page_url);
        }

        public function deleteKey(int $key_id){
            global $wpdb;

            return $wpdb->delete($wpdb->prefix."bria_licenses", ['id' => $key_id]);
        }

        public function createKey( array $data){
            global $wpdb;
            
            $data['created_at'] = date('Y-m-d h:i:s');
            $data['updated_at'] = date('Y-m-d h:i:s');

            return $wpdb->insert($wpdb->prefix."bria_licenses", $data);
        }

        public function editKey(int $key_id, array $data){
            global $wpdb;

            $data['updated_at'] = date('Y-m-d h:i:s');

            return $wpdb->update($wpdb->prefix."bria_licenses", $data, ['id' => $key_id]);
        }

        public function getUnassignedKeyEmployees(array $columns){
            global $wpdb;

            return $wpdb->get_results("
                select E.id, E.name
                from {$wpdb->prefix}employees E
                where E.id not in (
                    select BL.employee_id from {$wpdb->prefix}bria_licenses BL where BL.employee_id is not null
                )
                and E.application_status = 'verified'
                and E.role_id = '2'
            ");
        }    
    }

    new BriaAdmin();
}

class BriaSession extends Bria{

    function __construct(){
        add_action("admin_post_link_bria_key_by_employee", array($this, "link_bria_key_by_employee"));
        add_action("admin_post_nopriv_link_bria_key_by_employee", array($this, "link_bria_key_by_employee"));
    }

    public function link_bria_key_by_employee(){
        global $wpdb;

        $this->verify_nonce_field('link_bria_key_by_employee');

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['license_key_id'])) $this->sendErrorMessage($page_url);

        $license_key_id = $this->sanitizeEscape($_POST['license_key_id']);
        $cold_caller_id = (new ColdCaller)->getLoggedInColdCallerId();
        $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);

        if(!$employee_id) $this->sendErrorMessage($page_url);

        $response = $this->linkLicenseKey($employee_id, $license_key_id);

        if(!$response) $this->sendErrorMessage($page_url);

        $message = "Key linked successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }
    
}
new BriaSession();