<?php

use Mpdf\Tag\Em;

class InvoiceFlow extends Invoice{

    private $client_data;
    
    function __construct(){
        $this->invoice_data = $_SESSION['invoice-data'] ?? ''; 
        $this->client_data = $_SESSION['invoice-data']['client-data'] ?? '';
        $this->invoice_step = $_SESSION['invoice_step'] ?? 'select_calendar_event';
        $this->invoice_flow_page_url = 'invoice';

		add_action( 'admin_post_invoice_flow_maintenance_step', array($this,'invoice_flow_maintenance_step'));
        add_action( 'admin_post_nopriv_invoice_flow_maintenance_step', array($this,'invoice_flow_maintenance_step'));

		add_action( 'admin_post_invoice_flow_office_notes', array($this,'invoice_flow_office_notes'));
        add_action( 'admin_post_nopriv_invoice_flow_office_notes', array($this,'invoice_flow_office_notes'));

		add_action( 'admin_post_invoice_flow_animal_cage_tracker', array($this,'invoice_flow_animal_cage_tracker'));
        add_action( 'admin_post_nopriv_invoice_flow_animal_cage_tracker', array($this,'invoice_flow_animal_cage_tracker'));

		add_action( 'wp_ajax_invoice_flow_get_callrail_type', array($this,'invoice_flow_get_callrail_type'));
        add_action( 'wp_ajax_nopriv_invoice_flow_get_callrail_type', array($this,'invoice_flow_get_callrail_type'));

    }

    public function callNextPageInFlow(bool $reloadPage = true){
        if($this->isProspectEvent()){

            if($prospect_id = $this->isRecurringProspect()){
                $this->setProspectId($prospect_id);
                $this->setInvoiceStep("update_prospect_status");
            }
            else{
                $this->setInvoiceStep("prospect_form");
            }
        }
        else{
            switch ($this->invoice_step) {

                case 'select_calendar_event':
                    if(
                        $this->canBypassChemicalReportStep() &&
                        $this->canBypassMaintenanceStep()
                    ){
                        $this->setInvoiceStep("invoice");
                    }
                    elseif(
                        $this->canBypassChemicalReportStep()
                    ){
                        $this->setInvoiceStep("maintenance_plan");
                    }
                    elseif($this->canBypassChemicalReportStep()){
                        $this->setInvoiceStep('animal-cage-tracker');
                    }
                    else{
                        $this->setInvoiceStep("chemical_report");
                    }
                break;
    
                case 'chemical_report':
                    $this->setInvoiceStep('animal-cage-tracker');
                break;

                case 'animal-cage-tracker':
                    if($this->canBypassMaintenanceStep()){
                        $this->setInvoiceStep("invoice");
                    }
                    else{
                        $this->setInvoiceStep("maintenance_plan");
                    }
                break;

                case 'maintenance_plan':
                    $this->setInvoiceStep("invoice");
                break;

                case 'invoice':
                    $payment_method = $this->getPaymentType();
                    if($payment_method == "credit_card"){
                        $this->setInvoiceStep("tekcard_payment");
                    }
                    else{
                        $this->setInvoiceStep('office_feedback');
                    }
                break;

                case 'tekcard_payment':
                    $this->setInvoiceStep('office_feedback');
                break;
            
                case 'office_feedback':
                    $this->resetInvoiceFlow();
                break;
            }
        }

        if($reloadPage){
            wp_redirect($this->invoice_flow_page_url);
            exit;
        }
    }

    public function setProspectId(int $prospect_id){
        $_SESSION['invoice-data']['prospect_id'] = $prospect_id;
        return $this;
    }

    public function getProspectId(){
        if(!isset($_SESSION['invoice-data']['prospect_id'])) return false;
        return $_SESSION['invoice-data']['prospect_id'];
    }

    public function isRecurringProspect(){
        global $wpdb;

        $client_address = $this->getClientAddress();
        $email = $this->getClientEmail();
        $phone_no = $this->getClientPhoneNo();

        $conditions = [];

        if(empty($client_address) && empty($email) && empty($phone_no)) return false;

        if(!empty($client_address)) $conditions[] = " address = '$client_address' ";
        if(!empty($email)) $conditions[] = " email = '$email' ";
        if(!empty($phone_no)) $conditions[] = " phone = '$phone_no' ";

        $conditions = "where" . implode(' or ', $conditions);

        return $wpdb->get_var("
            select id
            from {$wpdb->prefix}prospectus
            $conditions
        ");

    }

    public function isProspectEvent(): bool{
        if(
            isset($this->client_data['prospect_event']) && 
            $this->client_data['prospect_event'] == "true"
        ) return true;

        return false;
    }

    public function invoice_flow_office_notes(){
        global $wpdb;

        $this->verify_nonce_field('invoice_flow_office_notes');

        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['client_satisfied']) ||
            empty($_POST['notes'])
        ) $this->sendErrorMessage($page_url, "Please fill all the required fields");

        $client_satisfied = $this->sanitizeEscape($_POST['client_satisfied']);
        $notes = $this->sanitizeEscape($_POST['notes'], 'textarea');

        $invoice_id = $this->getInvoiceId();
        $calendar_event_id = $this->getCalendarId();
        $technician_id = (new Technician_details)->get_technician_id();
        $invoice_date = $this->getInvoiceDate();

        // START TRANSACTION
        $this->beginTransaction();

        $data = [
            'calendar_event_id' =>  $calendar_event_id,
            'invoice_id'        =>  $invoice_id,
            'technician_id'     =>  $technician_id,
            'note'              =>  $notes,
            'client_name'       =>  $this->getClientName(),
            'date'              =>  $invoice_date
        ];

        if($client_satisfied == "no"){

            if(empty($_POST['disatisfaction_reason'])) $this->rollBackTransaction($page_url);
            $disatisfaction_reason = $this->sanitizeEscape($_POST['disatisfaction_reason']);

            $unsatisfied_client_id = $this->processUnsatisfiedClient($invoice_id, $disatisfaction_reason);
            if(!$unsatisfied_client_id) $this->rollBackTransaction($page_url, "unable to process unsaitisfied client");

            $data['unsatisfied_client_id'] = $unsatisfied_client_id;
        }

		if(isset($_FILES['optional_images'])&& !empty($_FILES['optional_images']['name'][0])){
			$optional_images = $this->uploadFiles($_FILES['optional_images']);
			$data['optional_images'] = json_encode($optional_images);
		}

        $response = (new Notes)->createNotes($data);
        if(!$response) $this->rollBackTransaction($page_url, "Unable to create notes for client");

        // upload notes on google calendar event as well
        $response = (new Calendar)->uploadNotesInEvent($technician_id, $calendar_event_id, $notes, $invoice_date);
        if(!$response) $this->rollBackTransaction($page_url, "unable to upload notes on calendar");

        // set message for technician
        $message = "Office notes submitted successfully and invoice flow is completed!.";
        $this->setFlashMessage($message, 'success');

        // COMMIT TRANSACTION
        $this->commitTransaction();

        $this->callNextPageInFlow();
    }

    public function isCagesOnSite(){
        global $wpdb;

        $client_address = $this->getClientAddress();
        if(empty($client_address)) return false;

        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}animal_cage_tracking_new ACT
            left join {$wpdb->prefix}invoices I
            on ACT.invoice_id = I.id
            where I.address like '$client_address'
            and ACT.retrieved = 0            
        ");
    }

    public function isInvoiceFLowActive(){
        return empty($this->invoice_step) ? false : true;
    }

    public function invoice_flow_get_callrail_type(){
        $recurring_status = $this->getRecurringStatus();
        $lead_source = $this->getLeadSource();

        $response = [
            'recurring_status' =>  $recurring_status,
            'lead_source'       =>  $lead_source
        ];

        $this->response('success', '', $response);
    }

    public function isChemicalReportExist(){
        return empty($this->invoice_data['chemical-report-data']) ? false : true;
    }

    public function getInvoiceDate(){
        if(empty($this->client_data['date'])) return '';
        return $this->client_data['date'];
    }

    public function getChemicalReportType(){
        if(empty($this->invoice_data['chemical-report-data']['report_type'])) return '';
        return $this->invoice_data['chemical-report-data']['report_type'];
    }

    public function getChemicalReportId(){
        if(empty($report_id = $this->invoice_data['chemical-report-data']['report_id'])) return '';
        return is_array($report_id) ? json_encode($report_id) : $report_id;
    }

    public function getServiceFee(){
        return array_key_exists('service_fee', $this->client_data) ? $this->client_data['service_fee'] : '';
    }

    public function getPaymentMethod(){
        return array_key_exists('payment_method', $this->client_data) ? $this->client_data['payment_method'] : '';
    }

    public function getRecurringStatus(){
        if(empty($this->client_data['recurring-client'])) return '';
        return $this->client_data['recurring-client'];
    }

    public function getClientName(){
        if(empty($this->client_data['client-name'])) return '';
        return $this->client_data['client-name'];
    }

    public function getClientEmail(){
        if(empty($this->client_data['client-email'])) return '';
        return $this->client_data['client-email'];
    }

    public function getInvoiceLabel(){
        if(empty($this->client_data['invoice_label'])) return '';
        return $this->client_data['invoice_label'];
    }

    public function getCalendarId(){
        if(empty($this->client_data['calendar_id'])) return '';
        return $this->client_data['calendar_id'];
    }

    public function getEventDate(){
        if(empty($this->client_data['date'])) return '';
        return $this->client_data['date'];
    }

    public function getClientPhoneNo(){
        if(empty($this->client_data['phone-no'])) return '';
        return $this->client_data['phone-no'];
    }

    public function getLeadSource(){
        if(empty($this->client_data['lead-source'])) return '';
        return $this->client_data['lead-source'];
    }

    public function getClientAddress(){
        if(empty($this->client_data['client-location'])) return '';
        return $this->sanitizeAddressField($this->client_data['client-location']);
    }

    public function canBypassMaintenanceStep(): bool{
        if(isset($this->client_data['maintenance_bypass']) && $this->client_data['maintenance_bypass']=="true") return true;
        return false;
    }

    public function canBypassChemicalReportStep(): bool{
        if(isset($this->client_data['chemical-bypass']) && $this->client_data['chemical-bypass']=="true") return true;
        return false;
    }

    public function setInvoiceStep( string $invoice_step){
        $_SESSION['invoice_step'] = $invoice_step;
        return $this;
    }

    public function resetInvoiceFlow(){
        unset($_SESSION['invoice-data']);
        unset($_SESSION['invoice_step']);
        unset($_SESSION['invoice_details']);
        wp_redirect(site_url()."/invoice");
        exit;
    }

    public function setClientInterestedInMaintenance( string $status ){
        $status = @$_SESSION['invoice-data']['client-data']['interested_in_maintenance'];
        return $this;        
    }

    public function getClientInterestedInMaintenance(){
        if(!isset($_SESSION['invoice-data']['client-data']['interested_in_maintenance'])) return null;
        return $_SESSION['invoice-data']['client-data']['interested_in_maintenance'];
    }

	// for rejection and accept of maintenance offer 
	public function invoice_flow_maintenance_step(){
		global $wpdb;

        $page_url = @$_POST['page_url'];

        $this->verify_nonce_field('invoice_flow_maintenance_step');

        if(empty($_POST['client_interested'])) $this->sendErrorMessage($page_url);

        $client_interested = sanitize_text_field($_POST['client_interested']);

        $this->setClientInterestedInMaintenance($client_interested);

		if($client_interested == "no"){

            $technician_id = (new Technician_details)->get_technician_id();
            $branch_id = (new Technician_Details)->getTechnicianBranchId($technician_id);

			$client_details=[
                'branch_id'     =>  $branch_id,
				'name'			=>	$this->getClientName(),
				'email'			=>	$this->getClientEmail(),
				'address'		=>	$this->getClientAddress(),
				'phone_no'		=>	$this->getClientPhoneNo(),
                'status'        =>  'non_reocurring',
				'date'	        =>	date('Y-m-d')
			];

            (new Emails)->save_email($client_details);

            $this->callNextPageInFlow();
		}
		elseif($client_interested == "yes"){
			// redirect to corrosponding maintenance page 
			$page_url='';
			switch ($_POST['maintenane_type']) {
                case 'monthly':
                    $page_url = (new Maintenance)->monthlyPageUrl();
                break;
                case 'quarterly':
                    $page_url = (new Maintenance)->quarterlyPageUrl();
                break;
                case 'commercial':
                    $page_url = (new Maintenance)->commercialPageUrl();
                break;
                case 'special':
                    $page_url = (new Maintenance)->specialPageUrl();
                break;
				default:
					$page_url= (new Maintenance)->monthlyPageUrl();
				break;
			}
			$page_url = add_query_arg('invoice-flow', 'true', $page_url);
            $this->setMaintenancePageUrl($page_url);
            wp_redirect($page_url);
		}
	}

    public function invoice_flow_animal_cage_tracker(){
        global $wpdb;

        // first verify nonce field
        $this->verify_nonce_field('invoice_flow_animal_cage_tracker');

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['cage_option'])) $this->sendErrorMessage($page_url);


        $cage_option = esc_html($_POST['cage_option']);

        if($cage_option == "yes"){

            if(
                !isset($_POST['cages_data']) ||
                !is_array($_POST['cages_data']) ||
                count($_POST['cages_data']) <= 0
            ) $this->sendErrorMessage($page_url);

            $cage_types = (new AnimalCageTracker)->getCagesTypes();
            $invoice_id = $this->getInvoiceId();

            if(!is_array($cage_types) || count($cage_types) <= 0) $this->callNextPageInFlow();

            $client_address = $this->getClientAddress();
            $cage_data = $_POST['cages_data'];

            foreach($cage_types as $cage_type){

                $type = $cage_type->slug;

                if(isset($cage_data[$type]) && !empty($cage_data[$type])){
                    $cage_quantity = esc_html($cage_data[$type]);

                    if((int) $cage_quantity > 0){
                        (new AnimalCageTracker)->generateCageTrackerRecord($client_address,  $cage_type->id, $cage_quantity);
                    }
                }
            }

            // set the message for invoice completion 
            $message = "Animal cage record created successfully";
            $this->setFlashMessage($message,'success');                
            
        }

        $this->callNextPageInFlow();
    }

    public function setMaintenancePageUrl( string $url ){
        $_SESSION['invoice-data']['maintenance_page_url'] = $url;
        return $this;
    }

    public function getMaintenancePageUrl(){
        if(!empty($_SESSION['invoice-data']['maintenance_page_url'])) return $_SESSION['invoice-data']['maintenance_page_url'];
        return null;
    }

    public function setVariableInSession(array $data){
        $_SESSION['invoice-data'][$data['key']] = $data['value'];
        return $this;
    }

    public function getVariableInSession($var){
        if(!empty($_SESSION['invoice-data'][$var])) return $_SESSION['invoice-data'][$var];
        return null;
    }
    
    public function setInvoiceId( int $invoice_id){
        $_SESSION['invoice-data']['invoice_id'] = $invoice_id;
        return $this;
    }

    public function getInvoiceId(){
        return $this->invoice_data['invoice_id'];
    }

    public function setPaymentType( string $payment_type ){
        $_SESSION['invoice-data']['payment_type'] = $payment_type;
        return $this;
    }

    public function getPaymentType(){
        return $this->invoice_data['payment_type'];
    }

    public function getSalesTaxRate(){
        if(empty($this->client_data['sale-tax'])) return null;
        return $this->client_data['sale-tax'];
    }

	public function addUnsatisfiedClient(int $invoice_id, string $reason){
		global $wpdb;

		$data = [
			'invoice_id'	=>	$invoice_id,
			'reason'		=>	$reason
		];
		
		$response =  $wpdb->insert($wpdb->prefix."unsatisfied_clients", $data);

        return !$response ? false : $wpdb->insert_id;
	}    

    public function processUnsatisfiedClient(int $invoice_id, string $disatisfaction_reason){

        // create task for office staff to call the client
        $response = (new OfficeTasks)->callDisatisfactionTask($invoice_id);
        if(!$response) return false;

        // add to unsatisfied clients list
        $unsatisfied_client_id = $this->addUnsatisfiedClient($invoice_id, $disatisfaction_reason);
        if(!$unsatisfied_client_id) return false;

        // return unsatisfied client id
        return $unsatisfied_client_id;
    }

    public function isTaxExempted(): bool{
        if(!$this->client_data || empty($this->client_data['tax_exempt']) || $this->client_data['tax_exempt'] != 'true') return false;
        return true;
    }
}

new InvoiceFlow();