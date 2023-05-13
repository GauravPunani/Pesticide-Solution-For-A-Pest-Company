<?php

namespace EmployeePayment;

class ParkingTickets extends \GamFunctions{
    
    function __construct(){

        add_action("admin_post_create_parking_ticket", array($this, "create_parking_ticket"));
        add_action("admin_post_nopriv_create_parking_ticket", array($this, "create_parking_ticket"));

        add_action("wp_ajax_delete_parking_ticket", array($this, "delete_parking_ticket"));
        add_action("wp_ajax_nopriv_delete_parking_ticket", array($this, "delete_parking_ticket"));

        add_action("wp_ajax_view_parking_ticket_notes", array($this, "view_parking_ticket_notes"));
        add_action("wp_ajax_nopriv_view_parking_ticket_notes", array($this, "view_parking_ticket_notes"));

        add_action("wp_ajax_act_mark_ticket_completed", array($this, "mark_parking_ticket_as_completed"));
        add_action("wp_ajax_nopriv_act_mark_ticket_completed", array($this, "mark_parking_ticket_as_completed"));
        
        add_action("admin_post_add_ticket_note", array($this, "add_ticket_note"));
        add_action("admin_post_nopriv_add_ticket_note", array($this, "add_ticket_note"));
    }

    public function add_ticket_note(){
        global $wpdb;

        $this->verify_nonce_field('add_ticket_note');

        $page_url = $_POST['page_url'];

        if(
            empty($_POST['ticket_id']) ||
            empty($_POST['note'])
        ) $this->sendErrorMessage($page_url);

        $ticket_id = $this->sanitizeEscape($_POST['ticket_id']);
        $note = $this->sanitizeEscape($_POST['note'], 'textarea');
        
        $data = [
            'parking_ticket_id' =>  $ticket_id,
            'note'              =>  $note
        ];

        $response = $this->createTicketNotes($data);
        if(!$response) $this->sendErrorMessage($page_url, "Unable to create ticket note");

        $message = "Parking ticket note created successfully";
        $this->setFlashMessage($message, "success");

        wp_redirect($page_url);
    }

    public function mark_parking_ticket_as_completed(){
        global $wpdb;

        $this->verify_nonce_field('act_mark_ticket_completed');

        if(empty($_POST['ticket_id'])) $this->response('error');

        $ticket_id = $this->sanitizeEscape($_POST['ticket_id']);

        $update_ticket_status = $this->editTicket($ticket_id,['ticket_status' => 1]);
        if($update_ticket_status){
            $this->response('success', 'Ticket successfully mark as completed');
        }else{
            $this->response('error', 'Error on marking ticket as completed');
        }
    }

    public function view_parking_ticket_notes(){
        global $wpdb;

        $this->verify_nonce_field('view_parking_ticket_notes');

        if(empty($_POST['ticket_id'])) $this->response('error');

        $ticket_id = $this->sanitizeEscape($_POST['ticket_id']);

        $ticket_notes = $this->getTicketNotes($ticket_id);
        if(!$ticket_notes) $this->response('success', '<p class="text-danger">No Notes found</p>');

        $ticket_notes_html = "";

        foreach($ticket_notes as $note){
            $ticket_notes_html .= "
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        ".nl2br($note->note)." - ".date('d M Y', strtotime($note->created_at))."
                    </div>
                </div>
            ";
        }

        $this->response('success', $ticket_notes_html);
    }

    public function getTicketNotes(int $ticket_id){
        global $wpdb;

        return $wpdb->get_results("
            select *
            from {$wpdb->prefix}parking_ticket_notes
            where parking_ticket_id = '$ticket_id'
        ");
    }

    public function create_parking_ticket(){
        global $wpdb;

        $this->verify_nonce_field('create_parking_ticket');

        $page_url = esc_url_raw($_POST['page_url']);

        if(!isset($_POST['employee_id']) || empty($_POST['employee_id'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['week']) || empty($_POST['week'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['ticket_amount']) || empty($_POST['ticket_amount'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['description']) || empty($_POST['description'])) $this->sendErrorMessage($page_url);
        if(!isset($_FILES['proof_document']['name']) || count($_FILES['proof_document']['name']) <0) $this->sendErrorMessage($page_url, "Please fill all the required fields");

        $employee_id = $this->sanitizeEscape($_POST['employee_id']);
        $week = $this->sanitizeEscape($_POST['week']);
        $ticket_amount = $this->sanitizeEscape($_POST['ticket_amount']);
        $description = $this->sanitizeEscape($_POST['description'], 'textarea');
        $proof_docs = $this->uploadFiles($_FILES['proof_document']);

        $ticket_data = [
            'employee_id'       =>  $employee_id,
            'week'              =>  $week,
            'amount'            =>  $ticket_amount,
            'proof_doc'         =>  json_encode($proof_docs),
        ];

        $this->beginTransaction();

        $response = $this->createTicket($ticket_data);
        if(!$response) $this->rollBackTransaction($page_url, "Unable to create ticket");

        $parking_ticket_id = $wpdb->insert_id;

        // create ticket notes
        $ticket_notes_data = [
            'parking_ticket_id' =>  $parking_ticket_id,
            'note'              =>  $description
        ];

        $response = $this->createTicketNotes($ticket_notes_data);
        if(!$response) $this->rollBackTransaction($page_url, "Unable to create ticket notes");

        $this->commitTransaction();

        $message = "Parking ticket record created successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function createTicketNotes(array $data){
        global $wpdb;

        return $wpdb->insert($wpdb->prefix."parking_ticket_notes", $data);
    }

    public function delete_parking_ticket(){

        if(!isset($_POST['ticket_id']) || empty($_POST['ticket_id'])) $this->response('error', 'Something went wrong, please');

        $ticket_id = esc_html($_POST['ticket_id']);
        
        $this->beginTransaction();

        $response = $this->deleteTicket($ticket_id);
        if(!$response) $this->rollbackResponse('error');

        $this->commitTransaction();

        $this->response('success', 'Ticket deleted successfully');
    }

    public function createTicket( array $ticket_data ){
        global $wpdb;

        $ticket_data['created_at'] = date('Y-m-d h:i:s');
        $ticket_data['updated_at'] = date('Y-m-d h:i:s');
        return $wpdb->insert($wpdb->prefix."parking_tickets", $ticket_data);
    }

    public function deleteTicket($ticket_id){
        global $wpdb;

        // delete ticket notes first
        $response = $wpdb->delete($wpdb->prefix."parking_ticket_notes", ['parking_ticket_id' => $ticket_id]);
        if(!$response) return false;

        $response = $wpdb->delete($wpdb->prefix."parking_tickets", ['id' => $ticket_id]);
        if(!$response) return false;

        return true;
    }

    public function editTicket( int $ticket_id, array $ticket_data ){
        global $wpdb;

        $ticket_data['updated_at'] = date('Y-m-d h:i:s');

        return $wpdb->update($wpdb->prefix."parking_tickets", $ticket_data, ['id' => $ticket_id]);

    }

    public function getTicket(int $ticket_id){
        global $wpdb;
        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}parking_tickets
            where id = '$ticket_id'
        ");
    }

    public function getAllTickets( int $employee_id = 0, string $week = ''){
        global $wpdb;

        $conditions = [];

        if(!empty($employee_id)) $conditions[] = " employee_id = '$employee_id'";
        if(!empty($week)) $conditions[] = " week = '$week'";

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

        return $wpdb->get_results("
            select *
            from {$wpdb->prefix}parking_tickets
            $conditions
        ");
    }

    public function getTicketsTotal(int $employee_id, string $week){
        global $wpdb;

        $conditions = [];

        if(!empty($employee_id)) $conditions[] = " employee_id = '$employee_id'";
        if(!empty($week)) $conditions[] = " week = '$week'";

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

        return $wpdb->get_var("
            select COALESCE(SUM(amount),0)
            from {$wpdb->prefix}parking_tickets
            $conditions
        ");
    }

}

new ParkingTickets();