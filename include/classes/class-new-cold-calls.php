<?php

class Coldcalls extends GamFunctions{
    function __construct(){

        // add_action('admin_post_create_cold_calls', array($this,'create_cold_calls'));
        // add_action('admin_post_nopriv_create_cold_calls', array($this,'create_cold_calls'));

        // add_action('admin_post_edit_cold_calls', array($this,'edit_cold_calls'));
        // add_action('admin_post_nopriv_edit_cold_calls', array($this,'edit_cold_calls'));

        // add_action('admin_post_update_cold_calls_status', array($this,'update_cold_calls_status'));
        // add_action('admin_post_nopriv_update_cold_calls_status', array($this,'update_cold_calls_status'));

        // add_action('wp_ajax_delete_cold_calls',array($this,'delete_cold_calls'));
        // add_action('wp_ajax_nopriv_delete_cold_calls',array($this,'delete_cold_calls'));

        // add_action('admin_post_create_cold_calls_log',array($this,'create_cold_calls_log'));
        // add_action('admin_post_nopriv_create_cold_calls_log',array($this,'create_cold_calls_log'));

        // add_action('admin_post_edit_call_log_status',array($this,'edit_call_log_status'));
        // add_action('admin_post_nopriv_edit_call_log_status',array($this,'edit_call_log_status'));


        // add_action('wp_ajax_view_cold_call_logs',array($this,'view_cold_call_logs'));
        // add_action('wp_ajax_nopriv_view_cold_call_logs',array($this,'view_cold_call_logs'));
    }

    // Create Cold Call Data 
    function create_cold_calls(){
        global $wpdb;
        $this->verify_nonce_field('create_cold_calls');

           // set variables
           $name = esc_html($_POST['name']);
           $email = esc_html($_POST['email']);
           $phone = esc_html($_POST['phone']);
           $address = esc_html($_POST['address']);

           $data = [
                'name'    =>  $name,
                'email'   =>  $email,
                'phone'   =>  $phone,
                'address' =>  $address
           ];

           if(!empty($_POST['review_link'])) $data['review_link'] = $this->sanitizeEscape($_POST['review_link']);
   
           $res = $wpdb->insert($wpdb->prefix."new_cold_calls", $data);

           if($res){
               $message="Cold Calls created successfully";
               $this->setFlashMessage($message,'success');
           }
           else{
               $message="Something went wrong , please try again later";
               $this->setFlashMessage($message,'danger');
           }
           wp_redirect($_POST['page_url']);
   
    }

    // Edit Cold Call Data 
    public function edit_cold_calls(){
		$this->verify_nonce_field('edit_cold_calls');
        if(isset($_POST['edit_cold_calls_id']) && !empty($_POST['edit_cold_calls_id'])){
            global $wpdb;

            $data=[
                    'name'     =>  $_POST['name'],
                    'email'    =>  $_POST['email'],
                    'phone'    =>  $_POST['phone'],
                    'address'  =>  $_POST['address'],
            ];
    
            $status=$wpdb->update($wpdb->prefix."new_cold_calls",$data,['id'=>$_POST['edit_cold_calls_id']]);
            if($status){
                $message="Cold Calls data updated successfully";
                $this->setFlashMessage($message,'success');    
            }
            else{
                $message="Something Went wrong, please try again later";
                $this->setFlashMessage($message,'warning');    
            }
    
        }
        else{
            $message="Something Went wrong, please try again later";
            $this->setFlashMessage($message,'warning');
        }

        wp_redirect(admin_url('admin.php?page='.$_POST['page']));


    }

    // Select Cold Calls 
    public function getColdcalls( int $cold_calls_id ){
        global $wpdb;
        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}new_cold_calls
            where id = '$cold_calls_id'
        ");
    }

    // Delete Cold Calls 
    public function delete_cold_calls(){
        global $wpdb;

        $this->verify_nonce_field('delete_cold_calls');

        $data=[
            'cold_calls_id'     =>  $_POST['cold_calls_id'],
        ];
        
        $id = $_POST['cold_calls_id'];
        $table = 'wp_new_cold_calls';


        if(empty($_POST['cold_calls_id'])) $this->response('error');
            
        $tracker_id = $this->sanitizeEscape($_POST['cold_calls_id']);

        $response = $wpdb->delete( $table, array( 'id' => $id ) );

    
        if(!$response) $this->response('error');

        $this->response("success",'Cold Calls Deleted successfully');
    }

    // Update status
    public function update_cold_calls_status() {

        $this->verify_nonce_field('update_cold_calls_status');
        if(isset($_POST['update_status_id']) && !empty($_POST['update_status_id'])){
            global $wpdb;

            $data=[
                    'status'     =>  $_POST['status'],
                    'note'     =>  $_POST['note'],
            ];
    
            $status=$wpdb->update($wpdb->prefix."new_cold_calls",$data,['id'=>$_POST['update_status_id']]);
            
            if($status == 1){
                $message="Cold Calls Status updated successfully";
                $this->setFlashMessage($message,'success');    
            }
            else{
                $message="Something Went wrong, please try again later";
                $this->setFlashMessage($message,'warning');    
            }
    
        }
        else{
            $message="Something Went wrong, please try again later";
            $this->setFlashMessage($message,'warning');
        }

        wp_redirect(admin_url('admin.php?page=cold-calls'));

    }

    // Create Cold Calls Log 
    public function create_cold_calls_log() {
        global $wpdb;
        $this->verify_nonce_field('create_cold_calls_log');

           // set variables
           $date = esc_html($_POST['date']);
           $description = esc_html($_POST['description']);
           $cold_call_id = esc_html($_POST['cold_call_id']);

           $data = [
                'date'    =>  $date,
                'description'   =>  $description,
                'cold_call_id'   =>  $cold_call_id
           ];

           $res = $wpdb->insert($wpdb->prefix."cold_calls_log", $data);

           if($res){
               $message="Cold Calls Log created successfully";
               $this->setFlashMessage($message,'success');
           }
           else{
               $message="Something went wrong , please try again later";
               $this->setFlashMessage($message,'danger');
           }
           wp_redirect($_POST['page_url']);

    }

    // View Cold Calls Log 
    public function getColdcallstatus( int $cold_calls_id ) {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM wp_cold_calls_log
            RIGHT JOIN wp_new_cold_calls
            ON wp_cold_calls_log.cold_call_id = wp_new_cold_calls.id
            WHERE wp_cold_calls_log.cold_call_id = '$cold_calls_id'
        ");

        
    }

    public function getColdlogs(){
        $wpdb;

        // $this->verify_nonce_field('view_cold_call_logs');

		// if(empty($_POST['deposit_id'])) $this->response('error');

		// $deposit_id = $this->sanitizeEscape($_POST['deposit_id']);

		// $deposit_proof = $wpdb->get_var("select * from {$wpdb->prefix}cold_calls_log where id='$deposit_id'");

        // alert($deposit_proof);

		// if(empty($deposit_proof)){
		// 	echo "No Record Found"; wp_die();
		// }

		// get_template_part('include/admin/proof-of-deoposit/discrepancy-template-docs', null, ['data' => json_decode($deposit_proof)]);
		// wp_die();


        
    }

    // public function view_cold_call_logs(){
    //     global $wpdb;

    //         $this->verify_nonce_field('view_cold_call_logs');

    //         $s_id = $_POST['student_id'];
    //         // echo $return = $s_id;
    //         // die();


    //         $query = $wpdb->get_results("select * from wp_cold_calls_log WHERE ID = '$s_id'");


    //         $deposit_proof = $wpdb->get_results("
    //         select *
    //         from wp_cold_calls_log
    //         where id = '$s_id'  ");

    //         print_r($query);
    //         // die();

    //         foreach($query as $row) {
    //             echo $return  = '
    //             <h5>ID : '. $row['cold_call_id'].'</h5>
    //             <h5>name : '. $row['name'].'</h5>
    //             <h5>email : '. $row['email'].'</h5>
    //             <h5>phone : '. $row['date'].'</h5>
    //             <h5>address : '. $row['description'].'</h5>
    //             ';
    //         } 
           
    // }


}

new Coldcalls();