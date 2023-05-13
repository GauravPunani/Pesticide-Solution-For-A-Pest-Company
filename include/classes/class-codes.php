<?php

interface Code{
    public function generateCode(string $employee_id, string $type);
    public function verifyCode(int $db_id, int $code);
    public function deleteCode(int $db_id);
    public function getCode(int $db_id);
}

class Codes extends GamFunctions implements Code{

    function __construct(){

        add_action("wp_ajax_code_module_generate_code", array($this, "code_module_generate_code"));
        add_action("wp_ajax_nopriv_code_module_generate_code", array($this, "code_module_generate_code"));

        add_action("wp_ajax_code_module_verify_code", array($this, "code_module_verify_code"));
        add_action("wp_ajax_nopriv_code_module_verify_code", array($this, "code_module_verify_code"));
    }

    public function code_module_verify_code(){
        global $wpdb;

        $this->verify_nonce_field('code_module_verify_code');

        if(empty($_POST['code'])) $this->response('error', $this->err_msg);
        if(empty($_POST['db_id'])) $this->response('error', $this->err_msg);

        $code = sanitize_text_field($_POST['code']);
        $db_id = $this->encrypt_data($_POST['db_id'], 'd');

        if(!$this->verifyCode($db_id, $code)) $this->response('error','code did not matched');
        
        $this->response('success');
    }

    public function code_module_generate_code(){
        global $wpdb;

        $this->verify_nonce_field('code_module_generate_code');

        if(empty($_POST['type'])) $this->response('error');

        try{
            $type = $this->sanitizeEscape($_POST['type']);
            $technician_id = (new Technician_details)->get_technician_id();
            $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($technician_id, 1);

            $enc_db_id = $this->generateCode($employee_id, $type);
            $this->response('success', '', ['db_id' => $enc_db_id]);            
        }
        catch(Exception $e){
            $this->response('error');
        }
    }

    public function generateCode(string $employee_id, string $type){
		global $wpdb;

		$employee = (new Employee\Employee)->getEmployee($employee_id);

		$data = [
			'name'	=>	$employee->name,
			'type'	=>	$type,
			'code'	=>	mt_rand(100000, 999999)
		];

		$response = $wpdb->insert($wpdb->prefix."technician_codes", $data);
		if(!$response) return false;

		return $this->encrypt_data($wpdb->insert_id);
	}

    public function verifyCode(int $db_id, int $code, bool $deleteRecord = false){
		global $wpdb;

        $db_code = $this->getCode($db_id);
		if(!$code) return false;

		if($code != $db_code) return false;

        if($deleteRecord) $this->deleteCode($db_id);
		return true;
	}

    public function deleteCode(int $db_id){
		global $wpdb;
		return $wpdb->delete($wpdb->prefix."technician_codes", ['id' => $db_id]);
	}

    public function getCode(int $db_id){
        global $wpdb;
        return $wpdb->get_var("
            select code
            from {$wpdb->prefix}technician_codes 
            where id = '$db_id'
        ");
    }
}

new Codes();