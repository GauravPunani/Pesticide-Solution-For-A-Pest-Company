<?php

class Quote extends GamFunctions{

    use GamValidation;

	function __construct(){
		add_action( 'admin_post_nopriv_download_residential_quote_pdf', array($this,'download_residential_quote_pdf') );
		add_action( 'admin_post_download_residential_quote_pdf', array($this,'download_residential_quote_pdf') );

		add_action( 'admin_post_nopriv_download_commercial_quote_pdf', array($this,'download_commercial_quote_pdf') );
		add_action( 'admin_post_download_commercial_quote_pdf', array($this,'download_commercial_quote_pdf') );

		add_action( 'wp_ajax_nopriv_delete_quotesheet', array($this,'delete_quotesheet') );
		add_action( 'wp_ajax_delete_quotesheet', array($this,'delete_quotesheet') );

		add_action( 'wp_ajax_nopriv_udpate_quote_status', array($this,'udpate_quote_status') );
        add_action( 'wp_ajax_udpate_quote_status', array($this,'udpate_quote_status') );
        
        add_action( 'wp_ajax_residential_quotesheet', array($this,'residential_quotesheet'));
        add_action( 'wp_ajax_nopriv_residential_quotesheet', array($this,'residential_quotesheet'));

        add_action( 'admin_post_commercial_quotesheet', array($this,'commercial_quotesheet'));
        add_action( 'admin_post_nopriv_commercial_quotesheet', array($this,'commercial_quotesheet'));

        add_action( 'admin_post_residential_quote_update', array($this,'residential_quote_update'));
        add_action( 'admin_post_nopriv_residential_quote_update', array($this,'residential_quote_update'));

        add_action( 'admin_post_update_commercial_quote', array($this,'update_commercial_quote'));
        add_action( 'admin_post_nopriv_update_commercial_quote', array($this,'update_commercial_quote'));

        add_action( 'wp_ajax_quote_data_calculation', array($this,'quote_data_calculation'));
        add_action( 'wp_ajax_nopriv_quote_data_calculation', array($this,'quote_data_calculation'));

        add_action( 'wp_ajax_delete_commercial_quotesheet', array($this,'delete_commercial_quotesheet'));
        add_action( 'wp_ajax_nopriv_delete_commercial_quotesheet', array($this,'delete_commercial_quotesheet'));

        add_action( 'wp_ajax_sms_quote_link', array($this,'sms_quote_link'));
        add_action( 'wp_ajax_nopriv_sms_quote_link', array($this,'sms_quote_link'));

        add_action( 'wp_ajax_sms_commercial_quote_link', array($this,'sms_commercial_quote_link'));
        add_action( 'wp_ajax_nopriv_sms_commercial_quote_link', array($this,'sms_commercial_quote_link'));

        add_action( 'admin_post_add_update_office_notes', array($this,'add_update_office_notes'));
        add_action( 'admin_post_nopriv_add_update_office_notes', array($this,'add_update_office_notes'));

        add_action( 'wp_ajax_email_quote', array($this,'email_quote'));
        add_action( 'wp_ajax_nopriv_email_quote', array($this,'email_quote'));

        add_action( 'admin_post_quote_add_service_tags', array($this,'quote_add_service_tags'));
        add_action( 'admin_post_nopriv_quote_add_service_tags', array($this,'quote_add_service_tags'));

        add_action( 'admin_post_download_quote_by_technician', array($this,'download_quote_by_technician'));
        add_action( 'admin_post_nopriv_download_quote_by_technician', array($this,'download_quote_by_technician'));
    }

    public function visit_frequency(){
        return [
            'every_month' => 'Every month',
            'quarterly' => 'Quarterly',
            'weekly' => 'Weekly',
            'no_additional_visits_required' => 'No additional visits required',
            'custom' => 'Custom'
        ];
    }

    public function visit_duration(){
        return [
            'months' => 'Months',
            'weeks' => 'Weeks',
            'year' => 'Year'
        ];
    }

    public function download_quote_by_technician(){
        $this->verify_nonce_field('download_quote_by_technician');

        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['quote_id']) ||
            empty($_POST['quote_type'])
        ) $this->sendErrorMessage($page_url);

        $quote_id = $this->sanitizeEscape($_POST['quote_id']);
        $quote_type = $this->sanitizeEscape($_POST['quote_type']);
        $technician_id = (new Technician_details)->get_technician_id();

        if($quote_type == "residential"){

            // if quote belongs to technician, then only continue 
            $quote = $this->getResidentialQuoteById($quote_id, ['technician_id']);
            if($quote->technician_id != $technician_id){
                $this->sendErrorMessage($page_url);
            }

            // download quote
            $this->downloadResidentialQuote($quote_id);
        }
        elseif($quote_type == "commercial"){

            // if quote belongs to technician, then only continue 
            $quote = $this->getCommercialQuoteById($quote_id, ['technician_id']);
            if($quote->technician_id != $technician_id){
                $this->sendErrorMessage($page_url);
            }

            // download quote
            $this->downloadCommercialQuote($quote_id);
        }
        else{
            $this->sendErrorMessage($page_url);
        }
    }

    public function quote_add_service_tags(){
        global $wpdb;

        $page_url = esc_url_raw($_POST['page_url']);
        $this->verify_nonce_field('quote_add_service_tags');

        if(empty($_POST['service_id'])) $this->sendErrorMessage($page_url);
        if(!GamFunctions::isArrayExistWithValues('tags')) $this->sendErrorMessage($page_url, "Please select atleast one tag");

        $service_id = $_POST['service_id'];
        $tags = $_POST['tags'];

        list($services, $message) = DB::table('quotesheet_services')->getById($service_id, ['tags']);
        if(!empty($services->tags)){
            $system_tags = explode(',', $services->tags);
            foreach($tags as $key => $tag){
                if(in_array($tag, $system_tags)) unset($tags[$key]);
            }
        }

        $update_data = ['tags' => implode(',', $tags)];

        list($response, $message) = DB::table('quotesheet_services')->update($service_id, $update_data);
        if(!$response) $this->sendErrorMessage($page_url, $message);

        $message = "Tags added successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function email_quote(){
        global $wpdb;

        $required_fields = ['quote_type','quote_id','email'];

        list($response, $message) = $this->requiredValidation($required_fields, $_POST);
        if(!$response) $this->response('error', $message);

        $quote_type = $this->sanitizeEscape($_POST['quote_type']);
        $quote_id = $this->sanitizeEscape($_POST['quote_id']);
        $email = $this->sanitizeEscape($_POST['email']);

        if($quote_type == 'residential'){
            $response = (new Emails)->emailResidentialQuote($quote_id, $email);
            if(!$response) $this->response('error');
        }
        elseif($quote_type == 'commercial'){
            $response = (new Emails)->emailCommercialQuote($quote_id, $email);
            if(!$response) $this->response('error');
        }
        else{
            $this->response('error', 'Provided quote type is not valid');
        }

        $this->response('success', 'Quote email sent successfully');
    }

    public function add_update_office_notes(){
        global $wpdb;

        $this->verify_nonce_field('add_update_office_notes');

        $page_url = esc_url_raw($_POST['page_url']);

        $required_fields = [
            'quote_id',
            'office_notes',
            'quote_type'
        ];

        foreach($required_fields as $field){
            if(empty($_POST[$field])) $this->sendErrorMessage($page_url. $field." is required");
        }

        $quote_id = $this->sanitizeEscape($_POST['quote_id']);
        $office_notes = $this->sanitizeEscape($_POST['office_notes'], 'textarea');
        $quote_type = $this->sanitizeEscape($_POST['quote_type']);

        $update_data = ['office_notes' => $office_notes];

        if($quote_type == 'residential'){
            $response = $this->updateResidentialQuote($quote_id, $update_data);
        }
        elseif($quote_type == 'commercial'){
            $response = $this->updateCommercialQuote($quote_id, $update_data);
        }
        else{
            $this->sendErrorMessage($page_url, "Quote type is invalid");
        }

        $message = "Office notes updated successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function sms_commercial_quote_link(){

		$this->verify_nonce_field('sms_commercial_quote_link');

		if(empty($_POST['phone_no'])) $this->response('error');
		if(empty($_POST['quote_id'])) $this->response('error');

		$quote_id = sanitize_text_field($_POST['quote_id']);
		$phone_no = sanitize_text_field($_POST['phone_no']);

		$response = (new Twilio)->sendCommercialQuoteLink($quote_id, $phone_no);
		if(!$response) $this->response('error');

		$this->response('succes', 'Quote link messaged to client phone number successfully');        
    }

    public function sms_quote_link(){

		$this->verify_nonce_field('sms_quote_link');

		if(empty($_POST['phone_no'])) $this->response('error');
		if(empty($_POST['quote_id'])) $this->response('error');

		$quote_id = sanitize_text_field($_POST['quote_id']);
		$phone_no = sanitize_text_field($_POST['phone_no']);

		$response = (new Twilio)->sendResidentialQuoteLink($quote_id, $phone_no);
		if(!$response) $this->response('error');

		$this->response('succes', 'Quote link messaged to client phone number successfully');        
    }

    public function download_residential_quote_pdf(){
        global $wpdb;

        $this->verify_nonce_field('download_residential_quote_pdf');

        $page_url = esc_url_raw($_POST['quote_id']);
        if(empty($_POST['quote_id'])) $this->sendErrorMessage($page_url);

        $quote_id = $this->sanitizeEscape($_POST['quote_id']);

        $this->downloadResidentialQuote($quote_id);
    }

    public function downloadResidentialQuote(int $quote_id){

        $quote_template = $this->residentialQuotePDFContent($quote_id);
        
        // load mpdf php sdk from vendor
        self::loadVendor();

        $mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
        $mpdf->WriteHTML($quote_template);
        $mpdf->Output('Residential Quotesheet.pdf',"D");
    }

    public function downloadCommercialQuote(int $quote_id){

        $quote_template = $this->commercialQuotesheetPDFContent($quote_id);
        
        // load mpdf php sdk from vendor
        self::loadVendor();

        $mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
        $mpdf->WriteHTML($quote_template);
        $mpdf->Output('Commercial Quotesheet.pdf',"D");
    }

    public function download_commercial_quote_pdf(){
        global $wpdb;

        $this->verify_nonce_field('download_commercial_quote_pdf');

        $page_url = esc_url_raw($_POST['quote_id']);
        if(empty($_POST['quote_id'])) $this->sendErrorMessage($page_url);

        $quote_id = $this->sanitizeEscape($_POST['quote_id']);

        $this->downloadCommercialQuote($quote_id);
    }

    public function quote_data_calculation(){
        global $wpdb;

        $this->verify_nonce_field('quote_data_calculation');

        $residential_conditions=[];
        $commercial_conditions=[];

        if(isset($_POST['branch_ids']) && count($_POST['branch_ids'])){

            $branches="'".implode("','", $_POST['branch_ids'])."'";

            if($_POST['quote_type']=="residential" || $_POST['quote_type']=="both"){
                $residential_conditions[]=" branch_id IN ($branches)";
            }
            if($_POST['quote_type']=="commercial" || $_POST['quote_type']=="both"){
                $commercial_conditions[]=" branch_id IN ($branches)";
            }

        }

        if(isset($_POST['technician_ids']) && count($_POST['technician_ids'])){
            $technicians="'".implode("','", $_POST['technician_ids'])."'";
            if($_POST['quote_type']=="residential" || $_POST['quote_type']=="both"){
                $residential_conditions[]=" technician_id IN ($technicians)";
            }
            if($_POST['quote_type']=="commercial" || $_POST['quote_type']=="both"){
                $commercial_conditions[]=" technician_id IN ($technicians)";
            }
        }

        if(isset($_POST['lead_sources']) && count($_POST['lead_sources'])){
            $lead_sources="'".implode("','", $_POST['lead_sources'])."'";
            if($_POST['quote_type']=="residential" || $_POST['quote_type']=="both"){
                $residential_conditions[]=" callrail_id IN ($lead_sources)";
            }
            if($_POST['quote_type']=="commercial" || $_POST['quote_type']=="both"){
                $commercial_conditions[]=" callrail_id IN ($lead_sources)";
            }
        }
        
        if(!empty($_POST['from_date'])){
            if($_POST['quote_type']=="residential" || $_POST['quote_type']=="both"){
                $residential_conditions[]=" DATE(date) >= '{$_POST['from_date']}'";
            }
            if($_POST['quote_type']=="commercial" || $_POST['quote_type']=="both"){
                $commercial_conditions[]=" DATE(date) >= '{$_POST['from_date']}'";
            }
        }
        
        if(!empty($_POST['to_date'])){
            if($_POST['quote_type']=="residential" || $_POST['quote_type']=="both"){
                $residential_conditions[]=" DATE(date) <= '{$_POST['to_date']}'";
            }
            if($_POST['quote_type']=="commercial" || $_POST['quote_type']=="both"){
                $commercial_conditions[]=" DATE(date) <= '{$_POST['to_date']}'";
            }
        }

        if(count($residential_conditions)>0){
            $residential_conditions=$this->generate_query($residential_conditions);
        }
        else{
            $residential_conditions="";
        }

        if(count($commercial_conditions)>0){
            $commercial_conditions = $this->generate_query($commercial_conditions);
        }
        else{
            $commercial_conditions="";
        }

        $calculation_html="<table class='table table-striped'>";
        if($_POST['quote_type']=="residential" || $_POST['quote_type']=="both"){

            $residential_data=$wpdb->get_row("
                select count(*) as total_quotes, sum(total_cost) as total_cost 
                from {$wpdb->prefix}quotesheet 
                $residential_conditions
            ");

            $calculation_html.="
                <tr>
                    <th>Total Resdiential Quotes</th>
                    <td>$residential_data->total_quotes</td>
                </tr>
                <tr>
                    <th>Total Amount</th>
                    <td>$residential_data->total_cost</td>
                </tr>
            ";
        }

        if($_POST['quote_type']=="commercial" || $_POST['quote_type']=="both"){

            $commercial_data=$wpdb->get_row("select count(*) as total_quotes, sum(cost_per_visit) as total_cost from {$wpdb->prefix}commercial_quotesheet $residential_conditions");

            $calculation_html.="
                <tr>
                    <th>Total Commercial Quotes</th>
                    <td>$commercial_data->total_quotes</td>
                </tr>
                <tr>
                    <th>Total Amount</th>
                    <td>$commercial_data->total_cost</td>
                </tr>
            ";
        }

        $calculation_html.="</tbody>
                            </table>";

        
        echo $calculation_html;
        wp_die();
    }

    public function updateCommercialQuote(int $quote_id, array $quote_data){
        global $wpdb;
        $quote_data['updated_at'] = date('Y-m-d h:i:s');
        $response =  $wpdb->update($wpdb->prefix."commercial_quotesheet", $quote_data, ['id' => $quote_id]);
        return $response === false ? false : true;
    }

    public function update_commercial_quote(){
        global $wpdb;

        $this->verify_nonce_field('update_commercial_quote');

        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['quote_id']) ||
            empty($_POST['client_name']) ||
            empty($_POST['decision_maker_name']) ||
            empty($_POST['client_phone']) ||
            empty($_POST['visits_duration_recurring']) ||
            empty($_POST['initial_cost']) ||
            empty($_POST['notes_for_client']) ||
            ($_POST['cost_per_visit'] == '') ||
            empty($_POST['client_address']) ||
            empty($_POST['callrail_id'])         
        ) $this->sendErrorMessage($page_url,'One or more required fields are empty');
        
        if(isset($_POST['visits_duration_recurring']) && $_POST['visits_duration_recurring'] == 'custom'){
            if(
                empty($_POST['no_of_times']) ||
                empty($_POST['visits_duration_fixed'])
            ) $this->sendErrorMessage($page_url);

            // no if times
            $no_of_times = $this->sanitizeEscape($_POST['no_of_times']);
            $visits_duration = $this->sanitizeEscape($_POST['visits_duration_fixed']);
        }else{
            $no_of_times = null;
            $visits_duration = $this->sanitizeEscape($_POST['visits_duration_recurring']);
        }

        $quote_id = $this->sanitizeEscape($_POST['quote_id']);

        $data = [
            'client_name'			=>	$this->sanitizeEscape($_POST['client_name']),
            'tech_diff_name'        =>  $this->sanitizeEscape($_POST['tech_diff_name']),
            'decision_maker_name'	=>	$this->sanitizeEscape($_POST['decision_maker_name']),
            'client_phone'			=>	$this->sanitizeEscape($_POST['client_phone']),
            'no_of_times'			=>	$no_of_times,
            'visits_duration'       =>  $visits_duration,
            'initial_cost'			=>	$this->sanitizeEscape($_POST['initial_cost']),
            'cost_per_visit'		=>	$this->sanitizeEscape($_POST['cost_per_visit']),
            'client_address'		=>	$this->sanitizeEscape($_POST['client_address']),
            'notes_for_client'      =>  $this->sanitizeEscape($_POST['notes_for_client'], 'textarea'),
            'callrail_id'   		=>	$this->sanitizeEscape($_POST['callrail_id']),
        ];

        if(!empty($_POST['clientEmail'])) $data['clientEmail'] = sanitize_email($_POST['clientEmail']);
    
        $response = $this->updateCommercialQuote($quote_id, $data);

        if(!$response) $this->sendErrorMessage($page_url);

        $message = "Commercial quote updated successfully";
        $this->setFlashMessage($message, 'success');

        // unset the session for edit as well 
        if(isset($_SESSION['commercial_quote_editable'])) unset($_SESSION['commercial_quote_editable']);

        wp_redirect($page_url);
    }
    
    public function residential_quote_update(){
        global $wpdb;

		$this->verify_nonce_field('residential_quote_update');

        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['clientName']) ||
            empty($_POST['clientAddress']) ||
            empty($_POST['clientPhn']) ||
            !isset($_POST['total_cost']) || !is_numeric($_POST['total_cost']) ||
            empty($_POST['notes_for_client']) ||
            empty($_POST['callrail_id']) ||
            empty($_POST['quote_id'])
        ) $this->sendErrorMessage($page_url, "Please fill all the required fields");

        $quote_id = $this->sanitizeEscape($_POST['quote_id']);

        $data=[
            'clientName'			=>	$_POST['clientName'],
            'tech_diff_name'        =>  $_POST['tech_diff_name'],
            'clientAddress'			=>	$_POST['clientAddress'],
            'clientPhn'				=>	$_POST['clientPhn'],
            'total_cost'			=>	$_POST['total_cost'],
            'notes_for_client'		=>	$_POST['notes_for_client'],
            'callrail_id'   		=>	$_POST['callrail_id']
        ];

        if(!empty($_POST['clientEmail'])) $data['clientEmail'] = sanitize_email($_POST['clientEmail']);

        if(!empty($_POST['discount_with_plan'])) $data['discount_with_plan'] = $_POST['discount_with_plan'];
        if(!empty($_POST['maintenance_price'])) $data['maintenance_price'] = $_POST['maintenance_price'];        

        if(isset($_POST['service']) && count($_POST['service']) >0){
            $temp_service = [];
            foreach($_POST['service'] as $service){
                $temp_service[] = $service;
            }
            $data['service'] = json_encode($temp_service);
        }
        else{
            $data['service'] = null;
        }

        if(isset($_POST['items']) && count($_POST['items']) >0){
            $temp_items = [];
            foreach($_POST['items'] as $items){
                $temp_items[] = $items;
            }
            $data['items'] = json_encode($temp_items);
        }
        else{
            $data['items'] = null;
        }

        $response = $this->updateResidentialQuote($quote_id, $data);
        if(!$response) $this->sendErrorMessage($page_url);

        $message="Quotesheet updated successfully";
        $this->setFlashMessage($message,'success');

        // unset the session so that edit page don't open again 
        if(isset($_SESSION['residential_quote_editable'])) unset($_SESSION['residential_quote_editable']);

        wp_redirect($_POST['page_url']);
    }

    public function delete_commercial_quotesheet(){
        global $wpdb;

        $this->verify_nonce_field('delete_commercial_quotesheet');

        if(empty($_POST['quote_id'])) $this->response('error');

        $quote_id = sanitize_text_field($_POST['quote_id']);

        if(!$this->deleteCommercialQuote($quote_id)) $this->response('error');
        $this->response('success');
    }

    public function deleteCommercialQuote(int $quote_id){
        global $wpdb;
        return $wpdb->delete($wpdb->prefix."commercial_quotesheet", ['id' => $quote_id]);
    }

    public function deleteResidentialQuote(int $quote_id){
        global $wpdb;
        return $wpdb->delete($wpdb->prefix."quotesheet", ['id' => $quote_id]);
    }

    public function delete_quotesheet(){
        global $wpdb;

        $this->verify_nonce_field('delete_quotesheet');

        if(empty($_POST['quote_id'])) $this->response('error');

        $quote_id = sanitize_text_field($_POST['quote_id']);

        if(!$this->deleteResidentialQuote($quote_id)) $this->response('error');
        $this->response('success');
    }

    public function updateCommercialQuoteStatus(array $data){
        global $wpdb;

        $required_fields = [
            'source',
            'source_id',
            'quote_id',
            'quote_status'
        ];

        list($response, $message) = $this->requiredValidation($required_fields, $data);
        if(!$response) return [false, $message];

        $quote = $this->getCommercialQuoteById($data['quote_id']);
        $lead_data = [
            'email'     =>  $quote->clientEmail,
            'address'   =>  $quote->client_address,
            'phone'     =>  $quote->client_phone,
        ];        

        list($lead_id, $message) = (new Leads)->isLeadExist($lead_data);

        if($lead_id){

            $lead_update_data = [
                'lead_id'       =>  $lead_id,
                'status'        =>  $data['quote_status'],  
                'source'        =>  $data['source'],
                'source_id'     =>  $data['source_id'],
            ];

            list($response, $message) = (new Leads)->updateLeadStatus($lead_update_data);
            if(!$response) return [false, $message];

        }

        $quote_update_data = ['lead_status'  =>  $data['quote_status']];

        $response = $this->updateCommercialQuote($data['quote_id'], $quote_update_data);
        if(!$response) return [false, $wpdb->last_error];

        return [true, null];
    }

    public function updateResidentialQuoteStatus(array $data){
        global $wpdb;

        $required_fields = [
            'source',
            'source_id',
            'quote_id',
            'quote_status'
        ];

        list($response, $message) = $this->requiredValidation($required_fields, $data);
        if(!$response) return [false, $message];

        $quote = $this->getResidentialQuoteById($data['quote_id']);
        $lead_data = [
            'email'     =>  $quote->clientEmail,
            'address'   =>  $quote->clientAddress,
            'phone'     =>  $quote->clientPhn,
        ];

        list($lead_id, $message) = (new Leads)->isLeadExist($lead_data);

        if($lead_id){

            $lead_update_data = [
                'lead_id'       =>  $lead_id,  
                'status'        =>  $data['quote_status'],  
                'source'        =>  $data['source'],
                'source_id'     =>  $data['source_id'],
            ];

            list($response, $message) = (new Leads)->updateLeadStatus($lead_update_data);
            if(!$response) return [false, $message];

        }

        $quote_update_data = ['lead_status'  =>  $data['quote_status']];

        $response = $this->updateResidentialQuote($data['quote_id'], $quote_update_data);
        if(!$response) return [false, $wpdb->last_error];

        return [true, null];
    }

	public function udpate_quote_status(){
        global $wpdb;

	    $this->verify_nonce_field('udpate_quote_status');

        if(
            empty($_POST['quote_id']) ||
            empty($_POST['quote_type']) ||
            empty($_POST['quote_status'])
        ) $this->response('error', 'Please fill all required fields');

        $quote_id = $this->sanitizeEscape($_POST['quote_id']);
        $quote_type = $this->sanitizeEscape($_POST['quote_type']);
        $quote_status = $this->sanitizeEscape($_POST['quote_status']);
        
        $data = [
            'quote_id'      =>  $quote_id, 
            'quote_type'    =>  $quote_type, 
            'quote_status'  =>  $quote_status, 
        ];

        $data['source_id'] = $data['quote_id'];
        $data['source'] = $quote_type == 'residential' ? 'residential_quote' : 'commercial_quote';

        if($quote_type == "residential"){
            list($response, $message) = $this->updateResidentialQuoteStatus($data);
        }
        elseif($quote_type == "commercial"){
            list($response, $message) = $this->updateCommercialQuoteStatus($data);
        }
        else{
            $this->response('error');
        }

        if(!$response) $this->response("error", $message);

        $this->response('success', 'Quote status updated successfully');

	}

    public function commercial_quotesheet(){
        global $wpdb;

        $this->verify_nonce_field('commercial_quotesheet');

        $page_url = esc_url_raw($_POST['page_url']);

        $required_fields = [
            'licenses',
            'client_name',
            'decision_maker_name',
            'event_date',
            'technician_appointment',
            'client_address',
            'notes_for_client',
            'office_notes',
            'visits_duration_recurring'
        ];

        if(isset($_POST['visits_duration_fixed']) && !empty($_POST['visits_duration_fixed'])) array_push($required_fields,'visits_duration_fixed');

        foreach($required_fields as $field){
            if(empty($_POST[$field])) $this->sendErrorMessage($page_url, $field." is required");
        }

        $required_numeric_fields = [
            'initial_cost',
            'cost_per_visit',            
        ];

        if(isset($_POST['no_of_times']) && !empty($_POST['no_of_times'])) array_push($required_numeric_fields,'no_of_times');

        foreach($required_numeric_fields as $field){
            if(!isset($_POST[$field]) || !is_numeric($_POST[$field])) $this->sendErrorMessage($page_url, $field." is required");
        }

        $licenses = $this->sanitizeEscape($_POST['licenses']);
        $client_name = $this->sanitizeEscape($_POST['client_name']);
        $techDiffName = $this->sanitizeEscape($_POST['tech_diff_name']);
        $decision_maker_name = $this->sanitizeEscape($_POST['decision_maker_name']);
        $no_of_times = $this->sanitizeEscape($_POST['no_of_times']);
        $initial_cost = $this->sanitizeEscape($_POST['initial_cost']);
        $cost_per_visit = $this->sanitizeEscape($_POST['cost_per_visit']);
        $event_date = $this->sanitizeEscape($_POST['event_date']);
        $calendar_event_id = $this->sanitizeEscape($_POST['technician_appointment']);
        $client_address = $this->sanitizeEscape($_POST['client_address']);
        $notes_for_client = $this->sanitizeEscape($_POST['notes_for_client'], 'textarea');
        $office_notes = $this->sanitizeEscape($_POST['office_notes'], 'textarea');
        $visits_duration_recurring = $this->sanitizeEscape($_POST['visits_duration_recurring']);
        $visits_duration_fixed = $this->sanitizeEscape($_POST['visits_duration_fixed']);

        $callrail_id = "";
        if(!empty($_POST['callrail_id'])) $callrail_id = $this->sanitizeEscape($_POST['callrail_id']);

        // no if times
        if($visits_duration_recurring == 'custom'){
            $no_of_times = $no_of_times;
            $visits_duration = $visits_duration_fixed;
        }else{
            $no_of_times = null;
            $visits_duration = $visits_duration_recurring;
        }

        $this->beginTransaction();

        $technician_id = (new Technician_details)->get_technician_id();
        $technician_name = (new Technician_details)->get_technician_name();
        $branch_id = (new Technician_details)->getTechnicianBranchId($technician_id);
        $branch_slug = (new Branches)->getBranchSlug($branch_id);

        // check table for quote no exist or not
        $sql_query = ['tbl' => 'commercial_quotesheet', 'col' => 'quote_no'];

        $data=[
            'quote_no'              => $this->generateGamUniqueNumber($sql_query),
            'license'               =>  $licenses,
            'client_name'			=>	$client_name,
            'tech_diff_name'        =>  $techDiffName,
            'decision_maker_name'	=>	$decision_maker_name,
            'no_of_times'			=>	$no_of_times,
            'visits_duration'       =>  $visits_duration,
            'initial_cost'			=>	$initial_cost,
            'cost_per_visit'		=>	$cost_per_visit,
            'date'					=>	$event_date,
            'calendar_event_Id'		=>	$calendar_event_id,
            'client_address'		=>	$client_address,
            'callrail_id'   		=>	$callrail_id,
            'date_created'          =>  date('Y-m-d h:i:s'),
            'technician_id'         =>  $technician_id,
            'notes_for_client'      =>  $notes_for_client,
            'tech_notes_for_office' =>  $office_notes,
            'branch_id'             =>  $branch_id,
            'lead_status'           =>  'pending'
        ];

        if(!empty($_POST['clientEmail'])) $data['clientEmail'] = sanitize_email($_POST['clientEmail']);

        if(!empty($_POST['client_phone'])) $data['client_phone'] = $this->sanitizeEscape($_POST['client_phone']);

        if(is_array($_POST['additional_materials']) && count($_POST['additional_materials'])>0){
            $data['additonal_material']=json_encode($_POST['additional_materials']);
        }

        list($quote_id, $message) = $this->createCommercialQuoteSheet($data);
        if(!$quote_id) $this->rollBackTransaction($page_url, $message);

        // updload notes on calendar event as well
        $response = (new Calendar)->uploadNotesInEvent($technician_id, $calendar_event_id, $office_notes, $event_date);
        if(!$response) $this->rollBackTransaction($page_url, "Unable to upload office notes in calendar event");

        if(!empty($_POST['clientEmail'])){

            // email quote 
            $response = (new Emails)->emailCommercialQuote($quote_id);

            // update email send status
            $update_data = ['email_status'  =>  $response ? 1 : 0];
            $response = $this->updateCommercialQuote($quote_id, $update_data);
            if($response === false) $this->rollBackTransaction($page_url);            
        }

		// save new email in email database 
        $email_data=[
            'branch_id'	=>	$branch_id,
            'name'		=>	$client_name,
            'address'	=>	$client_address,
            'phone'		=>	$client_phone,
            'date'		=>	date('Y-m-d'),
        ];

        if(!empty($_POST['clientEmail'])) $email_data['email'] = sanitize_email($_POST['clientEmail']);

        $email_data['status'] = $callrail_id == "reoccuring_customer" ? "reocurring" : "non_reocurring";

        $response = (new Emails)->save_email($email_data);
        if(!$response) $this->rollBackTransaction($response);
        
        $this->commitTransaction();

        $message="Commercial Quotesheet has been submited successfully";
        $this->setFlashMessage($message,'success');        

        wp_redirect($_POST['page_url']);
    }

    public function createCommercialQuoteSheet(array $data){
        global $wpdb;

        $response = $wpdb->insert($wpdb->prefix."commercial_quotesheet", $data);
        if(!$response) return [false, $wpdb->last_error];

        $quote_id = $wpdb->insert_id;

        $lead_data =  [
            'address'   =>  $data['client_address'],
            'phone'     =>  $data['client_phone']
        ];
        
        if(!empty($data['clientEmail'])) $lead_data['email'] = $data['clientEmail'];

        list($lead_id, $message) = (new Leads)->isLeadExist($lead_data);

        if($lead_id){
            $lead_update_data = [
                'lead_id'       =>  $lead_id,
                'status'        =>  'pending',
                'source'        =>  'commercial_quote',
                'source_id'     =>  $quote_id,
            ];

            list($response, $message) = (new Leads)->updateLeadStatus($lead_update_data);
            if(!$response) return [false, $message];
        }

        return [$quote_id, null];
    }
    
    public function residential_quotesheet(){
        global $wpdb;

        $this->verify_nonce_field('residential_quotesheet');

        $required_fields = [
            'licenses',
            'clientName',
            'clientAddress',
            'clientPhn',            
            'notes_for_client',
            'office_notes',
            'event_date',
            'technician_appointment',
            'maintenance_plan_offered',            
        ];

        foreach($required_fields as $field){
            if(empty($_POST[$field])) $this->response('error', $field. "is required");
        }

        if(!isset($_POST['total_cost']) || !is_numeric($_POST['total_cost']))
            $this->response('error', "Total cost is required fields");

        $licenses = $this->sanitizeEscape($_POST['licenses']);
        $clientName = $this->sanitizeEscape($_POST['clientName']);
        $techDiffName = $this->sanitizeEscape($_POST['tech_diff_name']);
        $clientAddress = $this->sanitizeEscape($_POST['clientAddress']);
        $clientPhn = $this->sanitizeEscape($_POST['clientPhn']);
        $total_cost = $this->sanitizeEscape($_POST['total_cost']);
        $notes_for_client = $this->sanitizeEscape($_POST['notes_for_client'], 'textarea');
        $office_notes = $this->sanitizeEscape($_POST['office_notes'], 'textarea');
        $event_date = $this->sanitizeEscape($_POST['event_date']);
        $calendar_event_id = $this->sanitizeEscape($_POST['technician_appointment']);
        $maintenance_plan_offered = $this->sanitizeEscape($_POST['maintenance_plan_offered']);

        $services = (isset($_POST['service']) && is_array($_POST['service'])) ?  $_POST['service'] : [];
        $items = (isset($_POST['items']) && is_array($_POST['items'])) ?  $_POST['items'] : [];
        
        $callrail_id = "";

        if(!empty($_POST['callrail_id'])) $callrail_id = $this->sanitizeEscape($_POST['callrail_id']);

        $this->beginTransaction();

        $technician_id = (new Technician_details)->get_technician_id();
        $technician_name = (new Technician_details)->get_technician_name();
        $branch_id = (new Technician_details)->getTechnicianBranchId($technician_id);
        $branch_slug = (new Branches)->getBranchSlug($branch_id);

		// save new email in email database 
        $email_data=[
            'branch_id'	=>	$branch_id,
            'name'		=>	$clientName,
            'address'	=>	$clientAddress,
            'phone'		=>	$clientPhn,
            'date'		=>	date('Y-m-d'),
        ];

        if(!empty($_POST['clientEmail'])) $email_data['email'] = sanitize_email($_POST['clientEmail']);

        $email_data['status'] = $callrail_id == "reoccuring_customer" ? "reocurring" : "non_reocurring";
        $response = (new Emails)->save_email($email_data);
        if(!$response) $this->rollbackResponse('error', "Unable to create email record in system");

        // check table for quote no exist or not
        $sql_query = ['tbl' => 'quotesheet', 'col' => 'quote_no'];

        $data=[
            'quote_no'  => $this->generateGamUniqueNumber($sql_query),
            'license'			    =>	$licenses,
            'clientName'            =>  $clientName,
            'tech_diff_name'        =>  $techDiffName,
            'clientAddress'			=>	$clientAddress,
            'clientPhn'				=>	$clientPhn,
            'service'				=>	json_encode($services),
            'items'					=>	json_encode($items),
            'total_cost'			=>	$total_cost,
            'notes_for_client'	    =>	$notes_for_client,
            'tech_notes_for_office' =>	$office_notes,
            'date'					=>	$event_date,
            'calendar_event_id'		=>	$calendar_event_id,
            'technician_id'         =>  $technician_id,            
            'date_created'          =>  date('Y-m-d h:i:s'),
            'branch_id'             =>  $branch_id,
            'callrail_id'           =>  $callrail_id,
            'lead_status'           =>  'pending'
        ];

        if(!empty($_POST['clientEmail'])) $data['clientEmail'] = sanitize_email($_POST['clientEmail']);

        if($maintenance_plan_offered == "yes"){

            if(
                empty($_POST['discount_with_plan']) ||
                empty($_POST['maintenance_price'])
            )   $this->rollbackResponse('error', "Discount with plan and maintenance price are required if maintenance plan is offered");

            $discount_with_plan = $this->sanitizeEscape($_POST['discount_with_plan']);
            $maintenance_price = $this->sanitizeEscape($_POST['maintenance_price']);            

            $data['discount_with_plan'] = $discount_with_plan;
            $data['maintenance_price'] = $maintenance_price;
        }
        
        list($quote_id, $message) = $this->createResidentialQuoteSheet($data);
        if(!$quote_id) $this->rollbackResponse('error', $message);

        // upload notes in calendar event as well
        $response = (new Calendar)->uploadNotesInEvent($technician_id, $calendar_event_id, $office_notes, $event_date);
        if(!$response) $this->rollbackResponse('error', "Unable to upload office notes in calendar event");

        // send quote email to client, if email provided
        if(!empty($_POST['clientEmail'])){

            // send quote email
            $response = (new Emails)->emailResidentialQuote($quote_id);
            
            // update email sent status
            $update_data = ['email_status'  =>  $response ? 1 : 0];
            $response = $this->updateResidentialQuote($quote_id, $update_data);
            if($response === false) $this->rollbackResponse('error', "Unable to update email status");        
        }

        $this->commitTransaction();

        $this->response('success', 'Residential Quotesheet has been submited successfully');
    }

    public function createResidentialQuoteSheet(array $data){
        global $wpdb;

        $response = $wpdb->insert($wpdb->prefix."quotesheet",$data);
        if(!$response) return [false, $wpdb->last_error];

        $quote_id = $wpdb->insert_id;

        $lead_data =  [
            'address'   =>  $data['clientAddress'],
            'phone'     =>  $data['clientPhn']
        ];
        
        if(!empty($data['clientEmail'])) $lead_data['email'] = $data['clientEmail'];

        list($lead_id, $message) = (new Leads)->isLeadExist($lead_data);

        if($lead_id){
            $lead_update_data = [
                'lead_id'       =>  $lead_id,
                'status'        =>  'pending',
                'source'        =>  'residential_quote',
                'source_id'     =>  $quote_id,
            ];

            list($response, $message) = (new Leads)->updateLeadStatus($lead_update_data);
            if(!$response) return [false, $message];
        }

        return [$quote_id, null];
    }

    public function quotesheetServices(){
        global $wpdb;
        return $wpdb->get_results("
            select * 
            from {$wpdb->prefix}quotesheet_services 
            order by name
        ");
    }

    public function getTechnicianResidentialQuotesByWeek( int $employee_id, string $week, array $columns = []){

        global $wpdb;

        $technician_id = (new Employee\Employee)->getReferenceIdByEmployeeId($employee_id);
        if(!$technician_id) throw new Exception('Employee ref id not found');

        list($week_start, $week_end) = $this->weekRange($week);

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}quotesheet
            where DATE(date) >= '$week_start'
			and DATE(date) <= '$week_end'
			and technician_id = '$technician_id'            
        ");
    }

    public function getTechnicianCommercialQuotesByWeek( int $employee_id, string $week, array $columns = []){
        global $wpdb;

        $technician_id = (new Employee\Employee)->getReferenceIdByEmployeeId($employee_id);
        if(!$technician_id) throw new Exception('Employee ref id not found');
        
        list($week_start, $week_end) = $this->weekRange($week);

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}commercial_quotesheet
            where DATE(date) >= '$week_start'
			and DATE(date) <= '$week_end'
			and technician_id = '$technician_id'            
        ");
    }

    public function getCommercialQuoteById(int $quote_id, array $columns = []){
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns
            from {$wpdb->prefix}commercial_quotesheet 
            where id='$quote_id'
        ");
    }

    public function getResidentialQuoteById(int $quote_id, array $columns = []){
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns
            from {$wpdb->prefix}quotesheet 
            where id='$quote_id'
        ");
    }

    public function updateResidentialQuote(int $quote_id, array $data){
        global $wpdb;

        $response = $wpdb->update($wpdb->prefix."quotesheet", $data, ['id' => $quote_id]);
        return $response ===  false ? false : true;
    }

    public function getResidentialQuotes(array $conditions = [], array $columns = [], bool $single = false){
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

        $method = $single ? 'get_row' : 'get_results';

        return $wpdb->$method("
            select $columns
            from {$wpdb->prefix}quotesheet
            $conditions
            order by created_at desc
        ");
    }

    public function getCommercialQuotes(array $conditions = [], array $columns = [], bool $single = false){
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

        $method = $single ? 'get_row' : 'get_results';
        
        return $wpdb->$method("
            select $columns
            from {$wpdb->prefix}commercial_quotesheet
            $conditions
            order by created_at desc

        ");
    }

    public function residenialQuoteBody($quote_data){

        $technician_name = (!empty($quote_data->tech_diff_name)) ? $quote_data->tech_diff_name : (new Technician_details)->getTechnicianName($quote_data->technician_id);
        $tech_branch_id = (new Technician_details)->getTechnicianBranchId($quote_data->technician_id);

        $services=json_decode($quote_data->service);
        $items=json_decode($quote_data->items);

        $body = "
            <div class='clearfix'>
                <div id='company' style='float:right; width:40%' class='clearfix'>
                    <div style='float:right;width:100%;' class='rt_text'>".(new GamFunctions)->generateLicenseNoBasedOnBranch($tech_branch_id)."</div>
                    <div style='float:right;text-align:right;' class='rt_text_date'><b>Date:</b> ".date('d M Y',strtotime($quote_data->date))."</div>
                </div>
                <div class='project'>
                    <div class='name_gam'><span>GAM Exterminating</span>
                    </div>  
                </div>
            </div>
            <main>
                <div class='client_info'>
                    <p><b>Company Email : </b> ".esc_attr( get_option('gam_company_email'))."</p>
                    <p><b>Company Phone No : </b> ".esc_attr( get_option('gam_company_phone_no'))."</p>
                </div>
                <p class='subject'>Proposal of Pest Control Service</p>
            </main>
        ";
    
        $body.="
            <table class='table table-striped table-hover' >
                <tr>
                    <th>QUOTE NO</th>
                    <td>$quote_data->quote_no</td>
                </tr> 

                <tr>
                    <th>CLIENT NAME</th>
                    <td>$quote_data->clientName</td>
                </tr>
                <tr>
                    <th>CLIENT ADDRESS</th>
                    <td>$quote_data->clientAddress</td>
                </tr>
                <tr>
                    <th>CLIENT PHONE NUMBER</th>
                    <td>$quote_data->clientPhn</td>
                </tr>
                <tr>
                    <th>CLIENT EMAIL</th>
                    <td>$quote_data->clientEmail</td>
                </tr>
                <tr>
                    <th>TECHNICIAN NAME</th>
                    <td>$technician_name</td>
                </tr>
            </table>

            <table class='table table-striped table-hover'>
                <tr>
                    <th>SERVICES BEING OFFERED</th>
                    <th>PRICE</th>
                </tr>";
            
        if(is_array($services) && count($services)>0){
            foreach($services as $key=>$val){
                $body.="
                    <tr>
                        <td>$val->service</td>
                        <td>$val->price</td>
                    </tr>";
            }    
        }

        $body.="</table>";

        $body.="
            <table class='table table-striped table-hover'>
                <tr>
                    <th>MATERIAL</th>
                    <th>AMOUNT</th>
                </tr>";

        if(is_array($services) && count($services)>0){
            foreach($items as $key=>$val){
                $body.="
                    <tr>
                        <td>$val->material</td>
                        <td>$val->material_price</td>
                    </tr>
                
                ";
            }                    
        }
                
        $body.="
            </table>
            <table class='table table-striped table-hover'>
                <tr>
                    <th>TOTAL COST</th>
                    <td>\$$quote_data->total_cost</td>
                </tr>
                <tr>
                    <th>MAINTENANCE PLAN OFFERED?</th>
                    <td>".(empty($quote_data->discount_with_plan) ? 'No' : 'Yes')."</td>
                </tr>

        ";

        if(!empty($quote_data->discount_with_plan)){
            $body.="
            <tr>
                <th>DISCOUNT WITH MAINTENANCE PLAN</th>
                <td>$quote_data->discount_with_plan</td>
            </tr>
            <tr>
                <th>MAINTENANCE PRICE</th>
                <td>\$$quote_data->maintenance_price</td>
            </tr>";
        }

        $body.="
            <tr>
                <th>CLIENT NOTES</th>
                <td>".nl2br($quote_data->notes_for_client)."</td>
            </tr>";

        $body.="</table>";

        return $body;
    }

    public function residentialQuotePDFContent($quote_id){
        global $wpdb;

        $quote_data = $this->getResidentialQuoteById($quote_id);
        if(!$quote_data) return;
                
        $message="
            <!DOCTYPE html>
                <html lang='en'>
                
                <head>
                    <meta charset='utf-8'>
                    <title>pdf</title>
                    <style>
                        table{
                            font-family: arial, sans-serif;
                            border-collapse: collapse;
                            width: 100%;
                        }

                        td, th{
                            border: 1px solid #dddddd;
                            text-align: left;
                            padding: 8px;
                        }

                        tr:nth-child(even){
                            background-color: #dddddd;
                        }

                        .clearfix:after {
                            content: '';
                            display: table;
                            clear: both;
                        }
                        
                        a {
                            color: #5D6975;
                            text-decoration: underline;
                        }
                        
                        body {
                            position: relative;
                            width: 21cm;  
                            height: 29.7cm; 
                            margin: 0 auto; 
                            color: #001028;
                            background: #FFFFFF; 
                            font-size: 12px; 
                        }
                        
                        header {
                            padding: 10px 0;
                            margin-bottom: 30px;
                        }
                        .name_gam span {
                            font-size: 25px;
                            font-style: italic;
                            font-weight: bold;
                            border-bottom: 3px solid #000;
                            padding: 0px 0px 2px 0px;
                        }
                        
                        .small_title span {
                            font-weight: bold;
                            font-style: italic;
                            padding: 5px 0px 0px;
                            float: left;
                            font-size: 14px;
                        }
                        .rt_text {
                            font-size: 16px;
                            font-style: italic;
                            font-weight: bold;
                            margin-bottom: 5px;
                            text-align: right;
                        }
                        .rt_text_date {
                            margin: 20px 0px 0px;
                            font-size: 16px;
                        }
                        .client_info {
                            float: left;
                            width: 100%;
                            margin-bottom: 40px;
                        }
                        
                        .client_info p {
                            padding: 0px;
                            margin: 0px;
                            font-size: 16px;
                        }
                        p.subject {
                            text-align: center;
                            float: left;
                            width: 100%;
                            text-decoration: underline;
                            font-size: 16px;
                        }
                        .inner_content {
                            float: left;
                            width: 100%;
                        }
                        
                        .inner_content p {
                            font-size: 16px;
                            line-height: 28px;
                            margin: 0px;
                        }
                        .inner_content.inner2 {
                            margin: 20px 0px;
                        }
                        
                        .inner_content.inner2 p {
                            line-height: 20px;
                        }
                        .project { 
                            float: left;
                        }
                        
                        footer {
                            color: #6487b2;
                            width: 100%;
                            height: 30px;
                            position: relative;
                            bottom: 0;
                            padding: 8px 0;
                            text-align: center;
                            float:left;
                        }
                        footer p {
                            font-size: 16px;
                            line-height: 18px;
                            margin: 0 auto !important;
                            max-width: 430px;
                            border-top: 3px solid #800000;
                        }
                    </style>
                </head>
                
                <body>
        ";

        $message .= $this->residenialQuoteBody($quote_data);
    
        $message.="
            </body>
            </html>
        ";

        return $message;
    }

    public function commercialQuoteBody($quote_data){

        $technician_name = (!empty($quote_data->tech_diff_name)) ? $quote_data->tech_diff_name : (new Technician_details)->getTechnicianName($quote_data->technician_id);
        $tech_branch_id = (new Technician_details)->getTechnicianBranchId($quote_data->technician_id);

        $body = "
            <div class='clearfix'>
                <div id='company' style='float:right; width:40%' class='clearfix'>
                    <div style='float:right;width:100%;' class='rt_text'>".(new GamFunctions)->generateLicenseNoBasedOnBranch($tech_branch_id)."</div>
                    <div style='float:right;text-align:right;' class='rt_text_date'><b>Date:</b>".date('d M Y',strtotime($quote_data->date))."</div>
                </div>
                <div class='project'>
                    <div class='name_gam'><span>GAM Exterminating</span></div>
                </div>
            </div>
            <main>
                <div class='client_info'>
                    <p><b>Company Email : </b> ".esc_attr( get_option('gam_company_email'))."</p>
                    <p><b>Company Phone No : </b> ".esc_attr( get_option('gam_company_phone_no'))."</p>
                </div>
                <p class='subject'>Proposal of Pest Control Service</p>
            </main>
        ";
    
        // visit frequency
        if(!empty($quote_data->no_of_times)){
            if(!empty($quote_data->visits_duration)){
                $no_of_times = sprintf("Every %u %s",$quote_data->no_of_times,$quote_data->visits_duration);
            }else{
                $no_of_times = sprintf("Every %u %s",$quote_data->no_of_times,'month');
            }
        }else{
            $no_of_times = (new GamFunctions)->beautify_string($quote_data->visits_duration);
        }
        $body.="
            <table class='table table-hover table-striped'>
                <tbody>
                    <tr>
                        <th>QUOTE NO</th>
                        <td>$quote_data->quote_no</td>
                    </tr>
                    <tr>
                        <th>CLIENT NAME</th>
                        <td>$quote_data->client_name</td>
                    </tr>
                    <tr>
                        <th>CLIENT ADDRESS</th>
                        <td>$quote_data->client_address</td>
                    </tr>
                    <tr>
                        <th>DECISION MAKER NAME</th>
                        <td>$quote_data->decision_maker_name</td>
                    </tr>
                    <tr>
                        <th>FREQUENCY OF VISITS?</th>
                        <td>$no_of_times</td>
                    </tr>
                    <tr>
                        <th>INTIAL COST</th>
                        <td>\$$quote_data->initial_cost</td>
                    </tr>
                    <tr>
                        <th>COST PER VISIT</th>
                        <td>\$$quote_data->cost_per_visit</td>
                    </tr>
                    <tr>
                        <th>CLIENT PHONE NUMBER</th>
                        <td>$quote_data->client_phone</td>
                    </tr>
                    <tr>
                        <th>CLIENT NOTES</th>
                        <td>".nl2br($quote_data->notes_for_client)."</td>
                    </tr>
                    <tr>
                        <th>CLIENT EMAIL</th>
                        <td>$quote_data->clientEmail</td>
                    </tr>
                    <tr>
                        <th>TECHNICIAN NAME</th>
                        <td>$technician_name</td>
                    </tr>
        ";

        if(isset($quote_data->additonal_material) && !empty($quote_data->additonal_material)){
            $additional_materials = implode(', ', json_decode($quote_data->additonal_material));
            $body.="
                    <tr>
                        <th>ADDITIONAL Material</th>
                        <td>$additional_materials</td> 
                    </tr>
            ";
        }


        $body.="
                </tbody>
            </table>
        ";        

        return $body;
    }

    public function commercialQuotesheetPDFContent($quote_id){
        global $wpdb;

        $quote_data = $this->getCommercialQuoteById($quote_id);
        if(!$quote_data) return false;

        $message="
            <!DOCTYPE html>
            <html lang='en'>
            
                <head>
                    <meta charset='utf-8'>
                    <title>pdf</title>
                    <style>
                    table{
                        font-family: arial, sans-serif;
                        border-collapse: collapse;
                        width: 100%;
                    }

                    td, th{
                        border: 1px solid #dddddd;
                        text-align: left;
                        padding: 8px;
                    }

                    tr:nth-child(even){
                        background-color: #dddddd;
                    }

                    .clearfix:after {
                        content: '';
                        display: table;
                        clear: both;
                    }
                    
                    a {
                        color: #5D6975;
                        text-decoration: underline;
                    }
                    
                    body {
                        position: relative;
                        width: 21cm;  
                        height: 29.7cm; 
                        margin: 0 auto; 
                        color: #001028;
                        background: #FFFFFF; 
                        font-size: 12px; 
                    }
                    
                    header {
                        padding: 10px 0;
                        margin-bottom: 30px;
                    }
                    .name_gam span {
                        font-size: 25px;
                        font-style: italic;
                        font-weight: bold;
                        border-bottom: 3px solid #000;
                        padding: 0px 0px 2px 0px;
                    }
                    
                    .small_title span {
                        font-weight: bold;
                        font-style: italic;
                        padding: 5px 0px 0px;
                        float: left;
                        font-size: 14px;
                    }
                    .rt_text {
                        font-size: 16px;
                        font-style: italic;
                        font-weight: bold;
                        margin-bottom: 5px;
                        text-align: right;
                    }
                    .rt_text_date {
                        margin: 20px 0px 0px;
                        font-size: 16px;
                    }
                    .client_info {
                        float: left;
                        width: 100%;
                        margin-bottom: 40px;
                    }
                    
                    .client_info p {
                        padding: 0px;
                        margin: 0px;
                        font-size: 16px;
                    }
                    p.subject {
                        text-align: center;
                        float: left;
                        width: 100%;
                        text-decoration: underline;
                        font-size: 16px;
                    }
                    .inner_content {
                        float: left;
                        width: 100%;
                    }
                    
                    .inner_content p {
                        font-size: 16px;
                        line-height: 28px;
                        margin: 0px;
                    }
                    .inner_content.inner2 {
                        margin: 20px 0px;
                    }
                    
                    .inner_content.inner2 p {
                        line-height: 20px;
                    }
                    .project { 
                        float: left;
                    }
                    
                    footer {
                        color: #6487b2;
                        width: 100%;
                        height: 30px;
                        position: relative;
                        bottom: 0;
                        padding: 8px 0;
                        text-align: center;
                        float:left;
                    }
                    footer p {
                        font-size: 16px;
                        line-height: 18px;
                        margin: 0 auto !important;
                        max-width: 430px;
                        border-top: 3px solid #800000;
                    }
                </style>
                </head>
            
            <body>
        ";
        
        $message .= $this->commercialQuoteBody($quote_data);
    
        $message .= "
            </body>
            </html>
        ";
        return $message;
    }

    public function isQuoteExist(array $data){
        if(
            empty($data['email']) &&
            empty($data['phone']) &&
            empty($data['address'])
        ) return [false, 'Email, phone and address all cannot be empty. Please provide atleast one field'];        

        $conditions = [];

        if(!empty($data['address'])) $conditions[] = " clientAddress = '{$data['address']}' ";
        if(!empty($data['phone'])) $conditions[] = " clientPhn = '{$data['phone']}' ";
        if(!empty($data['email'])) $conditions[] = " clientEmail = '{$data['email']}' ";

        $conditions = ["(".implode(' or ', $conditions).")"];
        
        $residential_quote = $this->getResidentialQuotes($conditions, ['id'], true);
        if($residential_quote && $residential_quote->id){
            return [$residential_quote->id, 'residential', null];
        }

        if(!empty($data['address'])) $conditions[] = " clientAddress = '{$data['address']}' ";
        if(!empty($data['phone'])) $conditions[] = " clientPhn = '{$data['phone']}' ";
        if(!empty($data['email'])) $conditions[] = " clientEmail = '{$data['email']}' ";

        $commercial_quote = $this->getCommercialQuotes($conditions, ['id'], true);
        if($commercial_quote && $commercial_quote->id){
            return [$commercial_quote->id, 'commercial', null];
        }

        return [false, null, null];
    }

    public function getResidentialQuoteUrl(int $quote_id){
        return admin_url("admin.php?page=resdiential-quotesheet&&quote_id=$quote_id");
    }

    public function getCommercialQuoteUrl(int $quote_id){
        return admin_url("admin.php?page=commercial-quotesheet&&quote_id=$quote_id");
    }

    public function servicesOffered(){
        global $wpdb;
        return $wpdb->get_results("select * from {$wpdb->prefix}quotesheet_services");
    }
}

new Quote();