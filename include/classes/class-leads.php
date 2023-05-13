<?php

class Leads extends GamFunctions{

    use GamValidation;

    function __construct(){
        add_action('wp_ajax_gam_export_leads', array($this, 'gam_export_leads'));
        add_action('wp_ajax_nopriv_gam_export_leads', array($this, 'gam_export_leads'));

        add_action('admin_post_update_lead_status', array($this, 'update_lead_status'));
        add_action('admin_post_nopriv_update_lead_status', array($this, 'update_lead_status'));

        add_action('admin_post_update_lead_status', array($this, 'update_lead_status_desc'));
        add_action('admin_post_nopriv_update_lead_status', array($this, 'update_lead_status_desc'));

        add_action('admin_post_cc_realtor_lead', array($this, 'cc_realtor_lead'));
        add_action('admin_post_nopriv_cc_realtor_lead', array($this, 'cc_realtor_lead'));

        add_action('wp_ajax_update_lead', array($this, 'update_lead'));
        add_action('wp_ajax_nopriv_update_lead', array($this, 'update_lead'));
    }

    public function update_lead(){
        global $wpdb;

        $this->verify_nonce_field('update_lead');

        if(empty($_POST['lead_id'])) $this->response('error', 'Lead id is required');

        if(!isset($_POST['data']) || !is_array(($_POST['data'])) || count($_POST['data']) <= 0)
            $this->response('error', 'Atleast one field is required to update');

        $lead_id = $this->sanitizeEscape($_POST['lead_id']);
        $update_fields = $_POST['data'];

        $allowed_fields = ['status_desc'];

        foreach($update_fields as $key => $field){
            if(!in_array($key, $allowed_fields)) $this->response('error', $field. "is not allowed to be updated or invalid field");
        }

        $response = $this->updateLead($lead_id, $update_fields);
        if(!$response) $this->response('error', $wpdb->last_error);

        $this->response('success', 'Lead data updated successfully');
    }

    public function cc_realtor_lead(){
        global $wpdb;

        $this->verify_nonce_field('cc_realtor_lead');

        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['name']) ||
            empty($_POST['email']) ||
            empty($_POST['phone']) ||
            empty($_POST['notes']) ||
            empty($_POST['realtor_wanna_meet'])
        ) $this->sendErrorMessage($page_url, "please fill all required fields for realtor form");

        $name = $this->sanitizeEscape($_POST['name']);
        $email = $this->sanitizeEscape($_POST['email']);
        $phone = $this->sanitizeEscape($_POST['phone']);
        $notes = $this->sanitizeEscape($_POST['notes']);
        $realtor_wanna_meet = $this->sanitizeEscape($_POST['realtor_wanna_meet']);

        if($realtor_wanna_meet == "yes"){
            if(empty($_POST['appointement_date'])) $this->sendErrorMessage($page_url, "Appointment date is required if realtor want to meet");
        }

        $data = [
            'cold_caller_id'    =>  (new ColdCaller)->getLoggedInColdCallerId(),
            'name'              =>  $name,
            'email'             =>  $email,
            'phone'             =>  $phone,
            'notes'             =>  $notes,
            'date'              =>  date('Y-m-d')
        ];

        if($realtor_wanna_meet == "yes"){
            $data['appointement_date'] = $this->sanitizeEscape($_POST['appointement_date']);
        }
        
        $this->beginTransaction();

        list($response, $responseMsg) = $this->createRealtorLead($data);
        if(!$response) $this->rollBackTransaction($page_url, $responseMsg);

        $this->commitTransaction();

        $message = "Realtor lead created successfully";
        $this->setFlashMessage($message, "success");

        wp_redirect($page_url);
    }

    public function createRealtorLead(array $data){
        global $wpdb;

        $debug =  constant('WP_DEBUG');

        // set lead type as realtor
        $data['lead_type'] = 'realtor';
        $response =  $wpdb->insert($wpdb->prefix."leads", $data);
        if(!$response) return [false, $debug ? $wpdb->last_error : 'Unable to create realtor record in system'];

        $lead_id = $wpdb->insert_id;

        // if appointemnt date is set , create task to set on calendar
        if(!empty($data['appointement_date'])){
            $response = (new OfficeTasks)->setRealtorAppointement($lead_id);
            if(!$response) return [false, 'Unable to create appointement task for realtor'];
        }

        $response = (new Emails)->sendColdCallerRealtorEmail($lead_id);
        
        $email_status = $response ? 1 : 0;
        $response = $this->updateLead($lead_id, ['email_status' => $email_status]);
        if(!$response) return [false, $debug ? $wpdb->last_error : 'Unable to update email status for realtor lead'];

        return [true, null];
    }


    public function update_lead_status(){
        global $wpdb;

        $page_url = $_POST['_wp_http_referer'];

        $this->verify_nonce_field('update_lead_status');

        if(
            empty($_POST['lead_status']) ||
            empty($_POST['lead_id'])
        ) $this->sendErrorMessage($page_url);

        $lead_status = $this->sanitizeEscape($_POST['lead_status']);
        $lead_id = $this->sanitizeEscape($_POST['lead_id']);

        $respose = $this->updateLead($lead_id, ['status_id' => $lead_status]);
        if(!$respose) $this->sendErrorMessage($page_url, "unable to update lead status");

        $message = "Lead status updated successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function updateLead(int $lead_id, array $data){
        global $wpdb;

        $response = $wpdb->update($wpdb->prefix."leads", $data, ['id' => $lead_id]);
        return $response === false ? false : true;
    }

    public function gam_export_leads(){
        global $wpdb;

        $conditions = [];

        if(!empty($_POST['cold_caller'])){
            $conditions[] = " L.cold_caller_id='{$_POST['cold_caller']}'"; 
        }
        
        if(!empty($_POST['from_date'])){
            $conditions[] = " DATE(L.date) >= '{$_POST['from_date']}' ";
        }
        
        if(!empty($_POST['to_date'])){
            $conditions[] = " DATE(L.date) <= '{$_POST['to_date']}' ";
        }

        if(!empty($_POST['role_id'])){
            $role_id = sanitize_text_field($_POST['role_id']);
            $linked_callers = (new ColdCallerRoles)->getLinkedColdCallers($role_id);
            $linked_callers = implode("','", $linked_callers);
        
            $conditions[] = " E.id in ('$linked_callers') ";
        }
        
        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

        $leads = $wpdb->get_results("
            select L.*,C.name as cold_caller
            from {$wpdb->prefix}leads L

            left join {$wpdb->prefix}cold_callers C
            on L.cold_caller_id=C.id

            left join {$wpdb->prefix}employees E
            on E.employee_ref_id = C.id    

            $conditions
        ");

        $export_data = [];
        $export_data[] = [
            'Cold Caller',
            'Establishment Name',
            'Name',
            'Email',
            'Phone',
            'Address',
            'Date'
        ];

        foreach($leads as $lead){
            $export_data[] = [
                $lead->cold_caller,
                $lead->establishment_name,
                $lead->name,
                $lead->email,
                $lead->phone,
                $lead->address,
                $lead->date,
            ];
        }

        $this->response('success','data', $export_data);
    }

    public function getLeadStauses(){
        global $wpdb;

        return $wpdb->get_results("
            select *
            from {$wpdb->prefix}lead_status_list
        ");
    }

    public function getLead(int $lead_id){
        global $wpdb;

        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}leads
            where id = '$lead_id'
        ");
    }

    public function isLeadExist(array $data){
        global $wpdb;

        if(
            empty($data['email']) &&
            empty($data['phone']) &&
            empty($data['address'])
        ) return [false, 'Email, phone and address all cannot be empty. Please provide atleast one field'];        

        $conditions = [];

        if(!empty($data['address'])) $conditions[] = " address = '{$data['address']}' ";
        if(!empty($data['phone'])) $conditions[] = " phone = '{$data['phone']}' ";
        if(!empty($data['email'])) $conditions[] = " email = '{$data['email']}' ";

        $where_condition = "where ".implode(' or ', $conditions);

        $lead_id = $wpdb->get_var("
            select id
            from {$wpdb->prefix}leads
            $where_condition
        ");

        return $lead_id ? [$lead_id, null] : [null, $wpdb->last_error];
    }

    public function generateSourceUrl(array $data){

        $required_fields = [
            'source',
            'source_id'
        ];

        list($response, $message) = $this->requiredValidation($required_fields, $data);
        if(!$response) return [false, $message];

        switch ($data['source']) {
            case 'residential_quote':
                $url = (new Quote)->getResidentialQuoteUrl($data['source_id']);
                return [$url, null];
            break;
            case 'commercial_quote':
                $url = (new Quote)->getCommercialQuoteUrl($data['source_id']);
                return [$url, null];
            break;
            case 'invoice':
                $url = (new Invoice)->adminInvoiceViewPageUrl($data['source_id']);
                return [$url, null];
            break;
            case 'monthly_maintenance':
                return ['Url not available right now', null];
            break;
            case 'quarterly_maintenance':
                return ['Url not available right now', null];
            break;
            case 'special_maintenance':
                return ['Url not available right now', null];
            break;
            case 'commercial_maintenance':
                return ['Url not available right now', null];
            break;
            
            default:
                return [false, 'Source not found in system'];
            break;
        }
    }

    public function updateLeadStatus(array $data){
        global $wpdb;

        $required_fields = [
            'status',
            'lead_id',
            'source',
            'source_id'
        ];

        list($response, $message) = $this->requiredValidation($required_fields, $data);
        if(!$response) return [false, $message];

        $lead_status_id = $this->getStatusIdBySlug($data['status']);
        if(!$lead_status_id) return [false, 'lead status id not found for the given status'];

        // create task for office if lead is closed
        if($data['status'] == 'closed'){
            (new OfficeTasks)->closedLeadExplanation($data['lead_id'], $data['source']);
        }

        list($source_url, $message) = $this->generateSourceUrl($data);
        if(!$source_url) return[false, $message];

        $status_desc = "
            <p>Source : ".$this->beautify_string($data['source'])." </p>
            <p>Source Link : $source_url </p>
        ";

        $update_data = [
            'status_id'     =>  $lead_status_id,
            'status_desc'   =>  $status_desc
        ];

        $response = $this->updateLead($data['lead_id'], $update_data);
        if(!$response) return [false, $wpdb->last_error];

        return [true, null];
    }

    public function getStatusIdBySlug(string $slug){
        global $wpdb;
        return $wpdb->get_var("select id from {$wpdb->prefix}lead_status_list where slug = '$slug'");
    }

    public function __quoteLeadStatusMiddleware(array $data){

        if(
            empty($data['address']) ||
            empty($data['phone']) ||
            empty($data['email'])
        ) return [false, 'Atleast one of these fields is required : address, phone, email'];

        if(empty($data['source'])) return [false, 'source is required'];
        if(empty($data['source_id'])) return [false, 'source id is required'];
        if(empty($data['status'])) return [false, 'status is required'];

        if(!empty($data['address'])) $query_data['address'] = $data['address'];
        if(!empty($data['phone'])) $query_data['phone'] = $data['phone'];
        if(!empty($data['email'])) $query_data['email'] = $data['email'];

        $allowed_status = ['pending', 'closed', 'dead'];

        if(!in_array($data['status'], $allowed_status)) return [false, 'provided status is not in allowed list'];

		// check if quote exist, then set it's status to pending, quote will update status for lead as well
		list($quote_id, $quote_type, $message) = (new Quote)->isQuoteExist($query_data);

		if($quote_id){

			$quote_update_data = [
				'quote_id'		=>	$quote_id,
				'quote_status'	=>	$data['status'],
				'source'		=>	$data['source'],
				'source_id'		=>	$data['source_id'],
			];

			if($quote_type == 'residential'){
				list($response, $message) = (new Quote)->updateResidentialQuoteStatus($quote_update_data);
				if(!$response) return [false, $message];
			}
			elseif($quote_type == 'commercial'){
				list($response, $message) = (new Quote)->updateCommercialQuoteStatus($quote_update_data);
				if(!$response) return [false, $message];
			}
			else{
				return [false, 'Linked Quote Error : method - createInvoice'];
			}

		}
		else{
			// if quote does't exist, then check if lead exist and set it's status to pending
			list($lead_id, $message) = $this->isLeadExist($query_data);
			if($lead_id){
				$lead_update_data = [
					'lead_id'		=>	$lead_id,
					'status'		=>	$data['status'],
					'source'		=>	$data['source'],
					'source_id'		=>	$data['source_id']
				];

				list($response, $message) = $this->updateLeadStatus($lead_update_data);
				if(!$response) return [false, $message];

			}
		}

		return [true, null];
	}    
}

new Leads();