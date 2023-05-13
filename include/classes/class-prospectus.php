<?php

class Prospectus extends GamFunctions{
    
    function __construct(){
        add_action( 'admin_post_add_prospect', array($this,'add_prospect') );
        add_action( 'admin_post_nopriv_add_prospect', array($this,'add_prospect') );

        add_action( 'wp_ajax_update_prospectus_status', array($this,'update_prospectus_status') );
        add_action( 'wp_ajax_nopriv_update_prospectus_status', array($this,'update_prospectus_status') );

        add_action( 'wp_ajax_set_reminder_week', array($this,'set_reminder_week') );
        add_action( 'wp_ajax_nopriv_set_reminder_week', array($this,'set_reminder_week') );

        add_action( 'wp_ajax_get_prospect_notes', array($this,'get_prospect_notes') );
        add_action( 'wp_ajax_nopriv_get_prospect_notes', array($this,'get_prospect_notes') );

        add_action( 'admin_post_update_recurring_prospect_status', array($this,'update_recurring_prospect_status') );
        add_action( 'admin_post_nopriv_update_recurring_prospect_status', array($this,'update_recurring_prospect_status') );
    }

    public function update_recurring_prospect_status(){
        global $wpdb;

        $this->verify_nonce_field('update_recurring_prospect_status');
        
        $page_url = $_POST['_wp_http_referer'];

        if(
            empty($_POST['status']) ||
            empty($_POST['notes'])
        ) $this->sendErrorMessage($page_url, "Please fill all the fields");

        $status = $this->sanitizeEscape($_POST['status']);
        $notes = $this->sanitizeEscape($_POST['notes'], 'textarea');
        
        // get prospect id 
        $prospect_id = (new InvoiceFlow)->getProspectId();
        if(!$prospect_id) $this->sendErrorMessage($page_url, 'unable to fetch prospect id');
        
        // get calendar event id
        $calendar_event_id = (new InvoiceFlow)->getCalendarId();
        if(!$calendar_event_id) $this->sendErrorMessage($page_url, 'unable to fetch calendar event id');
        
        // start transaction
        $this->beginTransaction();

        // update status in prospect table
        $update_data = ['status' => $status];
        $response = $this->updateProspectus($prospect_id, $update_data);
        if(!$response) $this->rollBackTransaction($page_url, 'Unable to update status for prospect');

        // insert notes in prospect notes table
        $prospect_notes_data = [
            'calendar_event_id' =>  $calendar_event_id,
            'prospect_id'       =>  $prospect_id,
            'notes'             =>  $this->sanitizeEscape($_POST['notes'], 'textarea')
        ];
        $response = $this->createProspectNotes($prospect_notes_data);
        if(!$response) $this->rollBackTransaction($page_url, 'unable to save prospect in system');

        // commit transaction
        $this->commitTransaction();

        $message = "Prospect status updated and notes submitted in system as well.";
        $this->setFlashMessage($message, 'success');

        (new InvoiceFlow)->resetInvoiceFlow();
    }

    public function get_prospect_notes(){
        global $wpdb;
        
        $this->verify_nonce_field('get_prospect_notes');

        if(
            empty($_POST['prospect_id'])
        ) $this->response('error');

        $prospect_id = $this->sanitizeEscape($_POST['prospect_id']);

        $conditions = [];
        $conditions[] = " prospect_id = '$prospect_id' ";

        $prospect_notes = $this->getProspectNotes($conditions, ['notes', 'created_at']);
        if(!$prospect_notes) $this->response('error', 'No Notes Found');
        
        $prospect_notes = array_map(function($note){
            return "
            <div class='panel panel-default'>
                <div class='panel-body'>
                <p>
                <span class='pull-right'>".date('d M Y h:i A', strtotime($note->created_at))."</span>
                ".nl2br($note->notes)."
                </p>
                </div>        
            </div>";
        }, $prospect_notes);

        $this->response('success', '', $prospect_notes);

    }

    public function set_reminder_week(){
        global $wpdb;

        $this->verify_nonce_field('set_reminder_week');

        if(
            empty($_POST['prospectus_id']) ||
            empty($_POST['week'])
        ) $this->response('error');

        $prospectus_id = $this->sanitizeEscape($_POST['prospectus_id']);
        $week = $this->sanitizeEscape($_POST['week']);

        $update_data = ['reminder_week' => $week];
        $response = $this->updateProspectus($prospectus_id, $update_data);
        if(!$response) $this->response('error');

        $this->response('success', 'Reminder week updated successfully');
    }

    public function update_prospectus_status(){
        global $wpdb;

        $this->verify_nonce_field('update_prospectus_status');

        if(
            empty($_POST['prospectus_id']) ||
            empty($_POST['status'])
        ) $this->response('error');

        $prospectus_id = $this->sanitizeEscape($_POST['prospectus_id']);
        $status = $this->sanitizeEscape($_POST['status']);

        $update_data = ['status' => $status];
        $response = $this->updateProspectus($prospectus_id, $update_data);
        if(!$response) $this->response('error');

        $this->response('success', 'Prospectus labeled successfully');
    }

    public function updateProspectus(int $prospectus_id, array $data){
        global $wpdb;

        $response = $wpdb->update($wpdb->prefix."prospectus", $data, ['id' => $prospectus_id]);
        return $response === false ? false : true;
    }

    public function add_prospect(){
        global $wpdb;

        $this->verify_nonce_field('add_prospect');

        $invocie_flow = new InvoiceFlow;
        $event_date = $invocie_flow->getEventDate();

        $calendar_event_id = $invocie_flow->getCalendarId();
        $employee_id = (new Employee\Employee)->__getLoggedInEmployeeId();
        $technician_id = (new Technician_details)->get_technician_id();
        $page_url = esc_url_raw($_POST['page_url']);

        if(
            empty($_POST['client_name']) ||
            empty($_POST['status']) ||
            empty($_POST['notes']) ||
            empty($calendar_event_id) ||
            empty($employee_id) ||
            empty($technician_id)
        ) $this->sendErrorMessage($page_url);

        $status = $this->sanitizeEscape($_POST['status']);
        $notes = $this->sanitizeEscape($_POST['notes'], 'textarea');

        $data = [
            "calendar_event_id" =>  $calendar_event_id,
            "employee_id"       =>  $employee_id,
            "name"              =>  $this->sanitizeEscape($_POST['client_name']),
            'status'            =>  $status,
            'event_date'        =>  $event_date
        ];

        if(!empty($_POST['phone'])) $data['phone'] = $this->sanitizeEscape($_POST['phone']);
        if(!empty($_POST['email'])) $data['email'] = sanitize_email($_POST['email']);
        if(!empty($_POST['address'])) $data['address'] = $this->sanitizeEscape($_POST['address']);
        if(!empty($_POST['business_name'])) $data['business_name'] = $this->sanitizeEscape($_POST['business_name']);

        $this->beginTransaction();

        $response = $wpdb->insert($wpdb->prefix."prospectus",$data);
        if(!$response) $this->rollBackTransaction($page_url, 'error inserting prospect');

        $prospect_id = $wpdb->insert_id;

        $prospect_notes_data = [
            'calendar_event_id' =>  $calendar_event_id,
            'prospect_id'       =>  $prospect_id,
            'notes'             =>  $notes,
            'event_date'        =>  $event_date
        ];

        $response = $wpdb->insert($wpdb->prefix."prospect_notes", $prospect_notes_data);
        if(!$response) $this->rollBackTransaction($page_url, 'error inserting notes');

        // if prospect interested , create task for office to set reminder or put on calendar
        if($status == "interested" || $status == "semi_interested"){
            $response = (new OfficeTasks)->prospectReminderTask($prospect_id);
            if(!$response) $this->rollBackTransaction($page_url, 'Unable to create office task for reminder');
        }

        // push the notes to calendar event as well
        $response = (new Calendar)->uploadNotesInEvent(
            $technician_id,
            $calendar_event_id,
            $notes,
            date('d M Y')
        );
        if(!$response) $this->rollBackTransaction($page_url, "Unable to save notes in calendar event");

        $this->commitTransaction();

        $message="Prospect added to system successfully";
        $this->setFlashMessage($message,'success');

        (new InvoiceFlow)->resetInvoiceFlow();
    }

    public function getProspect(int $prospect_id){
        global $wpdb;

        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}prospectus
            where id = '$prospect_id'
        ");
    }

    public function getProspectNotes(array $conditions = [], array $columns = []){
        global $wpdb;

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';
        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}prospect_notes
            $conditions
        ");
    }

    public function createProspectNotes(array $data){
        global $wpdb;
        return $wpdb->insert($wpdb->prefix."prospect_notes", $data);
    }    
}
new Prospectus();