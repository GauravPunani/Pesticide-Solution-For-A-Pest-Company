<?php

class AnimalCageTracker extends GamFunctions{

    private $err_msg = 'Something went wrong, please try again later';

    function __construct(){

        add_action( 'wp_ajax_delete_cage_record', array($this,'delete_cage_record'));
		add_action( 'wp_ajax_nopriv_delete_cage_record', array($this,'delete_cage_record'));

        add_action( 'admin_post_generate_cage_tracker_record', array($this,'generate_cage_tracker_record'));
		add_action( 'admin_post_nopriv_generate_cage_tracker_record', array($this,'generate_cage_tracker_record'));

        add_action( 'admin_post_upload_retrieved_quantity', array($this,'upload_retrieved_quantity'));
		add_action( 'admin_post_nopriv_upload_retrieved_quantity', array($this,'upload_retrieved_quantity'));

        add_action( 'admin_post_update_cage_notes', array($this,'update_cage_notes'));
		add_action( 'admin_post_nopriv_update_cage_notes', array($this,'update_cage_notes'));

        add_action( 'wp_ajax_extend_cage_alert', array($this,'extend_cage_alert'));
		add_action( 'wp_ajax_nopriv_extend_cage_alert', array($this,'extend_cage_alert'));

        add_action( 'wp_ajax_get_animal_cage_office_notes', array($this,'get_animal_cage_office_notes'));
		add_action( 'wp_ajax_nopriv_get_animal_cage_office_notes', array($this,'get_animal_cage_office_notes'));

        add_action( 'wp_ajax_add_cage_notes', array($this,'add_cage_notes'));
		add_action( 'wp_ajax_nopriv_add_cage_notes', array($this,'add_cage_notes'));

    }

    public function update_cage_notes(){

        $this->verify_nonce_field('update_cage_notes');

        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['cage_id']) ||
            !isset($_POST['notes']) || !is_array($_POST['notes']) || count($_POST['notes']) <= 0
        ) $this->sendErrorMessage($page_url);

        $cage_id = $this->sanitizeEscape($_POST['cage_id']);
        $notes = $_POST['notes'];

        foreach($notes as $note_id => $note){
            if(!$this->updateNote($note_id, ['notes' => $note])) $this->sendErrorMessage($page_url);
        }

        $message = "Cage updated successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function updateNote( int $note_id, array $data){
        global $wpdb;

        $data['updated_at'] = date('Y-m-d h:i:s');

        $response = $wpdb->update($wpdb->prefix."animal_cage_notes", $data, ['id' => $note_id]);

        if($response === false) return false;
        return true;
    }

    public function add_cage_notes(){
        global $wpdb;

        $this->verify_nonce_field('add_cage_notes');

        if(empty($_POST['cage_id'])) $this->response('error');
        if(empty($_POST['notes'])) $this->response('error');

        $cage_id = $this->sanitizeEscape($_POST['cage_id']);
        $notes = $_POST['notes'];

        if(!$this->createNotes($cage_id, $notes)) $this->response('error');

        $this->response('success', 'Notes added successfully');
    }

    public function createNotes( int $cage_id, string $notes){
        global $wpdb;

        $data = [
            'created_at'    =>  date('Y-m-d h:i:s'),
            'updated_at'    =>  date('Y-m-d h:i:s'),
            'cage_id'       =>  $cage_id,
            'notes'         =>  $notes
        ];

        return $wpdb->insert($wpdb->prefix."animal_cage_notes", $data);
    }

    public function get_animal_cage_office_notes(){

        $this->verify_nonce_field('fbcs_nonce');
        
        if(empty($_POST['record_id'])) $this->response('error');

        $record_id = $this->sanitizeEscape($_POST['record_id']);
        $cage_notes = $this->getCageNotes($record_id);

        $this->response('success', 'cage notes', ['notes' => $cage_notes]);
    }

    public function getCageNotes( int $cage_id){
        global $wpdb;

        $notes = $wpdb->get_results("
            select *
            from {$wpdb->prefix}animal_cage_notes
            where cage_id = '$cage_id'
        ");

        if(!$notes) return [];

        foreach($notes as $key => $note){
            $notes[$key]->notes = stripslashes($note->notes);
            $notes[$key]->created_at = date('d M Y h:i A', strtotime($note->created_at));
        }

        return $notes;
    }

    public function extend_cage_alert(){
        global $wpdb;

        // first verify nonce field
        $this->verify_nonce_field('extend_cage_alert');

        if(empty($_POST['record_id'])) $this->response('error', $this->err_msg);
        if(empty($_POST['pickup_date'])) $this->response('error', $this->err_msg);
        if(empty($_POST['office_notes'])) $this->response('error', $this->err_msg);

        $record_id = filter_var($_POST['record_id'], FILTER_SANITIZE_STRING);
        $pickup_date = filter_var($_POST['pickup_date'], FILTER_SANITIZE_STRING);
        $office_notes = filter_var($_POST['office_notes'], FILTER_SANITIZE_STRING);

        $response = $this->extendCageAlert($record_id, $pickup_date, $office_notes);

        if(!$response) $this->response('error', $this->err_msg);

        // remove admin alert
        $this->deleteAlertForAdmin( $record_id );
        
        // remove technician alert
        $this->deleteAlertForTechnician ( $record_id );

        // return success message if verification was done correct
        $this->response('success', 'Cage alert extended successfully');
    }

    public function upload_retrieved_quantity(){

        $this->verify_nonce_field('upload_retrieved_quantity');

        $page_url = $_POST['page_url'];

        if(empty($_POST['record_id'])) $this->sendErrorMessage($page_url);
        if(empty($_POST['quantity_reterieved'])) $this->sendErrorMessage($page_url);

        $record_id = filter_var($_POST['record_id'], FILTER_SANITIZE_STRING);
        $quantity_retrieved = filter_var($_POST['quantity_reterieved'], FILTER_SANITIZE_STRING);

        // update the retrieved quanitity in cages table
        $response = $this->updateRetrievedQuantity($record_id, $quantity_retrieved);

        if(!$response) $this->sendErrorMessage($page_url);

        $message = "Retrieved quantity updated successfully";
        $this->setFlashMessage($message, "success");

        wp_redirect($page_url);
    }

    public function generate_cage_tracker_record(){
        global $wpdb;

        // first verify nonce field
        $this->verify_nonce_field('generate_cage_tracker_record');

        $cage_types = $this->getCagesTypes();
        $invoice_id = (int) esc_html($_POST['invoice_id']);

        if(is_array($cage_types) && count($cage_types) > 0 ){
            foreach($cage_types as $cage_type){

                $type = $cage_type->slug;

                if(!empty($_POST['cages_data'][$type])){
                    $cage_quantity = esc_html($_POST['cages_data'][$type]);
                    if((int) $cage_quantity > 0){
                        $this->generateCageTrackerRecord($invoice_id,  $cage_type->id, $cage_quantity);
                    }
                }
            }
        }
        
        $message="Animal cage record created successfully";
        $this->setFlashMessage($message,'success');

        wp_redirect($_POST['page_url']);
    }

    public function delete_cage_record(){
        global $wpdb;

		$this->verify_nonce_field('delete_cage_record');

        if(empty($_POST['cage_id'])) $this->response('error',$this->err_msg);

        $cage_record_id = esc_html($_POST['cage_id']);
        
        $res = $wpdb->delete($wpdb->prefix."animal_cage_tracking_new",['id' => $cage_record_id]);

        if(!$res) $this->response('error',$this->err_msg);

        $this->response('success','Cage Record deleted successfully');
    }

    public function extendCageAlert( int $record_id, string $pickup_date, string $notes){
        global $wpdb;

        if(strtotime($pickup_date) < strtotime(date('Y-m-d'))) return false;

        $cage_data = $this->getAnimalCageRecordById($record_id, ['pickup_date']);

        if(empty($cage_data->pickup_date)) return false;
        if(strtotime($pickup_date) <= strtotime($cage_data->pickup_date)) return false;

        if(!$this->createNotes($record_id, $notes)) return false;

        $update_data = [ 'pickup_date' => $pickup_date ];

        return $wpdb->update($wpdb->prefix."animal_cage_tracking_new", $update_data, ['id' => $record_id]);
    }

    public function updateRetrievedQuantity( int $record_id, int $quantity_retrieved){
        global $wpdb;

        // first get the cage record
        $cage_record_data = $this->getAnimalCageRecordById($record_id);

        // first check if quantity retrieved + quantity already retrieved not more than total quantity
        $new_retrieved_quantity = (int) $cage_record_data->quantity_retrieved + $quantity_retrieved;

        if($cage_record_data->quantity < $new_retrieved_quantity) return false;

        // update the new retrieved quantitty 
        $res = $wpdb->update($wpdb->prefix."animal_cage_tracking_new", ['quantity_retrieved' => $new_retrieved_quantity], ['id' => $record_id]);

        if(!$res) return false;

        // if new retrieved quantity is equal to total quantity, then remove notices
        if($cage_record_data->quantity == $new_retrieved_quantity){

            // set the retrieved to 1
            $wpdb->update($wpdb->prefix."animal_cage_tracking_new", ['retrieved' => 1], ['id' => $record_id]);

            // delete alert from admin dashboard if any
            $this->deleteAlertForAdmin($record_id);

            // delete alert from technician dashboard if any
            $this->deleteAlertForTechnician($record_id);
        }

        return true;

    }

    public function deleteAlertForAdmin( int $record_id ){
        global $wpdb;

        $where_record = [
            'type'  =>  'animal_cage_alert',
            'type_id'   =>  $record_id
        ];

        return $wpdb->delete($wpdb->prefix."notices", $where_record);
        
    }

    public function deleteAlertForTechnician( int $record_id ){
        global $wpdb;

        $where_record = [
            'type'          =>  'animal_cage_alert',
            'technician_id' =>  $record_id
        ];

        return $wpdb->delete($wpdb->prefix."technician_account_status", $where_record);
        
    }

    public function getCageQuantityByRecordId( int $record_id ){
        global $wpdb;
        return $wpdb->get_var("
            select quantity
            from {$wpdb->prefix}animal_cage_tracking_new
            where id = '$record_id'
        ");
    }

    public function generateCageTrackerRecord( int $invoice_id, int $cage_type_id, int $cage_quantity, int $reterived = 0): bool{
        global $wpdb;
        
        $cage_data = [
            'invoice_id'            =>  $invoice_id,
            'cage_type_id'          =>  $cage_type_id,
            'quantity'              =>  $cage_quantity,
            'quantity_retrieved'    =>  0,
            'retrieved'             =>  $reterived,
            'pickup_date'           =>  date('Y-m-d', strtotime('+30 days')),
        ];

        return $wpdb->insert($wpdb->prefix."animal_cage_tracking_new", $cage_data);
    }

    public function getCagesTypes(){
        global $wpdb;
        return $wpdb->get_results("
            select *
            from {$wpdb->prefix}cages_type
        ");
    }

    public function getAnimalCageRecordById( int $record_id, array $columns = [] ){
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns
            from {$wpdb->prefix}animal_cage_tracking_new
            where id = '$record_id'
        ");
    }

    public function getCagesRecordsByAddress( string $address){
        global $wpdb;

        return $wpdb->get_results("
            select ACT.id, ACT.quantity_retrieved, I.invoice_no, I.date, ACT.quantity, CT.name
            from {$wpdb->prefix}animal_cage_tracking_new ACT
            left join {$wpdb->prefix}invoices I
            on ACT.invoice_id = I.id
            left join {$wpdb->prefix}cages_type CT
            on CT.id = ACT.cage_type_id
            where I.address like '$address'
            and ACT.retrieved = 0
        ");

    }

    public function getCagesRecordsByTechnicianId( int $technician_id ){
        global $wpdb;

        return $wpdb->get_results("
            select ACT.id, I.client_name, I.address, I.phone_no, I.email, ACT.quantity_retrieved, I.invoice_no, I.date, ACT.quantity, CT.name
            from {$wpdb->prefix}animal_cage_tracking_new ACT
            left join {$wpdb->prefix}invoices I
            on ACT.invoice_id = I.id
            left join {$wpdb->prefix}cages_type CT
            on CT.id = ACT.cage_type_id
            where I.technician_id = '$technician_id'
            and ACT.retrieved = 0
        ");

    }

    public function getUnretrievedCagesRecords( string $time_period){
        global $wpdb;

        $conditions = [];

        $conditions[] = " ACT.retrieved = 0";

        if($time_period == "last_30_days"){
            $conditions[] = " date(I.date) < (NOW() - INTERVAL 30 DAY)";
            $conditions[] = " (alert_extended = 0 || alert_extended='' || alert_extended is null)";
        }

        if($time_period == "last_45_days"){
            $conditions[] = " date(I.date) < (NOW() - INTERVAL 45 DAY)";
        }

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

        return $wpdb->get_results("
            select ACT.id, ACT.created_at, ACT.pickup_date, I.client_name, I.address, I.phone_no, I.email, ACT.quantity_retrieved, I.invoice_no, I.date, I.technician_id, ACT.quantity
            from {$wpdb->prefix}animal_cage_tracking_new ACT

            left join {$wpdb->prefix}invoices I
            on ACT.invoice_id = I.id
            $conditions
        ");
    }

}

new AnimalCageTracker();