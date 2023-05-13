<?php

class Notices extends GamFunctions{

    function __construct(){

        add_action('wp_ajax_change_notice_status',array($this,'change_notice_status'));
        add_action('wp_ajax_nopriv_change_notice_status',array($this,'change_notice_status'));

        add_action('wp_ajax_get_week_unattributed_invoices',array($this,'get_week_unattributed_invoices'));
        add_action('wp_ajax_get_week_unattributed_invoices',array($this,'get_week_unattributed_invoices'));

        add_action('wp_ajax_delete_critical_notice',array($this,'delete_critical_notice'));
        add_action('wp_ajax_delete_critical_notice',array($this,'delete_critical_notice'));

        add_action("admin_post_add_new_employee_notice", array($this, "add_new_employee_notice"));
        add_action("admin_post_nopriv_add_new_employee_notice", array($this, "add_new_employee_notice"));
    }

    /*
        This Method Generate A Notification In System
        Return Type : Boolean
    */
    public function generateNotification(array $data){
        global $wpdb;

        $notice = [
            'notice'        =>  $data['notice'],
            'class'         =>  $data['class'],
            'type'          =>  $data['type'],
            'date_created'  =>  date('Y-m-d'),
            'week'          =>  date('Y-\WW'),
            'status'        =>  1,
            'type_id'       =>  isset($data['type_id']) ? $data['type_id'] : ''
        ];

        return $wpdb->insert($wpdb->prefix."notices", $notice);
    }

    public function isNoticeAlreadyExist( int $technician_id, string $type ){
        global $wpdb;

        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}technician_account_status
            where technician_id = '$technician_id'
            and type = '$type'
        ");
    }

    public function deleteAccountNotices($technician_id, $type){
        global $wpdb;

        $where_data=[
            'type'          =>  $type,
            'technician_id' =>  $technician_id
        ];
        $response =  $wpdb->delete($wpdb->prefix."technician_account_status", $where_data);

        return $response === false ? false : true;
    }

    /**
     * Create Notice for technician account in order to lock their accont untill they clear the notice issue.
     *
     * @param array      $data       array of input.
     * @param bool       $checkForDuplicate       will check if same type record exist in sytem
     * @return bool wether notice generated or not true or false.
     */    
    public function generateTechnicianNotice( array $data , bool $checkForDuplicate = false){
        global $wpdb;

        // first check if this notice already exist for technician

        if($checkForDuplicate && $this->isNoticeAlreadyExist($data['technician_id'], $data['type'])) return true;

        if(!isset($data['date'])) $data['date'] = date('Y-m-d');
        if(!isset($data['week'])) $data['week'] = date('Y-\WW');

        return $wpdb->insert($wpdb->prefix."technician_account_status", $data);        
    }

    public function createNotice( array $data){
        global $wpdb;

        $data['created_at'] = date('Y-m-d h:i:s');
        $data['updated_at'] = date('Y-m-d h:i:s');

        return $wpdb->insert($wpdb->prefix."notice", $data);
    }

    public function linkEmployeeWithNotice(int $notice_id, int $employee_id){
        global $wpdb;
        
        $data = [
            'notice_id'     =>  $notice_id,
            'employee_id'   =>  $employee_id
        ];

        return $wpdb->insert($wpdb->prefix."employee_notice", $data);
    }

    public function add_new_employee_notice(){
        global $wpdb;

        $this->verify_nonce_field('add_new_employee_notice');

        $page_url = esc_url_raw($_POST['page_url']);

        if(!isset($_POST['notice_type']) || empty($_POST['notice_type'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['notice']) || empty($_POST['notice'])) $this->sendErrorMessage($page_url);

        $notice_type = $this->sanitizeEscape($_POST['notice_type']);
        $notice = $this->sanitizeEscape($_POST['notice'], 'textarea');

        $data = [
            "notice" => $notice,
            "type" =>  $notice_type,
        ];

        $response = $this->createNotice($data);

        if(!$response) $this->sendErrorMessage($page_url);
        $notice_id = $wpdb->insert_id;

        if($notice_type == "single"){
            if(empty($_POST['employee_id'])) $this->sendErrorMessage($page_url);

            $employee_id = $this->sanitizeEscape($_POST['employee_id']);
            $response = $this->linkEmployeeWithNotice($notice_id, $employee_id);

            if(!$response) $this->sendErrorMessage($page_url);
        }

        $message = "Notice created successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function delete_critical_notice(){

        $this->verify_nonce_field('delete_critical_notice');

        global $wpdb;

        if(empty($_POST['notice_id'])) $this->response('error','Something went wrong');

        $res=$wpdb->delete($wpdb->prefix."technician_account_status",['id' => $_POST['notice_id']]);

        if(!$res) $this->response('error','Something went wrong');

        $this->response('success','Notice Deleted Successfully');

    }

    public function getAccountNotices(int $technician_id, string $level = 'normal'){
        global $wpdb;

        return $wpdb->get_results("
            select *
            from {$wpdb->prefix}technician_account_status
            where technician_id='$technician_id' 
            and level='$level'
        ");
    }

    public function get_all_notices( string $type=''){
		global $wpdb;

		$where_type = !empty($type) ? " and type='$type'" : '';

		return $wpdb->get_results("
            select * 
            from {$wpdb->prefix}notices 
            where status='1'
            $where_type order by date_created desc
        ");
	}

    public function get_notice_with_html($type=''){
		global $wpdb;

		$where_type='';

		if(!empty($type)) $where_type="and type='$type'";

		$notices = $wpdb->get_results("
            select *
            from {$wpdb->prefix}notices 
            where status='1' 
            $where_type 
            order by date_created desc
        ");

        if(count($notices) <=0 ) return '';

        $notice_html = "";
        foreach ($notices as $notice) {
            $notice_html .= "
                <div data-notice-id='$notice->id' class='notice notice-warning is-dismissible'>
                    <p>$notice->notice</p>
                </div>
            ";
        }

        return $notice_html;
    }

    public function change_notice_status(){
        global $wpdb;

        if(empty($_POST['notice_id'])) $this->response('error','notice id not found');

        $res=$wpdb->update($wpdb->prefix."notices",['status'=>'0'],['id'=>$_POST['notice_id']]);

        if(!$res) $this->response('error','something went wrong');

        $this->response('success','notices status changed');
    }

    public function get_week_unattributed_invoices(){
	    $this->verify_nonce_field('get_week_unattributed_invoices');

        if(empty($_POST['week'])) wp_die();

        get_template_part('/include/admin/weekly-alert/pending-and-generate-alert',null,['week'=>$_POST['week']]);
        wp_die();
    }
    

}

new Notices();