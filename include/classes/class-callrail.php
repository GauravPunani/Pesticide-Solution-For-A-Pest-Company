<?php

class Callrail_new extends GamFunctions {

    function __construct(){
        add_action('wp_ajax_get_lead_source_from_callrail',array($this,'get_lead_source_from_callrail'));
        add_action('wp_ajax_nopriv_get_lead_source_from_callrail',array($this,'get_lead_source_from_callrail'));

        add_action('wp_ajax_get_tracking_no_by_location',array($this,'get_tracking_no_by_location'));
        add_action('wp_ajax_nopriv_get_tracking_no_by_location',array($this,'get_tracking_no_by_location'));

        add_action('wp_ajax_delete_callrail_tracker',array($this,'delete_callrail_tracker'));
        add_action('wp_ajax_nopriv_delete_callrail_tracker',array($this,'delete_callrail_tracker'));

        add_action('wp_ajax_check_for_tracker_name',array($this,'check_for_tracker_name'));
        add_action('wp_ajax_nopriv_check_for_tracker_name',array($this,'check_for_tracker_name'));

        add_action('admin_post_update_callrail_tracker_location',array($this,'update_callrail_tracker_location'));
        add_action('admin_post_nopriv_update_callrail_tracker_location',array($this,'update_callrail_tracker_location'));

        add_action('admin_post_create_tracker',array($this,'create_tracker'));
        add_action('admin_post_nopriv_create_tracker',array($this,'create_tracker'));
    }

    public function getLeadSourceByCalendarEvent( object $event ): string{

        if (strpos($event->description, '@mb') !== false) {
            return "reoccuring_customer";
        }

        if (strpos($event->description, '@cc') !== false) {
            return "cold_call";
        }


    }

    public function create_tracker(){
        global $wpdb;

		$this->verify_nonce_field('create_tracker');

        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['tracker_name']) ||
            empty($_POST['tracker_no']) ||
            empty($_POST['callrail_ac']) ||
            empty($_POST['tracker_location'])            
        ) $this->sendErrorMessage($page_url);

        $data=[
            'tracking_name'     =>  $this->sanitizeEscape($_POST['tracker_name']),
            'tracking_phone_no' =>  $this->sanitizeEscape($_POST['tracker_no']),
            'location'          =>  $this->sanitizeEscape($_POST['callrail_ac']),
            'actual_location'   =>  $this->sanitizeEscape($_POST['tracker_location']),
        ];

        $response = $this->createCallrailTracker($data);
        if(!$response) $this->sendErrorMessage($page_url);

        $message = "Callrail tracker created successfully";
        $this->setFlashMessage($message,'success');

        wp_redirect($page_url);
    }

    public function createCallrailTracker(array $data){
        global $wpdb;
        return $wpdb->insert($wpdb->prefix."callrail", $data);
    }

    public function check_for_tracker_name(){
        global $wpdb;

        if(isset($_POST['tracker_name']) && !empty($_POST['tracker_name'])){
            $tracker_name = trim($_POST['tracker_name']);
            $tracker = $wpdb->get_var("
                select count(*) 
                from {$wpdb->prefix}callrail 
                where tracking_name = '$tracker_name'
            ");

            if($tracker){
                echo "false";
            }
            else{
                echo "true";
            }

        }
        else{
            echo "false";
        }
        wp_die();
    }

    public function deleteCallrailTracker(int $tracker_id){
        global $wpdb;
        return $wpdb->delete($wpdb->prefix."callrail", ['id' => $tracker_id]);
    }

    public function delete_callrail_tracker(){
        global $wpdb;

        $this->verify_nonce_field('delete_callrail_tracker');

        if(empty($_POST['tracker_id'])) $this->response('error');
            
        $tracker_id = $this->sanitizeEscape($_POST['tracker_id']);

        $response = $this->deleteCallrailTracker($tracker_id);
        if(!$response) $this->response('error');

        $this->response("success",'Tracker Deleted successfully');
    }

    public function updateCallrailTracker(int $tracker_id, array $data){
        global $wpdb;
        return $wpdb->update($wpdb->prefix."callrail", $data, ['id' => $tracker_id]);
    }

    public function update_callrail_tracker_location(){
        global $wpdb;

		$this->verify_nonce_field('update_callrail_tracker_location');

        $page_url = esc_url_raw($_POST['page_url']);
        
        $message = $type = "";

        if(
            empty($_POST['tracker_id']) ||
            empty($_POST['tracker_location'])
        ) $this->sendErrorMessage($page_url);

        $tracker_id = $this->sanitizeEscape($_POST['tracker_id']);
        $tracker_location = $this->sanitizeEscape($_POST['tracker_location']);        

        $update_data = ['actual_location' => $tracker_location];
        $response = $this->updateCallrailTracker($tracker_id, $update_data);
        if(!$response) $this->sendErrorMessage($page_url);

        $message = "Tracker location updated successfully";
        $this->setFlashMessage($message,$type);

        wp_redirect($page_url);
    }

    public function getCallrailIdByPhoneNo( string $phone_no){

        // get all callrail accounts info first
        $callrail_accounts = $this->getAllCallrailAccounts();

        if(!is_array($callrail_accounts) || count($callrail_accounts) <= 0) return null;

        foreach ($callrail_accounts as $callrail_account) {
            
            $api_key = $callrail_account->api_key;
            $account_no = $callrail_account->callrail_account_no;

            // check for phone no in all account, if found and id returned , then exit this loop with id 
            $callrail_id = $this->getSystemCallrailIdByPhoneNo($api_key, $account_no, $phone_no);

            // if id found then return response with id
            if($callrail_id) return $callrail_id;
        }

        return null;

    }

    public function get_lead_source_from_callrail(){
        global $wpdb;

        if(!isset($_POST['phone_no']) || empty($_POST['phone_no'])){
            $this->response('error', 'fields are not set');
        }

        $phone_no = esc_html($_POST['phone_no']);

        $callrail_id = $this->getCallrailIdByPhoneNo($phone_no);

        if(!is_null($callrail_id)) $this->response('success', '', [ 'callrail_id' => $callrail_id]);

        $this->response('error', 'no callrail id found');
    }

    public function getSystemCallrailIdByPhoneNo( string $token, string $callrail_account_no, string $phone_no ){

        global $wpdb;

        $authorization="Authorization: Token token=$token";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://api.callrail.com/v3/a/$callrail_account_no/calls.json?search=$phone_no&&fields=source_name");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close ($ch);

        try{

            $data = json_decode($server_output);
            if($data->total_records > 0){
                $source_name = $data->calls[0]->source_name;
                return $this->getTrackerIdByName($source_name);
            }
            else{
                return false;
            }
        }
        catch(Exception $e){
            return false;
        }  
    }

    public function getTrackerIdByName( string $tracker_name ): int{
        global $wpdb;
        $tracker_name = trim($tracker_name);
        $tracker_id = $wpdb->get_var("select id from {$wpdb->prefix}callrail where tracking_name='$tracker_name' ");
        return !empty($tracker_id) ? (int) $tracker_id : 0;
    }

    public function getAllCallrailAccounts(){
        global $wpdb;
        return $wpdb->get_results("select * from {$wpdb->prefix}callrail_accounts_info ");
    }

    public function get_all_tracking_no($location='',$other_than_callrail=true){
        global $wpdb;
        $where_location='';
        if(!empty($location)){
            $where_location="where actual_location='$location'";
        }

        $callrail_numbers=$wpdb->get_results("select * from {$wpdb->prefix}callrail $where_location order by 
        case 
          when tracking_name like '%ny%' then 0 
          when tracking_name like '%san fran%' then 1 
          when tracking_name like '%la%' then 2 
          when tracking_name like '%buffalo%' then 3 
          when tracking_name like '%rochester%' then 4 
          else 5 end, tracking_name");
          
        $lead_sources=[];
        if($other_than_callrail){
            $lead_sources=$wpdb->get_results("select tracking_phone_no,slug as id, name as tracking_name from {$wpdb->prefix}lead_source");
        }

        $data=array_merge($callrail_numbers,$lead_sources);
          
        return $data;
    }

    public function getCallrailDataByBranch( string $branch ){
        global $wpdb;
        $data = $wpdb->get_row("
            select CAI.api_key, CAI.callrail_account_no
            from {$wpdb->prefix}callrail_accounts_info CAI
            left join {$wpdb->prefix}branches L
            on CAI.id = L.callrail_id
            where L.slug='$branch'
        ");

        return $data;
    }

    public function getCallrailAccountDataById( int $account_id){
        global $wpdb;
        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}callrail_accounts_info 
            where id='$account_id'
        ");
    }

    public function get_calls_by_tracking_no($tracking_no, $branch,$start_date='',$end_date='',$date_type=''){
        global $wpdb;

        // get the api key and account no
        $callrail_api_data = $this->getCallrailDataByBranch($branch);

        if($callrail_api_data){
            $authorization="Authorization: Token token=$callrail_api_data->api_key";

            $url="https://api.callrail.com/v3/a/$callrail_api_data->callrail_account_no/calls.json";
    
            $params=[
                'search'    => $tracking_no,
                'call_type' =>  'first_call' 
            ];
    
            if(isset($_GET['from_date']) && isset($_GET['to_date'])){
                if(!empty($_GET['from_date']) && !empty($_GET['to_date'])){
                    $params['start_date']=date("Y-m-d",strtotime($_GET['from_date']));
                    $params['end_date']=date("Y-m-d",strtotime($_GET['to_date']));
                }
            }
            
            if(!empty($start_date) && !empty($end_date)){
                $params['start_date']=date("Y-m-d",strtotime($start_date));
                $params['end_date']=date("Y-m-d",strtotime($end_date));            
            }

            if(!empty($date_type)){
                $params['date_range']=$date_type;
            }
    
            $endpoint=$url."?".http_build_query($params);
            // echo $endpoint;wp_die();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$endpoint);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close ($ch);
    
            $res=json_decode($server_output);

            return $res->total_records;
        }
        else{
            return null;
        }

    }

    // this function return last week total amount generated by a tracking number
    public function weekly_amount_generated_by_tracking_number($tracking_id=''){

        global $wpdb;

        $week_start_date=date('Y-m-d',strtotime('last week tuesday'));
        $week_end_date=date('Y-m-d',strtotime('monday this week'));
        

        $total_amount=$wpdb->get_row("select SUM(total_amount) as total_amount from wp_invoices where date >='$week_start_date' && date <='$week_end_date' and callrail_id='$tracking_id' ");

        if(empty($total_amount->total_amount)){
            return "0";
        }else{
            return $total_amount->total_amount;
        }

        
    }

    // this function return tracking no options for ads spen calculator page by location 
    public function get_tracking_no_by_location(){

	    $this->verify_nonce_field('get_tracking_no_by_location');

        if(empty($_POST['location'])){
            echo "No location provided";
            wp_die();
        }

        $this->get_callrail_radio_form_by_location($_POST['location'],[]);
        wp_die();
    }

    public function get_callrail_radio_form_by_location($location,$selected_ids=[]){

        $tracking_nos=$this->get_all_tracking_no($location,false);

        if(is_array($tracking_nos) && count($tracking_nos)>0){
            ?>
                <h5><b>Select Tracking No.</b></h5>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" value="" id="select_all"> <b>Select All</b> 
                    </label>
                </div>
            <?php
            foreach($tracking_nos as $tracking_no){
                ?>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" class="tracking_no_checkboxes" name="tracking_id[]" value="<?= $tracking_no->id; ?>" <?= in_array($tracking_no->id,$selected_ids)  ? 'checked' : ''; ?>><?= $tracking_no->tracking_name; ?>
                    </label>
                </div>                    
                <?php
            }
        }
        else{
            ?>
                <p>No Callrail Number Found</p>                
            <?php
        }

    }

}

new Callrail_new();