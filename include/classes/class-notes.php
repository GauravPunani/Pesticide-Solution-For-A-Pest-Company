<?php

class Notes extends Technician_details{

    private $service;

    function __construct(){

        add_action('wp_ajax_get_normal_notes_input',array($this,'get_normal_notes_input'));
        add_action('wp_ajax_nopriv_get_normal_notes_input',array($this,'get_normal_notes_input'));

        add_action('wp_ajax_get_special_notes_input',array($this,'get_special_notes_input'));
        add_action('wp_ajax_nopriv_get_special_notes_input',array($this,'get_special_notes_input'));

        add_action('wp_ajax_get_notes_fields',array($this,'get_notes_fields'));
        add_action('wp_ajax_nopriv_get_notes_fields',array($this,'get_notes_fields'));

        add_action( 'admin_post_nopriv_office_notes', array($this,'office_notes') );
		add_action( 'admin_post_office_notes', array($this,'office_notes') );

        add_action( 'admin_post_add_special_notes', array($this,'add_special_notes') );
        add_action( 'admin_post_nopriv_add_special_notes', array($this,'add_special_notes') );

		add_action( 'admin_post_nopriv_update_office_notes', array($this,'update_office_notes') );
		add_action( 'admin_post_update_office_notes', array($this,'update_office_notes') );

    }

    public function isNormalNotesAlreadySubmitted(int $technician_id, string $date){
        global $wpdb;

        return $wpdb->get_var("
            select COUNT(*)
            from {$wpdb->prefix}office_notes
            where technician_id = '$technician_id'
            and date='$date'
        ");
    }

    public function isSpecialNotesAlreadySubmitted(int $technician_id, string $date){
        global $wpdb;

        return $wpdb->get_var("
            select COUNT(*)
            from {$wpdb->prefix}special_notes
            where technician_id = '$technician_id'
            and date='$date'
        ");
    }

    public function get_normal_notes_input(){
        global $wpdb;
        
		$this->verify_nonce_field('get_normal_notes_input');

        if(empty($_POST['date'])) $this->response('error','Event Date Not found');

        $date = $this->sanitizeEscape($_POST['date']);

        $techician_data = $this->get_technician_data(['id','calendar_id']);

        if($this->isNormalNotesAlreadySubmitted($techician_data->id, $date)){
            echo "<div class='alert alert-success'>You've already submitted notes for this date </div>";
            wp_die();
        }

        $calendar_events = (new Calendar)->getNormalNotesEvents($techician_data->id, $date);

        if(count((array)$calendar_events) <= 0){
            echo "<div class='alert alert-info'>You've no event in calendar for the date ".date('d M Y',strtotime($date))." to submit notes for.</div>";
            wp_die();
        }

        $cloned_calendar_events = clone $calendar_events;
        $pending_events = (new Invoice)->getPendingEventsForDate($cloned_calendar_events, $techician_data->id, $date);

        if(count((array) $pending_events) > 0){
            if($date != date('Y-m-d')){
                echo "<div class='alert alert-info'>You've events pending in calendar for the. Please clear those events as well in order to continue with your account.</div>";
            }
            else{
                echo "<div class='alert alert-danger'>Please clear calendar events for the date before you try to add note for the date</div>";
                wp_die();
            }
        }

        $this->render_notes_input_fields($calendar_events);
        wp_die();
    }

    public function get_special_notes_input(){
        global $wpdb;
        
		$this->verify_nonce_field('get_special_notes_input');

        if(empty($_POST['date'])) $this->response('error','Event Date Not found');

        $date = $this->sanitizeEscape($_POST['date']);

        $techician_data = $this->get_technician_data(['id','calendar_id']);

        if($this->isSpecialNotesAlreadySubmitted($techician_data->id, $date)){
            echo "<div class='alert alert-success'>You've already submitted special notes for this date </div>";
            wp_die();
        }

        $calendar_events = (new Calendar)->getSpecialNotesEvents($techician_data->id, $date);

        if(count((array)$calendar_events) <= 0){
            echo "<div class='alert alert-info'>You've no special event in calendar for the date ".date('d M Y',strtotime($date))." to submit notes for.</div>";
            wp_die();
        }

        $cloned_calendar_events = clone $calendar_events;

        $this->render_special_notes_input_fields($calendar_events);

        wp_die();
    }

    public function render_notes_input_fields( object $calendar_events){

        foreach($calendar_events as $key=>$event){
            $data_notes = array();
            if (strpos($event->description, '@sn') === false) {
                $client_name=explode('-',$event->summary);
                $client_name=$client_name[0];
                $data_notes[] =  $client_name;

                ?>
                <div class="form-group">
                    <p>Client Name : <b><?= $client_name; ?></b></p>
                    <input type="hidden" name="notes[<?= $key; ?>][client_name]" value="<?= $client_name; ?>">
                    <input type="hidden" name="notes[<?= $key; ?>][event_id]" value="<?= $event->id; ?>">
                    <textarea placeholder="Note.." name="notes[<?= $key; ?>][note]" id="" cols="30" rows="5" class="form-control" required></textarea>
                    <p><b>Upload Images (optional)</b></p>
                    <input type="file" name="notes[<?= $key; ?>][optional_images][]" class="form-control" multiple="multiple">
                </div>
                <?php
            }
        }
        if(!empty($data_notes)){
            ?>
                <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit Notes</button>
            <?php
        }
    }

    public function update_office_notes(){
		global $wpdb;

		if($this->is_technician_logged_in()){

			if(isset($_POST['note_id']) && !empty($_POST['note_id'])){
				$data=[
					'note'			=>	$_POST['note'],
					'client_name'	=>	$_POST['client_name'],
				];

				$wpdb->update($wpdb->prefix."office_notes",$data,['id'=>$_POST['note_id']]);

				unset($_SESSION['note_editable']);

				if(isset($_POST['page_url']) && !empty($_POST['page_url'])){
					wp_redirect($_POST['page_url']);
				}else{
					wp_redirect('/technician-dashboard');
				}
			}
			else{
				wp_die('somehting went wrong, please try again later');
			}

		}
		else{
			wp_redirect('/technician-dashboard');
		}

    }

	public function office_notes(){
		global $wpdb;
        $page_url = esc_url_raw($_POST['page_url']);
        
        
        if(empty($_POST['date'])) $this->sendErrorMessage($page_url);
        if(!is_array($_POST['notes']) || count($_POST['notes']) <= 0) $this->sendErrorMessage($page_url);

        $technician_id=$this->get_technician_id();
        $date = $this->sanitizeEscape($_POST['date']);
        $notes = $_POST['notes'];
        $cdate = date("d M Y", strtotime($date));

        $calendar_token_path = $this->getCalendarAccessToken($technician_id);
        $calendar_id = $this->getTechnicianCalendarId($technician_id);

        $obj=new Calendar();

        foreach($notes as $key=>$note){

            $event = $obj->setAccessToken($calendar_token_path)
                        ->getEventById($calendar_id,$note['event_id']);

            if(empty($event)) $this->sendErrorMessage($page_url, "3");

            $description = $event->description;
            

            $desc_notes = $description."\n----------------------------------------------\n Dated : ".$cdate." ".$note['note']."\n----------------------------------------------\n";

            $response = $obj->updateCalendarEvent($calendar_id,$event->id,$desc_notes);

            $data=[
                'technician_id'	=>	$technician_id,
                'note'			=>	$note['note'],
                'type'			=>	'invoice',
                'client_name'	=>	$note['client_name'],
                'date'			=>	$date
            ];
            
            // check for optional proof images
            $opt_images = [];
            if(isset($_FILES['notes'])){
                $image_files = count($_FILES['notes']['name'][$key]['optional_images']);
                if ($image_files > 0){
                    for ($i = 0;$i < $image_files;$i++){
                        if ($_FILES['notes']['tmp_name'][$key]['optional_images'][$i] != ""){
                            $tmp_name = $_FILES['notes']['tmp_name'][$key]['optional_images'][$i];
                            $file_name = $_FILES['notes']['name'][$key]['optional_images'][$i];
                            $opt_images[$i]['file_name'] = $file_name;
                            $upload = wp_upload_bits($file_name, null, file_get_contents($tmp_name));
                            if (array_key_exists('url', $upload))
                            {
                                $opt_images[$i]['file_url'] = $upload['url'];
                            }
                        }
                    }
                }
            }

            

            if(count($opt_images)>0) $data['optional_images']=json_encode($opt_images);
            
            $response2 = $wpdb->insert($wpdb->prefix."office_notes",$data);
        }

        if(!$response2) $this->sendErrorMessage($page_url);

        if(!$response){
            $message = "Notes were submitted sucessfully in system but were not able to upload on google calendar due to permission issue. Please update the same to developers to fix.";
            $this->setFlashMessage($message, 'warning');
        }
        else{
            $message="Note Submitted Successfully";
            $this->setFlashMessage($message,'success');
        }

        // delete the note notice from technician account as well
        $conditions=[
            'date'			=>	$date,
            'technician_id'	=>	$technician_id,
            'type'			=>	'pending_notes'
        ];

        $wpdb->delete($wpdb->prefix."technician_account_status",$conditions);

        wp_redirect($page_url);
    }

    public function render_special_notes_input_fields($calendar_events){
        $data = array();
        foreach($calendar_events as $key=>$event){
            if (strpos($event->description, '@sn') !== false) {
                $client_name=explode('-',$event->summary);
                $client_name=$client_name[0];
                $data[] =  $client_name;
                ?>
                <div class="form-group">
                    <p>Client Name : <b><?= $client_name; ?></b></p>
                    <input type="hidden" name="notes[<?= $key; ?>][client_name]" value="<?= $client_name; ?>">
                    <input type="hidden" name="notes[<?= $key; ?>][event_id]" value="<?= $event->id; ?>">
                    <textarea placeholder="Note.." name="notes[<?= $key; ?>][note]" id="" cols="30" rows="5" class="form-control" required></textarea>
                    <p><b>Upload Images (optional)</b></p>
                    <input type="file" name="notes[<?= $key; ?>][optional_images][]" class="form-control" multiple="multiple">
                </div>
                <?php
            }
        }
            if(!empty($data)){
        ?>
            <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit Notes</button>
        <?php

            }

    }

    public function add_special_notes(){
		global $wpdb;

        $this->verify_nonce_field('add_special_notes');

        $page_url = esc_url_raw($_POST['page_url']);

        if(empty($_POST['date'])) $this->sendErrorMessage($page_url, "Date field is required");
        if(!is_array($_POST['notes']) || count($_POST['notes']) <= 0) $this->sendErrorMessage($page_url, "notes fields are required");

        $technician_id = $this->get_technician_id();
        $date = $this->sanitizeEscape($_POST['date']);
        $notes = $_POST['notes'];
        $cdate = date("d M Y", strtotime($date));

        $calendar_token_path = $this->getCalendarAccessToken($technician_id);
        $calendar_id = $this->getTechnicianCalendarId($technician_id);

        $this->beginTransaction();
        
        $obj = new Calendar();

        foreach($notes as $key => $note){

            $event = $obj->setAccessToken($calendar_token_path)
                        ->getEventById($calendar_id,$note['event_id']);

            if(empty($event)) $this->sendErrorMessage($page_url, "error finding event on calendar");

            $description = $event->description;

            $desc_notes = $description."\n----------------------------------------------\n Dated : ".$cdate." ".$note['note']."\n----------------------------------------------\n";

            $response = $obj->updateCalendarEvent($calendar_id,$event->id,$desc_notes);
            if(!$response) $this->rollBackTransaction($page_url, "unable to update notes on calendar");

            $data=[
                'technician_id'	    =>	$technician_id,
                'calendar_event_id'	=>	$note['event_id'],
                'note'			    =>	$note['note'],
                'client_name'	    =>	$note['client_name'],
                'date'			    =>	$date,
            ];

            $opt_images = [];
            if(isset($_FILES['notes'])){
                $image_files = count($_FILES['notes']['name'][$key]['optional_images']);
                if ($image_files > 0){
                    for ($i = 0;$i < $image_files;$i++){
                        if ($_FILES['notes']['tmp_name'][$key]['optional_images'][$i] != ""){
                            $tmp_name = $_FILES['notes']['tmp_name'][$key]['optional_images'][$i];
                            $file_name = $_FILES['notes']['name'][$key]['optional_images'][$i];
                            $opt_images[$i]['file_name'] = $file_name;
                            $upload = wp_upload_bits($file_name, null, file_get_contents($tmp_name));
                            if (array_key_exists('url', $upload))
                            {
                                $opt_images[$i]['file_url'] = $upload['url'];
                            }
                        }
                    }
                }
            }

            if(count($opt_images)>0) $data['optional_images']=json_encode($opt_images);

            $response = $wpdb->insert($wpdb->prefix."special_notes",$data);
            if(!$response) $this->rollBackTransaction($page_url, "Unable to save notes in database");
        }

        $this->commitTransaction();

        $message="Special Note Submitted Successfully";
        $this->setFlashMessage($message,'success');

        wp_redirect($page_url);
    }

    public function getSpecialNotes(array $conditions = [], array $columns = []){
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';
        
        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}special_notes
            $conditions
        ");
    }

    public function createNotes(array $data){
        global $wpdb;

        return $wpdb->insert($wpdb->prefix."office_notes", $data);
    }


}

new Notes();