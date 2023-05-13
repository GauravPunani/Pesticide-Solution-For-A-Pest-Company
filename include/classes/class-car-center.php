<?php

class CarCenter extends Technician_details{

    use GamValidation;

    function __construct(){

        add_action('admin_post_upload_pesticide_decal_proof',array($this,'upload_pesticide_decal_proof'));
        add_action('admin_post_nopriv_upload_pesticide_decal_proof',array($this,'upload_pesticide_decal_proof'));

        add_action('admin_post_confirm_current_vehicle',array($this,'confirm_current_vehicle'));
        add_action('admin_post_nopriv_confirm_current_vehicle',array($this,'confirm_current_vehicle'));

        add_action('admin_post_upload_mileage',array($this,'upload_mileage'));
        add_action('admin_post_nopriv_upload_mileage',array($this,'upload_mileage'));

        add_action('admin_post_upload_oil_change',array($this,'upload_oil_change'));
        add_action('admin_post_nopriv_upload_oil_change',array($this,'upload_oil_change'));

        add_action('wp_ajax_upload_vehicle_condition',array($this,'upload_vehicle_condition'));
        add_action('wp_ajax_nopriv_upload_vehicle_condition',array($this,'upload_vehicle_condition'));

        add_action('admin_post_create_link_vehicle',array($this,'create_link_vehicle'));
        add_action('admin_post_nopriv_create_link_vehicle',array($this,'create_link_vehicle'));

        add_action('admin_post_create_vehicle',array($this,'create_vehicle'));
        add_action('admin_post_nopriv_create_vehicle',array($this,'create_vehicle'));

        add_action('admin_post_link_vehicle',array($this,'link_vehicle'));
        add_action('admin_post_nopriv_link_vehicle',array($this,'link_vehicle'));

        add_action('admin_post_edit_vehicle_information',array($this,'edit_vehicle_information'));
        add_action('admin_post_nopriv_edit_vehicle_information',array($this,'edit_vehicle_information'));

        add_action('admin_post_upload_break_pads_change_proof',array($this,'upload_break_pads_change_proof'));
        add_action('admin_post_nopriv_upload_break_pads_change_proof',array($this,'upload_break_pads_change_proof'));

        add_action('admin_post_update_mileage_fields_information',array($this,'update_mileage_fields_information'));
        add_action('admin_post_nopriv_update_mileage_fields_information',array($this,'update_mileage_fields_information'));

        add_action('wp_ajax_approve_reject_mileage_proof',array($this,'approve_reject_mileage_proof'));
        add_action('wp_ajax_nopriv_approve_reject_mileage_proof',array($this,'approve_reject_mileage_proof'));

        add_action('wp_ajax_approve_reject_oil_change_proof',array($this,'approve_reject_oil_change_proof'));
        add_action('wp_ajax_nopriv_approve_reject_oil_change_proof',array($this,'approve_reject_oil_change_proof'));

        add_action('wp_ajax_approve_reject_break_pads_change_proof',array($this,'approve_reject_break_pads_change_proof'));
        add_action('wp_ajax_nopriv_approve_reject_break_pads_change_proof',array($this,'approve_reject_break_pads_change_proof'));

        add_action('wp_ajax_approve_reject_vehicle_condition_proof',array($this,'approve_reject_vehicle_condition_proof'));
        add_action('wp_ajax_nopriv_approve_reject_vehicle_condition_proof',array($this,'approve_reject_vehicle_condition_proof'));

        add_action('wp_ajax_approve_reject_car_wash_proof',array($this,'approve_reject_car_wash_proof'));

        add_action('admin_post_update_vehicle_status',array($this,'update_vehicle_status'));
        add_action('admin_post_nopriv_update_vehicle_status',array($this,'update_vehicle_status'));

		
		add_action('admin_post_update_parking_address_information',array($this,'update_parking_address_information'));
        add_action('admin_post_nopriv_update_parking_address_information',array($this,'update_parking_address_information'));

		add_action('admin_post_unlink_vehicle_from_technician',array($this,'unlink_vehicle_from_technician'));
        add_action('admin_post_nopriv_unlink_vehicle_from_technician',array($this,'unlink_vehicle_from_technician'));

        // Car wash proof
        add_action('wp_ajax_car_wash_upload_proof',array($this,'car_wash_upload_proof'));
        add_action('wp_ajax_nopriv_car_wash_upload_proof',array($this,'car_wash_upload_proof'));

        add_action( 'wp_ajax_nopriv_delete_car_center', array($this,'delete_car_center') );
        add_action( 'wp_ajax_delete_car_center', array($this,'delete_car_center') );

        add_action('wp_ajax_swap_car_within_technician',array($this,'swap_tech_cars'));
        add_action('wp_ajax_nopriv_swap_car_within_technician',array($this,'swap_tech_cars'));

    }

     // Delete Car Center
     public function delete_car_center(){
        global $wpdb;

        $this->verify_nonce_field('delete_car_center');

        if(empty($_POST['vehicle_id'])) $this->response('error');

        $vehicle_id = sanitize_text_field($_POST['vehicle_id']);
        $wpdb->delete($wpdb->prefix . "vehicles", ['id' => $vehicle_id]);
        $this->response('success');
    }

    // swap technician cars
    public function swap_tech_cars(){
        global $wpdb;
        $this->verify_nonce_field('swap_car_within_technician');

        // check for validation
        if(
            empty($_POST['tech_from'][0]) ||
            empty($_POST['vehicle_id']) ||
            empty($_POST['tech_with'][0])
        ) $this->response('error', "Swap between Technician details are missing.");

        $case_sql = [];
        for ($x = 0; $x < count($_POST['tech_from']); $x++) {
           $case_sql[]= 'when id='.$_POST['tech_from'][$x].' then '.$this->getTechnicianVehicleId($_POST['tech_with'][$x]).'';
           $case_sql[]= 'when id='.$_POST['tech_with'][$x].' then '.$this->getTechnicianVehicleId($_POST['tech_from'][$x]).'';
        }

        $update_query = "
            UPDATE 
                {$wpdb->prefix}technician_details
            SET 
                vehicle_id = (
            case 
                ".implode(PHP_EOL, $case_sql)."
            else
                vehicle_id
            END
            )
        ";

        $result = $wpdb->query($update_query);
        if (FALSE === $result) {
            $this->response('error','Opps ! Car swapping failed please try again');
        } else {
            $this->response('success','Car Swapping Completed Successfully');
        }
    }
    
    // car wash proof
    public function car_wash_upload_proof(){
        global $wpdb;

		$this->verify_nonce_field('upload_car_wash_change_proof');

        $technician_id = $this->get_technician_id();

        $vehicle_id = $this->getTechnicianVehicleId($technician_id);

         // check for pending approval first
        if((new Notices)->isNoticeAlreadyExist($technician_id, 'pending_car_wash_proof_approval')){
            $message = 'You have already submitted car wash proof please wait for office decision.';
            $this->response('error', $message);
        }

        // check at least one image exist
		if(!isset($_FILES['car_wash_proof']) || empty($_FILES['car_wash_proof']['name'][0])){
			$this->response('error', 'Car wash proof image is required');
		}

        // loop each item
		$car_wash_proof = $this->uploadFiles($_FILES['car_wash_proof']);		

        // car data to save
		$car_wash_data=[
            'vehicle_id'			=>	$vehicle_id,
			'technician_id'			=>	$technician_id,
			'proof_of_wash'			=>	json_encode($car_wash_proof)
		];

        // check if current month car wash proof already uploaded 
        $status = self::isCarWashMonthProofExist($technician_id,$vehicle_id);

        if($status){
            $message="You'd already uploaded the car wash proof for this month";
            $this->response('error', $message);
        }

		$this->beginTransaction();

		$response = $wpdb->insert($wpdb->prefix."car_wash_proof",$car_wash_data);
		if(!$response) $this->rollbackResponse('error');

        // delete car wash proof notice from tech dashboard
        (new Notices)->deleteAccountNotices($technician_id, 'vehicle_car_wash_proof_required');
        (new Notices)->deleteAccountNotices($technician_id, 'rejected_car_wash_proof');

        // add new notice in tech dashboard for office approval
        $message="You need to get your car wash proof approved from office within 2 days in order to avoid account freeze.";
        $data=[
            'type'              =>  'pending_car_wash_proof_approval',
            'level'             =>  'normal',
            'class'             =>  'info',
            'notice'            =>  $message,
            'date'              =>  date('Y-m-d'),
            'technician_id'     =>  $technician_id
        ];  
        $response = (new Notices)->generateTechnicianNotice($data, true);

		if(!$response) $this->rollbackResponse('error');

		$this->commitTransaction();

		$this->response('success', 'Car wash proof uploaded successfully');
	}

    public function isCarWashMonthProofExist(int $technician_id, int $vehicle_id){
        global $wpdb;
        return $wpdb->get_var("
            select COUNT(*) 
            from {$wpdb->prefix}car_wash_proof 
            where technician_id='$technician_id' 
            and vehicle_id='$vehicle_id' 
            and MONTH(created_at)=MONTH(now()) 
            and status='approved'
        ");
    }

    public function isVehicleProofExist(array $data){
        global $wpdb;
        return $wpdb->get_var("
            select COUNT(*) 
            from {$wpdb->prefix}{$data['tbl']} 
            {$data['where']}
        ");
    }

    public function unlink_vehicle_from_technician(){
        global $wpdb;

        $this->verify_nonce_field('unlink_vehicle_from_technician');

        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['vehicle_id']) ||
            empty($_POST['parking_address'])
        ) $this->sendErrorMessage($page_url, "vehicle id and parking address are required");

        $data = [
            'vehicle_id'        =>  $this->sanitizeEscape($_POST['vehicle_id']),
            'parking_address'   =>  $this->sanitizeEscape($_POST['parking_address'])
        ];

        list($response, $message) = $this->unlinkVehicle($data);
        if(!$response) $this->sendErrorMessage($page_url, $message);

        $message = "Vehicle unlinked from technician successfully";
        $this->setFlashMessage($message, "success");

        wp_redirect($page_url);
    }

    public function setVehiclePlacedInGarbage(int $vehicle_id, string $description){
        global $wpdb;

        // unlink from technician
        $update_data = ['vehicle_id' => ''];
        $response = $wpdb->update($wpdb->prefix."technician_details", ['vehicle_id' => ''], ['vehicle_id' => $vehicle_id]);
        if($response === false) return [false, $wpdb->last_error];

        $slug = "placed_in_garbage";
        $status_id = $this->getVehicleStatusIdBySlug($slug);
        if(!$status_id) return [false, 'Unable to find status by slug'];

        // update vehicle status and description to placed in garbage
        $update_data = [
            'status_id'             =>  $status_id,
            'status_description'    =>  $description,
            'parking_address'       =>  '*No Longer needed'
        ];

        $response = $this->updateVehicle($vehicle_id, $update_data);
        if(!$response) return [false, $wpdb->last_error];

        return [true, null];
    }

    public function setTechNoLongerUsingHisVehicle(int $vehicle_id){
        global $wpdb;

        // unlink from technician if still linked
        $update_data = ['vehicle_id' => ''];
        $response = $wpdb->update($wpdb->prefix."technician_details", ['vehicle_id' => ''], ['vehicle_id' => $vehicle_id]);
        if($response === false) return [false, $wpdb->last_error];

        $slug = "technician_no_longer_using_this_vehicle";
        $status_id = $this->getVehicleStatusIdBySlug($slug);
        if(!$status_id) return [false, 'Unable to find status by slug'];

        // update vehicle status and description to tech no longer using this vehicle
        $update_data = [
            'status_id'             =>  $status_id,
            'status_description'    =>  '',
            'parking_address'       =>  ''
        ];

        $response = $this->updateVehicle($vehicle_id, $update_data);
        if(!$response) return [false, $wpdb->last_error];

        return [true, null];        
    }

    public function setVehicleSold(int $vehicle_id, string $description){
        global $wpdb;

        // unlink from technician
        $update_data = ['vehicle_id' => ''];
        $response = $wpdb->update($wpdb->prefix."technician_details", ['vehicle_id' => ''], ['vehicle_id' => $vehicle_id]);
        if($response === false) return [false, $wpdb->last_error];

        $slug = "sold";
        $status_id = $this->getVehicleStatusIdBySlug($slug);
        if(!$status_id) return [false, 'Unable to find status by slug'];

        // update vehicle status and description to sold
        $update_data = [
            'status_id'             =>  $status_id,
            'status_description'    =>  $description,
            'parking_address'       =>  '*No Longer needed'
        ];

        $response = $this->updateVehicle($vehicle_id, $update_data);
        if(!$response) return [false, $wpdb->last_error];

        return [true, null];
    }

    public function updateVehicleStatus(array $data){

        if(empty($data['status_id'])) return [false, 'status_id is required'];
        if(empty($data['vehicle_id'])) return [false, 'vehicle_id is required'];

        $status = $this->getStatusById($data['status_id']);
        if(!$status) return [false, 'Unable to fetch status by id'];

        $vehicle_id = $data['vehicle_id'];

        // fields chekcing if present 
        if($status->slug == "assigned_to_employee" && (empty($data['parking_address']) || empty($data['technician_id'])))
            return [false, 'Parking address and technician id is required if assigned to technician'];
        elseif($status->slug == "parked_somewhere_secure" && empty($_POST['parking_address']))
            return [false, 'Parking address is required if selected "parked somewhere secure" '];
        elseif(($status->slug == "placed_in_garbage" || $status->slug == "sold") && empty($data['status_description']))
            return [false, 'Descripiton is required if setting to garbage or sold'];

        // link vehicle to technician and it will automaticaly update vehicle status_id and status_description
        switch($status->slug){
            
            case 'assigned_to_employee':
                list($response, $message) = $this->linkVehicle(
                    $vehicle_id, 
                    $data['technician_id'], 
                    $data['parking_address']
                );
                if(!$response) return [false, $message];                
            break;

            case 'placed_in_garbage':
                list($response, $message) = $this->setVehiclePlacedInGarbage($vehicle_id, $data['status_description']);
                if(!$response) return [false, $message];
            break;

            case 'sold':
                list($response, $message) = $this->setVehicleSold($vehicle_id, $data['status_description']);
                if(!$response) return [false, $message];
            break;

            case 'parked_somewhere_secure':
                list($response, $message) = $this->setVehicleParkedSomewhereSecure($vehicle_id, $data['parking_address']);
                if(!$response) return [false, $message];
            break;

            case 'technician_no_longer_using_this_vehicle':
                list($response, $message) = $this->setTechNoLongerUsingHisVehicle($vehicle_id);
                if(!$response) return [false, $message];
            break;

            default:
                return [false, 'Something went wrong, please try again later'];
            break;
        }

        return [true, null];
    }

    public function getStatusById(int $status_id){
        global $wpdb;
        return $wpdb->get_row("select * from {$wpdb->prefix}vehicle_status where id = '$status_id'");
    }

    public function create_vehicle(){
        
        $this->verify_nonce_field('create_vehicle');

        $page_url = esc_url_raw($_POST['page_url']);

        $status_slug = "parked_somewhere_secure";
        $status_id = $this->getVehicleStatusIdBySlug($status_slug);
        if(!$status_id) $this->sendErrorMessage($page_url, "Unable to find status id");

        $_POST['status_id'] = $status_id;

        list($response, $message) = $this->createVehicle($_POST, $_FILES);
        if(!$response) $this->sendErrorMessage($page_url, $message);

        $message = "Vehicle created successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }
    

    public function link_vehicle(){

        $this->verify_nonce_field('link_vehicle');

        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['technician_id']) ||
            empty($_POST['vehicle_id']) ||
            empty($_POST['parking_address'])
        ) $this->sendErrorMessage($page_url, "Technician id, vehicle id and parkinga address are required");

        $technician_id = $this->sanitizeEscape($_POST['technician_id']);
        $vehicle_id = $this->sanitizeEscape($_POST['vehicle_id']);
        $parking_address = $this->sanitizeEscape($_POST['parking_address']);

        list($response, $message) = $this->linkVehicle($vehicle_id, $technician_id, $parking_address);
        if(!$response) $this->sendErrorMessage($page_url, $message);

        $message = "Vehicle linked to technician account successfully";
        $this->setFlashMessage($message, "success");

        wp_redirect($page_url);
    }

    public function update_vehicle_status(){
        global $wpdb;

        $this->verify_nonce_field('update_vehicle_status');

        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['status_slug']) ||
            empty($_POST['vehicle_id'])
        ) $this->sendErrorMessage($page_url);

        $status_slug = $this->sanitizeEscape($_POST['status_slug']);

        if($status_slug == "assigned_to_employee" && ( empty($_POST['technician_id']) || empty($_POST['parking_address']) ) )
            $this->sendErrorMessage($page_url, "Parking address and technician id is required if vehicle assigned to employee");
        elseif($status_slug == "parked_somewhere_secure" && empty($_POST['parking_address']))
            $this->sendErrorMessage($page_url, "Parking address is required if parked somewhere secure");
        elseif(($status_slug == "placed_in_garbage" || $status_slug == "sold") && empty($_POST['status_description']))
            $this->sendErrorMessage($page_url, "Descripiton is required if setting to garbage or sold");

        $status_id = $this->getVehicleStatusIdBySlug($status_slug);
        if(!$status_id) $this->sendErrorMessage($page_url, "selected status not found in system");

        $data = [
            'status_id'             =>  $status_id,
            'vehicle_id'            =>  $this->sanitizeEscape($_POST['vehicle_id']),            
        ];

        if($status_slug == "assigned_to_employee"){
            $data['technician_id'] = $this->sanitizeEscape($_POST['technician_id']);
            $data['parking_address'] = $this->sanitizeEscape($_POST['parking_address']);
        }
        elseif($status_slug == "parked_somewhere_secure"){
            $data['parking_address'] = $this->sanitizeEscape($_POST['parking_address']);
        }
        elseif($status_slug == "sold" || $status_slug == "placed_in_garbage"){
            $data['status_description'] = $this->sanitizeEscape($_POST['status_description'], 'textarea');
        }

        $this->beginTransaction();

        list($response, $message) = $this->updateVehicleStatus($data);
        if(!$response) $this->rollBackTransaction($page_url, $message);

        $this->commitTransaction();

        $message = "Vehicle status updated successfully";
        $this->setFlashMessage($message, "success");

        wp_redirect($page_url);
    }

    /*
        This method confirms if tech have same vehicle else generate notice to office
    */
    public function confirm_current_vehicle(){
        global $wpdb;

        $this->verify_nonce_field('confirm_current_vehicle');

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['confirm_vehicle'])) $this->sendErrorMessage($page_url);

        $confirm_vehicle = $this->sanitizeEscape($_POST['confirm_vehicle']);

        $technician_id = $this->get_technician_id();
        $vehicle_id = $this->getTechnicianVehicleId($technician_id);

        $this->beginTransaction();

        // set last confirmed to today so system don't ask it again
        if($confirm_vehicle == "yes"){
            $message="Thanks for confirming your current vehicle";
            $this->setFlashMessage($message,'success');
        }
        else{

            $response = $this->requestForLinkNewVehicle($technician_id);
            if(!$response) $this->rollBackTransaction($page_url);

            $message="Please create/link your vehicle with your account.";
            $this->setFlashMessage($message, 'info');
        }

        $data = ['last_confirmed'    =>  date('Y-m-d')];
        $response = $this->updateVehicle($vehicle_id, $data);
        if(!$response) $this->rollBackTransaction($page_url);        

        // delete verification notice
        (new Notices)->deleteAccountNotices($technician_id, 'requestForVehicleVerification');

        $this->commitTransaction();

        if($confirm_vehicle == "no") $page_url = $this->createVehiclePageUrl();

        wp_redirect($page_url);
    }

    public function getVehicleConditionProof(int $proof_id){
        global $wpdb;

        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}vehilce_inspection
            where id='$proof_id'
        ");
    }

    public function getCarConditionProof(int $proof_id){
        global $wpdb;

        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}car_wash_proof
            where id='$proof_id'
        ");
    }

    public function approve_reject_car_wash_proof(){
        global $wpdb;

		$this->verify_nonce_field('approve_reject_car_wash_proof');

        if(empty($_POST['proof_id'])) $this->response('error');
        if(empty($_POST['status'])) $this->response('error');

        $proof_id = sanitize_text_field($_POST['proof_id']);
        $status = sanitize_text_field($_POST['status']);

        if($status == "reject" && empty($_POST['notes'])) $this->response('error');
        if($status == "reject") $notes = sanitize_textarea_field($_POST['notes']);

        $this->beginTransaction();

        $proof = $this->getCarConditionProof($proof_id);
        if(!$proof) $this->rollbackResponse('error');

        if($status == "approve"){
            
            // update the status in database 
            $update_data  = ['status' => 'approved'];
            $response = $this->updateCarConditionRecord($proof_id, $update_data);
            if(!$response) $this->rollbackResponse('error');

            // delete the proof notice from tech dashboard
            (new Notices)->deleteAccountNotices($proof->technician_id, 'vehicle_car_wash_proof_required');
            (new Notices)->deleteAccountNotices($proof->technician_id, 'rejected_car_wash_proof');
            (new Notices)->deleteAccountNotices($proof->technician_id, 'pending_car_wash_proof_approval');

            $this->commitTransaction();

            $this->response('success','Car Wash Proof Approved Successfully');
        }
        else{

            // update the status in database 
            $update_data  = ['status' => 'rejected'];
            $response = $this->updateCarConditionRecord($proof_id, $update_data);
            if(!$response) $this->rollbackResponse('error');

            // push the notice that their proof was rejected and they need to re upload the proof for the week
            $notice="<b>CAR WASH PROOF REJECTED:</b> $notes";

            $data=[
                'type'          =>  'rejected_car_wash_proof',
                'level'         =>  'normal',
                'class'         =>  'error',
                'notice'        =>  $notice,
                'technician_id' =>  $proof->technician_id
            ];

            $response = (new Notices)->generateTechnicianNotice($data, true);
            if(!$response) $this->rollbackResponse('error');

            $this->commitTransaction();

            $this->response('success','Car wash proof rejected and notice pushed on tech dashboard ');
        }
    }

    public function approve_reject_vehicle_condition_proof(){
        global $wpdb;

		$this->verify_nonce_field('approve_reject_vehicle_condition_proof');

        if(empty($_POST['proof_id'])) $this->response('error');
        if(empty($_POST['status'])) $this->response('error');

        $proof_id = sanitize_text_field($_POST['proof_id']);
        $status = sanitize_text_field($_POST['status']);

        if($status == "reject" && empty($_POST['notes'])) $this->response('error');
        if($status == "reject") $notes = sanitize_textarea_field($_POST['notes']);
        
        $this->beginTransaction();

        $proof = $this->getVehicleConditionProof($proof_id);
        if(!$proof) $this->rollbackResponse('error');

        $this->loadClass('class-aws-s3-bucket');
        $aws_s3 = (new S3bucket)->deleteAWS3BucketObject($proof->aws_video_key);
        if(!$aws_s3) $this->rollbackResponse('error');

        if($status == "approve"){
            
            // update the status in database 
            $update_data  = ['status' => 'approved'];
            $response = $this->updateVehicleConditionReocord($proof_id, $update_data);
            if(!$response) $this->rollbackResponse('error');

            // delete the approval pending notice from tech dashboard
            $response = (new Notices)->deleteAccountNotices($proof->technician_id, 'pending_approval_for_vehicle_condition_record');
            if(!$response) $this->rollbackResponse('error');
            
            $this->commitTransaction();

            $this->response('success','Vehicle Condition Proof Approved Successfully');
        }
        else{

            // update the status in database 
            $update_data  = ['status' => 'rejected'];
            $response = $this->updateVehicleConditionReocord($proof_id, $update_data);
            if(!$response) $this->rollbackResponse('error');

            // push the notice that their proof was rejected and they need to re upload the proof for the week
            $notice="<b>VEHICLE CONDITION PROOF REJECTED:</b> $notes";

            $data=[
                'type'          =>  'rejected_vehicle_condition_proof',
                'level'         =>  'normal',
                'class'         =>  'error',
                'notice'        =>  $notice,
                'technician_id' =>  $proof->technician_id
            ];

            $response = (new Notices)->generateTechnicianNotice($data, true);
            if(!$response) $this->rollbackResponse('error');

            $this->commitTransaction();

            $this->response('success','vehicle condition proof rejected and notice pushed on tech dashboard ');
        }
    }

    public function updateVehicleConditionReocord(int $record_id, array $data){
        global $wpdb;

        $data['updated_at'] = date('Y-m-d h:i:s');
        $response = $wpdb->update($wpdb->prefix."vehilce_inspection", $data, ['id' => $record_id]);
        return $response === false ? false : true;
    }

    public function updateCarConditionRecord(int $record_id, array $data){
        global $wpdb;

        $data['updated_at'] = date('Y-m-d h:i:s');
        $response = $wpdb->update($wpdb->prefix."car_wash_proof", $data, ['id' => $record_id]);
        return $response === false ? false : true;
    }

    public function getBreakPadChangeProof(int $proof_id){
        global $wpdb;

        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}vehicle_breakpads_change_proof
            where id = '$proof_id'
        ");
    }


    public function approve_reject_break_pads_change_proof(){
        global $wpdb;

		$this->verify_nonce_field('approve_reject_break_pads_change_proof');

        if(empty($_POST['proof_id'])) $this->response('error');
        if(empty($_POST['status'])) $this->response('error');

        $proof_id = $this->sanitizeEscape($_POST['proof_id']);
        $status = $this->sanitizeEscape($_POST['status']);

        $this->beginTransaction();

        $proof = $this->getBreakPadChangeProof($proof_id);
        if(!$proof) $this->rollbackResponse('error');

        // delete the approval pending notice from tech dashboard
        (new Notices)->deleteAccountNotices($proof->technician_id, 'pending_approval_for_break_pad_change_record');
        (new Notices)->deleteAccountNotices($proof->technician_id, '20k_break_pads_change_proof_required');
        
        if($status == "approve"){
        
            // update the status in database 
            $response = $wpdb->update($wpdb->prefix."vehicle_breakpads_change_proof", ['status'=>'approved'], ['id'=> $proof_id]);
            if($response === false) $this->rollbackResponse('error');

            // update the breakpad change mileage of vehicle 
            $this->updateBreakpadChangeMileage( $proof->vehicle_id, $proof->mileage);

            $this->commitTransaction();
        }
        else{
            // set the status rejected
            $response = $wpdb->update($wpdb->prefix."vehicle_breakpads_change_proof",['status'=>'rejected'],['id'=> $proof_id]);
            if($response === false) $this->rollbackResponse('error');

            // push the notice that their proof was rejected and they need to re upload the proof for the week
            $notice="Your last <b>BREAK PADS CHANGE PROOF</b> got rejected and you'll have to upload it again with correct information.";

            $data=[
                'type'      =>  'rejected_break_pads_change_proof',
                'level'     =>  'normal',
                'class'     =>  'error',
                'notice'    =>  $notice,
                'date'      =>  date('Y-m-d'),
                'week'      =>  date('Y-\WW',strtotime($proof->date)),
                'technician_id'  =>  $proof->technician_id
            ];

            $response = (new Notices)->generateTechnicianNotice($data, true);
            if(!$response) $this->rollbackResponse('error');

            $this->commitTransaction();

            $this->response('success','break pads change proof rejected and notice pushed on tech dashboard ');
        }
    }

    /*
        This Method uplaods breadk pad change time mileage into the system with proof
    */
    public function upload_break_pads_change_proof(){
        global $wpdb;

        // verify nonce field
        $this->verify_nonce_field('upload_break_pads_change_proof');

        $page_url = esc_url_raw($_POST['page_url']);

        $this->beginTransaction();

        $technician_id = $this->get_technician_id();
        $vehicle_id = $this->getTechnicianVehicleId( $technician_id );

        // check for pending approval first
        if((new Notices)->isNoticeAlreadyExist($technician_id, 'pending_approval_for_break_pad_change_record')){
            $message = 'you already have an oil change record proof pending to be approved';
            $this->sendErrorMessage($page_url, $message);
        }

        $db_breakpad_change_mileage = $this->getBreakpadChangeMileage($vehicle_id);
        $new_breakpad_change_mileage = (int) esc_html($_POST['break_pad_change_mileage']);

        $message = "Break pad change mileage cannot be less than or equals to last break pad mileage";
        if($new_breakpad_change_mileage <= $db_breakpad_change_mileage) $this->sendErrorMessage($page_url, $message);

        $data=[
            'technician_id' =>  $technician_id,
            'vehicle_id'    =>  $vehicle_id,
            'mileage'       =>  $new_breakpad_change_mileage,
            'status'        =>  'pending',
            'date'          =>  date('Y-m-d'),
        ];

        // upload mileage proof file 
        if(isset($_FILES['mileage_proof']) && !empty($_FILES['mileage_proof']['tmp_name'])){
            $upload = $this->uploadSingleFile($_FILES['mileage_proof']);
            $data['mileage_proof']=$upload['url'];
        }

        // upload break pad change proof file 
        if(isset($_FILES['breadkpad_proof']) && !empty($_FILES['breadkpad_proof']['tmp_name'])){
            $upload = $this->uploadSingleFile($_FILES['breadkpad_proof']);
            $data['breadkpad_proof']=$upload['url'];
        }

        // insert the proof in database
        $response = $wpdb->insert($wpdb->prefix."vehicle_breakpads_change_proof", $data);
        if(!$response) $this->rollBackTransaction($page_url);

        $response = (new Notices)->deleteAccountNotices($technician_id, 'rejected_break_pads_change_proof');
        if(!$response) $this->rollBackTransaction($page_url);

        $response = (new Notices)->deleteAccountNotices($technician_id, '20k_break_pads_change_proof_required');
        if(!$response) $this->rollBackTransaction($page_url);

        // create notice to get the oil change proof approved till tomorrow
        $message="You need to get the break pad change proof approved from office till tomorrow in order to avoid account freeze";

        $data=[
            'type'              =>  'pending_approval_for_break_pad_change_record',
            'level'             =>  'normal',
            'class'             =>  'info',
            'notice'            =>  $message,
            'date'              =>  date('Y-m-d'),
            'technician_id'     =>  $technician_id
        ];

        $response = (new Notices)->generateTechnicianNotice($data, true);
        if(!$response) $this->rollBackTransaction($page_url);

        $this->commitTransaction();

        $message="Break pads change proof uploaded successfully";
        $this->setFlashMessage($message,'success');

        wp_redirect($page_url);
    }

    /*
        This Method returns the system break pad change mileage 
        Return Type : int (mileage)
        Date : 2021-07-22
    */
    public function getBreakpadChangeMileage(int $vehicle_id): int {
        global $wpdb;

        $mileage = $wpdb->get_var("
            select last_break_change_mileage 
            from {$wpdb->prefix}vehicles 
            where id='$vehicle_id'
        ");

        return $mileage ? (int) $mileage : 0;
    }

    /*
        This Method approove or rejcts the upload oil change and mileage proof
    */
    public function approve_reject_oil_change_proof(){
        global $wpdb;

		$this->verify_nonce_field('approve_reject_oil_change_proof');

        if(empty($_POST['proof_id'])) $this->response('error');
        if(empty($_POST['status'])) $this->response('error');
        
        $proof_id = $this->sanitizeEscape($_POST['proof_id']);
        $status = $this->sanitizeEscape($_POST['status']);

        $this->beginTransaction();

        $proof=$wpdb->get_row("
            select * from 
            {$wpdb->prefix}vehicle_oil_change 
            where id='$proof_id'
        ");

        $response = (new Notices)->deleteAccountNotices($proof->technician_id, 'pending_approval_for_oil_change_record');
        if(!$response) $this->rollbackResponse('error');

        if($_POST['status']=="approve"){
            
            // update the status in database 
            $response = $wpdb->update($wpdb->prefix."vehicle_oil_change",['status'=>'approved'],['id'=> $proof_id]);
            if($response === false) $this->rollbackResponse('error');

            // update the oil change mileage of vehicle 
            $this->updateOilChangeMileage( $proof->vehicle_id, $proof->mileage);

            $this->commitTransaction();

            $this->response('success','Oil Change Proof Approved Successfully');
        }
        else{
            // set the status rejected
            $response = $wpdb->update($wpdb->prefix."vehicle_oil_change", ['status'=>'rejected'], ['id'=>$_POST['proof_id']]);
            if($response === false) $this->rollbackResponse('error');

            // push the notice that their proof was rejected and they need to re upload the proof for the week
            $notice="Your last <b>OIL CHANGE PROOF</b> got rejected and you'll have to upload it again with correct information.";

            $data=[
                'type'          =>  'rejected_oil_change_proof',
                'level'         =>  'normal',
                'class'         =>  'error',
                'notice'        =>  $notice,
                'week'          =>  date('Y-\WW',strtotime($proof->date)),
                'technician_id' =>  $proof->technician_id
            ];

            $response = (new Notices)->generateTechnicianNotice($data, true);
            if(!$response) $this->rollbackResponse('error');

            $this->commitTransaction();

            $this->response('success','oil change proof rejected and notice pushed on tech dashboard ');
        }

    }

    public function updateOilChangeMileage(int $vehicle_id, int $mileage){
        global $wpdb;

        $db_oil_change_mileage = $this->get_last_oil_change_mileage($vehicle_id);

        if($mileage <= $db_oil_change_mileage) return false;
        
        $response = $wpdb->update($wpdb->prefix."vehicles", ['last_oil_change_mileage' => $mileage], ['id' => $vehicle_id]);        
        return $response === false ? false : true;
    }

    public function updateBreakpadChangeMileage(int $vehicle_id, int $mileage){
        global $wpdb;

        // get the db break change mileage
        $db_breakpad_change_mileage = $this->getBreakpadChangeMileage($vehicle_id);

        if($mileage <= $db_breakpad_change_mileage) return false;

        $response = $wpdb->update($wpdb->prefix."vehicles", ['last_break_change_mileage' => $mileage], ['id' => $vehicle_id]);
        return $response === false ? false : true;
    }

    public function upload_oil_change(){
        global $wpdb;

        $this->verify_nonce_field('upload_oil_change');

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['oil_change_mileage'])) $this->sendErrorMessage($page_url);

        $current_oil_change_mileage = (int) $this->sanitizeEscape($_POST['oil_change_mileage']);

        $this->beginTransaction();

        $technician_id = $this->get_technician_id();
        $vehicle_id = $this->getTechnicianVehicleId($technician_id);

        // check for pending approval first
        if((new Notices)->isNoticeAlreadyExist($technician_id, 'pending_approval_for_oil_change_record')){
            $message = 'you already have an oil change record proof pending to be approved';
            $this->sendErrorMessage($page_url, $message);
        }


        // check if last oil change mileage is greater than or equal to current uploaded mileage
        $db_oil_change_mileage = $this->get_last_oil_change_mileage($vehicle_id);

        $message = "Oil change mileage cannot be less than or equals to last oil mileage";
        if($current_oil_change_mileage <= $db_oil_change_mileage) $this->sendErrorMessage($page_url, $message);

        // insert the proof in system if everything fine
        $data=[
            'technician_id' =>  $technician_id,
            'vehicle_id'    =>  $this->getTechnicianVehicleId( $technician_id ),
            'mileage'       =>  $current_oil_change_mileage,
            'status'        =>  'pending',
            'date'          =>  date('Y-m-d'),
        ];

        // upload mileage proof file 
        if(isset($_FILES['mileage_proof']) && !empty($_FILES['mileage_proof']['tmp_name'])){
            $upload = $this->uploadSingleFile($_FILES['mileage_proof']);
            $data['proof_of_mileage'] = $upload['url'];
        }

        // upload oil change proof file 
        if(isset($_FILES['oil_change_proof']) && !empty($_FILES['oil_change_proof']['tmp_name'])){
            $upload = $this->uploadSingleFile($_FILES['oil_change_proof']);
            $data['proof_of_oil_change'] = $upload['url'];
        }

        $response = $wpdb->insert($wpdb->prefix."vehicle_oil_change", $data);
        if(!$response) $this->rollBackTransaction($page_url);

        $response = (new Notices)->deleteAccountNotices($technician_id, 'rejected_oil_change_proof');
        if(!$response) $this->rollBackTransaction($page_url);
        
        $response = (new Notices)->deleteAccountNotices($technician_id, '3k_oil_change_proof_required');
        if(!$response) $this->rollBackTransaction($page_url);
        

        // create notice to get the oil change proof approved till tomorrow
        $message="You need to get the oil change proof approved from office till tomorrow in order to avoid account freeze";

        $data=[
            'type'              =>  'pending_approval_for_oil_change_record',
            'level'             =>  'normal',
            'class'             =>  'info',
            'notice'            =>  $message,
            'date'              =>  date('Y-m-d'),
            'technician_id'     =>  $technician_id
        ];

        $response = (new Notices)->generateTechnicianNotice($data, true);
        if(!$response) $this->rollBackTransaction($page_url);
        
        $this->commitTransaction();

        // set the flash message of success
        $message="Oil Change proof uploaded successfully";
        $this->setFlashMessage($message,'success');

        wp_redirect($page_url);
    }

    public function get_last_oil_change_mileage( int $vehicle_id): int {
        global $wpdb;

        $mileage = $wpdb->get_var("
            select last_oil_change_mileage 
            from {$wpdb->prefix}vehicles 
            where id='$vehicle_id'
        ");

        return $mileage ? (int) $mileage : 0;
    }

    public function getVehicleMileageProof(int $proof_id){
        global $wpdb;
        
        return $wpdb->get_row("
            select * from 
            {$wpdb->prefix}vehicle_mileage 
            where id='$proof_id'
        ");
    }

    public function approve_reject_mileage_proof(){
        global $wpdb;

		$this->verify_nonce_field('approve_reject_mileage_proof');

        if(empty($_POST['proof_id'])) $this->response('error');

        $proof_id = $this->sanitizeEscape($_POST['proof_id']);

        $this->beginTransaction();

        $proof = $this->getVehicleMileageProof($proof_id);
        if(!$proof) $this->rollbackResponse('error');

        if($_POST['status']=="approve"){
            
            // update the status in database 
            $response = $wpdb->update($wpdb->prefix."vehicle_mileage",['status'=>'approved'],['id' => $proof_id]);
            if($response === false) $this->rollbackResponse('error');

            // update the mileage of vehicle 
            $this->update_mileage( $proof->vehicle_id, $proof->mileage );

            $response = (new Notices)->deleteAccountNotices($proof->technician_id, 'pending_approval_for_mileage_record');
            if(!$response) $this->rollbackResponse('error');

            $response = (new Notices)->deleteAccountNotices($proof->technician_id, 'requestForMileageRelatedInformation');
            if(!$response) $this->rollbackResponse('error');

            $this->commitTransaction();
            $this->response('success','Mileage Proof Approved Successfully');
        }
        else{
            $week=date('W',strtotime($proof->date));            

            // set the status rejected
            $response = $wpdb->update($wpdb->prefix."vehicle_mileage",['status'=>'rejected'],['id'=>$_POST['proof_id']]);
            if($response === false) $this->rollbackResponse('error');

            // push the notice that their proof was rejected and they need to re upload the proof for the week
            $notice="Your last mileage proof got rejected and you'll have to upload it again with correct information.";

            $data=[
                'type'          =>  'rejected_mileage_proof',
                'level'         =>  'normal',
                'class'         =>  'error',
                'notice'        =>  $notice,
                'date'          =>  date('Y-m-d'),
                'week'          =>  date('Y-\WW',strtotime($proof->date)),
                'technician_id' =>  $proof->technician_id
            ];

            $response = (new Notices)->generateTechnicianNotice($data, true);
            if(!$response) $this->rollbackResponse('error');

            $this->commitTransaction();

            $this->response('success','Mileage proof rejected and notice pushed on tech dashboard ');
        }
    }

    public function upload_mileage(){
        global $wpdb;

        $this->verify_nonce_field('upload_mileage');

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['milage'])) $this->sendErrorMessage($page_url);

        $mileage = $this->sanitizeEscape($_POST['milage']);

        $technician_id = $this->get_technician_id();
        $vehicle_id = $this->getTechnicianVehicleId($technician_id);
        $week=date('Y-\WW');

        // check if current week proof already uploaded 
        $status = $wpdb->get_var("
            select COUNT(*) 
            from {$wpdb->prefix}vehicle_mileage 
            where technician_id='$technician_id' 
            and vehicle_id='$vehicle_id' 
            and week='$week' 
            and status <> 'rejected'
        ");

        if($status){
            $message="You'd already uploaded the mileage proof for this week";
            $this->sendErrorMessage($page_url, $message);
        }

        // check if db mileage is greater than or equals to uploaded mileage 
        $db_mileage = $this->get_vehicle_mileage($vehicle_id);

        if($mileage <= $db_mileage){
            $message = "Mileage cannot be less than or equals to old mileage";
            $this->sendErrorMessage($page_url, $message);
        }

        $this->beginTransaction();

        $data=[
            'technician_id' =>  $technician_id,
            'vehicle_id'    =>  $vehicle_id,
            'mileage'       =>  $mileage,
            'status'        =>  'pending',
            'date'          =>  date('Y-m-d'),
            'week'          =>  $week
        ];

        // upload mileage proof file 
        if(isset($_FILES['mileage_proof']) && !empty($_FILES['mileage_proof']['tmp_name'])){
            $upload = $this->uploadSingleFile($_FILES['mileage_proof']);
            $data['proof_pic'] = $upload['url'];
        }

        $status=$wpdb->insert($wpdb->prefix."vehicle_mileage",$data);
        if(!$status) $this->rollBackTransaction($page_url);

        // delete the notice if any for rejection of mileage update proof
        $response = (new Notices)->deleteAccountNotices($technician_id, 'rejected_mileage_proof');
        if(!$response) $this->rollBackTransaction($page_url);

        $response = (new Notices)->deleteAccountNotices($technician_id, 'mileage_proof_record');
        if(!$response) $this->rollBackTransaction($page_url);

        // create notice to get the proof approved till tomorrow
        $message="You need to get the mileage proof approved from office till tomorrow in order to avoid account freeze";

        $data=[
            'type'              =>  'pending_approval_for_mileage_record',
            'level'             =>  'normal',
            'class'             =>  'info',
            'notice'            =>  $message,
            'technician_id'     =>  $technician_id
        ];

        $response = (new Notices)->generateTechnicianNotice($data, true);
        if(!$response) $this->rollBackTransaction($page_url);

        $this->commitTransaction();
        
        $message="Mileage proof uploaded successfully";
        $this->setFlashMessage($message,'success');

        wp_redirect($page_url);
    }

    public function edit_vehicle_information(){
        global $wpdb;

        // verify nonce field first
        $this->verify_nonce_field('edit_vehicle_information');

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['year'])) $this->sendErrorMessage($page_url, 'year can\'t be empty');
        if(empty($_POST['make'])) $this->sendErrorMessage($page_url, 'make can\'t be empty');
        if(empty($_POST['model'])) $this->sendErrorMessage($page_url, 'model can\'t be empty');
        if(empty($_POST['plate_number'])) $this->sendErrorMessage($page_url, 'plate no can\'t be empty');
        if(empty($_POST['vin_number'])) $this->sendErrorMessage($page_url, 'vin no can\'t be empty');
        if(empty($_POST['color'])) $this->sendErrorMessage($page_url, 'color can\'t be empty');
        if(empty($_POST['parking_address'])) $this->sendErrorMessage($page_url, 'parking address can\'t be empty');
        if(empty($_POST['registration_expiry_date'])) $this->sendErrorMessage($page_url, 'registration expiry date can\'t be empty');
        if(empty($_POST['insurance_expiry_date'])) $this->sendErrorMessage($page_url, 'insurance can\'t be empty');

        $vehicle_id = "";

        if(current_user_can( 'administrator') && isset($_POST['vehicle_id'])){
            if(empty($_POST['owner'])) $this->sendErrorMessage($page_url, 'owner can\'t be empty');
            if(empty($_POST['current_mileage'])) $this->sendErrorMessage($page_url, 'current mileage can\'t be empty');
            if(empty($_POST['last_break_change_mileage'])) $this->sendErrorMessage($page_url, 'last break change mileage can\'t be empty');
            if(empty($_POST['last_oil_change_mileage'])) $this->sendErrorMessage($page_url, 'last oil change mileage can\'t be empty');

            $vehicle_id = $this->sanitizeEscape($_POST['vehicle_id']);
        }
        else{
            // get the technician id
            $technician_id = $this->get_technician_id();
            $vehicle_id = $this->getTechnicianVehicleId($technician_id);
        }

        $columns = ['registration_document', 'insurance_document', 'pesticide_decal'];
        $vehicle_data = $this->getVehicleById($vehicle_id, $columns);
        if(!$vehicle_data) $this->sendErrorMessage($page_url);

        $data=[
            'year'                      =>  $this->sanitizeEscape($_POST['year']),
            'make'                      =>  $this->sanitizeEscape($_POST['make']),
            'model'                     =>  $this->sanitizeEscape($_POST['model']),
            'plate_number'              =>  $this->sanitizeEscape($_POST['plate_number']),
            'vin_number'                =>  $this->sanitizeEscape($_POST['vin_number']),
            'color'                     =>  $this->sanitizeEscape($_POST['color']),
            'parking_address'           =>  $this->sanitizeEscape($_POST['parking_address']),
            'registration_expiry_date'  =>  $this->sanitizeEscape($_POST['registration_expiry_date']),
            'insurance_expiry_date'     =>  $this->sanitizeEscape($_POST['insurance_expiry_date']),
        ];

        if(!empty($_FILES['registration_document']['name'])){
            $reg_doc = $this->uploadSingleFile($_FILES['registration_document']);
            if(!$reg_doc) $this->sendErrorMessage($page_url);

            if(!empty($vehicle_data->registration_document))
                $this->deleteFileByUrl($vehicle_data->registration_document);
            
            $data['registration_document'] = $reg_doc['url'];                
        }

        if(!empty($_FILES['insurance_document']['name'])){
            $insurance_doc = $this->uploadSingleFile($_FILES['insurance_document']);
            if(!$insurance_doc) $this->sendErrorMessage($page_url);

            $data['insurance_document'] = $insurance_doc['url'];

            if(!empty($vehicle_data->insurance_document))
                $this->deleteFileByUrl($vehicle_data->insurance_document);
        }

        if(!empty($_FILES['pesticide_decal']['name'])){
            $upload = $this->uploadSingleFile($_FILES['pesticide_decal']);
            if(!$upload) $this->sendErrorMessage($page_url);
            
            $data['pesticide_decal'] = $upload['url'];
            $this->deleteFileByUrl($vehicle_data->pesticide_decal);
        }

        // if is admin, then he can add the following fields 
        if(current_user_can( 'administrator')){
            
            $data['owner'] = $this->sanitizeEscape($_POST['owner']);
    
            $data['last_break_change_mileage'] = $this->sanitizeEscape($_POST['last_break_change_mileage']);
            $data['last_oil_change_mileage'] = $this->sanitizeEscape($_POST['last_oil_change_mileage']);
            $data['current_mileage'] = $this->sanitizeEscape($_POST['current_mileage']);
            
        }

        // update vehicle table
        $response = $this->updateVehicle($vehicle_id, $data); 
        if(!$response) $this->sendErrorMessage($page_url);

        // clear vehicle information notice from tech dashboard
        if(isset($technician_id) && !empty($technician_id)) (new Notices)->deleteAccountNotices($technician_id, 'vehicle_information');

        $message="Vehicle information updated successfully";
        $this->setFlashMessage($message,'success');

        // unset session
        if(isset($_SESSION['vehicle_editable'])) unset($_SESSION['vehicle_editable']);

        wp_redirect($page_url);
    }

    public function unlinkVehicle(array $data){
        global $wpdb;

        if(empty($data['vehicle_id'])) return [false, 'Vehicle id is required to unlink the vehicle'];

        $vehicle = $this->getVehicleById($data['vehicle_id']);

        if($vehicle->owner == "company"){

            if(empty($data['parking_address'])) return [false, 'Parking address is required if vehicle was of company'];

            $response = (new OfficeTasks)->reassignVehicleAfterChange($vehicle->id, $data['parking_address']);
            if(!$response) return [false, 'Unable to create vehicle assign task for office'];

            list($response, $message) = $this->setVehicleParkedSomewhereSecure($vehicle->id, $data['parking_address']);
            if(!$response) return [false, $message]; 

        }
        else{
            // set as technician no longer using this vehicle
            list($response, $message) = $this->setTechNoLongerUsingHisVehicle($data['vehicle_id']);
            if(!$response) return [false, $message];
        }

        $response = $wpdb->update($wpdb->prefix."technician_details", ['vehicle_id' => ''], ['vehicle_id' => $data['vehicle_id']]);
        if($response === false) return false;

        return [true, null];
    }

    public function setVehicleParkedSomewhereSecure(int $vehicle_id, string $parking_address, string $description = ''){
        global $wpdb;

        $update_data = ['vehicle_id' => ''];
        $response = $wpdb->update($wpdb->prefix."technician_details", ['vehicle_id' => ''], ['vehicle_id' => $vehicle_id]);
        if($response === false) return [false, $wpdb->last_error];

        $status_slug = "parked_somewhere_secure";
        $status_id = $this->getVehicleStatusIdBySlug($status_slug);
        if(!$status_id) return [false, $wpdb->last_error];

        $update_data = [
            'status_id'             =>  $status_id,
            'status_description'    =>  $description,
            'parking_address'       =>  $parking_address
        ];
        $response = $this->updateVehicle($vehicle_id, $update_data);
        if(!$response) return [false, $wpdb->last_error];

        return [true, null];
    }

    public function create_link_vehicle(){
        global $wpdb;

        // verify nonce field
        $this->verify_nonce_field('create_link_vehicle');

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['vehicle_owner'])) $this->sendErrorMessage($page_url, "Please select vehicle owner");


        $array_fields = ['registration_document', 'insurance_document'];
        list($response, $message) =  $this->arrayValidation($array_fields, $_FILES);
        if(!$response) $this->sendErrorMessage($page_url, $message);

        $vehicle_owner = $this->sanitizeEscape($_POST['vehicle_owner']);

        if($vehicle_owner == 'company' && empty($_POST['office_vehicle_parking_address']))
            $this->sendErrorMessage($page_url, 'Office new vehicle parking address is required');

        $this->beginTransaction();

        $technician_id = $this->get_technician_id();

        if($vehicle_owner == "technician"){

            $status_slug = "assigned_to_employee";
            $status_id = $this->getVehicleStatusIdBySlug($status_slug);
            if(!$status_id) $this->rollBackTransaction($page_url, "Unable to fetch status id");

            $_POST['status_id'] = $status_id;

            list($vehicle_id, $message) = $this->createVehicle($_POST, $_FILES);
            if(!$vehicle_id) $this->rollBackTransaction($page_url, $message);
            $parking_address  = $this->sanitizeEscape($_POST['parking_address']);
        }
        elseif($vehicle_owner == 'company'){

            if(
                empty($_POST['vehicle_id']) ||
                empty($_POST['office_vehicle_parking_address'])
            ) $this->rollBackTransaction($page_url, "Please select a vehicle to link");

            $vehicle_id = $this->sanitizeEscape($_POST['vehicle_id']);
            $parking_address = $this->sanitizeEscape($_POST['office_vehicle_parking_address']);

        }
        else{
            $this->rollBackTransaction($page_url, 'Unable to find vehicle owner');
        }

        if(!$vehicle_id) $this->rollBackTransaction($page_url, "Vehicle id not found");

        $old_vehicle_parking_address = !empty($_POST['old_vehicle_parking_address']) ? $this->sanitizeEscape($_POST['old_vehicle_parking_address'])  : '';

        list($response, $message) = $this->linkVehicle($vehicle_id, $technician_id, $parking_address, '', $old_vehicle_parking_address); 
        if(!$response) $this->rollBackTransaction($page_url, $message);

        (new Notices)->deleteAccountNotices($technician_id, 'vehicle_information');
        (new Notices)->deleteAccountNotices($technician_id, 'requestForVehicle');
        (new Notices)->deleteAccountNotices($technician_id, 'requestForLinkNewVehicle');

        $this->commitTransaction();

        $message = "Vehicle linked to your account successfully";
        $this->setFlashMessage($message,'success'); 

        wp_redirect($page_url);
    }

    public function upload_vehicle_condition(){
        global $wpdb;

        $this->verify_nonce_field('upload_vehicle_condition');

        $technician_id = $this->get_technician_id();
        $upload_dir = wp_upload_dir();

        if(empty($_POST['chunk_end'])) $this->response('error');

        // check for pending approval first
        if((new Notices)->isNoticeAlreadyExist($technician_id, 'pending_approval_for_vehicle_condition_record')){
            $message = 'you already have vehicle condition record proof pending to be approved';
            $this->rollbackResponse('error', $message);
        }        

        $array_fields = ['file'];
        list($response, $message) = $this->arrayValidation($array_fields, $_FILES);
        if(!$response) $this->response('error', $message);

        $temp_file_name = $this->genereateSlug($_FILES['file']['name']);
        $file_name = "/vehicle-condition/".date('Y-m-d')."_".$technician_id."_".$temp_file_name;

        $resouce_path = $upload_dir['basedir'].$file_name;
        $file_path = fopen($resouce_path, 'a');

        if($_POST['chunk_end'] <= filesize($resouce_path)) $this->response('success');

        $uploaded_file = fopen($_FILES['file']['tmp_name'], 'r');
        $binary = fread($uploaded_file, filesize($_FILES['file']['tmp_name']));

        fwrite($file_path, $binary);

        // if file is fully uploaded then move the file to s3 bucket and update table data
        if(!empty($_POST['finished'])){

            $this->beginTransaction();

            $data=[
                'technician_id' =>  $technician_id,
                'vehicle_id'    =>  $this->getTechnicianVehicleId($technician_id),
                'status'        =>  'pending',
                'date'          =>  date('Y-m-d'),
                'video_url'     =>  $file_name
            ];

            $response = $wpdb->insert($wpdb->prefix."vehilce_inspection",$data);
            if(!$response) $this->rollbackResponse('error', 'error uploading data');

            $vehicle_condition_record_id = $wpdb->insert_id;

            $response = (new Notices)->deleteAccountNotices($technician_id, 'rejected_vehicle_condition_proof');
            if(!$response) $this->rollbackResponse('error');
    
            $response = (new Notices)->deleteAccountNotices($technician_id, 'vehicle_condition_proof_record');
            if(!$response) $this->rollbackResponse('error');

            $message="You need to get the vehicle condition proof approved from office till tomorrow in order to avoid account freeze";
            $data=[
                'type'              =>  'pending_approval_for_vehicle_condition_record',
                'level'             =>  'normal',
                'class'             =>  'info',
                'notice'            =>  $message,
                'date'              =>  date('Y-m-d'),
                'technician_id'     =>  $technician_id
            ];
    
            $response = (new Notices)->generateTechnicianNotice($data, true);
            if(!$response) $this->rollbackResponse('error');
    

            // Buffer all upcoming output...
            ob_start();

            // Send your response.
            echo json_encode(['status' => 'success']);

            // Get the size of the output.
            $size = ob_get_length();

            // Disable compression (in case content length is compressed).
            header("Content-Encoding: none");

            // Set the content length of the response.
            header("Content-Length: {$size}");

            // Close the connection.
            header("Connection: close");

            // Flush all output.
            ob_end_flush();
            ob_flush();
            flush();

            // Close current session (if it exists).
            if (session_id()) {
                session_write_close();
            }

            sleep(10);
            
            // global $wpdb;
            $this->loadClass('class-aws-s3-bucket');
            $s3 = new S3bucket;
            list($key, $message) = $s3->uploadObject($resouce_path, $resouce_path, "vehicle_condition");
            if(!empty($key)){
                $wpdb->update($wpdb->prefix."vehilce_inspection", ['aws_video_key' => $key], ['id' => $vehicle_condition_record_id]);
                unlink($resouce_path);
            }

            $this->commitTransaction();
        }
        else{
            $this->response('success', 'Vehicle condition proof uploaded successfully');
        }
    }

    public function getTechnicianVehicleId( int $technician_id ){
        global $wpdb;

        return $wpdb->get_var("
            select vehicle_id
            from {$wpdb->prefix}technician_details 
            where id='$technician_id'
        ");
    }

    public function getVehicleById( int $vehicle_id, array $columns = []){
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns
            from {$wpdb->prefix}vehicles
            where id='$vehicle_id'
        ");
    }

    public function get_vehicle_mileage( int $vehicle_id ){
        global $wpdb;

        $mileage = $wpdb->get_var("
            select current_mileage 
            from {$wpdb->prefix}vehicles 
            where id='$vehicle_id'
        ");
        return $mileage ? $mileage : 0;
    }

    public function update_mileage( int $vehicle_id, int $current_mileage){
        global $wpdb;

        $db_mileage = $this->get_vehicle_mileage($vehicle_id);
        if($current_mileage <= $db_mileage) return false;
        $response = $wpdb->update($wpdb->prefix."vehicles", ['current_mileage' => $current_mileage], ['id' => $vehicle_id]);
        return $response === false ? false : true;
    }

    public function getAllVehicles(array $conditions = [], array $columns = []){
        global $wpdb;

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';
        $columns = count($columns) > 0 ? implode($columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}vehicles
            $conditions
        ");
    }

    public function getVehicles(bool $onlyLinkedVehicles = true){
        global $wpdb;
        $where_condition = "";

        if($onlyLinkedVehicles == true){
            $where_condition = " where TD.vehicle_id <> '' and TD.vehicle_id IS NOT NULL";
        }

        return $wpdb->get_results("
            select V.*,TD.id as technician_id 
            from {$wpdb->prefix}vehicles V
            left join {$wpdb->prefix}technician_details TD
            on V.id = TD.vehicle_id
            $where_condition
        ");
    }

    public function update_mileage_fields_information(){
        global $wpdb;

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['current_mileage'])) $this->sendErrorMessage($page_url);
        if(empty($_POST['oil_change_mileage'])) $this->sendErrorMessage($page_url);
        if(empty($_POST['break_pad_change_mileage'])) $this->sendErrorMessage($page_url);


        $mileage = (int) $this->sanitizeEscape($_POST['current_mileage']);
        $oil_change_mileage = (int) $this->sanitizeEscape($_POST['oil_change_mileage']);
        $break_pad_change_mileage = (int) $this->sanitizeEscape($_POST['break_pad_change_mileage']);


        $technician_id = $this->get_technician_id();
        $vehicle_id = $this->getTechnicianVehicleId($technician_id);

        // update mileage information
        $response = $this->update_mileage($vehicle_id, $mileage);
        if(!$response) $this->sendErrorMessage($page_url);

        // update last oil change mileage
        $this->updateOilChangeMileage($vehicle_id, $oil_change_mileage);

        // update break pad change mileage
        $this->updateBreakpadChangeMileage($vehicle_id, $break_pad_change_mileage);

        (new Notices)->deleteAccountNotices($technician_id, 'mileage_information_form');
        (new Notices)->deleteAccountNotices($technician_id, 'requestForMileageRelatedInformation');

        $message = 'Mileage records information udpated successfully';
        $this->setFlashMessage($message,'success');

        wp_redirect($page_url);
    }

    public function isVehicleLinkedToAnyTechnician(int $vehicle_id){
        global $wpdb;
        return $wpdb->get_var("select count(*) from {$wpdb->prefix}technician_details where vehicle_id = '$vehicle_id'");
    }

    public function getVehicleByTechnicianId(int $technician_id){
        global $wpdb;
        return $wpdb->get_row("
            select V.*
            from {$wpdb->prefix}vehicles V
            left join {$wpdb->prefix}technician_details TD
            on V.id = TD.vehicle_id
            where TD.id = '$technician_id'
        ");
    }

    public function linkVehicle( 
            int $vehicle_id, 
            int $technician_id, 
            string $parking_address, 
            string $status_desc = '',
            string $old_vehicle_parking_address = ''
        ){

        global $wpdb;

        $old_vehicle = $this->getVehicleByTechnicianId($technician_id);
        
        // check if technician is already assigned any vehicle, if yes then create task for office to reassign that vehicle
        if($old_vehicle){

            $old_vehicle_data = ['vehicle_id'    =>  $old_vehicle->id];

            if($old_vehicle->owner == "company" && empty($old_vehicle_parking_address))
                return [false, 'Old vehicle parking address is required for record if switching to new vehicle'];

            if($old_vehicle->owner == 'company') $old_vehicle_data['parking_address'] = $old_vehicle_parking_address;

            list($response, $message) = $this->unlinkVehicle($old_vehicle_data);
            if(!$response) return [false, $message];
        }

        // remove vehicle link from current tech if any
        $response = $wpdb->update($wpdb->prefix."technician_details", ['vehicle_id' => ''], ['vehicle_id' => $vehicle_id]);
        if($response === false) return [false, $wpdb->last_error];

        // update technician vehicle id
        $response = $this->updateTechnician($technician_id, compact('vehicle_id'));
        if(!$response) return [false, $wpdb->last_error];

        // update vehicle status to "Assigned to technician" and update vehicle parkng address
        $slug = "assigned_to_employee";
        $vehicle_status_id = $this->getVehicleStatusIdBySlug($slug);
        if(!$vehicle_status_id) return [false, $wpdb->last_error];
        $update_data = [
            'status_id'             => $vehicle_status_id, 
            'parking_address'       => $parking_address,
            'current_mileage'       => 0,
            'status_description'    => '',
        ];
        $response = $this->updateVehicle($vehicle_id, $update_data);
        if(!$response) return [false, $wpdb->last_error];

        // delete notice for link vehicle
        (new Notices)->deleteAccountNotices($technician_id, 'requestForVehicle');

        return [true, null];
    }

    public function getVehicleStatuses(){
        global $wpdb;
        return $wpdb->get_results("select * from {$wpdb->prefix}vehicle_status");
    }

    public function getVehicleStatusIdBySlug(string $slug){
        global $wpdb;
        return $wpdb->get_var("select id from {$wpdb->prefix}vehicle_status where slug = '$slug'");
    }

    public function createVehicle(array $data, array $files){
        global $wpdb;

        $required_fields = [
            'year',
            'make',
            'model',
            'plate_number',
            'vin_number',
            'color',
            'last_break_change_mileage',
            'last_oil_change_mileage',
            'current_mileage',
            'parking_address',
            'vehicle_owner',
            'status_id',
            'registration_expiry_date',
            'insurance_expiry_date',            
        ];

        $registration_file = $this->uploadSingleFile($_FILES['registration_document']);
        $insurance_file = $this->uploadSingleFile($_FILES['insurance_document']);

        foreach($required_fields as $field){
            if(empty($_POST[$field])) return [false, $field." is required"];
        }

        $vehicle_data = [
            'year'                      =>  $this->sanitizeEscape($data['year']),
            'make'                      =>  $this->sanitizeEscape($data['make']),
            'model'                     =>  $this->sanitizeEscape($data['model']),
            'plate_number'              =>  $this->sanitizeEscape($data['plate_number']),
            'vin_number'                =>  $this->sanitizeEscape($data['vin_number']),
            'color'                     =>  $this->sanitizeEscape($data['color']),
            'last_break_change_mileage' =>  $this->sanitizeEscape($data['last_break_change_mileage']),
            'last_oil_change_mileage'   =>  $this->sanitizeEscape($data['last_oil_change_mileage']),
            'current_mileage'           =>  $this->sanitizeEscape($data['current_mileage']),
            'parking_address'           =>  $this->sanitizeEscape($data['parking_address']),
            'owner'                     =>  $this->sanitizeEscape($data['vehicle_owner']),
            'status_id'                 =>  $this->sanitizeEscape($data['status_id']),
            'registration_document'     =>  $registration_file['url'],
            'insurance_document'        =>  $insurance_file['url'],
            'registration_expiry_date'  =>  $data['registration_expiry_date'],
            'insurance_expiry_date'     =>  $data['insurance_expiry_date'],
            'last_confirmed'            =>  date('Y-m-d')
        ];

        $response = $wpdb->insert($wpdb->prefix."vehicles", $vehicle_data);
        if(!$response) return [false, $wpdb->last_error];

        return [$wpdb->insert_id, null];
    }

    public function vehicleExist( int $vehicle_id ): bool{
        global $wpdb;
        return $wpdb->get_var("
            select count(*) 
            from {$wpdb->prefix}vehicles 
            where id='$vehicle_id'
        ");
    }

    public function getVehicleOwner( int $vehicle_id ){
        global $wpdb;

        return $wpdb->get_var("
            select owner 
            from {$wpdb->prefix}vehicles 
            where id='$vehicle_id'
        ");
    }

    public function getFreelyParkedVehicles(){
        global $wpdb;

        $slug = "parked_somewhere_secure";
        $vehicle_status_id = $this->getVehicleStatusIdBySlug($slug);
        if(!$vehicle_status_id) return false;

        return $wpdb->get_results("
            select *
            from {$wpdb->prefix}vehicles V
            where status_id = '$vehicle_status_id'
        ");
    }
	
	public function update_parking_address_information(){
        global $wpdb;

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['parking_address'])) $this->sendErrorMessage($page_url);

        $parking_address = sanitize_textarea_field($_POST['parking_address']);


        $technician_id = $this->get_technician_id();
        $vehicle_id = $this->getTechnicianVehicleId($technician_id);

        // update mileage information
        $response = $wpdb->update($wpdb->prefix."vehicles",['parking_address'=>$parking_address],['id'=>$vehicle_id]);
        if($response === false) $this->sendErrorMessage($page_url);
        
        (new Notices)->deleteAccountNotices($technician_id, 'empty_parking_address');

        $message = 'Vehicle parking address information udpated successfully';
        $this->setFlashMessage($message,'success');

        wp_redirect($page_url);
    }

    public function upload_pesticide_decal_proof(){
        global $wpdb;
        
        $this->verify_nonce_field('upload_pesticide_decal_proof');

        $page_url = esc_url_raw($_POST['page_url']);

        $technician_id = $this->get_technician_id();
        $vehicle_id = $this->getTechnicianVehicleId($technician_id);

        if(!$vehicle_id) $this->sendErrorMessage($page_url);

        if(!isset($_FILES['decal_proof']['name'])) $this->sendErrorMessage($page_url);
        if(empty($_FILES['decal_proof']['name'])) $this->sendErrorMessage($page_url);

        $file = $this->uploadSingleFile($_FILES['decal_proof']);

        if(!$file) $this->sendErrorMessage($page_url);

        $update_data = ['pesticide_decal' => $file['url']];
        
        if(!$this->updateVehicle($vehicle_id, $update_data)) $this->sendErrorMessage($page_url);

        (new Notices)->deleteAccountNotices($technician_id, 'pending_pesticide_decal_proof');
        (new Notices)->deleteAccountNotices($technician_id, 'requestForPesticideDecalProof');
        
        $message = "Pesticie decal proof uploaded successfully";
        $this->setFlashMessage($message, "success");

        wp_redirect($page_url);
    }

    public function updateVehicle(int $vehicle_id, array $data){
        global $wpdb;
        $response =  $wpdb->update($wpdb->prefix."vehicles", $data, ['id' => $vehicle_id]);
        return $response ===  false ? false : true;
    }

    public function getAssignedTechnicianId(int $vehicle_id){
        global $wpdb;

        return $wpdb->get_var("
            select id
            from {$wpdb->prefix}technician_details
            where vehicle_id = '$vehicle_id'
        ");
    }

    public function isPesticideDecalApplicable(int $vehicle_id){

        $technician_id = $this->getAssignedTechnicianId($vehicle_id);

        if(!$technician_id) return true;

        $branch_id = $this->getTechnicianBranchId($technician_id);

        $branch = (new Branches)->getBranchSlug($branch_id);
        $parent_branch = (new Branches)->getParentBranchSlug($branch_id);

        if($branch != "ny_metro" && $parent_branch != "ny_metro") return false;

        return true;
    }

    public function requestForVehicleVerification(int $technician_id){

        $notice = "Please confirm your vehicle by submitting the form on dashboard for vehicle verification.";

        $data = [
            'notice'  =>   $notice,
            'type'      =>  'requestForVehicleVerification',
            'level'     =>  'critical',
            'technician_id' =>  $technician_id
        ];

        return (new Notices)->generateTechnicianNotice($data, true);
    }

    public function requestForLinkNewVehicle(int $technician_id){

        $notice = "As mentioned by you that you've changed vehicle, please first link new vehicle with your account in order to continue with your account.";

        $data = [
            'notice'  =>   $notice,
            'type'      =>  'requestForLinkNewVehicle',
            'level'     =>  'critical',
            'technician_id' =>  $technician_id
        ];

        return (new Notices)->generateTechnicianNotice($data, true);
    }

    public function requestForVehicle(int $technician_id){

        $notice = "No vehicle is currently linked with your account, please link a vehicle with your account in order to unlock your account.";

        $data = [
            'notice'  =>   $notice,
            'type'      =>  'requestForVehicle',
            'level'     =>  'critical',
            'technician_id' =>  $technician_id
        ];

        return (new Notices)->generateTechnicianNotice($data, true);
    }

    public function requestForPesticideDecalProof(int $technician_id){

        $notice = "Please upload your pesticide decal proof in system. If you don't have one, request the one from office and then upload it.";

        $data = [
            'notice'        =>  $notice,
            'type'          =>  'requestForPesticideDecalProof',
            'level'         =>  'normal',
            'technician_id' =>  $technician_id
        ];

        return (new Notices)->generateTechnicianNotice($data, true);
    }

    public function requestForMileageRelatedInformation(int $technician_id){

        $notice = "Please upload your mileage related information on the dashboard 'Update Mileage Information' form.";

        $data = [
            'notice'  =>   $notice,
            'type'      =>  'requestForMileageRelatedInformation',
            'level'     =>  'critical',
            'technician_id' =>  $technician_id
        ];

        return (new Notices)->generateTechnicianNotice($data, true);
    }

    public function getName(int $vehicle_id){
        global $wpdb;

        $vehicle_fields = ['year', 'make', 'model', 'plate_number'];
        $vehicle = $this->getVehicleById($vehicle_id, $vehicle_fields);

        return $vehicle->year." ".$vehicle->make." ".$vehicle->model." ".$vehicle->plate_number;
    }

}

new CarCenter();