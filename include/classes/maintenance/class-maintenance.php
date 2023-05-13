<?php

class Maintenance extends ContractTemplates{
	
	function __construct(){

		add_action( 'wp_ajax_nopriv_delete_maintenance_record',array($this,'delete_maintenance_record'));
		add_action( 'wp_ajax_delete_maintenance_record', array($this,'delete_maintenance_record'));

        add_action( 'wp_ajax_send_signature_email', array($this,'send_signature_email'));
        add_action( 'wp_ajax_nopriv_send_signature_email', array($this,'send_signature_email'));

        add_action( 'wp_ajax_sms_maintenance_contract_link', array($this,'sms_maintenance_contract_link'));
        add_action( 'wp_ajax_nopriv_sms_maintenance_contract_link', array($this,'sms_maintenance_contract_link'));

        add_action('admin_post_nopriv_regular_contract_download', array($this, 'download_regular_contract'));
        add_action('admin_post_regular_contract_download', array($this, 'download_regular_contract'));
    }

    public function download_regular_contract(){
        global $wpdb;

        $this->verify_nonce_field('regular_contract_download');

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['contract_id'])
        ) {
            $this->sendErrorMessage($page_url);
        }

        $contract_id = $this->sanitizeEscape($_POST['contract_id']);
        $downloadContract = (new MonthlyQuarterlyMaintenance)->monthly_maintenance_template($contract_id);
        // load sendgrid php sdk from vendor
        self::loadVendor();

        $mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
        $mpdf->WriteHTML($downloadContract);
        $title = sprintf('maintenance_contract_%s', $contract_id);
        $mpdf->Output("$title.pdf", "D");
        return;
    }

    public function sms_maintenance_contract_link(){
        
        $this->verify_nonce_field('sms_maintenance_contract_link');

        if(
            empty($_POST['contract_id']) ||
            empty($_POST['contract_type']) ||
            empty($_POST['phone_no'])
        ) $this->response('error');
        
        $contract_id = $this->sanitizeEscape($_POST['contract_id']);
        $contract_type = $this->sanitizeEscape($_POST['contract_type']);
        $phone_no = $this->sanitizeEscape($_POST['phone_no']);
        
        $response = false;

        switch ($contract_type) {

            case 'monthly':
                $response = (new Twilio)->sendMonthlyMaintenanceLink($contract_id, $phone_no);
            break;

            case 'quarterly':
                $response = (new Twilio)->sendQuarterlyMaintenanceLink($contract_id, $phone_no);
            break;

            case 'special':
                $response = (new Twilio)->sendSpecialMaintenanceLink($contract_id, $phone_no);
            break;

            case 'commercial':
                $response = (new Twilio)->sendCommercialMaintenanceLink($contract_id, $phone_no);
            break;

            default:
                $this->response('error');
            break;
        }

        if(!$response) $this->response('error');

        $this->response('success', 'Contract link messaged to client successfully');
    }

    public function send_signature_email(){

        if(
            empty($_POST['client_email']) ||
            empty($_POST['contract_id']) ||
            empty($_POST['contract_type'])
        ) $this->response('error');

        $client_email = $this->sanitizeEscape($_POST['client_email']);
        $contract_id = $this->sanitizeEscape($_POST['contract_id']);
        $contract_type = $this->sanitizeEscape($_POST['contract_type']);

        $response = (new Emails)->contractCcEmail($contract_id, $contract_type, $client_email);
        if(!$response) $this->response('error');

        $this->response('success','Email sent to client successfully');
    }

	public function delete_maintenance_record(){
        global $wpdb;

        $this->verify_nonce_field('maintenance_script_nonce');

        if(
            empty($_POST['contract_id']) ||
            empty($_POST['contract_type'])
        ) $this->response('error');

        $upload_dir = wp_upload_dir();

        $contract_id = $this->sanitizeEscape($_POST['contract_id']);
        $contract_type = $this->sanitizeEscape($_POST['contract_type']);        

        switch ($contract_type) {
            case 'monthly':
            case 'quarterly':
                $db_table_name="maintenance_contract";
            break;
            case 'special':
                $db_table_name="special_contract";
            break;
            case 'commercial':
                $db_table_name="commercial_maintenance";
            break;
            default:
                $this->response('error');
            break;
        }

        // delete the pdf for maintenanace plan first by getting the pdf path
        $pdf_path = $wpdb->get_var("
            select pdf_path 
            from {$wpdb->prefix}$db_table_name 
            where id='$contract_id'
        ");

        if($pdf_path) unlink($upload_dir['basedir'].$pdf_path);

        $response = $wpdb->delete($wpdb->prefix.$db_table_name, ['id' => $contract_id]);
        if(!$response) $this->response('error');

        $this->response('success', 'Contract deleted Successfully');
	}
    
	public function genereate_token($length_of_token) { 
	  
		// String of all alphanumeric character 
		$str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
	  
		// Shufle the $str_result and returns substring of specified length 
		return substr(str_shuffle($str_result),  
						   0, $length_of_token); 
	}

	public function mail_template( bool $rodent_included = false){
        $file_path = get_template_directory()."/template/maintenance-forms/disclaimer.html";
        return file_get_contents($file_path);
    }

    public function getCcDetailPageLink(){
        return site_url()."/maintenance-cc-information";
    }

    public function isClientCcVerificationForm(): bool{

        if(empty($_GET['contract-id']) ) return false;

        $contract_id = $this->sanitizeEscape($_GET['contract-id']);
        $contract_id = $this->encrypt_data($contract_id, 'd');
        if(!$contract_id) return false;

        return true;
    }

    public function __codeValidation(){
        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['db_id']) || empty($_POST['db_code'])) $this->sendErrorMessage($page_url, 'unable to verify code 1');

        $db_id = $this->encrypt_data($_POST['db_id'], 'd');
        $db_code = $this->sanitizeEscape($_POST['db_code']);

        if(!(new Codes)->verifyCode($db_id, $db_code, true)) $this->sendErrorMessage($page_url, 'unable to verify code 2');
    }

    public function monthlyMaintenanceLandinagePagerUrl(){
        return site_url().'/monthly-pest-control';
    }

    public function springTreatmentPageUrl(){
        return site_url().'/spring-treatment';
    }

    public function returnCagePageUrl(){
        return site_url().'/return-cage';
    }
    
    public function renewContractPageUrl(){
        return site_url().'/renew-maintenance';
    }

    public function monthlyPageUrl(){
        return site_url().'/monthly-maintenance';
    }

    public function quarterlyPageUrl(){
        return site_url()."/quarterly-maintenance";
    }

    public function specialPageUrl(){
        return site_url()."/special-maintenance";
    }

    public function commercialPageUrl(){
        return site_url()."/commercial-maintenance-contract";
    }

    public function yearlyTermitePageUrl(){
        return site_url()."/yearly-termite-contract";
    }

    public function thankyouPageUrl(){
        return site_url()."/thank-you-maintenance-contract";
    }

	public function fakeEmailAlertMessage(){
		return '<i class="text-danger">Please do not enter any fake email address in email field. Make sure email is an actuall email, if client did\'t provided any than use office email address.</i>';
	}

    public function getContract(string $contract_type, int $contract_id, array $columns = []){

		if($contract_type == "monthly" || $contract_type == "quarterly"){
			$contract = (new MonthlyQuarterlyMaintenance)->getContractById($contract_id, $columns);
		}
		elseif ($contract_type == "special"){
			$contract = (new SpecialMaintenance)->getContractById($contract_id, $columns);
		}
		elseif($contract_type == "commercial"){
			$contract = (new CommercialMaintenance)->getContractById($contract_id, $columns);
		}
		else{
			return false;
		}

        return $contract;

    }

    public function getContractBasicInfo(string $contract_type, object $contract){

        $basic_info = new stdClass();
        if($contract_type == "monthly" || $contract_type == "quarterly"){
            $basic_info->name = $contract->client_name;
            $basic_info->address = $contract->client_address;
            $basic_info->phone = $contract->client_phone_no;
            $basic_info->email = $contract->client_email;
        }
        elseif($contract_type == "commercial"){
            $basic_info->name = $contract->person_in_charge;
            $basic_info->address = $contract->client_address;
            $basic_info->phone = $contract->res_person_in_charge_phone_no;
            $basic_info->email = $contract->client_email;            
        }
        elseif($contract_type == "special"){
            $basic_info->name = $contract->client_name;
            $basic_info->address = $contract->client_address;
            $basic_info->phone = $contract->client_phone;
            $basic_info->email = $contract->client_email;   
        }
        else{
            return false;
        }

        return $basic_info;
    }

    public function includedExludedPests(string $included_pests_string){

        $included_pests =  DB::table('pests')
                    ->where(function() use ($included_pests_string) {
                        return "id in ($included_pests_string)";
                    })
                    ->get();
        
        $excluded_pests =  DB::table('pests')
                            ->where(function() use ($included_pests_string) {
                                return "id not in ($included_pests_string)";
                            })
                            ->get();

        $pests_html = "<p><b>Included Pests</b></p>";

        if(is_array($included_pests)){
            $pests_html .= "<ul>";
            foreach($included_pests as $pest){
                $pests_html .= "<li>$pest->name</li>";
            }
            $pests_html .= "</ul>";
        }

        $pests_html .= "<p><b>Excluded Pests</b></p>";

        if(is_array($excluded_pests)){
            $pests_html .= "<ul>";
            foreach($excluded_pests as $pest){
                $pests_html .= "<li>$pest->name</li>";
            }
            $pests_html .= "</ul>";
        }

        return $pests_html;
    }

    public function creditCardDetailsLabel(object $card_details){
        return "
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
    }
}

new Maintenance();