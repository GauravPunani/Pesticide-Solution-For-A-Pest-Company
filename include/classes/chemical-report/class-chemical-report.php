<?php

class ChemicalReport extends GamFunctions{

	function __construct(){

        add_action( 'admin_post_nopriv_technician_skip_chemical_report', array($this,'technician_skip_chemical_report'));
		add_action( 'admin_post_technician_skip_chemical_report', array($this,'technician_skip_chemical_report'));
		
		add_action('wp_ajax_get_chemical_reports_data',array($this,'get_chemical_reports_data'));
		add_action('wp_ajax_nopriv_get_chemical_reports_data',array($this,'get_chemical_reports_data'));

		add_action('wp_ajax_get_chemical_reports_addon',array($this,'get_chemical_reports_addon'));
		add_action('wp_ajax_nopriv_get_chemical_reports_addon',array($this,'get_chemical_reports_addon'));

		add_action('wp_ajax_notify_client_service_report',array($this,'send_service_report_notification'));
		add_action('wp_ajax_nopriv_notify_client_service_report',array($this,'send_service_report_notification'));
	}

	public function send_service_report_notification()
    {
        global $wpdb;
        $this->verify_nonce_field('client_service_notify');
		
		$service_data = $_POST['service_data'] ?? '';
		$client_email = sanitize_email($_POST['client_email']);
		if (empty($service_data)) $this->response('error', 'Service report data is missing');
		if (empty($client_email)) $this->response('error', 'Client email is missing');
		
		$jsonData = json_decode(stripslashes($_POST['service_data']));
		$sent = (new Invoice)->send_service_report($jsonData,$client_email);
		if ($sent['status'] != 'success') $this->response('error');
		$res = $wpdb->update($wpdb->prefix . 'office_notes', ['service_report' => 1], ['invoice_id' => $jsonData->invoice_id]);

		if (!$res) $this->response('error');

		$this->response('success', 'Service Report Sent to Client');
	}
	public static function getChemicalsByIds(array $ids){
		global $wpdb;

		$ids = implode("','", $ids);

		return $wpdb->get_col("
			select name
			from {$wpdb->prefix}chemicals
			where id in ('$ids')
		");
	} 

	public function getPestsUsedInService(int $invoice_id){
		global $wpdb;

		$invoice = Invoice::getInvoiceById($invoice_id, ['chemical_report_type', 'chemical_report_id']);
		if(!$invoice) return null;
		
		if($invoice->chemical_report_type == "california" || $invoice->chemical_report_type == "texas"){
			$chemicals = ChemicalReportCalifornia::getChemicals($invoice->chemical_report_id);
			if(!$chemicals) return null;

			$product_ids = array_map(function($chemical){
				return $chemical->product_id;
			}, $chemicals);

			$products = $this->getChemicalsByIds($product_ids);
			if(!$products) return null;

			return implode(' || ', $products);
		}

		if($invoice->chemical_report_type == "newyork"){
			$chemical_ids = explode(',',str_replace(array('[',']'),'',$invoice->chemical_report_id));
			
			$chemicals = ChemicalReportCalifornia::getChemicalsNewyork($chemical_ids,['id','chemical_id']);
			
			$product_ids = array_column($chemicals, 'chemical_id');
			
			$products = $this->getChemicalsByIds($product_ids);
			if(!$products) return null;

			return implode(' || ', $products);
		}
	}

	public function get_all_chemicals(){
		global $wpdb;
		return $wpdb->get_results("select * from {$wpdb->prefix}chemicals");
	}

	public function get_chemical_reports_addon(){
		
		if(!empty($_POST['branch'])){
			switch ($_POST['branch']) {

				case 'ny_metro':
					get_template_part('/include/frontend/chemical-reports/new-york-addon');
				break;
				case 'california':
					get_template_part('/include/frontend/chemical-reports/california-addon');
				break;
				case 'newjersey':
					get_template_part('/include/frontend/chemical-reports/newjersey-chemical-template');
				break;
				case 'florida':
					get_template_part('include/frontend/chemical-reports/florida-chemical-template');
				break;
				case 'texas':
					get_template_part('include/frontend/chemical-reports/product-details-texas');
				break;

				default:
					echo "Something went wrong, Please try again later";
				break;
			}			
		}
		else{
			echo "Something went wrong, Please try again later";
		}
		wp_die();
	}

	public function get_chemical_reports_data(){

		if(isset($_POST['branch']) && !empty($_POST['branch'])){
			switch ($_POST['branch']) {

				case 'ny_metro':
					get_template_part("/include/frontend/chemical-reports/newyork-chemical-report");
				break;
				case 'california':
					get_template_part("/include/frontend/chemical-reports/chemical-report-california");
				break;

				case 'florida':
					get_template_part("/include/frontend/chemical-reports/florida-chemical-report");
				break;
				
				case 'new_jersey':
					get_template_part("/include/frontend/chemical-reports/newjersey-chemical-report");
				break;
				
				case 'texas':
					get_template_part("/include/frontend/chemical-reports/chemical-report-texas");
				break;
				default:
				break;
			}			
		}
		else{
			echo "location is not set";
		}

		wp_die();
	}

    public function technician_skip_chemical_report(){

		$this->verify_nonce_field('skip_chemical_report');

		if(!empty($_POST['technician_appointment'])){
			global $wpdb;
			$data=[
				'event_id'	=>	$_POST['technician_appointment'],
				'type'		=>	$_POST['type'],
			];
			$wpdb->insert($wpdb->prefix."bypassed_events",$data);
		}

		(new InvoiceFlow)->callNextPageInFlow();
	}
	
	public function get_all_organisms(){
		global $wpdb;
		return $wpdb->get_results("select * from {$wpdb->prefix}organisms");
	}
    
}

new ChemicalReport();