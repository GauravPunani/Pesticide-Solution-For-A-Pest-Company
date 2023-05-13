<?php

class MonthlyQuarterlyMaintenance extends Maintenance{

    use GamValidation;

    function __construct(){
		add_action( 'wp_ajax_nopriv_maintenance_staff',array($this,'maintenance_staff'));
        add_action( 'wp_ajax_maintenance_staff', array($this,'maintenance_staff'));

		add_action( 'admin_post_nopriv_update_monthly_quarterly_contract',array($this,'update_monthly_quarterly_contract'));
		add_action( 'admin_post_update_monthly_quarterly_contract', array($this,'update_monthly_quarterly_contract'));

		add_action( 'wp_ajax_nopriv_download_maintenance_plan',array($this,'download_maintenance_plan'));
		add_action( 'wp_ajax_download_maintenance_plan', array($this,'download_maintenance_plan'));

        add_action( 'wp_ajax_nopriv_quarterly_maintenance', array($this,'quarterly_maintenance'));
        add_action( 'wp_ajax_quarterly_maintenance', array($this,'quarterly_maintenance'));

        add_action( 'wp_ajax_monthly_maintenance', array($this,'monthly_maintenance'));
        add_action( 'wp_ajax_nopriv_monthly_maintenance', array($this,'monthly_maintenance'));

        add_action( 'wp_ajax_monthly_maintenance_credit_card_part', array($this,'monthly_maintenance_credit_card_part'));
        add_action( 'wp_ajax_nopriv_monthly_maintenance_credit_card_part', array($this,'monthly_maintenance_credit_card_part'));

        add_action( 'wp_ajax_quarterly_maintenance_credit_card_part', array($this,'quarterly_maintenance_credit_card_part'));
        add_action( 'wp_ajax_nopriv_quarterly_maintenance_credit_card_part', array($this,'quarterly_maintenance_credit_card_part'));
    }

	public function maintenance_staff(){
		global $wpdb;

        $this->verify_nonce_field('maintenance_contracts');

        $page_url = esc_url_raw($_POST['page_url']);

        $required_fields = [
            'client_name',
            'client_address',
            'client_phone_no',
            'client_email',
            'contract_start_date',
            'contract_end_date',
            'type',
            'branch_id',
        ];

        list($response, $message) = $this->requiredValidation($required_fields, $_POST);
        if(!$response) $this->response('error', $message);

        $array_fields = ['pests_included'];
        list($response, $message) = $this->arrayValidation($array_fields, $_POST);
        if(!$response) $this->response('error', $message);

        $numeric_fields = ['cost_per_month'];
        list($response, $message) = $this->isNumericValidation($numeric_fields, $_POST);
        if(!$response) $this->response('error', $message);

        $client_name = $this->sanitizeEscape($_POST['client_name']);
        $client_address = $this->sanitizeEscape($_POST['client_address']);
        $client_phone_no = $this->sanitizeEscape($_POST['client_phone_no']);
        $client_email = $this->sanitizeEscape($_POST['client_email']);
        $cost_per_month = $this->sanitizeEscape($_POST['cost_per_month']);
        $contract_start_date = $this->sanitizeEscape($_POST['contract_start_date']);
        $contract_end_date = $this->sanitizeEscape($_POST['contract_end_date']);
        $type = $this->sanitizeEscape($_POST['type']);
        $branch_id = $this->sanitizeEscape($_POST['branch_id']);

        $this->beginTransaction();

        if($cost_per_month <= 59) $this->__codeValidation();

		$data=[
			'client_name'				=>  $client_name,
			'client_address'			=>  $client_address,
			'client_phone_no'			=>  $client_phone_no,
			'client_email'				=>  $client_email,
			'cost_per_month'			=>  $cost_per_month,
			'contract_start_date'		=>  $contract_start_date,
			'contract_end_date'			=>  $contract_end_date,
			'type'						=>  $type,
			'date'						=>  date('Y-m-d'),
			'form_status'				=>  'form_filled_by_staff',
            'credit'                    =>  'office',
            'branch_id'                 =>  $branch_id,
            'technician_id'             =>  (new Technician_details)->get_technician_id(),
            'pests_included'            =>  implode(',', $_POST['pests_included'])
        ];

        if(!empty($_POST['client_notes'])){
            $data['client_notes'] = $this->sanitizeEscape($_POST['client_notes'], 'textarea');
        }
        
        if($type == "monthly"){
            $data['total_cost'] = ($cost_per_month * 12);
        }
        elseif($type == "quarterly"){
    
            $data['total_cost']=($cost_per_month * 4);

            if(!empty($_POST['charge_type'])){
                $data['charge_type'] = $_POST['charge_type'];
            }
        }

        $source = $type == "monthly" ? 'monthly_maintenance' : 'quarterly_maintenance';

        list($contract_id, $message) = $this->createContract($data, $source);
        if(!$contract_id) $this->rollBackResponse('error', $message);

        // create task for office to remind client to sign contract
        $response = (new OfficeTasks)->remindClientToSignContract($type, $contract_id);
        if(!$response) $this->rollBackResponse('error');

        // send contract credit card details email
        $response = (new Emails)->contractCcEmail($contract_id, $type);

        $update_data = [ 'email_status'  =>  $response ? 1 : 0 ];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if(!$response) $this->rollBackResponse('error', $message);

        $this->commitTransaction();

        $redirect_url='';

        // if it was a part of invoice flow then redirect to invoice page on invoice flow 
        if(
            !empty($_POST['invoice_step']) && 
            $_POST['invoice_step'] == "maintenance_plan" && 
            @$_SESSION['invoice_step'] == "maintenance_plan"
        ){

            (new InvoiceFLow)->callNextPageInFlow(false);
        }
        else{
            $redirect_url = $page_url;
        }

        $this->response('success', 'An email to proceed with contract has been sent to the client', compact('redirect_url'));
    }
    
	public function update_monthly_quarterly_contract(){
        global $wpdb;

		$this->verify_nonce_field('update_monthly_quarterly_contract');
        
        $page_url = esc_url_raw($_POST['page_url']);

        $required_fields = [
            'branch_id',
            'client_name',
            'client_address',
            'client_phone_no',            
            'contract_start_date',
            'contract_end_date',
            'type',
            'client_email',
            'contract_id',
            'notes_for_client'            
        ];
        list($response, $message) = $this->requiredValidation($required_fields, $_POST);
        if(!$response) $this->response('error', $message);

        $numeric_fields = [
            'cost_per_month',
            'total_cost'            
        ];
        list($response, $message) = $this->isNumericValidation($numeric_fields, $_POST);
        if(!$response) $this->response('error', $message);

		$data=[
			'branch_id'		        =>	$_POST['branch_id'],
			'client_name'			=>	$_POST['client_name'],
			'client_address'		=>	$_POST['client_address'],
			'client_phone_no'		=>	$_POST['client_phone_no'],
			'cost_per_month'		=>	$_POST['cost_per_month'],
			'total_cost'			=>	$_POST['total_cost'],
			'contract_start_date'	=>	$_POST['contract_start_date'],
			'contract_end_date'		=>	$_POST['contract_end_date'],
			'type'					=>	$_POST['type'],
            'client_email'          =>	sanitize_email($_POST['client_email']),
            'client_notes'          =>  $_POST['notes_for_client']
		];

        $contract_id = $this->sanitizeEscape($_POST['contract_id']);

        list($response, $message) = $this->updateContract($contract_id, $data);
        if(!$response) $this->response('error', $message);

        // unset session for technician if he's doing the edit 
        if(isset($_SESSION['monthly_maintenance_editable'])) unset($_SESSION['monthly_maintenance_editable']);
        if(isset($_SESSION['quarterly_maintenance_editable'])) unset($_SESSION['quarterly_maintenance_editable']);
        
        $message = 'Contract updated successfully';

		$this->setFlashMessage($message,'success');

		wp_redirect($page_url);
	}
    
    public function quarterly_maintenance(){
        global $wpdb;

        $this->verify_nonce_field('maintenance_contracts');

        $page_url = esc_url_raw($_POST['page_url']);

        $required_fields = [
            'branch_id',
            'client_name',
            'client_address',
            'client_phone_no',
            'client_email',
            'contract_start_date',
            'contract_end_date',
            'charge_type',
            'signimgurl',
        ];
        list($response, $message) = $this->requiredValidation($required_fields, $_POST);
        if(!$response) $this->response('error', $message);

        $array_fields = ['pests_included', 'card_details'];
        list($response, $message) = $this->arrayValidation($array_fields, $_POST);
        if(!$response) $this->response('error', $message);

        $numeric_fields = ['cost_per_month'];
        list($response, $message) = $this->isNumericValidation($numeric_fields, $_POST);
        if(!$response) $this->response('error', $message);

        $callrail_id = "";
        if(!empty($_POST['callrail_id'])){
            $callrail_id = $this->sanitizeEscape($_POST['callrail_id']);
        }

        $branch_id = $this->sanitizeEscape($_POST['branch_id']);
        $client_name = $this->sanitizeEscape($_POST['client_name']);
        $client_address = $this->sanitizeEscape($_POST['client_address']);
        $client_phone_no = $this->sanitizeEscape($_POST['client_phone_no']);
        $client_email = $this->sanitizeEscape($_POST['client_email']);
        $cost_per_month = $this->sanitizeEscape($_POST['cost_per_month']);
        $contract_start_date = $this->sanitizeEscape($_POST['contract_start_date']);
        $contract_end_date = $this->sanitizeEscape($_POST['contract_end_date']);
        $charge_type = $this->sanitizeEscape($_POST['charge_type']);

        $signimgurl = $_POST['signimgurl'];
        $card_details = $_POST['card_details'];
        $this->beginTransaction();

        if($cost_per_month <= 59) $this->__codeValidation();        

        list($signature_img, $image_file)=$this->save_signature($signimgurl, 'maintenance', $client_name);

        $branch_slug = (new Branches)->getBranchSlug($branch_id);

		// save new email in email database 
        $email_data=[
            'branch_id'	=>	$branch_id,
            'email'		=>	$client_email,
            'name'		=>	$client_name,
            'address'	=>	$client_address,
            'phone'		=>	$client_phone_no,
            'date'		=>	date('Y-m-d'),
        ];
        $email_data['status'] = $callrail_id == "reoccuring_customer" ? "reocurring" : "non_reocurring";

        $response = (new Emails)->save_email($email_data);
        if(!$response) $this->rollBackResponse('error', "unable to save email in client databse");

        $data=[
            'client_name'				=>	$client_name,
            'client_address'			=>	$client_address,
            'client_phone_no'			=>	$client_phone_no,
            'client_email'				=>	$client_email,
            'cost_per_month'			=>	$cost_per_month,		//field name is cost_per_month in db as common table for both forms
            'total_cost'				=>	($cost_per_month * 4),
            'contract_start_date'		=>	$contract_start_date,
            'contract_end_date'			=>	$contract_end_date,
            'type'						=>	'quarterly',
            'form_status'				=>	'form_completed_by_client',
            'charge_type'               =>  $charge_type,
            'card_details'				=>	json_encode($card_details),
            'signature'                 =>  $signature_img,
            'date'                      =>  date('Y-m-d'),
            'branch_id'			        =>	$branch_id,
            'technician_id'             =>  (new Technician_details)->get_technician_id(),
            'callrail_id'               =>  $callrail_id,
            'pests_included'            =>  implode(',', $_POST['pests_included'])
        ];

        if(!empty($_POST['client_notes'])){
            $data['client_notes'] = $this->sanitizeEscape($_POST['client_notes'], 'textarea');
        }        

        if(!empty($_POST['office_notes'])){
            $data['office_notes'] = $this->sanitizeEscape($_POST['office_notes'], 'textarea');
        }

        list($contract_id, $message) = $this->createContract($data, 'quarterly_maintenance');
        if(!$contract_id) $this->rollBackResponse('error', $message);
        
        $message = $this->quarterly_maintenance_template($contract_id);
        list($file_path, $pdf_path) = $this->save_pdf($message, 'maintenance_quarterly', $client_name);	

        $update_data = ['pdf_path' => $pdf_path];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if(!$response) $this->rollBackResponse('error', $message);

        // email quarterly contract
        $response = (new Emails)->emailQuarterlyContract($contract_id);

        $update_data = [ 'email_status'  =>  $response ? 1 : 0];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if(!$response) $this->rollBackResponse('error', $message);

        // if client comes from receipt page , then update that offer is made
        if(isset($_POST['show_receipt']) && !empty($_POST['invoice_id'])){
            $invoice_id = $this->encrypt_data($_POST['invoice_id'],'d');
            $response = $wpdb->update($wpdb->prefix."invoices", ['maintenance_offered'=>'offered'], ['id' => $invoice_id]);
            if(!$response) $this->rollBackResponse('error', 'unable to save contract in system');
        }

        $this->commitTransaction();   

        // if it was a part of invoice flow then redirect to invoice page on invoice flow 
        if(
            !empty($_POST['invoice_step']) && 
            $_POST['invoice_step'] == "maintenance_plan" && 
            @$_SESSION['invoice_step'] == "maintenance_plan"
        ){

            (new InvoiceFLow)->callNextPageInFlow(false);
        }
        else{
            $redirect_url = $page_url;
        }

        $this->response('success', 'Quarterly contract submitted successfully', compact('redirect_url'));
    }

    public function monthly_maintenance(){
        global $wpdb;

		$this->verify_nonce_field('maintenance_contracts');

        $page_url = esc_url_raw($_POST['page_url']);

        $required_fields = [
            'client_name',
            'client_address',
            'client_phone_no',
            'client_email',
            'contract_start_date',
            'contract_end_date',
            'signimgurl',
            'branch_id',            
        ];
        
        foreach($required_fields as $required_field){
            if(empty($_POST[$required_field])) $this->response('error', $required_field." is required");
        }

        if(!$this->isArrayExistWithValues('pests_included')) $this->response('error', 'Please select atleast one pest');

        if(!isset($_POST['cost_per_month']) || !is_numeric($_POST['cost_per_month']))
            $this->response('error', "Cost Per Month is required");

        if(
            empty($_POST['card_details']) || 
            !is_array($_POST['card_details']) || 
            count($_POST['card_details']) <= 0            
        ) $this->response('error', "Card details are required");

        $client_name = $this->sanitizeEscape($_POST['client_name']);
        $client_address = $this->sanitizeEscape($_POST['client_address']);
        $client_phone_no = $this->sanitizeEscape($_POST['client_phone_no']);
        $client_email = $this->sanitizeEscape($_POST['client_email']);
        $cost_per_month = $this->sanitizeEscape($_POST['cost_per_month']);
        $contract_start_date = $this->sanitizeEscape($_POST['contract_start_date']);
        $contract_end_date = $this->sanitizeEscape($_POST['contract_end_date']);
        $branch_id = $this->sanitizeEscape($_POST['branch_id']);

        $card_details = $_POST['card_details'];
        
        $this->beginTransaction();

        if($cost_per_month <= 59) $this->__codeValidation();

        $branch_slug = (new Branches)->getBranchSlug($branch_id);

        list($image_path, $image_file) = $this->save_signature($_POST["signimgurl"], 'maintenance', $client_name);

        $callrail_id = "";
        if(!empty($_POST['callrail_id'])) $callrail_id = $this->sanitizeEscape($_POST['callrail_id']);

		// save new email in email database 
        $email_data=[
            'branch_id'	=>	$branch_id,
            'email'		=>	$client_email,
            'name'		=>	$client_name,
            'address'	=>	$client_address,
            'phone'		=>	$client_phone_no,
            'date'		=>	date('Y-m-d'),
        ];
        $email_data['status'] = $callrail_id == "reoccuring_customer" ? "reocurring" : "non_reocurring";

        $response = (new Emails)->save_email($email_data);
        if(!$response) $this->rollbackResponse('error', "Unable to save email record in system");
        
        $data=[
            'client_name'			=>	$client_name,
            'client_address'		=>	$client_address,
            'client_phone_no'		=>	$client_phone_no,
            'client_email'			=>	$client_email,
            'cost_per_month'		=>	$cost_per_month,
            'total_cost'			=>	($cost_per_month * 12),
            'contract_start_date'	=>	$contract_start_date,
            'contract_end_date'		=>	$contract_end_date,
            'type'					=>	'monthly',
            'form_status'			=>	'form_completed_by_client',
            'card_details'          =>  json_encode($card_details),
            'signature'             =>  $image_path,
            'branch_id'             =>  $branch_id,
            'date'		            =>	date('Y-m-d'),
            'technician_id'         =>  (new Technician_details)->get_technician_id(),
            'callrail_id'           =>  $callrail_id,
            'pests_included'        =>  implode(',', $_POST['pests_included'])
        ];

        if(!empty($_POST['client_notes'])){
            $data['client_notes'] = $this->sanitizeEscape($_POST['client_notes'], 'textarea');
        }

        if(!empty($_POST['office_notes'])){
            $data['office_notes'] = $this->sanitizeEscape($_POST['office_notes'], 'textarea');
        }    

        list($contract_id, $message) = $this->createContract($data, 'monthly_maintenance');
        if(!$contract_id) $this->rollbackResponse('error', $message);

        $emailContent = $this->monthly_maintenance_template($contract_id);
        list($file_path,$pdf_path) = $this->save_pdf($emailContent,'maintenance_monthly',$client_name);

        list($response, $message) = $this->updateContract($contract_id, ['pdf_path' => $pdf_path]);
        if(!$response) $this->rollbackResponse('error', $message);

        $response = (new Emails)->emailMonthlyContract($contract_id);

        // update email sent status in system
        $update_data = [ 'email_status'  =>  $response ? 1 : 0];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if(!$response) $this->rollbackResponse('error', $message);

        // if client comes from receipt page , then update that offer is made
        if(!empty($_POST['show_receipt']) && !empty($_POST['invoice_id'])){
            $invoice_id = $this->encrypt_data($_POST['invoice_id'],'d');

            $response = (new Invoice)->updateInvoice($invoice_id, ['maintenance_offered' => 'offered']);
            if(!$response) $this->rollbackResponse('error', "Unable to update maintenance offered status in system");
        }

        $this->commitTransaction();

        $redirect_url='';
        // if it was a part of invoice flow then redirect to invoice page on invoice flow 
        if(
            !empty($_POST['invoice_step']) && 
            $_POST['invoice_step'] == "maintenance_plan" && 
            @$_SESSION['invoice_step'] == "maintenance_plan"
        ){

            (new InvoiceFLow)->callNextPageInFlow(false);
        }
        else{
            $redirect_url = $page_url;
        }

        $this->response('success','Maintenance contract created successfully', ['redirect_url' => $redirect_url]);
    }

    public function createContract(array $data, string $source){
        global $wpdb;

        $response = $wpdb->insert($wpdb->prefix."maintenance_contract",$data);
        if(!$response) return [false, $wpdb->last_error];

        $contract_id = $wpdb->insert_id;

        $status = $data['form_status'] == "form_completed_by_client" ? 'closed' : 'pending';

        $data =  [
            'address'   =>  $data['client_address'],
            'phone'     =>  $data['client_phone_no'],
            'email'     =>  $data['client_email'],
            'status'    =>  $status,
            'source'    =>  $source,
            'source_id' =>  $contract_id
        ];

        list($response, $message) = (new Leads)->__quoteLeadStatusMiddleware($data);
        if(!$response) return [false, $message];

        return [$contract_id, null];
    }

    public function monthly_maintenance_credit_card_part(){
        global $wpdb;

		$this->verify_nonce_field('maintenance_contracts');

        $page_url = esc_url_raw($_POST['page_url']);

        $required_fields = ['contract_id', 'signimgurl'];
        list($response, $message) = $this->requiredValidation($required_fields, $_POST);
        if(!$response) $this->response('error', $message);

        $array_fields = ['card_details'];
        list($response, $message) = $this->arrayValidation($array_fields, $_POST);
        if(!$response) $this->response('error', $message);

        $contract_id = $this->sanitizeEscape($_POST['contract_id']);
        $signimgurl = $_POST['signimgurl'];
        $card_details = $_POST['card_details'];

        $this->beginTransaction();

        $contract = $this->getContractById($contract_id, ['client_name', 'client_email']);
        if(!$contract) $this->rollBackResponse('error');        

        // save signature & credit card details 
        list($image_path, $image_file) = $this->save_signature($signimgurl, 'maintenance', $contract->client_name);
        $update_data = [
            'card_details'  =>  json_encode($card_details),
            'signature'     =>  $image_path,
            'form_status'   =>  'form_completed_by_client'
        ];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if(!$response) $this->rollBackResponse('error', $message);

        // update contract pdf 
        $email_content = $this->monthly_maintenance_template($contract_id);
        list($file_path,$pdf_path)=$this->save_pdf($email_content,'maintenance_monthly',$contract->client_name);

        $update_data = ['pdf_path' => $pdf_path];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if(!$response) $this->rollBackResponse('error', $message);

        // email contract 
        $response = (new Emails)->emailMonthlyContract($contract_id);

        // update email sent status in system
        $update_data = [ 'email_status'  =>  $response ? 1 : 0];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if($response === false) $this->rollBackResponse('error', $message);

        $this->commitTransaction();

        $this->response('success', 'Contract details updated successfully');
    }

    public function quarterly_maintenance_credit_card_part(){
        global $wpdb;

		$this->verify_nonce_field('maintenance_contracts');

        $page_url = esc_url_raw($_POST['page_url']);

        $required_fields = ['contract_id', 'signimgurl'];
        list($response, $message) = $this->requiredValidation($required_fields, $_POST);
        if(!$response) $this->response('error', $message);

        $array_fields = ['card_details'];
        list($response, $message) = $this->arrayValidation($array_fields, $_POST);
        if(!$response) $this->response('error', $message);

        $contract_id = $this->sanitizeEscape($_POST['contract_id']);
        $signimgurl = $_POST['signimgurl'];
        $card_details = $_POST['card_details'];

        $this->beginTransaction();

        $contract = $this->getContractById($contract_id, ['client_name', 'client_email']);
        if(!$contract) $this->rollBackResponse('error');        

        // save signature & credit card details 
        list($image_path, $image_file) = $this->save_signature($signimgurl, 'maintenance', $contract->client_name);
        $update_data = [
            'card_details'  =>  json_encode($card_details),
            'signature'     =>  $image_path,
            'form_status'   =>  'form_completed_by_client'
        ];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if(!$response) $this->rollBackResponse('error', $message);

        // update contract pdf 
        $email_content = $this->quarterly_maintenance_template($contract_id);
        list($file_path,$pdf_path)=$this->save_pdf($email_content,'maintenance_monthly',$contract->client_name);

        $update_data = ['pdf_path' => $pdf_path];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if(!$response) $this->rollBackResponse('error', $message);

        $response = (new Emails)->emailQuarterlyContract($contract_id);
        if(!$response) $this->rollBackResponse('error');

        $this->commitTransaction();

        $this->response('success', 'Contract details updated successfully');
    }

    public function monthly_maintenance_template(int $contract_id){

        $contract = $this->getContractById($contract_id);

        $card_details = json_decode($contract->card_details);
        $contract_type = sprintf('%s MAINTENANCE CONTRACT',strtoupper($contract->type));
        $upload_dir=wp_upload_dir();

        $emailContent = (new Emails)->emailTemplateHeader(); 

        $emailContent .= "
            <table>
                <tr>
                    <th colspan='2' style='text-align:center'>
                        $contract_type 
                    </th>
                </tr>
        ";
    
        $emailContent .= "
                <tr>
                    <th>Name</th>
                    <td>$contract->client_name</td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>$contract->client_address</td>
                </tr>
                <tr>
                    <th>Phone No.</th>
                    <td>$contract->client_phone_no</td>
                </tr>
                <tr>
                <tr>
                    <th>Email</th>
                    <td>$contract->client_email</td>
                </tr>
                <tr>
                    <th>Cost Per Visit</th>
                    <td>\$$contract->cost_per_month</td>
                </tr>
                <tr>
                    <th>Total Cost</th>
                    <td>\$$contract->total_cost</td>
                </tr>
                <tr>
                    <th>Total Cost of annual contract</th>
                    <td>\$".($contract->cost_per_month * 12)."</td>
                </tr>
                <tr>
                    <th>Contract Start Date</th>
                    <td>".date('d M Y', strtotime($contract->contract_start_date))."</td>
                </tr>
                <tr>
                    <th>Contract End Date</th>
                    <td>".date('d M Y', strtotime($contract->contract_end_date))."</td>
                </tr>
                <tr>
                    <th>Notes</th>
                    <td>".nl2br($contract->client_notes)."</td>
                </tr>
            </table>

            <table>
                <tr>
                    <th>Credit Card No</th>
                    <th>Expiration</th>
                    <th>Security Code</th>
                </tr>
                <tr>
                    <td>$card_details->creditcardnumber</td>
                    <td>$card_details->cc_month / $card_details->cc_year</td>
                    <td>$card_details->cccode</td>
                </tr>
            </table>
        ";

        // get the included / excluded pests html
        $emailContent .= $this->includedExludedPests($contract->pests_included);        

        $emailContent .= $this->mail_template();

        $emailContent .= $this->commitementLine();
    
        $emailContent .= "<div style='float:left;width: 40%;margin: 8% 5% auto;font-size:22px;'><img src='".$contract->signature."'/>";
    
        $emailContent .= "
                </body>
            </html>
        ";

        return $emailContent;
    }

    public function quarterly_maintenance_template(int $contract_id){

        $contract = $this->getContractById($contract_id);

        $upload_dir=wp_upload_dir();

        $card_details = json_decode($contract->card_details);

        $message = (new Emails)->emailTemplateHeader();

        $message .= "
                <table>
                    <tr>
                        <th colspan='2' style='text-align:center'>QUARTERLY MAINTENANCE CONTRACT</th>
                    </tr>
        ";
            
        $total_cost = (float) ($contract->cost_per_month * 4);

        $message .= "
                <tr>
                    <th>Full Name</th>
                    <td>$contract->client_name</td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>$contract->client_address</td>
                </tr>
                <tr>
                    <th>Phone number</th>
                    <td>$contract->client_phone_no</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>$contract->client_email</td>
                </tr>
                <tr>
                    <th>Maintenance Charges Interval</th>
                    <td>".ucwords($contract->charge_type)."</td>
                </tr>
                <tr>
                    <th>Cost Per Quarter</th>
                    <td>\$$contract->cost_per_month</td>
                </tr>
                <tr>
                    <th>Total cost of annual contract</th>
                    <td>\$$total_cost</td>
                </tr>
                <tr>
                    <th>Notes</th>
                    <td>".nl2br($contract->client_notes)."</td>
                </tr>
                <tr>
                    <th>Contract Start Date</th>
                    <td>".date('d M Y', strtotime($contract->contract_start_date))."</td>                    
                </tr>
                <tr>
                    <th>Contract End Date</th>
                    <td>".date('d M Y', strtotime($contract->contract_end_date))."</td>                    
                </tr>
        ";

        if($contract->charge_type == "monthly"){
            $message.="
                <tr>
                    <th>Maintenance Charges Interval</th>
                    <td>Monthly</td>
                </tr>
            ";		
        }
        elseif($contract->charge_type == "quarterly"){
            $message.="
                <tr>
                    <th>Maintenance Charges Interval</th>
                    <td>Quarterly</td>
                </tr>
            ";
        }

        $message .= "
            </table>
        ";

        // card details
        $message .= $this->creditCardDetailsLabel($card_details);

        // get the included / excluded pests html
        $message .= $this->includedExludedPests($contract->pests_included);
        
        // get contract terms/policy
        $message.=$this->mail_template();

        // get the I agree line 
        $message .= $this->commitementLine();
        
        // signature 
        $message.="<div style='float:left;width: 40%;margin: 8% 5% auto;font-size:22px;'><img src='".$contract->signature."'/></div>";
    
        $message.="</body>
                </html>";
                
        return $message;
    }

    public function commitementLine(){

        $upload_dir = wp_upload_dir();

        return "\n <p style='font-size:12px; font-style:italic;'><img style='width:20px;height:20px;' src='".$upload_dir['baseurl']."/2019/11/checkmark.png' />I understand this is a 12 month commitment for the property I have listed, and I am responsible for the full value of this contract. I understand my card will be billed monthly for the amount stated above.</p>";
    }

    public function getMonthlyContracts( string $date, array $columns = []){
        global $wpdb;

        $date = date('Y-m-d', strtotime($date));
        $columns = (count($columns) > 0) ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}maintenance_contract
            where type = 'monthly'
            and DATE(date) = '$date'
        ");
    }

    public function getQuarterlyContracts( string $date, array $columns = []){
        global $wpdb;
        $date = date('Y-m-d', strtotime($date));

        $columns = (count($columns) > 0) ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}maintenance_contract
            where type = 'quarterly'
            and DATE(date) = '$date'
        ");
    }

    public function getMonthlyContractsByDate( string $from_date, string $to_date,  array $columns = []){
        global $wpdb;

        $from_date = date('Y-m-d', strtotime($from_date));
        $to_date = date('Y-m-d', strtotime($to_date));

        $columns = (count($columns) > 0) ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}maintenance_contract
            where type = 'monthly'
            and DATE(date) >= '$from_date'
            and DATE(date) <= '$to_date'
        ");
    }

    public function getMonthlyCount( string $from_date, string $to_date, int $employee_id = null, bool $tech_owned = false){
        global $wpdb;

        $conditions = [];
        $conditions[] = " type = 'monthly' ";

        $from_date = date('Y-m-d', strtotime($from_date));
        $to_date = date('Y-m-d', strtotime($to_date));

        $conditions[] = " DATE(date) >= '$from_date' ";
        $conditions[] = " DATE(date) <= '$to_date' ";

        if($tech_owned){
            $conditions[] = " credit = 'technician' ";
        }

        if(!is_null($employee_id)){
            $technician_id = (new Employee\Employee)->getReferenceIdByEmployeeId($employee_id);
            if(!$technician_id) throw new Exception('Employee ref id not found');
            $conditions[] =" technician_id = '$technician_id'";            
        }

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}maintenance_contract
            $conditions
        ");
    }
    
    public function getQuarterlyCount( string $from_date, string $to_date, int $employee_id = null, bool $tech_owned = false){
        global $wpdb;

        $conditions = [];
        $conditions[] = " type = 'quarterly' ";

        $from_date = date('Y-m-d', strtotime($from_date));
        $to_date = date('Y-m-d', strtotime($to_date));

        $conditions[] = " DATE(date) >= '$from_date' ";
        $conditions[] = " DATE(date) <= '$to_date' ";

        if($tech_owned){
            $conditions[] = " credit = 'technician' ";
        }        

        if(!is_null($employee_id)){
            $technician_id = (new Employee\Employee)->getReferenceIdByEmployeeId($employee_id);
            if(!$technician_id) throw new Exception('Employee ref id not found');
            $conditions[] =" technician_id = '$technician_id'";            
        }        

        if(!is_null($technician_id)) $conditions[] =" technician_id = '$technician_id'";

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}maintenance_contract
            $conditions
        ");
    }

    public function getQuarterlyContractsByDate( string $from_date, string $to_date,  array $columns = []){
        global $wpdb;

        $from_date = date('Y-m-d', strtotime($from_date));
        $to_date = date('Y-m-d', strtotime($to_date));

        $columns = (count($columns) > 0) ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}maintenance_contract
            where type = 'quarterly'
            and DATE(date) >= '$from_date'
            and DATE(date) <= '$to_date'
        ");
    }

    public function getContractById(int $contract_id, array $columns = []){
        global $wpdb;

        $columns = (count($columns) > 0) ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns
            from {$wpdb->prefix}maintenance_contract
            where id = '$contract_id'
        ");
    }

    public function updateContract(int $contract_id, array $data){
        global $wpdb;

        $response = $wpdb->update($wpdb->prefix."maintenance_contract", $data, ['id' => $contract_id]);
        if($response === false) return [false, $wpdb->last_error];

        //if status is changed to completed then set lead/quote as closed too
        if(!empty($data['form_status']) && $data['form_status'] == 'form_completed_by_client'){

            $contract = $this->getContractById($contract_id);

            $source = $contract->type == 'monthly' ? 'monthly_maintenance' : 'quarterly_maintenance';

            $data =  [
                'address'   =>  $contract->client_address,
                'phone'     =>  $contract->client_phone_no,
                'email'     =>  $contract->client_email,
                'status'    =>  'closed',
                'source'    =>  $source,
                'source_id' =>  $contract_id
            ];
    
            list($response, $message) = (new Leads)->__quoteLeadStatusMiddleware($data);
            if(!$response) return [false, $message];
        }

        return [true, null];        
    }

}

new MonthlyQuarterlyMaintenance();