<?php

class GamFunctions
{

	function __construct()
	{

		add_filter('login_redirect', array($this, 'my_login_redirect'), 10, 3);
		add_action('admin_bar_menu', array($this, 'add_toolbar_items'), 100);

		add_action('admin_init', array($this, 'register_links'));
	}

	public function isDebug()
	{
		return constant('WP_DEBUG');
	}

	public function register_links()
	{
		register_setting('review-links-group', 'buffalo_link');
		register_setting('review-links-group', 'la_link');
		register_setting('review-links-group', 'ny_link');
		register_setting('review-links-group', 'rochester_link');

		register_setting('gam-settings', 'facebook_url');
		register_setting('gam-settings', 'twitter_url');
		register_setting('gam-settings', 'instagram_url');

		// company information
		register_setting('gam-settings', 'gam_main_address');
		register_setting('gam-settings', 'gam_company_name');
		register_setting('gam-settings', 'gam_company_phone_no');
		register_setting('gam-settings', 'gam_company_email');

		// company timing
		register_setting('gam-settings', 'gam_office_start_time');
		register_setting('gam-settings', 'gam_office_end_time');
		register_setting('gam-settings', 'gam_office_off_days');

		// sendgrid details
		register_setting('gam-settings', 'gam_sg_template_id');
		register_setting('gam-settings', 'gam_email_api_key');
		register_setting('gam-settings', 'gam_email_validation_api_key');
		register_setting('gam-settings', 'gam_sg_asm_id');

		// aws s3 bucket details
		register_setting('gam-settings', 'gam_s3bucket_api_key');
		register_setting('gam-settings', 'gam_s3bucket_access_key');
	}

	public function gamlandingPageAdsProvider()
	{
		return [
			'1' => 'Google',
			'2' => 'Facebook',
			'3' => 'Bing'
		];
	}

	public function fetchActiveAdslandingPages(array $data)
	{
		return new WP_Query(
			array(
				'post_type'  => 'page',
				'posts_per_page' => $data['per_page'],
				'paged' => $data['page_no'],
				'meta_query' => $data['query']
			)
		);
	}

	public function generateGamUniqueNumber($query)
	{
		global $wpdb;

		$random_no = 'GAM/' . date('y-m/') . random_int(100000, 999999);

		if ($this->isGamWithNumberExist($random_no, $query)) return $this->generateGamUniqueNumber($query);

		return $random_no;
	}

	public function isGamWithNumberExist(string $rand_id, array $query)
	{
		global $wpdb;

		return $wpdb->get_var("
			select count(*)
			from {$wpdb->prefix}{$query['tbl']}
			where {$query['col']} = '$rand_id'
		");
	}

	public function getSalesTaxByCounty(string $county)
	{
		global $wpdb;
		return $wpdb->get_var("
			select sales_tax_rate
			from {$wpdb->prefix}ny_zip_county
			where county_name = '$county'
		");
	}

	public function getAllLicenses(array $columns)
	{
		global $wpdb;

		$columns = (count($columns) > 0) ? implode(',', $columns) : '*';

		return $wpdb->get_results("
			select $columns
			from {$wpdb->prefix}technician_details 
			where certification_id <> ''		
		");
	}

	public function getGamVideos(string $type = '')
	{
		global $wpdb;

		$conditions = [];

		if (!empty($type)) $conditions[] = " type like '%$type%' ";

		$conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

		return $wpdb->get_results("
			select *
			from {$wpdb->prefix}gam_videos
			$conditions
		");
	}

	public function add_toolbar_items($admin_bar)
	{
		$admin_bar->add_menu(array(
			'id'    => 'All Tools',
			'title' => 'All Tools',
			'href'  => admin_url('admin.php?page=all-tools'),
			'meta'  => array(
				'title' => __('All Tools'),
			),
		));
	}


	function time_elapsed_string($datetime, $full = true)
	{
		$now = new DateTime;
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);

		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;

		$string = array(
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}

		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . ' ago' : 'just now';
	}

	public function my_login_redirect($redirect_to, $request, $user)
	{
		//is there a user to check?
		if (isset($user->roles) && is_array($user->roles)) {
			//check for admins
			if (in_array('administrator', $user->roles)) {
				// redirect them to the default place
				return admin_url('admin.php?page=all-tools');
			} else {
				return home_url();
			}
		} else {
			return $redirect_to;
		}
	}

	public function getMainAddress()
	{
		return get_option('gam_main_address');
	}

	public function get_employee_role(int $role_id)
	{
		global $wpdb;
		return $wpdb->get_var("
			select name
			from {$wpdb->prefix}employees_roles
			where id = '$role_id'
		");
	}

	public function get_company_address(int $branch_id)
	{
		global $wpdb;

		$address = $wpdb->get_var("
			select address
			from {$wpdb->prefix}branches
			where id = '$branch_id'
		");

		return empty($address) ? $this->getMainAddress() : $address;
	}

	public function quickRandom($length = 25)
	{
		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
	}

	public function verify_nonce_field($key_string = '')
	{

		if (!array_key_exists('_wpnonce', $_POST)) $this->response('error', 'Token not found');

		if (wp_verify_nonce($_POST['_wpnonce'], $key_string)) return true;
		$this->response('error', 'Token expired, please refresh the page and try again');
	}

	public function get_invoice_receipt_type($payment_method)
	{

		$title = "Receipt";

		$invoice_types = [
			'office_to_bill_client',
			'reservice_no_charge',
			'client_covered_by_maintenance_agreement',
			'client_refused_to_pay',
			'client_refused_service_today'
		];

		if (in_array($payment_method, $invoice_types)) $title = "Invoice";

		return $title;
	}

	public function getPhoneNo($page_id)
	{

		if (empty($page_id)) return '877-732-2057';

		$phone_no = get_field('phone_number', $page_id);

		if (empty($phone_no)) return '877-732-2057';

		return $phone_no;
	}

	public function x_week_range($date, $start_day = '')
	{
		$ts = strtotime($date);

		if ($start_day == 'sunday') {
			$start = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
		} else {
			$start = (date('w', $ts) == 0) ? $ts : strtotime('monday this week', $ts);
		}
		return array(
			date('Y-m-d', $start),
			date('Y-m-d', strtotime('saturday', $start))
		);
	}

	public function get_date_range($date)
	{
		$ts = strtotime($date);
		$start = (date('w', $ts) == 0) ? $ts : strtotime('monday this week', $ts);
		return array(
			date('Y-m-d', $start),
			date('Y-m-d', strtotime('sunday', $start))
		);
	}

	public function get_google_ads_week_dates($week)
	{
		$from_date = date('Y-m-d', strtotime('last week tuesday', strtotime($week)));
		$to_date = date('Y-m-d', strtotime('this monday', strtotime($week)));

		return [$from_date, $to_date];
	}

	public function genereateSlug(string $value): string
	{
		return str_replace(" ", "_", strtolower($value));
	}

	public function sanitize_technician_name($tech_name)
	{
		return ucwords(str_replace('_', ' ', $tech_name));
	}
	public function beautify_string($string)
	{
		return ucwords(str_replace('_', ' ', $string));
	}

	public function get_all_techincians($office = true, $location = '', $status = true)
	{
		global $wpdb;

		$conditions = [];

		if ($status == true) $conditions[] = " status='1' ";
		if ($office == false) $conditions[] = " slug <> 'office'";
		if (!empty($location)) $conditions[] = " state='$location'";

		$conditions[] = " application_status='verified'";

		$search_query = $this->generate_query($conditions);

		$technicians = $wpdb->get_results("select * from {$wpdb->prefix}technician_details $search_query");

		return $technicians;
	}

	public function getSpecificTechnician($tech_ids){
		global $wpdb;
		return $wpdb->get_results("select * from {$wpdb->prefix}technician_details where id IN ($tech_ids)");
	}

	public function get_all_locations($office = true)
	{
		global $wpdb;

		$office_condtion = "";

		if ($office == false) $office_condtion = "where slug <> 'global'";

		$locations = $wpdb->get_results("select * from {$wpdb->prefix}branches $office_condtion");

		return $locations;
	}

	public function generate_query($search_array = [])
	{
		$whereSearch = "";
		if (count((array)$search_array) > 0) {
			$i = 0;
			foreach ($search_array as $key => $value) {
				if ($i == 0) {
					$whereSearch .= "where $value ";
				} else {
					$whereSearch .= " and $value ";
				}
				$i++;
			}
		}
		return (string)$whereSearch;
	}
	public function generate_search_string($search_array = [])
	{
		$whereSearch = "";
		if (count((array)$search_array) > 0) {
			$i = 0;
			foreach ($search_array as $key => $value) {
				if ($i == 0) {
					$whereSearch .= "where $key='$value' ";
				} else {
					$whereSearch .= " and $key='$value' ";
				}
				$i++;
			}
		}
		return (string)$whereSearch;
	}

	public function genreate_saving_directory($path)
	{
		$year = date("Y");
		$month = date("m");
		$day = date('d');

		$filename = $path . $year;
		$filename2 = $path . $year . "/" . $month;
		$filename3 = $path . $year . "/" . $month . "/" . $day;

		if (!file_exists($filename)) mkdir($filename, 0777);
		if (!file_exists($filename2)) mkdir($filename2, 0777);
		if (!file_exists($filename3)) mkdir($filename3, 0777);
	}

	public function response(string $status, string $message = '', $data = '', bool $exit_script = true)
	{

		if (empty($message) && $status == "error")
			$message = "Something went wrong, please try again later";

		if (empty($message) && $status == "success")
			$message = "Operation went successfully";


		echo json_encode([
			'status' => $status,
			'message' => $message,
			'data' => $data
		]);

		if ($exit_script) wp_die();
	}

	public function rollbackResponse(string $status, string $message = '', $data = '')
	{
		global $wpdb;

		$wpdb->query('ROLLBACK');

		$this->response($status, $message, $data);
	}

	public function Save_signImg($imgurl)
	{

		$upload_dir = wp_upload_dir();

		$file_name = date('Y/m/d') . "/" . uniqid() . ".png"; //file name
		$dir_path = $upload_dir['basedir'] . "/pdf/signatures/invoice/";

		$this->genreate_saving_directory($dir_path);

		$imagedata = base64_decode($imgurl);
		//path where you want to upload image
		$imagefile = $dir_path . $file_name;
		file_put_contents($imagefile, $imagedata);
		return $file_name;
	}

	public function render_pagination($pageno, $total_pages)
	{
		$page_url = explode("?", $_SERVER['REQUEST_URI']);
		$url_parameters = $_GET;

		if (isset($url_parameters['pageno'])) unset($url_parameters['pageno']);

		$page_url = $page_url[0] . "?" . http_build_query($url_parameters);
?>
		<div class="pagination_wrapper">
			<ul class="pagination">
				<li class="<?= $total_pages == "1" ? 'disabled' : ''; ?>"><a href=<?= $page_url . "&pageno=1"; ?>>First</a></li>
				<li class="<?php if ($pageno <= 1) {
								echo 'disabled';
							} ?>">
					<a href="<?php if ($pageno <= 1) {
									echo '#';
								} else {
									echo $page_url . "&pageno=" . ($pageno - 1);
								} ?>">Prev</a>
				</li>
				<li class="<?php if ($pageno >= $total_pages) {
								echo 'disabled';
							} ?>">
					<a href="<?php if ($pageno >= $total_pages) {
									echo '#';
								} else {
									echo $page_url . "&pageno=" . ($pageno + 1);
								} ?>">Next</a>
				</li>
				<li class="<?= $total_pages == "1" ? 'disabled' : ''; ?>"><a href="<?= $page_url . "&pageno=" . $total_pages; ?>">Last</a></li>
			</ul>
		</div>
		<?php
	}

	public function get_table_coloumn($table_name)
	{

		global $wpdb;

		$sql = "SHOW COLUMNS FROM $table_name";
		$columns = $wpdb->get_results($sql);

		foreach ($columns as $key => $value) {
			if ($value->Field == "id") unset($columns[$key]);
		}

		return $columns;
	}

	public function create_search_query_string($columns, $search_string, $type = "where", $table_name = '')
	{

		$not_allowed = ['id', 'created_at', 'updated_at'];
		foreach ($columns as $key => $column) {
			if (in_array($column->Field, $not_allowed)) unset($columns[$key]);
		}

		$whereString = [];
		foreach ($columns as $key => $value) {
			if (!empty($table_name)) {
				$whereString[] = "$table_name.$value->Field LIKE '%$search_string%' ";
			} else {
				$whereString[] = "$value->Field LIKE '%$search_string%' ";
			}
		}

		$whereString = implode(' OR ', $whereString);

		if ($type == "and")
			$whereSearch = " and ($whereString)";
		elseif ($type == "no_type")
			$whereSearch = " ($whereString)";
		else
			$whereSearch = " where $whereString";

		return $whereSearch;
	}

	public function pass_all_get_field_as_hidden_fields()
	{
		if (is_array($_GET) && count($_GET) > 0) {
			foreach ($_GET as $key => $val) {
				if ($key == "pageno") {
					continue;
				}
		?>
				<input type="hidden" name="<?= $key; ?>" value="<?= $val; ?>">
<?php
			}
		}
	}

	public function encrypt_data($string, $action = 'e')
	{
		// you may change these values to your own
		$secret_key = 'my_simple_secret_key';
		$secret_iv = 'my_simple_secret_iv';

		$output = false;
		$encrypt_method = "AES-256-CBC";
		$key = hash('sha256', $secret_key);
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		if ($action == 'e') {
			$output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
		} else if ($action == 'd') {
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}

		return $output;
	}

	public function save_pdf($file_content, $form_type)
	{

		$upload_dir = wp_upload_dir();

		switch ($form_type) {
			case 'maintenance_monthly':
				$path = "/pdf/maintenance/monthly/";
				break;
			case 'maintenance_quarterly':
				$path = "/pdf/maintenance/quarterly/";
				break;
			case 'maintenance_special':
				$path = "/pdf/maintenance/special/";
				break;
			case 'maintenance_commercial':
				$path = "/pdf/maintenance/commercial/";
				break;
			case 'yearly_termite_contract':
				$path = "/pdf/maintenance/yearly-termite-contract/";
				break;
			case 'invoice':
				$path = "/pdf/invoice/";
				break;
			case 'quotesheet':
				$path = "/pdf/quotesheet/";
				break;
			case 'statement':
				$path = "/pdf/statements/";
				break;
			default:
				return false;
				break;
		}

		// load mpdf php sdk from vendor
		self::loadVendor();

		$mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
		$mpdf->WriteHTML($file_content);

		$directory_path = $upload_dir['basedir'] . $path;
		$this->genreate_saving_directory($directory_path);

		$full_path = $path . date('Y/m/d/') . $form_type . "_" . date('is') . $this->quickRandom(6) . ".pdf";
		$file_path = $upload_dir['basedir'] . $full_path;
		$mpdf->Output($file_path, "F");

		return [$file_path, $full_path];
	}

	public function saveBase64EncodedImage($image_data, $path)
	{

		$upload_dir = wp_upload_dir();

		$file_name = date('Y/m/d') . "/" . date('ymdhis') . ".png";

		$dir_path = $upload_dir['basedir'] . $path;

		$path_for_db = $path . $file_name;

		$signature_path = $upload_dir['baseurl'] . $path_for_db;

		$this->genreate_saving_directory($dir_path);

		$imagedata = base64_decode($image_data);
		$imagefile = $dir_path . $file_name;

		file_put_contents($imagefile, $imagedata);

		return [$signature_path, $path_for_db];
	}


	public function save_signature($image, $form_type, $client_name)
	{

		switch ($form_type) {
			case 'maintenance':
				$path = "/pdf/signatures/maintenance/";
				break;
			case 'invoice':
				$path = "/pdf/signatures/invoice/";
				break;
			case 'non_recurring':
				$path = "/pdf/signatures/non-recurring/";
				break;
			case 'certificate':
				$path = "/pdf/signatures/certificate/";
				break;
			case 'certificate':
				$path = "/pdf/signatures/certificate/";
				break;
			case 'florida_consent_form':
				$path = "/pdf/signatures/florida-consent-form/";
				break;
			case 'employee_salary_contract':
				$path = "/pdf/signatures/salary-contract/";
				break;
			default:
				# code...
				break;
		}

		$upload_dir = wp_upload_dir();
		$imagedata = base64_decode($image);
		$filename = date("Y/m/d/") . $client_name . "_" . date('his') . ".png";

		$this->genreate_saving_directory($upload_dir['basedir'] . $path);

		//path where you want to upload image
		$imagefile = $upload_dir['basedir'] . $path . $filename;
		file_put_contents($imagefile, $imagedata);

		$imgpath = $upload_dir['baseurl'] . $path . $filename;

		$img_url = $path . $filename;

		return [$imgpath, $imagefile, $img_url];
	}

	public function setFlashMessage($message, $type = '')
	{
		$_SESSION['flash_message'] = "<div class='alert alert-$type'>$message</div>";
		return true;
	}

	public function getFlashMessage()
	{
		if (!empty($_SESSION['flash_message'])) {
			echo $_SESSION['flash_message'];
			unset($_SESSION['flash_message']);
		}
	}

	public function beautify_amount_field($amount)
	{

		if (empty($amount)) return "$0";

		$amount = number_format(floor((float)$amount * 100) / 100, 2, '.', '');
		return "$" . $amount;
	}

	public function push_notice($notice, $type, $class, $callrail_id = '', $week = '')
	{

		global $wpdb;

		$notice_data = [
			'notice'    	=>  $notice,
			'status'    	=>  '1',
			'class'     	=>  $class,
			'type'      	=>  $type,
			'date_created'  =>  date('Y-m-d'),
			'week'			=>	date('Y-\WW')
		];

		if (!empty($week)) {
			$notice_data['week'] = $week;
		}


		if (!empty($callrail_id)) {
			$notice_data['callrail_id'] = $callrail_id;
		}

		$wpdb->insert($wpdb->prefix . "notices", $notice_data);
	}

	public function sendErrorMessage(string $page_url = '/', $message = '')
	{

		$message = empty($message) ? 'Something went wrong, please try again later' : $message;

		$this->setFlashMessage($message, 'danger');
		wp_redirect($page_url);
		exit();
	}

	public function weekRange(string $week): array
	{
		$sunday = date('Y-m-d', strtotime('last sunday', strtotime($week)));
		$saturday = date('Y-m-d', strtotime('this saturday', strtotime($week)));
		return [$sunday, $saturday];
	}

	public function uploadFiles(array $files)
	{

		$files_path = [];
		$files_count = count($files['name']);

		for ($i = 0; $i < $files_count; $i++) {

			if ($files['tmp_name'][$i] != "") {

				$tmp_name = $files['tmp_name'][$i];
				$file_name = $files['name'][$i];
				$files_path[$i]['id'] = uniqid();
				$files_path[$i]['name'] = $file_name;

				$upload = wp_upload_bits($file_name, null, file_get_contents($tmp_name));
				if (array_key_exists('url', $upload)) {
					$files_path[$i]['url'] = $upload['url'];
				}
			}
		}

		return $files_path;
	}

	public function uploadSingleFile(array $file)
	{
		if (empty($file['tmp_name'])) return false;
		return wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));
	}

	public function deleteFileByUrl(string $file_url)
	{
		$upload_dir = wp_upload_dir();
		$parts = explode('/uploads', $file_url);
		$file_path = $upload_dir['basedir'] . $parts[1];
		unlink($file_path);
	}

	public function sanitizeEscape(string $string, string $type = 'text')
	{
		if ($type == "text") return sanitize_text_field($string);
		if ($type == "textarea") return sanitize_textarea_field($string);
	}

	public function getAllApplicationStatus()
	{
		global $wpdb;
		return $wpdb->get_results("
			select *
			from {$wpdb->prefix}application_status
		");
	}

	public function getFormattedServiceDuration(string $service){
		switch ($service) {
			case "Monthly Service":
			  return "Month";
			  break;
			case "Bi-Monthly":
			  return str_replace('-',' ',$service);
			  break;
			case "As needed service within 90 days of initial service":
			  return $service;
			  break;
		  }
	}

	public function getAllAccountStatus()
	{
		global $wpdb;
		return $wpdb->get_results("
			select *
			from {$wpdb->prefix}account_status
		");
	}

	public function rollBackTransaction(string $page_url = '/', $message = '')
	{
		global $wpdb;

		$wpdb->query('ROLLBACK');

		$message = empty($message) ? 'Something went wrong, please try again later' : $message;

		$this->setFlashMessage($message, 'danger');
		wp_redirect($page_url);
		exit();
	}

	public function updateRecordInDbTable(array $data, array $update_data)
	{
		global $wpdb;

		$response = $wpdb->update($wpdb->prefix . "{$data['tbl']}", $update_data, ['id' => $data['where']]);
		return !$response ? false : true;
    }

	public function updateMultipleRecordInDbTable(array $query){
        global $wpdb;
		
		$update_query = "
		UPDATE 
			{$wpdb->prefix}{$query['tbl']}
		SET 
			{$query['col']} = (CASE {$query['where_col']} ".$query['case_sql']." END)
		WHERE 
			{$query['where_col']} IN(".implode(",", $query['all_ids']).");
		";
		$result = $wpdb->query($update_query);
		return (FALSE === $result) ? $wpdb->last_error : true;
    }

	public function InsertMultipleRecordInDbTable(array $query){
        global $wpdb;
		
		$insert_query = "
		INSERT INTO 
			{$wpdb->prefix}{$query['tbl']}
            ({$query['col']})
            VALUES " . $query['values'];
		$result = $wpdb->query($insert_query);
		return (FALSE === $result) ? $wpdb->last_error : true;
    }
	
	public function sanitizeUSPhoneNo(string $phone_no){
		$phone_no = sanitize_text_field($phone_no);
		$phone_no = str_replace(" ", "", $phone_no);
		$phone_no = str_replace("-", "", $phone_no);

		$phone_no = (substr($phone_no, 0, 2) !== "+1") ? "+1" . $phone_no : $phone_no;

		return $phone_no;
	}

	public function beginTransaction()
	{
		global $wpdb;

		$wpdb->query('SET autocommit=0');
		$wpdb->query('START TRANSACTION');
		return true;
	}

	public function commitTransaction()
	{
		global $wpdb;

		$wpdb->query('COMMIT');
		return true;
	}

	public function rollbackCommand()
	{
		global $wpdb;

		$wpdb->query('ROLLBACK');
		return true;
	}

	public function getBannerImage()
	{
		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl'] . "/2019/10/GAM-Exterminating-logo-2.png";
	}

	public function displayTextField(string $string)
	{
		return nl2br(stripslashes($string));
	}

	public function getPriceSheetUrl()
	{
		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl'] . "/pdf/technician/PricingSheet-CaliforniaNEWWWW.pdf";
	}

	public function getReviewLine($branch_id = 2)
	{
		$branch_id = empty($branch_id) ? 2 : $branch_id;
		$review_link = $this->getReviewLink($branch_id);
		return "<p>Please leave us a 5 star review using the <a href='$review_link'>attached link</a></p>";
	}

	public function getReviewLink($branch_id = 2)
	{
		global $wpdb;

		$branch_id = !empty($branch_id) ? $branch_id : 2;

		$review_link = $wpdb->get_var("
			select review_link 
			from {$wpdb->prefix}branches 
			where id='$branch_id'
		");

		return empty($review_link) ? 'https://g.page/r/Ca_uJ8RqEmb2EAg/review' : $review_link;
	}

	public function emailStatusHtml(object $data)
	{

		if ((int)$data->email_status == 1) return '<p class="text-success">Sent</p>';
		elseif (is_null($data->email_status)) return '<p class="text-info">No info available</p>';
		elseif ((int) $data->email_status ==  0) return '<p class="text-danger">Not Sent</p>';
		else return '<p class="text-info">No info available</p>';
	}

	public function fakeEmailAlertMessage()
	{
		return '<i class="text-danger">Please do not enter any fake email address in email field if client does\'t have one to provide. Instead click on checkbox below email field with label "client does\'t have email to offer"</i>';
	}

	public function validEmailAlertMessage()
	{
		return '<i class="text-danger">Please do not enter any fake email address in email field</i>';
	}

	public function validPhoneAlertMessage()
	{
		return '<i class="text-danger">Please do not enter any fake phone no in phone field</i>';
	}

	public function getWeeksBetweenDateRanges(string $start_date, string $end_date)
	{

		$weeks = [];

		$p = new DatePeriod(
			new DateTime($start_date),
			new DateInterval('P1W'),
			new DateTime($end_date)
		);

		foreach ($p as $w) {
			$weeks[] = $w->format('Y-\WW');
		}

		return $weeks;
	}

	public static function isArrayExistWithValues($name)
	{
		if (
			!isset($_POST[$name]) ||
			!is_array($_POST[$name]) ||
			count($_POST[$name]) <= 0
		) return false;
		return true;
	}

	public function loadClass(string $class)
	{
		require_once get_template_directory() . "/include/classes/$class.php";
	}

	public static function loadVendor()
	{
		require_once get_template_directory() . "/libraries/vendor/autoload.php";
	}

	// License no based on technician branch id
	public function generateLicenseNoBasedOnBranch($branch_id){
		if(empty($branch_id)) return false;
		switch($branch_id){
			case 2:
				$license = 'dec reg # 15693';
				break;
		
			case 3:
				$license = 'dec reg # PR7373';
				break;
				
			case 8:
				$license = 'dec reg # 0817925';
				break;
			
			default:
				$license = 'dec reg # 15693';
		}
		return $license;
	}

	// fetch lead of specific technician based on calendar event code
	public function gamTechnicianLeadsBasedOnCalendarCode(){
		return array(
			'josh_client' => 'Josh Client',
			'jacob_client' => 'Jacob Client'
		);
	}


	// filter the address_components field for type : $type
	public function gamFilterAddress($components, $type)
	{
		return array_filter($components, function($component) use ($type) {
			return array_filter($component["types"], function($data) use ($type) {
				return $data == $type;
			});
		});
	}

	public function getLocationFromAddress(array $data){
		$formattedAddr = str_replace(' ','+',$data['address']);
		$url = "https://maps.googleapis.com/maps/api/geocode/json?address='".$formattedAddr."'&sensor=true_or_false&key=".$data['apiKey'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$url);
		$result=curl_exec($ch);
		curl_close($ch);

		$data = json_decode($result,true);
		if(!empty($data['status']) && $data['status'] == 'OK'){
			$components = $data["results"][0]["address_components"];
			return array(
				'street_number' => array_values(self::gamFilterAddress($components, "street_number"))[0]["long_name"] ?? '',
				'route' => array_values(self::gamFilterAddress($components, "route"))[0]["long_name"] ?? '',
				'neighborhood' => array_values(self::gamFilterAddress($components, "neighborhood"))[0]["long_name"] ?? '',
				'locality' => array_values(self::gamFilterAddress($components, "locality"))[0]["long_name"] ?? '',
				'administrative_area_level_2' => array_values(self::gamFilterAddress($components, "administrative_area_level_2"))[0]["long_name"] ?? '',
				'administrative_area_level_1' => array_values(self::gamFilterAddress($components, "administrative_area_level_1"))[0]["long_name"] ?? '',
				'country' => array_values(self::gamFilterAddress($components, "country"))[0]["long_name"] ?? '',
				'postal_code' => array_values(self::gamFilterAddress($components, "postal_code"))[0]["long_name"] ?? ''
			);
		}
		return false;
	}
}

new GamFunctions();
