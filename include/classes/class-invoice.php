<?php

class Invoice extends GamFunctions
{

	private $err_msg = 'Something went wrong, please try again later';

	function __construct()
	{

		add_action('wp_ajax_get_invoice_amount', array($this, 'get_invoice_amount'));
		add_action('wp_ajax_nopriv_get_invoice_amount', array($this, 'get_invoice_amount'));

		add_action('wp_ajax_search_invoice_by_ajax', array($this, 'search_invoice_by_ajax'));
		add_action('wp_ajax_nopriv_search_invoice_by_ajax', array($this, 'search_invoice_by_ajax'));

		add_action('wp_ajax_get_technician_events', array($this, 'get_technician_events'));
		add_action('wp_ajax_nopriv_get_technician_events', array($this, 'get_technician_events'));

		add_action('wp_ajax_get_event_processed_employee', array($this,'technician_invoice_processed_by_office_staff'));
		add_action('wp_ajax_nopriv_get_event_processed_employee', array($this, 'technician_invoice_processed_by_office_staff'));

		add_action('wp_ajax_get_calendar_events_by_technician', array($this, 'get_calendar_events_by_technician'));
		add_action('wp_ajax_nopriv_get_calendar_events_by_technician', array($this, 'get_calendar_events_by_technician'));

		add_action('wp_ajax_update_invoice_status', array($this, 'update_invoice_status'));
		add_action('wp_ajax_nopriv_update_invoice_status', array($this, 'update_invoice_status'));

		add_action('wp_ajax_calculate_invoice_data', array($this, 'calculate_invoice_data'));
		add_action('wp_ajax_nopriv_calculate_invoice_data', array($this, 'calculate_invoice_data'));

		add_action('wp_ajax_list_invoice_statements', array($this, 'list_invoice_statements'));
		add_action('wp_ajax_nopriv_list_invoice_statements', array($this, 'list_invoice_statements'));

		add_action('wp_ajax_select_invoice', array($this, 'select_invoice'));
		add_action('wp_ajax_nopriv_select_invoice', array($this, 'select_invoice'));

		add_action('wp_ajax_get_selected_invoices', array($this, 'get_selected_invoices'));
		add_action('wp_ajax_nopriv_get_selected_invoices', array($this, 'get_selected_invoices'));

		add_action('admin_post_set_event_details', array($this, 'set_event_details'));
		add_action('admin_post_nopriv_set_event_details', array($this, 'set_event_details'));

		add_action('wp_ajax_update_invoice_office_sent_bill', array($this, 'update_invoice_office_sent_bill'));
		add_action('wp_ajax_nopriv_update_invoice_office_sent_bill', array($this, 'update_invoice_office_sent_bill'));

		add_action('wp_ajax_update_invoice_client_status', array($this, 'update_invoice_client_status'));
		add_action('wp_ajax_nopriv_update_invoice_client_status', array($this, 'update_invoice_client_status'));

		add_action('wp_ajax_verify_technician_code', array($this, 'verify_technician_code'));
		add_action('wp_ajax_nopriv_verify_technician_code', array($this, 'verify_technician_code'));

		add_action('wp_ajax_insert_technician_code', array($this, 'insert_technician_code'));
		add_action('wp_ajax_nopriv_insert_technician_code', array($this, 'insert_technician_code'));

		add_action('wp_ajax_check_bypassed_events', array($this, 'check_bypassed_events'));
		add_action('wp_ajax_nopriv_check_bypassed_events', array($this, 'check_bypassed_events'));

		add_action('wp_ajax_invoice_update_email', array($this, 'invoice_update_email'));
		add_action('wp_ajax_nopriv_invoice_update_email', array($this, 'invoice_update_email'));

		add_action('wp_ajax_send_invoice_to_email', array($this, 'send_invoice_to_email'));
		add_action('wp_ajax_nopriv_send_invoice_to_email', array($this, 'send_invoice_to_email'));

		add_action('admin_post_nopriv_edit_invoice', array($this, 'edit_invoice'));
		add_action('admin_post_edit_invoice', array($this, 'edit_invoice'));

		add_action('admin_post_nopriv_download_invoice', array($this, 'download_invoice'));
		add_action('admin_post_download_invoice', array($this, 'download_invoice'));

		add_action('admin_post_nopriv_download_service_report', array($this, 'download_service_report'));
		add_action('admin_post_download_service_report', array($this, 'download_service_report'));

		add_action('admin_post_nopriv_invoice_additional_doc', array($this, 'invoice_additional_doc'));
		add_action('admin_post_invoice_additional_doc', array($this, 'invoice_additional_doc'));

		add_action('admin_post_nopriv_invoice_add_note', array($this, 'invoice_add_note'));
		add_action('admin_post_invoice_add_note', array($this, 'invoice_add_note'));

		add_action('admin_post_nopriv_invoice_form', array($this, 'invoice_form'));
		add_action('admin_post_invoice_form', array($this, 'invoice_form'));

		add_action('admin_post_nopriv_reset_invoice_form', array($this, 'reset_invoice_form'));
		add_action('admin_post_reset_invoice_form', array($this, 'reset_invoice_form'));

		add_action('wp_ajax_nopriv_sms_invoice_link', array($this, 'sms_invoice_link'));
		add_action('wp_ajax_sms_invoice_link', array($this, 'sms_invoice_link'));

		add_action('admin_post_change_maintenance_offered_status', array($this, 'change_maintenance_offered_status'));
		add_action('admin_post_nopriv_change_maintenance_offered_status', array($this, 'change_maintenance_offered_status'));

		add_action('wp_ajax_nopriv_delete_invoice', array($this, 'delete_invoice'));
		add_action('wp_ajax_delete_invoice', array($this, 'delete_invoice'));

		// bluk email notification to clients about special treatment
		add_action('wp_ajax_nopriv_notify_client_spring_treat', array($this, 'send_bulk_spring_notification'));
		add_action('wp_ajax_notify_client_spring_treat', array($this, 'send_bulk_spring_notification'));

		// delete extra invoice images
		add_action('wp_ajax_nopriv_invoice_delete_extra_images', array($this, 'invoice_delete_extra_imgs'));
		add_action('wp_ajax_invoice_delete_extra_images', array($this, 'invoice_delete_extra_imgs'));
	}

	public function invoice_delete_extra_imgs()
	{
		global $wpdb;

		$this->verify_nonce_field('remove_optional_invoice_images');

		if (empty($_POST['inv_id']) && empty($_POST['img_data'])) $this->response('error', 'Invoice ID and image data is missing.');

		$img_data = [];
		$invoice_id = sanitize_text_field($_POST['inv_id']);
		$img_data = $_POST['img_data'];
		$invoice_data = $this->getInvoiceById($invoice_id, ['id', 'optional_images']);
		$decode_inv_data = json_decode($invoice_data->optional_images);
		$output = self::remove_selected_invoice_img($decode_inv_data, $img_data);
		$update = $wpdb->update($wpdb->prefix . "invoices", ['optional_images' => count($output) > 0 ? json_encode($output) : null], ['id' => $invoice_id]);
		if ($update) {
			$this->response('success', '', ['img_id' => $img_data['id']]);
		} else {
			$this->response('error');
		}
	}

	public function send_bulk_spring_notification()
	{
		global $wpdb;

		$this->verify_nonce_field('client_bulk_spring_notify');

		$selected_client_list = $_POST['selected_clients'];

		$clients = [];
		if (is_array($selected_client_list) && count($selected_client_list) > 0) {
			// clean data
			for ($x = 0; $x < count($selected_client_list); $x++) {
				$clients[] = json_decode((new GamFunctions)->encrypt_data($selected_client_list[$x], 'd'));
			}

			// time to loop and send email to client
			if (count($clients) > 0) {
				for ($x = 0; $x < count($clients); $x++) {
					$data = [
						'tbl' => 'emails',
						'where' => $clients[$x]->id
					];
					$update_data = [
						'email_receive' => date('Y-m-d H:i:s')
					];
					$mail_send = (new Emails)->emailSpringTreatment($clients[$x]);
					if ($mail_send) {
						// save data in db
						$record_update = (new GamFunctions)->updateRecordInDbTable($data, $update_data);
					}
				}
			}
			if ($record_update) {
				$this->response('success', 'Notification sent succesfully');
			} else {
				$this->response('error', 'something went wrong');
			}
		}
	}

	public function sms_invoice_link()
	{

		$this->verify_nonce_field('sms_invoice_link');

		if (empty($_POST['phone_no'])) $this->response('error');
		if (empty($_POST['invoice_id'])) $this->response('error');

		$invoice_id = sanitize_text_field($_POST['invoice_id']);
		$phone_no = sanitize_text_field($_POST['phone_no']);

		$response = (new Twilio)->sendInvoiceLink($invoice_id, $phone_no);
		if (!$response) $this->response('error');

		$this->response('succes', 'Inovoice link messaged to client phone number successfully');
	}

	public function delete_customer_invoice(int $invoice_id)
	{
		global $wpdb;
		return $wpdb->update($wpdb->prefix . "invoices", ['is_deleted' => 1], ['id' => $invoice_id]);
	}

	public function delete_invoice()
	{
		global $wpdb;

		$this->verify_nonce_field('delete_invoice');

		if (empty($_POST['invoice_id'])) $this->response('error');

		$invoice_id = sanitize_text_field($_POST['invoice_id']);

		if (!$this->delete_customer_invoice($invoice_id)) $this->response('error');
		$this->response('success');
	}

	public function get_invoice_amount()
	{
		global $wpdb;

		if (empty($_POST['invoice_id'])) $this->response('error', $this->err_msg);

		$invoice_id = sanitize_text_field($_POST['invoice_id']);

		$invoice_amount = $wpdb->get_var("
			select total_amount 
			from {$wpdb->prefix}invoices 
			where id='$invoice_id'
		");

		if (!$invoice_amount) $this->response('error', $this->err_msg);

		$this->response('success', '', ['payble_amount' => $invoice_amount]);
	}

	public function set_event_details()
	{
		$event_data = json_decode(stripslashes($_POST['event_data']), true);
		// set the invoice data in session
		$_SESSION['invoice-data']['client-data'] = $event_data;
		$_SESSION['invoice-data']['client-data']['date'] = $_POST['event_date'];

		// set technician id in session if invoice process by office staff		
		if(isset($_POST['office_staff_invoice']) && !empty($_POST['office_staff_invoice'])){
			$ofc_event_data = json_decode(stripslashes($_POST['office_staff_invoice']), true);
			$_SESSION['technician_id'] = $ofc_event_data['tech_id'];
			$_SESSION['calendar_event_id'] = $ofc_event_data['event_id'];
		}

		(new InvoiceFlow)->callNextPageInFlow();
	}


	public function search_invoice_by_ajax()
	{

		global $wpdb;

		$data['results'] = [];

		if (empty($_POST['search'])) {
			echo json_encode($data);
			wp_die();
		}

		$search = sanitize_text_field($_POST['search']);

		$whereSearch = $this->get_table_coloumn($wpdb->prefix . 'invoices');
		$whereSearch = $this->create_search_query_string($whereSearch, $search);

		$invoices = $wpdb->get_results("
			select id,invoice_no,client_name,date 
			from {$wpdb->prefix}invoices 
			$whereSearch
			order by date Desc
		");

		if (is_array($invoices) && count($invoices) > 0) {
			foreach ($invoices as $invoice) {
				$data['results'][] = [
					'id'	=>	$invoice->id,
					'text'	=>	$invoice->invoice_no . " - " . $invoice->client_name . " - ($invoice->date)",
				];
			}
		}

		echo json_encode($data);
		wp_die();
	}

	public function change_maintenance_offered_status()
	{
		global $wpdb;

		$page_url = esc_url_raw($_POST['page_url']);

		if (empty($_POST['invoice_id'])) wp_redirect($page_url);
		exit;

		$invoice_id = sanitize_text_field($_POST['invoice_id']);
		$invoice_id = $this->encrypt_data($invoice_id, 'd');
		$wpdb->update($wpdb->prefix . "invoices", ['maintenance_offered' => 'offered'], ['id' => $invoice_id]);

		wp_redirect($page_url);
	}

	// checking for bypassed events 
	public function check_bypassed_events()
	{
		global $wpdb;

		if (empty($_POST['event_id'])) $this->response('error', $this->err_msg);
		if (empty($_POST['type'])) $this->response('error', $this->err_msg);

		$event_id = sanitize_text_field($_POST['event_id']);
		$type = sanitize_text_field($_POST['type']);

		$event = $wpdb->get_row("
			select * from 
			{$wpdb->prefix}bypassed_events 
			where event_id='$event_id' 
			and type='$type'
		");

		if (!$event) $this->response('error', $this->err_msg);

		$this->response('success', 'Event is in db');
	}

	public function getTechnicianEventsFromDb(int $technician_id, $start_date = '', $end_date = '', $table_name = '')
	{
		global $wpdb;
		$where = '';

		if (!empty($start_date)) $where .= " and DATE(date) >='$start_date' ";

		if (!empty($end_date)) $where .= " and DATE(date) <='$end_date' ";
		else $where .= " and DATE(date) ='$start_date' ";

		$technician_events = $wpdb->get_results("
			select * 
			from {$wpdb->prefix}$table_name 
			where technician_id = '$technician_id' 
			$where
		");

		return count((array)$technician_events) > 0 ? $technician_events : [];
	}

	public function getPendingEventsForDate(object $calendar_events, int $technician_id, string $date)
	{

		$conditions = [];
		$conditions[] = " employee_id = '$technician_id' ";
		$conditions[] = " date >= '$date' ";
		$conditions[] = " date <= '$date' ";

		$invoice_events = (new Invoice)->getTechnicianEventsFromDb($technician_id, $date, $date, 'invoices');
		$res_quote_events = (new Invoice)->getTechnicianEventsFromDb($technician_id, $date, $date, 'quotesheet');
		$comm_quote_events = (new Invoice)->getTechnicianEventsFromDb($technician_id, $date, $date, 'commercial_quotesheet');

		$prospectConditions = [];
		$prospectConditions[] = " date(event_date) = '$date' ";
		$prospectNotes = (new Prospectus)->getProspectNotes($prospectConditions, ['calendar_event_id']);

		$saved_events = array_merge($invoice_events, $res_quote_events, $comm_quote_events, $prospectNotes);

		// compare calendar and db event for current date and don't show which are already in database 
		return $this->filterEvents($calendar_events, $saved_events);
	}

	public function get_calendar_events_by_technician()
	{

		if (empty($_POST['technician_id'])) $this->response('error', 'Technician name not found');

		$technician_id = sanitize_text_field($_POST['technician_id']);

		//get technician calendar id and calendar_token_path
		$calendar_id = (new Technician_details)->getTechnicianCalendarId($technician_id);
		$calendar_token_path = (new Technician_details)->getCalendarAccessToken($technician_id);

		$calendar = new Calendar();
		$from_date = $to_date = '';

		if (!empty($_POST['from_date'])) {
			$calendar->from_date($_POST['from_date']);
			$from_date = esc_html($_POST['from_date']);
		}

		if (!empty($_POST['to_date'])) {
			$calendar->to_date($_POST['to_date']);
			$to_date = esc_html($_POST['to_date']);
		}

		try {
			$calendar_events = $calendar->setAccessToken($calendar_token_path)
				->getAllEvents($calendar_id);

			if (empty($calendar_events)) $this->response('error', 'no calendar events found', []);

			// fetch invoices(events) for those dates from db as well and compart there id's if match any 
			$db_invoice_events = $this->getTechnicianEventsFromDb($technician_id, $from_date, $to_date, 'invoices');

			// fetch quotesheet events from db 
			$db_commercial_quotesheet_events = $this->getTechnicianEventsFromDb($technician_id, $from_date, $to_date, 'commercial_quotesheet');

			$db_residential_quotesheet_events = $this->getTechnicianEventsFromDb($technician_id, $from_date, $to_date, 'quotesheet');

			$response = [
				'calendar'					=>	$calendar_events,
				'invoice_events'			=>	$db_invoice_events,
				'commercial_quote_events'	=>	$db_commercial_quotesheet_events,
				'residential_quote_events'	=>	$db_residential_quotesheet_events
			];

			$this->response('success', 'data found', $response);
		} catch (Exception $e) {
			$this->response('error', 'something went wrong with calendar api');
		}
	}

	public function getEventDataByTechId(string $table_name, int $technician_id, string $start_date, $end_date = '', array $columns = [])
	{
		global $wpdb;

		$conditions = [];

		$conditions[] = " DATE(date) >= '$start_date'";

		$conditions[] = empty($end_date) ? " DATE(date) <= '$start_date'" : " DATE(date) <= '$end_date'";

		$columns = count($columns) > 0 ? implode(',', $columns) : '*';

		$conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

		return $wpdb->get_results("
			select $columns
			from {$wpdb->prefix}$table_name
			$conditions
		");
	}

	public function checkPendingEvents($technician_id, $current_date)
	{

		$technician = new Technician_details;

		$calendar_id = $technician->getTechnicianCalendarId((int)$technician_id);
		$calendar_token_path = $technician->getCalendarAccessToken($technician_id);

		// if current date is from previous week , ignore checking for pending events 
		$this_sunday = date('Y-m-d', strtotime('last sunday'));
		if ($current_date < $this_sunday) return [];

		$day = date('w', strtotime($current_date));
		$one_day_before_date = date('Y-m-d', strtotime('-1 day', strtotime($current_date)));
		list($week_monday_date, $week_end_date) = $this->x_week_range($current_date);

		try {
			$obj = new Calendar();
			$obj->setAccessToken($calendar_token_path);
		} catch (Exception $e) {
			$this->response('error', 'Calendar token not found or incorrect');
		}

		$before_calendar_events = [];

		switch ($day) {

				// we don't check for event on sunday & monday
			case '0':
			case '1':
				return [];
				break;

				// on tuesday we check for events of monday only
			case '2':
				$before_calendar_events = $obj->getCurrentDateEvent($week_monday_date, $calendar_id);
				break;

				// on rest of the days we check for all previous days events from monday
			case '3':
			case '4':
			case '5':
			case '6':
				$before_calendar_events = $obj->getEventByDate($week_monday_date, $one_day_before_date, $calendar_id);
				break;
		}

		// check if calendar have atleast one event for that date 
		if (count((array)$before_calendar_events) <= 0) return [];

		$before_db_invoice_events = $this->getEventDataByTechId("invoices", $technician_id, $week_monday_date, $one_day_before_date, ['calendar_event_id']);

		$before_residential_quotes = $this->getEventDataByTechId("quotesheet", $technician_id, $week_monday_date, $one_day_before_date, ['calendar_event_id']);

		$before_commercial_quotes = $this->getEventDataByTechId("commercial_quotesheet", $technician_id, $week_monday_date, $one_day_before_date, ['calendar_event_id']);

		$conditions = [];
		$conditions[] = " date(event_date) >= '$week_monday_date' ";
		$conditions[] = " date(event_date) <= '$one_day_before_date' ";

		$prospectNotes = (new Prospectus)->getProspectNotes($conditions, ['calendar_event_id']);

		// merget all db events , (invoice , residential ,commerical quotes)
		$all_db_events = array_merge($before_db_invoice_events, $before_residential_quotes, $before_commercial_quotes, $prospectNotes);

		// compare calendar events from all db events and return the remaining calednar events
		$final_events = $this->filterEvents($before_calendar_events, $all_db_events);

		return count((array)$final_events) <= 0 ? [] : $final_events;
	}

	public function isEventExistInSystem(string $event_id)
	{
		global $wpdb;

		$invoice = $wpdb->get_var("
			select count(*)
			from {$wpdb->prefix}invoices
			where calendar_event_id = '$event_id'
		");

		if ($invoice) return [true, 'invoices'];

		$residential_quote = $wpdb->get_var("
			select count(*)
			from {$wpdb->prefix}quotesheet
			where calendar_event_id = '$event_id'
		");

		if ($residential_quote) return [true, 'quotesheet'];

		$commercial_quote = $wpdb->get_var("
			select count(*)
			from {$wpdb->prefix}commercial_quotesheet
			where calendar_event_id = '$event_id'
		");

		if ($commercial_quote) return [true, 'commercial_quotesheet'];

		return [false, null];
	}

	public function updateSystemEventId(string $old_event_id, string $new_event_id, $table_name)
	{
		global $wpdb;

		return $wpdb->update($wpdb->prefix . $table_name, ['calendar_event_id' => $new_event_id], ['calendar_event_id' => $old_event_id]);
	}

	public function filterEvents($calendar_events, $db_events)
	{

		if (count((array)$calendar_events) <= 0) return $calendar_events;
		if (count((array)$db_events) <= 0) return $calendar_events;

		foreach ($calendar_events as $key => $calendar_event) {

			$parts = explode('_', $calendar_event->id);
			$calendar_event_id = $parts[0];

			if (array_key_exists(1, $parts)) {
				list($status, $table_name) = $this->isEventExistInSystem($calendar_event_id);
				if ($status) {
					$this->updateSystemEventId($calendar_event_id, $calendar_event->id, $table_name);
					unset($calendar_events->$key);
					continue;
				}
			}

			foreach ($db_events as $db_event) {
				if ($calendar_event->id == $db_event->calendar_event_id) unset($calendar_events->$key);
			}
		}

		return $calendar_events;
	}

	public function sanitizeAddressField($address = '')
	{
		return preg_replace("/\r|\n/", " ", trim($address));
	}

	public function filter_invoice_fields($field = '')
	{
		$field = str_replace('"', '', $field);
		$field = htmlspecialchars($field, ENT_QUOTES);
		$field = $this->sanitizeEscape($field);
		return $field;
	}

	public function technician_invoice_processed_by_office_staff()
	{
		global $wpdb;

		$event_data = $_POST['event_data'];
		if (empty($event_data) && !is_array($event_data)) $this->response('error');

		$office_inv = sanitize_text_field($event_data['staff_invoice']);
		$event_date = sanitize_text_field($event_data['event_date']);
		if (empty($office_inv)) $this->response('error');
		$event_data['tech_name'] = (new Technician_details)->get_technician_name();
		$event_data['date'] = $event_date;
		$event_data['calendar_id'] = $event_data['calendar_id'];
		$response = (new OfficeTasks)->remindStaffOnCreateInvoice($event_data);
		if ($response) $this->response('success', 'Notification sent to Office staff and now its office responsibility to create invoice for you.');
	}

	public function get_technician_events()
	{
		global $wpdb;

		if (empty($_POST['appointement_date'])) $this->response('error', 'appointement date not found');

		$appointement_date = sanitize_text_field($_POST['appointement_date']);
		$tech_id = @sanitize_text_field($_POST['emp_id']);
		$event_id = @sanitize_text_field($_POST['event_id']);

		if(isset($tech_id) && !empty($tech_id)){
			$technician_id = (new GamFunctions)->encrypt_data($tech_id,'d');
		}else{
			$technician_id = (new Technician_details)->get_technician_id();
		}
		
		// if event check is false then we don't need to check pending events 
		if (!empty($_POST['event_check']) && $_POST['event_check'] == 'true') {

			// check for previous date pending events first for the current week
			$pending_events = $this->checkPendingEvents($technician_id, $appointement_date);

			if (is_object($pending_events) && count((array)$pending_events) > 0) {
				$this->response('error', 'These Events are pending before the current date, please clear those first ', $pending_events);
			}
		}

		$calendar_events = (new Calendar)->getTechnicianCalendarEvents($technician_id, $appointement_date);

		if (count((array)$calendar_events) <= 0)
			$this->response('error', 'No Event Found in calendar for the date');

		// get today invoices calendar event id
		$final_events = $this->getPendingEventsForDate($calendar_events, $technician_id, $appointement_date);

		if (count((array)$final_events) <= 0) $this->response('error', 'No pending event for the date', $final_events);

		$event_data = $ofc_event_data = [];

		foreach ($final_events as $key => $value) {

			$event_data[$key]['chemical_bypass'] = 'false';
			$event_data[$key]['maintenance_bypass'] = 'false';
			$event_data[$key]['prospect_event'] = 'false';
			$event_data[$key]['recurring'] = "false";
			$event_data[$key]['lead_source'] = "";
			$event_data[$key]['invoice_label'] = "";

			// check for chemical bypass in description
			if (strpos($value->description, '@sn') !== false) {
				$event_data[$key]['prospect_event'] = 'true';
			}

			if (strpos($value->description, '@cb') !== false) {
				$event_data[$key]['chemical_bypass'] = 'true';
			}

			// check for maintenance bypass in description
			if (strpos($value->description, '@mb') !== false) {
				$event_data[$key]['maintenance_bypass'] = 'true';
				$event_data[$key]['recurring'] = "true";
			}

			// check for lead source cold call in description
			if (strpos($value->description, '@cc') !== false) {
				$event_data[$key]['lead_source'] = 'cold_call';
			}

			// check for josh client code in description
			if (strpos($value->description, '@joshclient') !== false) {
				$event_data[$key]['invoice_label'] = 'josh_client';
			}

			// check for josh client code in description
			if (strpos($value->description, '@jacobclient') !== false) {
				$event_data[$key]['invoice_label'] = 'jacob_client';
			}

			// check for office staff invoice
			if (strpos($value->description, '@ofc_inv') !== false) {
				$event_data[$key]['staff_invoice'] = 'true';
			}

			$client_name = explode('-', $value->summary);
			$event_data[$key]['id'] = $value->id;
			$event_data[$key]['client_name'] = $this->filter_invoice_fields($client_name[0]);
			$event_data[$key]['summary'] = $this->filter_invoice_fields($value->summary);
			$event_data[$key]['location'] = $this->sanitizeAddressField($this->filter_invoice_fields($value->location));
			$event_data[$key]['start_time'] = date('d-m-Y', strtotime($value->start->dateTime));
			$event_data[$key]['description'] = $value->description;

			// foreach calendar event fetch sales tax rate from db 

			$phone_no_pattern = "/#([0-9]{3}-+[0-9]{3}-+[0-9]{4})|#([0-9]{10})/";
			$email_pattern = "/e:([\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+)/m";
			$sales_amount_pattern = '/@sf="(.*?)"/';
			$payment_method_pattern = '/@pm="(.*?)"/';


			preg_match($phone_no_pattern, $value->description, $phone_no);
			preg_match($email_pattern, $value->description, $client_email);
			preg_match($sales_amount_pattern, $value->description, $sales_amount);
			preg_match($payment_method_pattern, $value->description, $payment_method);

			if (array_key_exists(1, $sales_amount)) $event_data[$key]['service_fee'] = $sales_amount[1];
			if (array_key_exists(1, $payment_method)) $event_data[$key]['payment_method'] = $payment_method[1];

			if (is_array($phone_no) && count($phone_no) > 0) {
				$phone_no = ltrim($phone_no[0], '#');
				$event_data[$key]['phone_no'] = $phone_no;
			} else {
				$event_data[$key]['phone_no'] = "";
			}

			if (is_array($client_email) && count($client_email) > 0) {
				//$client_email = ltrim($client_email[0], 'e:');
				$client_email = trim($client_email[1]);
				$event_data[$key]['client_email'] = $client_email;
			} else {
				$event_data[$key]['client_email'] = "";
			}

			$event_data[$key]['tax_exempt'] = 'false';
			// if @te present , it means this invoice is tax exempted
			if (strpos($value->description, '@te') !== false) {
				$event_data[$key]['tax_exempt'] = 'true';
				$event_data[$key]['tax_rate'] = 0;
			}
			// if california  set tax to zero
			elseif (preg_match('/CA\ (\d{3,6})/', $value->location) === 1) {
				$event_data[$key]['tax_rate'] = 0;
			}
			// if hawai set tax to 4%
			elseif (preg_match('/HI\ (\d{3,6}) /mix', $value->location) === 1) {
				$event_data[$key]['tax_rate'] = 4;
			} else {

				$zip_code_pattern = "/NY\ (\d{3,6}) | LA\ (\d{3,6}) | FL\ (\d{3,6}) | NJ\ (\d{3,6}) | TX\ (\d{3,6}) | CA\ (\d{3,6}) | HI (\d{3,6}) /mix";

				preg_match($zip_code_pattern, $value->location, $client_zip_code);

				if (is_array($client_zip_code) && count($client_zip_code) > 0) {

					$client_zip_code = trim($client_zip_code[0]);
					$client_zip_code = explode(" ", $client_zip_code)[1];

					if (!empty($client_zip_code)) {

						$event_data[$key]['zip_code'] = $client_zip_code;
						$sales_tax_rate = $wpdb->get_var("
							select combined_rate
							from {$wpdb->prefix}sales_tax_rates 
							where zipcode='$client_zip_code'
						");

						if ($sales_tax_rate) {
							$event_data[$key]['tax_rate'] = $sales_tax_rate * 100;
						}
					}
				}
			}

			// only return specific key if event contain staff invoice code
			if (isset($tech_id) 
				&& !empty($tech_id) 
				&& array_key_exists('staff_invoice',$event_data[$key])
				&& $event_data[$key]['id'] == $event_id
			){
				$ofc_event_data[] = $event_data[$key];
			}

			//check if task already created for calendar event
			$calendar_ids = $this->getTaskManagerMetaDetails([
				'where' => 'event_calendar_id IS NOT NULL',
				'col' => 'event_calendar_id'
			]);
			if(count($calendar_ids) > 0 && in_array($event_data[$key]['id'],$calendar_ids)){
				unset($event_data[$key]);
			}
		}

		if(count($ofc_event_data) > 0) {
			$this->response('success', 'Office Calendar Events Found', (object)$ofc_event_data);
		}

		$this->response('success', 'Calendar Events Found', (object)$event_data);
	}

	public function getTaskManagerMetaDetails(array $data){
		global $wpdb;
		$event_ids = $wpdb->get_results("
			SELECT * FROM {$wpdb->prefix}task_meta WHERE {$data['where']}
		");
		return (!empty($data['col']) ? array_column($event_ids, $data['col']) : $event_ids);
	}

	public function list_invoice_statements()
	{
		global $wpdb;

		if (empty($_POST['invoice_id'])) $this->response('error', 'invoice id not set');

		$invoice_id = sanitize_text_field($_POST['invoice_id']);

		$statement = $wpdb->get_results("
			select *,DATE_FORMAT(date_created,'%d/%m/%Y') as date 
			from {$wpdb->prefix}mini_statements 
			where invoice_id='$invoice_id'
		");

		if (!$statement) $this->response('error', 'record not found for the id');

		$this->response('success', 'Statements found', $statement);
	}

	public function calculate_invoice_data()
	{
		global $wpdb;

		$conditions = [];

		if (isset($_POST['branch_ids']) && count($_POST['branch_ids'])) {
			$branches = "'" . implode("','", $_POST['branch_ids']) . "'";
			$conditions[] = " I.branch_id IN ($branches)";
		}

		if (isset($_POST['technician_ids']) && count($_POST['technician_ids'])) {
			$technician_ids = "'" . implode("','", $_POST['technician_ids']) . "'";
			$conditions[] = " TD.id IN ($technician_ids)";
		}

		if (isset($_POST['payment_methods']) && count($_POST['payment_methods'])) {
			$payment_methods = "'" . implode("','", $_POST['payment_methods']) . "'";
			$conditions[] = " I.payment_method IN ($payment_methods)";
		}

		if (isset($_POST['lead_sources']) && count($_POST['lead_sources'])) {
			$lead_sources = "'" . implode("','", $_POST['lead_sources']) . "'";
			$conditions[] = " I.callrail_id IN ($lead_sources)";
		}

		if (isset($_POST['from_date']) && !empty($_POST['from_date'])) {
			$conditions[] = " DATE(I.date) >= '{$_POST['from_date']}'";
		}
		if (isset($_POST['to_date']) && !empty($_POST['to_date'])) {
			$conditions[] = " DATE(I.date) <= '{$_POST['to_date']}'";
		}

		$conditions = $this->generate_query($conditions);

		$data = $wpdb->get_row("
			select COALESCE(sum(I.tax), 0) as sales_tax, COALESCE(SUM(I.total_amount), 0) as total, count(*) as total_invoices
			from {$wpdb->prefix}invoices I
			left join {$wpdb->prefix}technician_details TD
			on I.technician_id = TD.id
			$conditions
		");

		$calculation_html = "
			<table class='table table-striped table-hover'>
				<tbody>
		";

		$calculation_html .= "
			<tr>
				<th>Total Invoices</th>
				<td>$data->total_invoices</td>
			</tr>
			<tr>
				<th>Total Amount</th>
				<td>" . $this->beautify_amount_field($data->total) . "</td>
			</tr>
			<tr>
				<th>Total Tax</th>
				<td>" . $this->beautify_amount_field($data->sales_tax) . "</td>
			</tr>
		";

		$calculation_html .= "
			</tbody>
			</table>";

		echo $calculation_html;
		wp_die();
	}

	public function get_selected_invoices()
	{
		global $wpdb;

		if (!isset($_SESSION['invoice_selected_items']) && !is_array($_SESSION['invoice_selected_items']) || count($_SESSION['invoice_selected_items']) <= 0) $this->response('error', $this->err_msg);

		$invoice_items = $_SESSION['invoice_selected_items'];

		$ids = implode(',', array_values($invoice_items));

		$invoices = $wpdb->get_results("
			select id, branch_id, client_name, email, total_amount, date, address, invoice_no 
			from {$wpdb->prefix}invoices 
			where id in ($ids)
		");

		if (!$invoices) $this->response('error', $this->err_msg);

		$this->response('success', 'records found', $invoices);
	}

	public function select_invoice()
	{

		if (empty($_POST['invoice_id'])) $this->response('error', 'some of fields are not set');
		if (empty($_POST['checked'])) $this->response('error', 'some of fields are not set');

		$invoice_id = sanitize_text_field($_POST['invoice_id']);
		$checked = sanitize_text_field($_POST['checked']);

		if ($checked == "true") {
			$_SESSION['invoice_selected_items'][] = $_POST['invoice_id'];
			$this->response('success', 'session updated', $_SESSION['invoice_selected_items']);
		} else {
			if (($key = array_search($_POST['invoice_id'], $_SESSION['invoice_selected_items'])) !== false) {
				unset($_SESSION['invoice_selected_items'][$key]);
				$this->response('success', 'session item deleted');
			} else {
				$this->response('error', 'field not found in session');
			}
		}
	}

	public function invoice_add_note()
	{
		global $wpdb;

		$page_url = esc_url_raw($_POST['page_url']);

		if (empty($_POST['invoice_id'])) $this->sendErrorMessage($page_url);
		if (empty($_POST['page_url'])) $this->sendErrorMessage($page_url);
		if (empty($_POST['admin_note'])) $this->sendErrorMessage($page_url);

		$invoice_id = sanitize_text_field($_POST['invoice_id']);
		$admin_note = sanitize_textarea_field($_POST['admin_note']);

		$status = $wpdb->update($wpdb->prefix . "invoices", ['admin_note' => $admin_note], ['id' => $invoice_id]);

		if (!$status) $this->sendErrorMessage($page_url);

		$message = "Note Added Successfully";
		$this->setFlashMessage($message, 'success');

		wp_redirect($page_url);
	}

	public function update_invoice_office_sent_bill()
	{
		global $wpdb;

		if (empty($_POST['invoice_id'])) $this->response('error', $this->err_msg);
		if (empty($_POST['office_sent_bill'])) $this->response('error', $this->err_msg);

		$invoice_id = sanitize_text_field($_POST['invoice_id']);
		$office_sent_bill = sanitize_text_field($_POST['office_sent_bill']);

		$status = $wpdb->update($wpdb->prefix . "invoices", ['office_sent_bill' => $office_sent_bill], ['id' => $invoice_id]);

		if (!$status) $this->response('error', $this->err_msg);

		$this->response('success', 'operation went successfully');
	}

	public function invoice_additional_doc()
	{
		global $wpdb;

		$page_url = esc_url_raw($_POST['page_url']);

		if (!isset($_FILES['doc']) || empty($_FILES['doc']['name'])) $this->sendErrorMessage($page_url);
		if (empty($_POST['invoice_id'])) $this->sendErrorMessage($page_url);

		$file = $_FILES['doc'];
		$invoice_id = sanitize_text_field($_POST['invoice_id']);

		$upload_overrides = array('test_form' => false);

		$upload = $this->uploadSingleFile($file);

		if (!$upload) $this->sendErrorMessage($page_url);

		$response = $wpdb->update($wpdb->prefix . "invoices", ['additional_doc' => $upload['url']], ['id' => $invoice_id]);

		if (!$response) $this->sendErrorMessage($page_url);

		$message = "Additional Doc Saved Successfully";
		$this->setFlashMessage($message, 'success');

		wp_redirect($page_url);
	}

	public function invoice_template($invoice,$type='')
	{
		$template = '
			<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>Document</title>
			</head>
			<body>';

		$title = "Receipt";

		if ($invoice->payment_method == "office_to_bill_client") {
			$title = "Invoice";
		}

		if (!empty($type) && $type == "service_report") {
			$title = "Report";
		}

		$template .= "<p>Hello <b>$invoice->client_name</b>";

		$template .= "<p>Here is your copy of Gam Exterminating Service $title.";

		$template .= $this->get_google_rating_text($invoice->branch_id,$type);

		$template .= "<p>Thanks!</p>";

		$template .= "
			</body>
			</html>";


		return $template;
	}

	public function send_invoice_to_email()
	{

		$upload_dir = wp_upload_dir();

		if (empty($_POST['client_email'])) $this->response('error', $this->err_msg);
		if (empty($_POST['invoice_id'])) $this->response('success', $this->err_msg);

		$client_email = sanitize_text_field($_POST['client_email']);
		$invoice_id = sanitize_text_field($_POST['invoice_id']);

		$sent = $this->send_invoice($invoice_id, $client_email);

		if ($sent['status'] != 'success') $this->response('error', $this->err_msg);

		$this->response('success', 'Invoice Sent to Client');
	}

	public static function getInvoiceById(int $invoice_id, array $columns = [])
	{
		global $wpdb;

		$columns = count($columns) > 0 ? implode(',', $columns) : '*';

		return $wpdb->get_row("
			select $columns 
			from {$wpdb->prefix}invoices I
			where id='$invoice_id'
		");
	}

	public function download_invoice()
	{
		global $wpdb;

		$this->verify_nonce_field('download_invoice');

		$page_url = esc_url_raw($_POST['page_url']);

		if (empty($_POST['invoice_id'])) $this->sendErrorMessage($page_url);

		$invoice_id = sanitize_text_field($_POST['invoice_id']);

		$invoice = $this->getInvoiceById($invoice_id);

		if (!$invoice) $this->sendErrorMessage($page_url);

		$invoicePdfContent = $this->invoicePdfContent($invoice);

		// load sendgrid php sdk from vendor
		self::loadVendor();

		$mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
		$mpdf->WriteHTML($invoicePdfContent);
		$title = $this->get_invoice_receipt_type($invoice->payment_method);
		$mpdf->Output("$title.pdf", "D");
		return;
	}

	public function download_service_report()
	{
		global $wpdb;

		$this->verify_nonce_field('download_service_report');

		$page_url = esc_url_raw($_POST['page_url']);

		$service_report = $_POST['service_report_data'] ?? '';

		$service_data = json_decode(stripslashes($service_report));

		if (empty($service_data)) $this->sendErrorMessage($page_url);

		// get the invoice data first
		$getInvoiceData = $this->getInvoiceById($service_data->invoice_id,['invoice_no,email,address,phone_no,sign_img,chemical_report_type,chemical_report_id,branch_id,payment_method,manager_name,total_amount']);

		$invoice = (object) array_merge((array) $getInvoiceData, (array)$service_data);

		$invoicePdfContent = $this->invoicePdfContent($invoice,'service_report');

		// load sendgrid php sdk from vendor
		self::loadVendor();

		$mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
		$mpdf->WriteHTML($invoicePdfContent);
		$title = sprintf('service_report_%s', $service_data->invoice_no);
		$mpdf->Output("$title.pdf", "D");
		return;
	}

	public function send_service_report(object $service_data, string $client_email = ''){
		global $wpdb;

		$upload_dir = wp_upload_dir();

		// get the invoice data first
		$getInvoiceData = $this->getInvoiceById($service_data->invoice_id,['invoice_no,email,address,phone_no,sign_img,chemical_report_type,chemical_report_id,branch_id,payment_method,manager_name']);

		$invoice = (object) array_merge((array) $getInvoiceData, (array)$service_data);

		// first generate invoice message
		$message = $this->invoice_template($invoice,'service_report');

		$title = 'Service';

		$subject = sprintf('GAM Exterminating Service Report For %s', $invoice->invoice_no);

		// then send the client invoice in pdf form
		$invoicePdfContent = $this->invoicePdfContent($invoice,'service_report');

		// generate pdf and send to client
		$invoice_upload_path = $upload_dir['basedir'] . "/pdf/service-report/temp/report_" . date('d-m-y-h-i-s') . ".pdf";
		(new Mpdf)->save_file_with_content($invoicePdfContent, $invoice_upload_path);

		$pdf_files = [];

		$pdf_files[] = [
			'file'	=>	$invoice_upload_path,
			'type'	=>	'application/pdf',
			'name'	=>	$title . ".pdf"
		];

		$tos = [];
		$tos[] = [
			'email'	=>	!empty($client_email) ? $client_email : $invoice->email,
			'name'	=>	$invoice->client_name
		];

		// send invoice pdf with message to client
		return (new Sendgrid_child)->sendTemplateEmail(
			$tos,
			$subject,
			$message,
			$pdf_files,
			'',
			'service_report',
			$invoice->branch_id //branch id for mail tempate branch address af footer
		);
	}

	public function send_invoice(int $invoice_id, $client_email = '')
	{
		global $wpdb;

		$upload_dir = wp_upload_dir();

		// get the invoice data first
		$invoice = $this->getInvoiceById($invoice_id);

		// first generate invoice message
		$message = $this->invoice_template($invoice);

		$title = $invoice->payment_method == "office_to_bill_client" ? "Invoice" : "Receipt";
		$subject = sprintf('GAM Exterminating %s For %s', $title, $invoice->invoice_no);

		$chemical_report_data = "";
		// check if chemical report is attached with the invoice
		if (!empty($invoice->chemical_report_type) && !empty($invoice->chemical_report_id)) {
			switch ($invoice->chemical_report_type) {
				case 'newyork':
					$report_ids = json_decode($invoice->chemical_report_id);
					$chemical_report_data = (new ChemicalReportNewyork)->chemical_report_pdf_content($report_ids);
					break;
				case 'california':
					$chemical_report_data = (new ChemicalReportCalifornia)->chemical_report_pdf_content($invoice->chemical_report_id);
					break;
				case 'texas':
					$chemical_report_data = (new ChemicalReportTexas)->chemical_report_pdf_content($invoice->chemical_report_id);
					break;
				case 'newjersey':
					$report_ids = json_decode($invoice->chemical_report_id);
					$chemical_report_data = (new ChemicalReportNewjersey)->chemical_report_pdf_content($report_ids);
					break;
				case 'florida':
					$report_ids = json_decode($invoice->chemical_report_id);
					$chemical_report_data = (new ChemicalReportFlorida)->chemical_report_pdf_content($report_ids);
					break;
				case 'newyork_animal_trapping_report':
					$chemical_report_data = (new ChemicalReportNewyork)->ny_animal_trapping_pdf_content($invoice->chemical_report_id);
					break;
			}
		}

		// then send the client invoice in pdf form
		$invoicePdfContent = $this->invoicePdfContent($invoice);

		// generate pdf and send to client
		$invoice_upload_path = $upload_dir['basedir'] . "/pdf/invoice/temp/invoice_" . date('d-m-y-h-i-s') . ".pdf";
		(new Mpdf)->save_file_with_content($invoicePdfContent, $invoice_upload_path);

		$pdf_files = [];

		$pdf_files[] = [
			'file'	=>	$invoice_upload_path,
			'type'	=>	'application/pdf',
			'name'	=>	$title . ".pdf"
		];

		if (!empty($invoice->optional_images)) {
			$attachements = json_decode($invoice->optional_images);
			if (!is_null($attachements)) {
				foreach ($attachements as $attachement) {

					$file_path = explode('/uploads', $attachement->url)[1];
					$upload_path = $upload_dir['basedir'] . $file_path;
					$type = pathinfo($upload_path, PATHINFO_EXTENSION);

					$pdf_files[] = [
						'file'	=>	$upload_path,
						'type'	=>	"mage/$type",
						'name'	=>	$attachement->name
					];
				}
			}
		}

		if (!empty($chemical_report_data)) {
			$chemical_report_upload_path = $upload_dir['basedir'] . "/pdf/chemical-report/temp/" . date("ymdhis") . '.pdf';
			(new Mpdf)->save_file_with_content($chemical_report_data, $chemical_report_upload_path);

			$pdf_files[] = [
				'file'	=>	$chemical_report_upload_path,
				'type'	=>	'application/pdf',
				'name'	=>	'Chemical Report.pdf'
			];
		}

		$tos = [];

		// check client email is multiple or single
		if(strpos($client_email, ',') !== false) {
			foreach(explode(',',$client_email) as $email){
				$tos[] = [
					'email'	=>	$email,
					'name'	=>	$invoice->client_name
				];
			}
		} else {
			$tos[] = [
				'email'	=>	!empty($client_email) ? $client_email : $invoice->email,
				'name'	=>	$invoice->client_name
			];
		}

		// send invoice pdf with message to client
		return (new Sendgrid_child)->sendTemplateEmail(
			$tos,
			$subject,
			$message,
			$pdf_files,
			'',
			'invoice',
			$invoice->branch_id //branch id for mail tempate branch address af footer
		);
	}

	public function invoicePdfContent($invoice,$type='')
	{

		$message = '
			<!DOCTYPE html>
				<html lang="en">
				<head>
					<meta charset="UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<title>Document</title>
				</head>
				<body>
					<style>
						body{
							font-family: arial, sans-serif
						}
						table{
							font-family: arial, sans-serif;
							border-collapse: collapse;
							width: 100%;
						}

						td, th{
							border: 1px solid #dddddd;
							text-align: left;
							padding: 8px;
						}

						tr:nth-child(even){
							background-color: #dddddd;
						}

						.text-center{
							text-align: center;
						}
						.text-right{
							text-align: right;
						}
					</style>
		';

		if(!empty($type) && $type == 'service_report'){
			$message .= $this->service_report_body($invoice);
		}else{
			$message .= $this->invoice_body($invoice);
		}

		$message .= "
				</body>
			</html>";

		return $message;
	}

	public function service_report_body($invoice)
	{
		$tech_name = (new Technician_details)->getTechnicianName($invoice->technician_id);
		$tech_branch_id = (new Technician_details)->getTechnicianBranchId($invoice->technician_id);

		$upload_dir = wp_upload_dir();
		$imgpath = $upload_dir['baseurl'] . '/pdf/signatures/invoice/' . $invoice->sign_img;

		$title = 'Service Report';

		$message = '<img style="max-width:100%; margin-bottom: 2%;" src="' . $upload_dir['baseurl'] . '/2019/10/GAM-Exterminating-logo-2.png"/>';

		$message .= " <h2 class='text-center'>Gam Exterminating $title ".(new GamFunctions)->generateLicenseNoBasedOnBranch($tech_branch_id)."</h2> ";

		$message .= "<table class='table table-striped table-hover'>";
		$message .= "<caption class='text-center'><b>Company Information</b></caption>";

		$message .= "<tbody>";

		$message .= "<tr>";
		$message .= "<th>Invoice No.</th>";
		$message .= "<td>$invoice->invoice_no</td>";
		$message .= "<th>Company Address</th>";
		$message .= "<td>" . $this->get_company_address($invoice->branch_id) . "</td>";
		$message .= "</tr>";

		$message .= "<tr>";
		$message .= "<th>Company Phone No.</th>";
		$message .= "<td>877-732-2057</td>";
		$message .= "<th>Company Email</th>";
		$message .= "<td>service@gamexterminating.com</td>";
		$message .= "</tr>";
		
		$message .= "</tbody>";
		$message .= "</table>";

		$message .= "
			<table class='table table-striped table-hover'>
				<caption class='text-center'><b>Basic Information</b></caption>
				<tbody>
					<tr>
						<th>Client Name</th>
						<td>$invoice->client_name</td>
					</tr>
					<tr>
						<th>Telephone number</th>
						<td>$invoice->phone_no</td>
					</tr>
					<tr>
						<th>Address</th>
						<td>$invoice->address</td>
					</tr>
					<tr>
						<th>Service Date</th>
						<td>" . date('d M Y', strtotime($invoice->date)) . "</td>
					</tr>
					<tr>
						<th>Client Email</th>
						<td>$invoice->email</td>
					</tr>
					<tr>
						<th>Technician Name</th>
						<td>" . $this->beautify_string($tech_name) . "</td>
					</tr>
				</tbody>
			</table>
			
			<table class='table table-striped table-hover'>
				<caption><b>Service Information</b></caption>
				<tbody>
					<tr>
						<th>Type of service provided</th>
						<td>$invoice->type_of_service_provided</td>
					</tr>
					<tr>
						<th>Area of service</th>
						<td>$invoice->area_of_service</td>
					</tr>
					<tr>
						<th>Notes / Comment	</th>
						<td>".nl2br($invoice->client_notes)."</td>
					</tr>
					<tr>
						<th>Service Description</th>
						<td>$invoice->service_description</td>
					</tr>
					<tr>
						<th>Findings</th>
						<td>$invoice->findings</td>
					</tr>
					<tr>
						<th>Amount</th>
						<td>$invoice->total_amount</td>
					</tr>";

					if(!empty((new ChemicalReport)->getPestsUsedInService($invoice->invoice_id))){
						$message .= "<tr>
							<th>Pesticides Used</th>
							<td>".(new ChemicalReport)->getPestsUsedInService($invoice->invoice_id)."</td>
						</tr>";
					}

					if(!empty($invoice->manager_name)){
						$message .= "<tr>
							<th>Manager Name</th>
							<td>$invoice->manager_name</td>
						</tr>";
					}

					if(!empty($invoice->sign_img)){
						$message .= "<tr>
							<th>Signature</th>
							<td><img src='$imgpath'/></td>
						</tr>";
					}
					$message .= "</tbody></table>
		";
		return $message;
	}

	public function invoice_body($invoice)
	{

		$tech_name = (new Technician_details)->getTechnicianName($invoice->technician_id);
		$tech_branch_id = (new Technician_details)->getTechnicianBranchId($invoice->technician_id);

		$upload_dir = wp_upload_dir();
		$imgpath = $upload_dir['baseurl'] . '/pdf/signatures/invoice/' . $invoice->sign_img;
		$products = json_decode($invoice->product_used);

		$title = $this->get_invoice_receipt_type($invoice->payment_method);

		$message = '<img style="max-width:100%; margin-bottom: 2%;" src="' . $upload_dir['baseurl'] . '/2019/10/GAM-Exterminating-logo-2.png"/>';

		$message .= " <h2 class='text-center'>Gam Exterminating $title (".(new GamFunctions)->generateLicenseNoBasedOnBranch($tech_branch_id).")</h2> ";

		$message .= "<div class='table-responsive gam-responsive-tbl'>";
		$message .= "<table class='table table-striped table-hover'>";
		$message .= "<caption class='text-center'><b>Company Information</b></caption>";

		$message .= "<tbody>";

		$message .= "<tr>";
		$message .= "<th>Invoice No.</th>";
		$message .= "<td>$invoice->invoice_no</td>";
		$message .= "<th>Company Address</th>";
		$message .= "<td>" . $this->get_company_address($invoice->branch_id) . "</td>";
		$message .= "</tr>";

		$message .= "<tr>";
		$message .= "<th>Company Phone No.</th>";
		$message .= "<td>877-732-2057</td>";
		$message .= "<th>Company Email</th>";
		$message .= "<td>service@gamexterminating.com</td>";
		$message .= "</tr>";

		$message .= "</tbody>";

		$message .= "</table>";

		$message .= "
			<table class='table table-striped table-hover'>
				<caption class='text-center'><b>Basic Information</b></caption>
				<tbody>
					<tr>
						<th>Client Name</th>
						<td>$invoice->client_name</td>
					</tr>
					<tr>
						<th>Telephone number</th>
						<td>$invoice->phone_no</td>
					</tr>
					<tr>
						<th>Address</th>
						<td>$invoice->address</td>
					</tr>
					<tr>
						<th>Service Date</th>
						<td>" . date('d M Y', strtotime($invoice->date)) . "</td>
					</tr>
					<tr>
						<th>Client Email</th>
						<td>$invoice->email</td>
					</tr>
					<tr>
						<th>Payment Process</th>
						<td>" . $this->beautify_string($invoice->payment_method) . "</td>
					</tr>
					<tr>
						<th>Technician Name</th>
						<td>" . $this->beautify_string($tech_name) . "</td>
					</tr>
				</tbody>
			</table>
			
			<table class='table table-striped table-hover'>
				<caption><b>Service Information</b></caption>
				<tbody>
					<tr>
						<th>Service fee</th>
						<td>\$$invoice->service_fee</td>
					</tr>
					<tr>
						<th>Type of Service Provided</th>
						<td>$invoice->type_of_service_provided</td>
					</tr>
					<tr>
						<th>Service Description</th>
						<td>$invoice->service_description</td>
					</tr>
					<tr>
						<th>Area of service</th>
						<td>$invoice->area_of_service</td>
					</tr>
					<tr>
						<th>Findings</th>
						<td>$invoice->findings</td>
					</tr>
					<tr>
						<th>Any Additional Notes</th>
						<td>" . nl2br($invoice->client_notes) . "</td>
					</tr>
				</tbody>
			</table>
		";

		if (!empty($invoice->reservice_id)) {
			$reserviceData = $this->getReserviceData($invoice->reservice_id);
			$message .= "
				<table class='table table-striped table-hover'>
					<caption><b>Reservice Information</b></caption>
					<tbody>
						<tr>
							<th>Total Reservices Recommended</th>
							<td>$reserviceData->total_reservices</th>
						</tr>
						<tr>
							<th>Reservice Frequency</th>
							<td>Every $reserviceData->revisit_frequency_unit $reserviceData->revisit_frequency_timeperiod </th>
						</tr>
						<tr>
							<th>Reservice Fee</th>
							<td>\$$reserviceData->reservice_fee</th>
						</tr>
					</tbody>
				</table>
			";
		}

		$message .= "<table class='table table-striped table-hover'>
						<caption><b>Payment Calculation</b></caption>
						<thead>
							<tr>
								<th>Name</th>
								<th>Units</th>
								<th>Price per unit</th>			
								<th>Total</th>			
							</tr>
						</thead>
						<tbody>
						";
		if (array_key_exists('0', (array)$products)) {
			foreach ($products as $key => $val) {
				$message .= "<tr>
									<td>$val->name</td>
									<td> $val->Unit</td>
									<td>\$$val->Price</td>
									<td>\$$val->Total</td>
								</tr>";
			}
		} else {
			$message .= "<tr>
							<td>Bait Stations</td>
							<td>$products->bail_stationUnit</td>
							<td>$products->bail_stationPrice</td>
							<td>$products->bail_stationTotal</td>
						</tr>
						<tr>
							<td>Glue boards</td>
							<td>$products->glue_boardUnit</td>
							<td>$products->glue_boardPrice</td>
							<td>$products->glue_boardTotal</td>
						</tr>
						<tr>
							<td>Mouse snap traps</td>
							<td>$products->mouse_snapUnit</td>
							<td>$products->mouse_snapPrice</td>
							<td>$products->mouse_snapTotal</td>
						</tr>
						<tr>
							<td>Rat snap traps</td>
							<td>$products->rat_snapUnit</td>
							<td>$products->rat_snapPrice</td>
							<td>$products->rat_snapTotal</td>
						</tr>
						<tr>
							<td>Hole sealing</td>
							<td>$products->hole_sealUnit</td>
							<td>$products->hole_sealPrice</td>
							<td>$products->hole_sealTotal</td>
						</tr>
						<tr>
							<td>Tin cats</td>
							<td>$products->tin_catsUnit</td>
							<td>$products->tin_catsPrice</td>
							<td>$products->tin_catsTotal</td>
						</tr>
						<tr>
							<td>Poison</td>
							<td>$products->poisonUnit</td>
							<td>$products->poisonPrice</td>
							<td>$products->poisonTotal</td>
						</tr>
						<tr>
							<td>Fogging</td>
							<td>$products->fogginUnit</td>
							<td>$products->fogginPrice</td>
							<td>$products->fogginTotal</td>
						</tr>
						<tr>
							<td>Other</td>
							<td>$products->other_unit</td>
							<td>$products->other_price</td>
							<td>$products->other_total</td>
						</tr>";
		}

		$message .= "<tr>
						<th class='text-right' colspan='3'>Tax</th>
						<td>$invoice->tax</td>
					</tr>
					<tr>
						<th class='text-right' colspan='3'>Processing Fee</th>
						<td>$invoice->processing_fee</td>
					</tr>
					<tr>
						<th class='text-right' colspan='3'>Total amount</th>
						<td>\$$invoice->total_amount</td>
					</tr>
					<tr>
						<th class='text-right' colspan='3'>Signature</th>
						<td><img src='$imgpath'/></td>
					</tr>";

		$message .= "</table>";
		$message .= "</div>";

		$message .= "<p><b>Warranty if offered : </b> " . (!empty($invoice->warranty_explanation) ? nl2br($invoice->warranty_explanation) : 'N/A') . " </p>";

		if ($invoice->payment_method == "office_to_bill_client") {
			$message .= "<p><b>Disclaimer :</b> Please be advised Gam Exterminating requires this bill to be paid 30 days from date of service</p>";
		}

		$message .= "<p><b>Limits of Liability</b></p>
			<p>Although Gam Exterminating will exercise reasonable care in performing services under this Contract, Gam Exterminating will not be liable for injuries or damage to persons, property, birds, animals, or vegetation, except those damages resulting from gross negligence by Gam Exterminating. Further, under no circumstances will Gam Exterminating be responsible for any injury, disease or illness caused, or allegedly caused, by bites, stings or contamination of bed bugs or any other insects, spiders, dust-mites, mosquitoes, or fleas. Gam Exterminating’s representatives are not medically trained to diagnose bed bug borne illnesses or diseases. Please consult your physician for any medical diagnosis. To the fullest extent permitted by law, Gam Exterminating will not be liable for personal injury, death, property damage, loss of use, loss of income or any other damages whatsoever, including consequential and incidental damages, arising from this service Gam Exterminating’s liability is specifically limited to the labor and products necessary to help reduce pest activity.</p>";

		$message .= "<p><b>No Refund Policy</b></p>
			<p>Upon GAM Exterminating providing a pest control service, all payments are acknownledged by client to be final and non-refundable. GAM Exterminating guarentees a professional pest control service to be rendered, however results can never truly be guarenteed as many factors, chronic issues, and need for repeat service may occur. By signing this invoice and submitting payment you acknownledge you have received a professsional pest control service and that this payment is non-refundable.</p>";

		return $message;
	}

	public function verify_technician_code()
	{
		global $wpdb;

		if (empty($_POST['name'])) $this->response('error', $this->err_msg);
		if (empty($_POST['code'])) $this->response('error', $this->err_msg);

		$name = sanitize_text_field($_POST['name']);
		$code = sanitize_text_field($_POST['code']);

		$res = $wpdb->get_row("
			select * from 
			{$wpdb->prefix}technician_codes 
			where name='$name' 
			and code='$code'
		");

		if (!$res) $this->response('error', $this->err_msg);

		$wpdb->delete($wpdb->prefix . "technician_codes", ['id' => $res->id]);

		$this->response('success', 'code matched');
	}

	public function insert_technician_code()
	{
		global $wpdb;

		if (empty($_POST['name'])) $this->response('error', $this->err_msg);

		$name = sanitize_text_field($_POST['name']);

		$data = [
			'name' => $name,
			'code' => mt_rand(100000, 999999)
		];

		$res = $wpdb->insert($wpdb->prefix . 'technician_codes', $data);

		if (!$res) $this->response('error', $this->err_msg);

		$this->response('success', 'code generated');
	}

	public function edit_invoice()
	{
		global $wpdb;

		$this->verify_nonce_field('edit_invoice');

		$page_url = esc_url_raw($_POST['page_url']);

		if (
			empty($_POST['clientName']) ||
			empty($_POST['clientPhn']) ||
			empty($_POST['clientAddress']) ||
			empty($_POST['startDate']) ||
			empty($_POST['clientEmail']) ||
			empty($_POST['payment_process']) ||
			empty($_POST['technician_id']) ||
			empty($_POST['technician_service_type']) ||
			empty($_POST['callrail_id']) ||
			!isset($_POST['total_amount']) ||
			!isset($_POST['service_fee']) ||
			!isset($_POST['tax']) ||
			!isset($_POST['processing_fee']) ||
			!isset($_POST['invoice_id'])
		) $this->sendErrorMessage($page_url, "Please make sure all fields are filled");

		$invoice_id = $this->sanitizeEscape($_POST['invoice_id']);

		if (empty($_POST['service_fee']) && $_POST['service_fee'] != "0") $this->sendErrorMessage($page_url, "Service Fee can not be empty");

		if (empty($_POST['tax']) && $_POST['tax'] != 0) $this->sendErrorMessage($page_url, "Tax can not be empty");

		if (empty($_POST['processing_fee']) && $_POST['processing_fee'] != "0") $this->sendErrorMessage($page_url, "Processing fee can not be empty");

		if (empty($_POST['total_amount']) && $_POST['total_amount'] != "0") $this->sendErrorMessage($page_url, "Total Amount can not be empty");

		$client_require_reservice = $this->sanitizeEscape($_POST['client_require_reservice']);

		$reservice_id = (!empty($_POST['reservice_id']) ? $this->sanitizeEscape($_POST['reservice_id']) : '');

		$findings = $service_description = $area_of_service = "";

		// FINDINGS
		if (isset($_POST['findings']) && count($_POST['findings']) > 0) {
			$findings = implode(' || ', $_POST['findings']);
		}

		if (!empty($_POST['findings_other'])) {
			$temp_finding = $this->sanitizeEscape($_POST['findings_other']);

			$findings .= empty($findings) ? $temp_finding : " || " . $temp_finding;
		}
		if (empty($findings)) $this->sendErrorMessage($page_url, "Please select or enter atleast one findings");

		// SERVICE DESCRIPTION
		if (isset($_POST['service_description']) && count($_POST['service_description']) > 0) {
			$service_description = implode(' || ', $_POST['service_description']);
		}

		if (!empty($_POST['other_service_description'])) {
			$temp_service_description = $this->sanitizeEscape($_POST['other_service_description']);

			$service_description .= empty($service_description) ? $temp_service_description : " || " . $temp_service_description;
		}
		if (empty($service_description)) $this->sendErrorMessage($page_url, "Please select or enter atleast one service description");

		// AREA OF SERVICE
		if (isset($_POST['area_of_service']) && count($_POST['area_of_service']) > 0) {
			$area_of_service = implode(' || ', $_POST['area_of_service']);
		}

		if (!empty($_POST['other_area_of_service'])) {
			$temp_area_of_service = $this->sanitizeEscape($_POST['other_area_of_service']);

			$area_of_service .= empty($area_of_service) ? $temp_area_of_service : " || " . $temp_area_of_service;
		}
		if (empty($area_of_service)) $this->sendErrorMessage($page_url, "Please select or enter atleast one area of description");

		// invoice data
		$invoice_details = [
			'client_name'					=>	sanitize_text_field($_POST['clientName']),
			'phone_no'						=>	sanitize_text_field($_POST['clientPhn']),
			'address'						=>	sanitize_text_field($_POST['clientAddress']),
			'date'							=>	sanitize_text_field($_POST['startDate']),
			'email'							=>	sanitize_email($_POST['clientEmail']),
			'payment_method'				=>	sanitize_text_field($_POST['payment_process']),
			'technician_id'					=>	sanitize_text_field($_POST['technician_id']),
			'type_of_service_provided'		=>	sanitize_text_field($_POST['technician_service_type']),
			'total_amount'					=>	sanitize_text_field($_POST['total_amount']),
			'service_fee'					=>	sanitize_text_field($_POST['service_fee']),
			'service_description'			=>	$service_description,
			'area_of_service'				=>	$area_of_service,
			'findings'						=>	$findings,
			'other_service_description'		=>	$temp_service_description ?? null,
			'other_area_of_service'			=>	$temp_area_of_service ?? null,
			'other_findings'				=>	$temp_finding ?? null,
			'callrail_id'					=>	sanitize_text_field($_POST['callrail_id']),
			'tax'							=>	sanitize_text_field($_POST['tax']),
			'processing_fee'				=>	sanitize_text_field($_POST['processing_fee']),
		];

		$warranty_recommendation = $this->sanitizeEscape($_POST['warranty_recommendation']);
		$invoice_details['warranty_explanation'] = null;
		if (!empty($_POST['warranty_recommendation']) && $warranty_recommendation == "yes") {
			$invoice_details['warranty_explanation'] = $this->sanitizeEscape($_POST['warranty_explanation'], 'textarea');
		}

		if (!empty($_POST['client_notes'])) {
			$invoice_details['client_notes'] = $this->sanitizeEscape($_POST['client_notes'], 'textarea');
		}

		if (isset($_FILES['additional_doc']['tmp_name']) && !empty($_FILES['additional_doc']['tmp_name'])) {

			// delete old proof first
			$invoice = $this->getInvoiceById($invoice_id, ['additional_doc']);
			if (!empty($invoice->additional_doc)) $this->deleteFileByUrl($invoice->additional_doc);

			$upload = $this->uploadSingleFile($_FILES['additional_doc']);
			$invoice_details['additional_doc'] = $upload['url'];
		}

		// check for optional proof images
		if (isset($_FILES['optional_images']) && !empty($_FILES['optional_images']['name'][0])) {

			$decode_inv_imgs = [];
			$invoice = $this->getInvoiceById($invoice_id, ['optional_images']);
			if (!empty($invoice->optional_images)) {
				$decode_inv_imgs = array_map(function ($v) {
					return (array) $v;
				}, json_decode($invoice->optional_images));
			}
			$optional_inv_imgs = $this->uploadFiles($_FILES['optional_images']);
			$merge_optional_imgs = array_merge($optional_inv_imgs, $decode_inv_imgs);
			$invoice_details['optional_images'] = json_encode($merge_optional_imgs);
		}

		// data to send in reservice_clients table
		$data = [
			'total_reservices'	=>	$this->sanitizeEscape($_POST['total_reservices']),
			'revisit_frequency_unit'	=>	$this->sanitizeEscape($_POST['revisit_frequency_unit']),
			'revisit_frequency_timeperiod'	=>	$this->sanitizeEscape($_POST['revisit_frequency_timeperiod']),
			'reservice_fee'	=>	$this->sanitizeEscape($_POST['follow_up_fee'])
		];

		// check to see if reservice id found in request
		if (!empty($reservice_id)) {
			if ($client_require_reservice == "yes") {
				$reservice_id = $this->updateReserviceRecord($data);
				if (!$reservice_id) $this->rollBackTransaction($page_url);
			} else {
				$invoice_details['reservice_id'] = null;
			}
		} else {
			if ($client_require_reservice == "yes") {
				$reservice_id = $this->createReserviceRecord($data);
				if (!$reservice_id) $this->rollBackTransaction($page_url);
				$invoice_details['reservice_id'] = $reservice_id;
			}
		}

		$response = $this->updateInvoice($invoice_id, $invoice_details);
		if (!$response) $this->sendErrorMessage($page_url);

		$message = "Invoice updated successfully";
		$this->setFlashMessage($message, 'success');

		wp_redirect($page_url);
	}

	public function update_invoice_client_status()
	{
		global $wpdb;

		if (
			empty($_POST['invoice_id']) ||
			empty($_POST['checked']) ||
			empty($_POST['type'])
		) $this->response('error', $this->err_msg);


		$invoice_id = (int) sanitize_text_field($_POST['invoice_id']);
		$status = sanitize_text_field($_POST['checked']);
		$type = sanitize_text_field($_POST['type']);

		$column = $type == 'payment' ? 'client_refused_to_pay' : 'client_refused_service_today';

		$res = $wpdb->update($wpdb->prefix . 'invoices', [$column => $status], ['id' => $invoice_id]);

		if (!$res) $this->response('error', $this->err_msg);

		$this->response('success', 'client status updated');
	}

	public function update_invoice_status()
	{
		global $wpdb;

		if (
			empty($_POST['invoice_id']) ||
			empty($_POST['invoice_paid'])
		) $this->response('error', $this->err_msg);

		$invoice_id = (int) sanitize_text_field($_POST['invoice_id']);
		$payment_status = sanitize_text_field($_POST['invoice_paid']);
		$status = $payment_status == "true" ? 'paid' : 'not_paid';

		$res = $wpdb->update($wpdb->prefix . 'invoices', ['status' => $status], ['id' => $invoice_id]);

		if (!$res) $this->response('error', $this->err_msg);

		$this->response('success', 'client status updated');
	}

	public function invoice_form()
	{
		global $wpdb;

		// first verify nonce field
		$this->verify_nonce_field('invoice_form');

		$page_url = esc_url_raw($_POST['page_url']);
		$upload_dir = wp_upload_dir();

		if (!(new InvoiceFlow)->isInvoiceFLowActive()) $this->sendErrorMessage($page_url);

		$required_fields = [
			'clientName',
			'clientAddress',
			'payment_process',
			'type_of_service',
		];

		foreach ($required_fields as $required_field) {
			if (empty($_POST[$required_field])) $this->sendErrorMessage($page_url, $required_field . " is required");
		}

		$numeric_fields = [
			'serviceFee',
			'sales_tax_amount',
			'total_amount',
		];

		foreach ($numeric_fields as $numeric_field) {
			if (!isset($_POST[$numeric_field]) || !is_numeric($_POST[$numeric_field])) {
				$this->sendErrorMessage($page_url, $numeric_field . " should be a number and not empty");
			}
		}

		if (
			!isset($_POST['product']) || count((array)$_POST['product']) <= 0 &&
			!isset($_POST['callrail_id'])
		) {
			$this->sendErrorMessage($page_url);
		}

		$findings = $service_description = $area_of_service = "";

		// FINDINGS
		if (isset($_POST['findings']) && count($_POST['findings']) > 0) {
			$findings = implode(' || ', $_POST['findings']);
		}

		if (!empty($_POST['findings_other'])) {
			$temp_finding = $this->sanitizeEscape($_POST['findings_other']);

			$findings .= empty($findings) ? $temp_finding : " || " . $temp_finding;
		}
		if (empty($findings)) $this->sendErrorMessage($page_url, "Please select or enter atleast one findings");

		// SERVICE DESCRIPTION
		if (isset($_POST['service_description']) && count($_POST['service_description']) > 0) {
			$service_description = implode(' || ', $_POST['service_description']);
		}

		if (!empty($_POST['other_service_description'])) {
			$temp_service_description = $this->sanitizeEscape($_POST['other_service_description']);

			$service_description .= empty($service_description) ? $temp_service_description : " || " . $temp_service_description;
		}
		if (empty($service_description)) $this->sendErrorMessage($page_url, "Please select or enter atleast one service description");

		// AREA OF SERVICE
		if (isset($_POST['area_of_service']) && count($_POST['area_of_service']) > 0) {
			$area_of_service = implode(' || ', $_POST['area_of_service']);
		}

		if (!empty($_POST['other_area_of_service'])) {
			$temp_area_of_service = $this->sanitizeEscape($_POST['other_area_of_service']);

			$area_of_service .= empty($area_of_service) ? $temp_area_of_service : " || " . $temp_area_of_service;
		}
		if (empty($area_of_service)) $this->sendErrorMessage($page_url, "Please select or enter atleast one area of description");

		// SANITIZE AND DECLARE VARIABLES 
		$clientName = $this->sanitizeEscape($_POST['clientName']);
		$clientPhn = (isset($_POST['clientPhn']) && !empty($_POST['clientPhn']) ? $this->sanitizeEscape($_POST['clientPhn']) : '');
		$clientAddress = $this->sanitizeEscape($_POST['clientAddress']);
		$serviceFee = $this->sanitizeEscape($_POST['serviceFee']);
		$sales_tax_amount = $this->sanitizeEscape($_POST['sales_tax_amount']);
		$total_amount = $this->sanitizeEscape($_POST['total_amount']);
		$payment_process = $this->sanitizeEscape($_POST['payment_process']);
		$callrail_id = $this->sanitizeEscape($_POST['callrail_id']);
		$type_of_service = $this->sanitizeEscape($_POST['type_of_service']);
		$other_service_description = $this->sanitizeEscape($_POST['other_service_description']);
		$other_area_of_service	 = $this->sanitizeEscape($_POST['other_area_of_service']);
		$other_findings = $this->sanitizeEscape($_POST['findings_other']);
		$manager_name = $this->sanitizeEscape($_POST['manager_name']);
		$product = $_POST['product'];

		$client_require_reservice = "no";
		$warranty_recommendation = "no";

		if (!empty($_POST['client_require_reservice']))
			$client_require_reservice = $this->sanitizeEscape($_POST['client_require_reservice']);

		if (!empty($_POST['warranty_recommendation']))
			$warranty_recommendation = $this->sanitizeEscape($_POST['warranty_recommendation']);

		if ($client_require_reservice == "yes") {
			if (
				empty($_POST['total_reservices']) ||
				empty($_POST['revisit_frequency_unit']) ||
				empty($_POST['revisit_frequency_timeperiod']) ||
				empty($_POST['follow_up_fee'])
			) $this->sendErrorMessage($page_url, "Please fill all reservice fields of client need reservice");
		}

		if ($warranty_recommendation == "yes") {
			if (empty($_POST['warranty_explanation'])) $this->sendErrorMessage($page_url, "warranty explanation is required if selected for warranty checkbox");
		}

		$this->beginTransaction();

		if (empty($_POST['client-unable-to-sign'])) {
			$imgurl = $_POST["signimgurl"];
			$filename = $this->Save_signImg($imgurl);
			$imgpath = $upload_dir['baseurl'] . '/pdf/signatures/invoice/' . $filename;
		}

		$technician_id = (new Technician_details)->get_technician_id();
		$branch_slug = (new Technician_details)->getTechnicianBranchSlug($technician_id);
		$branch_id = (new Technician_details)->getTechnicianBranchId($technician_id);

		// save new email in email database 
		if (!empty($_POST['clientEmail'])) {
			$clientEmail = sanitize_email($_POST['clientEmail']);

			$email_data = [
				'branch_id'	=>	$branch_id,
				'email'		=>	$clientEmail,
				'name'		=>	$clientName,
				'address'	=>	$clientAddress,
				'phone'		=>	$clientPhn,
				'date'		=>	date('Y-m-d'),
			];
			$email_data['status'] = $callrail_id == "reoccuring_customer" ? "reocurring" : "non_reocurring";

			$response = (new Emails)->save_email($email_data);
			if (!$response) $this->rollBackTransaction($page_url);
		}

		// SAVE INVOICE IN DATABASE
		$invoice_details = [
			'invoice_no'					=>		$this->generateInvoiceNumber(),
			'client_name'					=>		$clientName,
			'phone_no'						=>		$clientPhn,
			'address'						=>		$clientAddress,
			'date'							=> (new InvoiceFlow)->getEventDate(),
			'service_fee'					=>		$serviceFee,
			'product_used'					=>		json_encode($product),
			'tax'							=>		$sales_tax_amount,
			'total_amount'					=>		$total_amount,
			'payment_method'				=>		$payment_process,
			'type_of_service_provided'		=>		$type_of_service,
			'service_description'			=>		$service_description,
			'area_of_service'				=>		$area_of_service,
			'findings'						=>		$findings,
			'other_service_description'		=>		$other_service_description,
			'other_area_of_service'			=>		$other_area_of_service,
			'other_findings'				=>		$other_findings,
			'email_status'					=>		'sent',
			'callrail_id'					=>		$callrail_id,
			'technician_id'					=>		$technician_id,
			'calendar_event_id'				=> (new InvoiceFlow)->getCalendarId(),
			'branch_id'						=>		$branch_id,
			'manager_name'                  => 		$manager_name
		];
		
		// check if multiple emails added in invoice form
		$multiple_inv_email_found = $multiple_inv_phn_found = false;
		if(isset($_POST['multiple_inv_emails']) && count($_POST['multiple_inv_emails']) > 0 && !empty($_POST['multiple_inv_emails'][0])){
			$invoice_details['multiple_inv_emails'] = serialize($_POST['multiple_inv_emails']);
			$multiple_inv_email_found = true;
		}

		// check if multiple phone added in invoice form
		if(isset($_POST['multiple_inv_phone']) && count($_POST['multiple_inv_phone']) > 0 && !empty($_POST['multiple_inv_phone'][0])){
			$invoice_details['multiple_inv_phone'] = serialize($_POST['multiple_inv_phone']);
			$multiple_inv_phn_found = true;
		}



		if ($client_require_reservice == "yes") {

			$data = [
				'total_reservices'	=>	$this->sanitizeEscape($_POST['total_reservices']),
				'revisit_frequency_unit'	=>	$this->sanitizeEscape($_POST['revisit_frequency_unit']),
				'revisit_frequency_timeperiod'	=>	$this->sanitizeEscape($_POST['revisit_frequency_timeperiod']),
				'reservice_fee'	=>	$this->sanitizeEscape($_POST['follow_up_fee'])
			];

			$reservice_id = $this->createReserviceRecord($data);
			if (!$reservice_id) $this->rollBackTransaction($page_url);

			$invoice_details['reservice_id'] = $reservice_id;
		}

		if ($warranty_recommendation == "yes") {
			$invoice_details['warranty_explanation'] = $this->sanitizeEscape($_POST['warranty_explanation'], 'textarea');
		}

		if ((new InvoiceFlow)->isChemicalReportExist()) {
			$invoice_details['chemical_report_type'] = (new InvoiceFlow)->getChemicalReportType();
			$invoice_details['chemical_report_id'] = (new InvoiceFlow)->getChemicalReportId();
		}

		if (!empty($_POST['interested_for_quote']) && isset($_POST['quote_amount']) && is_numeric($_POST['quote_amount'])) {
			$invoice_details['interested_in_quote'] = $this->sanitizeEscape($_POST['interested_for_quote']);
			$invoice_details['quote_amount'] = $this->sanitizeEscape($_POST['quote_amount']);
		}

		if (!empty($_POST['clientEmail'])) {
			$invoice_details['email'] = sanitize_email($_POST['clientEmail']);
		}

		if (isset($_POST['processing_fee']) && is_numeric($_POST['processing_fee'])) {
			$invoice_details['processing_fee'] = $this->sanitizeEscape($_POST['processing_fee']);
		}

		if (!empty($_POST['client_response'])) {
			$invoice_details['client_response'] = $this->sanitizeEscape($_POST['client_response']);
		}

		if (!empty($_POST['opt_out_for_maintenance'])) {
			$invoice_details['opt_out_for_maintenance'] = $this->sanitizeEscape($_POST['opt_out_for_maintenance']);
		}

		if (!empty($_POST['client_notes'])) {
			$invoice_details['client_notes'] = $this->sanitizeEscape($_POST['client_notes'], 'textarea');
		}

		if (!empty($filename)) $invoice_details['sign_img'] = $filename;

		if (isset($_FILES['check_image']) && !empty($_FILES['check_image']['name'])) {
			$upload = $this->uploadSingleFile($_FILES['check_image']);
			$invoice_details['additional_doc'] = $upload['url'];
		}

		// check for optional proof images
		if (isset($_FILES['optional_images']) && !empty($_FILES['optional_images']['name'][0])) {
			$proof_images = $this->uploadFiles($_FILES['optional_images']);
			$invoice_details['optional_images'] = json_encode($proof_images);
		}

		// check for optional invoice label added in calendar event by technician's
		$inv_label = (new InvoiceFlow)->getInvoiceLabel();
		$invoice_details['invoice_label'] = !empty($inv_label) ? $inv_label : null;

		list($invoice_id, $message) = $this->createInvoice($invoice_details);

		// update invoice id in cage address table to send due cage notification
		$cage_address_id = (new InvoiceFlow)->getVariableInSession('cage_address_id');
		if (!empty($cage_address_id)) {
			$data = [
				'tbl' => 'cage_address',
				'where' => $cage_address_id
			];
			$update_data = [
				'invoice_id' => $invoice_id
			];
			(new GamFunctions)->updateRecordInDbTable($data, $update_data);
		}

		if (!$invoice_id) $this->rollBackTransaction($page_url, $message);

		/** 
		 * If calendar event id found in session it means invoice
		 * created by staff so need to mark task as completed.
		 * */ 
		$calendar_event_id = $_SESSION['calendar_event_id'] ?? '';
		if (!empty($calendar_event_id)) {
			// grab task id from task meta table
			$task_id = @$this->getTaskManagerMetaDetails([
				'where' => "event_calendar_id = '$calendar_event_id'"
			])[0];
			(new Task_manager)->markAsCompleted($task_id->task_id,'System cleared the task automatically');
		}

		// IF CLIENT EMAIL IS NOT EMPTY, THEN ONLY TRY TO SEND EMAIL
		if (!empty($_POST['clientEmail'])) {
			if($multiple_inv_email_found){
				array_push($_POST['multiple_inv_emails'],$_POST['clientEmail']);
				$invClientEmail = implode(',', $_POST['multiple_inv_emails']);
			}else{
				$invClientEmail = $_POST['clientEmail'];
			}
			$sent = $this->send_invoice($invoice_id,$invClientEmail);
			if ($sent['status'] == 'error') {
				$response = $this->updateInvoice($invoice_id, ['email_status' => 'not_sent']);
				if ($response === false) $this->rollBackTransaction($page_url);
			}
		}

		// Send invoice link in text message if single or multiple no found
		if(!empty($clientPhn)){
			if($multiple_inv_phn_found){
				array_push($_POST['multiple_inv_phone'],$clientPhn);
				$invClientPhone = implode(',', $_POST['multiple_inv_emails']);
			}else{
				$invClientPhone = $clientPhn;
			}

			(new Twilio)->sendInvoiceLink($invoice_id,$invClientPhone);
		}

		$this->commitTransaction();

		(new Twilio)->sendThankYouMessage($invoice_id);

		$payment_type = $this->sanitizeEscape($_POST['payment_process']);

		(new InvoiceFlow)->setPaymentType($payment_type)
			->setInvoiceId($invoice_id);

		// set the message for invoice completion 
		$message = "Invoice Generated Successfully";
		$this->setFlashMessage($message, 'success');

		(new InvoiceFLow)->callNextPageInFlow();
	}

	public function createInvoice(array $data)
	{
		global $wpdb;

		$response = $wpdb->insert($wpdb->prefix . 'invoices', $data);
		if (!$response) return [false, $wpdb->last_error];

		$invoice_id = $wpdb->insert_id;

		$data['invoice_id'] = $invoice_id;

		list($response, $message) = $this->__invoiceQuoteLeadMiddleware($data);
		if (!$response) return [false, $message];

		return [$invoice_id, null];
	}

	public function __invoiceQuoteLeadMiddleware(array $data)
	{

		// check if quote exist, then set it's status to pending, quote will update status for lead as well
		$query_data = [
			'address'	=>	$data['address'],
			'phone'		=>	$data['phone_no'],
		];
		if (!empty($data['clientEmail'])) $query_data['email'] = $data['clientEmail'];

		list($quote_id, $quote_type, $message) = (new Quote)->isQuoteExist($query_data);
		if ($quote_id) {

			$quote_update_data = [
				'quote_id'		=>	$quote_id,
				'quote_status'	=>	'closed',
				'source'		=>	'invoice',
				'source_id'		=>	$data['invoice_id'],
			];

			if ($quote_type == 'residential') {
				list($response, $message) = (new Quote)->updateResidentialQuoteStatus($quote_update_data);
				if (!$response) return [false, $message];
			} elseif ($quote_type == 'commercial') {
				list($response, $message) = (new Quote)->updateCommercialQuoteStatus($quote_update_data);
				if (!$response) return [false, $message];
			} else {
				return [false, 'Linked Quote Error : method - createInvoice'];
			}
		} else {
			// if quote does't exist, then check if lead exist and set it's status to pending
			list($lead_id, $message) = (new Leads)->isLeadExist($query_data);
			if ($lead_id) {
				$lead_update_data = [
					'lead_id'		=>	$lead_id,
					'status'		=>	'closed',
					'source'		=>	'invoice',
					'source_id'		=>	$data['invoice_id']
				];

				list($response, $message) = (new Leads)->updateLeadStatus($lead_update_data);
				if (!$response) return [false, $message];
			}
		}

		return [true, null];
	}

	public function generateInvoiceNumber()
	{
		global $wpdb;

		$invoice_no = 'GAM/' . date('y-m/') . random_int(100000, 999999);

		if ($this->isInvoiceWithNumberExist($invoice_no)) return $this->generateInvoiceNumber();

		return $invoice_no;
	}

	public function isInvoiceWithCalendarEventExist(object $data)
	{
		global $wpdb;

		return $wpdb->get_var("
			select count(*)
			from {$wpdb->prefix}invoices
			where calendar_event_id = '$data->event_id'
			AND technician_id = '$data->tech_id'
			AND date = '$data->event_date'
		");
	}

	public function isInvoiceWithNumberExist(string $invoice_id)
	{
		global $wpdb;

		return $wpdb->get_var("
			select count(*)
			from {$wpdb->prefix}invoices
			where invoice_no = '$invoice_id'
		");
	}

	public function getReserviceData(int $reservice_id, array $columns = [])
	{
		global $wpdb;

		$columns = count($columns) > 0 ? implode(',', $columns) : '*';

		return $wpdb->get_row("
			select $columns
			from {$wpdb->prefix}reservice_clients
			where id = '$reservice_id'
		");
	}

	public function createReserviceRecord(array $data)
	{
		global $wpdb;

		$response = $wpdb->insert($wpdb->prefix . "reservice_clients", $data);

		return !$response ? false : $wpdb->insert_id;
	}

	public function updateReserviceRecord(array $data)
	{
		global $wpdb;

		$response = $wpdb->update($wpdb->prefix . "reservice_clients", $data, ['id' => $_POST['reservice_id']]);

		return $response === false ? false : true;
	}

	public function reset_invoice_form()
	{
		(new InvoiceFlow)->resetInvoiceFlow();
	}

	public function invoice_update_email()
	{
		global $wpdb;

		$response = false;

		if (isset($_POST['invoice-id']) && isset($_POST['invoice-email'])) {
			if (!empty($_POST['invoice-id']) && !empty($_POST['invoice-email'])) {
				$response = $wpdb->update($wpdb->prefix . "invoices", ['email' => $_POST['invoice-email']], ['id' => $_POST['invoice-id']]);
			} else {
				$response = false;
			}
		} else {
			$response = false;
		}

		if ($response) {
			$this->response('success', 'Email added succesfully');
		} else {
			$this->response('error', 'something went wrong');
		}
	}

	public function getNewyorkCounties()
	{
		global $wpdb;
		return $wpdb->get_results("
			select county_name, sales_tax_rate 
			from {$wpdb->prefix}ny_zip_county 
			group by county_name, sales_tax_rate
		");
	}

	public function get_google_rating_text(int $branch_id, string $type='')
	{
		global $wpdb;

		$review_link = $this->getReviewLink($branch_id);

		$google_rating_text = "<p>Please leave us a 5 star review using the <a href='$review_link'>attached link.</a></p>";

		if(empty($type)){
			$page_url = (new Maintenance)->monthlyMaintenanceLandinagePagerUrl();
			$google_rating_text .= "<p>Interested in a maintenance plan ? <a href='$page_url'>Click here</a> to join.</p>";
		}

		return $google_rating_text;
	}

	public function getTechnicianInvoicesByWeek(int $employee_id, string $week, array $columns = [])
	{
		global $wpdb;

		// get the technician id first
		$technician_id = (new Employee\Employee)->getReferenceIdByEmployeeId($employee_id);
		if (!$technician_id) throw new Exception('Employee ref id not found');

		list($week_start, $week_end) = $this->weekRange($week);

		$columns = count($columns) > 0 ? implode(',', $columns) : '*';

		return $wpdb->get_results("
			select $columns
			from {$wpdb->prefix}invoices
			where DATE(date) >= '$week_start'
			and DATE(date) <= '$week_end'
			and technician_id = '$technician_id'
		");
	}

	public function getInvoiceCount(string $from_date, string $to_date, int $employee_id = null)
	{
		global $wpdb;

		// get the technician id first
		$technician_id = (new Employee\Employee)->getReferenceIdByEmployeeId($employee_id);
		if (!$technician_id) throw new Exception('Employee ref id not found');

		$conditions = [];

		$conditions[] = " DATE(date_created) >= '$from_date' ";
		$conditions[] = " DATE(date_created) <= '$to_date' ";


		if (!is_null($technician_id)) $conditions[] = " technician_id = '$technician_id'";

		$conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

		return $wpdb->get_results("
			select count(*)
			from {$wpdb->prefix}invoices
			$conditions
		");
	}

	public function getInvoices(array $conditions = [], array $columns = [])
	{
		global $wpdb;

		$columns = count($columns) > 0 ? implode(',', $columns) : '*';

		$conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

		return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}invoices
            $conditions
        ");
	}

	public function updateInvoice(int $invoice_id, array $data)
	{
		global $wpdb;

		$response = $wpdb->update($wpdb->prefix . "invoices", $data, ['id' => $invoice_id]);
		return $response === false ? false : true;
	}

	public function adminInvoiceViewPageUrl(int $invoice_id)
	{
		return admin_url('admin.php?page=invoice&invoice_id=' . $invoice_id);
	}

	public function remove_selected_invoice_img($inv_imgs, $item)
	{
		$result = array();
		for ($i = 0; $i < count($inv_imgs); $i++) {
			if ($inv_imgs[$i]->id == $item['id']) {
				unset($inv_imgs[$i]);
				$this->deleteFileByUrl($item['url']);
			} else {
				array_push($result, $inv_imgs[$i]);
			}
		}
		return array_values($inv_imgs);
	}
}

new Invoice();
