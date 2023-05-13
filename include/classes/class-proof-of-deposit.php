<?php

class TechnicianDepositProof extends GamFunctions{

	use GamValidation;

	function __construct(){

		add_action("admin_post_add_proof_of_deoposit",array($this,"add_proof_of_deoposit"));
		add_action("admin_post_nopriv_add_proof_of_deoposit",array($this,"add_proof_of_deoposit"));

		add_action("admin_post_daily_deposit_proof",array($this,"daily_deposit_proof"));
		add_action("admin_post_nopriv_daily_deposit_proof",array($this,"daily_deposit_proof"));

		add_action("wp_ajax_delete_daily_deposit",array($this,"delete_daily_deposit"));
		add_action("wp_ajax_nopriv_delete_daily_deposit",array($this,"delete_daily_deposit"));

		add_action("wp_ajax_get_daily_deposit_doc_proofs",array($this,"get_daily_deposit_doc_proofs"));
		add_action("wp_ajax_nopriv_get_daily_deposit_doc_proofs",array($this,"get_daily_deposit_doc_proofs"));

		add_action("wp_ajax_get_daily_deposit_dscrepancy_doc_proofs",array($this,"get_daily_deposit_dscrepancy_doc_proofs"));
		add_action("wp_ajax_nopriv_get_daily_deposit_dscrepancy_doc_proofs",array($this,"get_daily_deposit_dscrepancy_doc_proofs"));

		add_action("wp_ajax_update_discrepancy_data",array($this,"update_discrepancy_data"));
		add_action("wp_ajax_nopriv_update_discrepancy_data",array($this,"update_discrepancy_data"));

		add_action("wp_ajax_get_week_dates",array($this,"get_week_dates"));
		add_action("wp_ajax_nopriv_get_week_dates",array($this,"get_week_dates"));

		add_action("wp_ajax_delete_proof_document",array($this,"delete_proof_document"));
		add_action("wp_ajax_nopriv_delete_proof_document",array($this,"delete_proof_document"));

		add_action("wp_ajax_delete_admin_paid_proof_document",array($this,"delete_admin_paid_proof_document"));
		add_action("wp_ajax_nopriv_delete_admin_paid_proof_document",array($this,"delete_admin_paid_proof_document"));

		add_action("wp_ajax_approve_disaprove_deposit",array($this,"approve_disaprove_deposit"));
		add_action("wp_ajax_nopriv_approve_disaprove_deposit",array($this,"approve_disaprove_deposit"));

		add_action("wp_ajax_edit_admin_daily_proof_of_deposit",array($this,"edit_admin_daily_proof_of_deposit"));
		add_action("wp_ajax_nopriv_edit_admin_daily_proof_of_deposit",array($this,"edit_admin_daily_proof_of_deposit"));

		add_action("wp_ajax_check_daily_deposit_exist_or_not",array($this,"check_daily_deposit_exist_or_not"));
		add_action("wp_ajax_nopriv_check_daily_deposit_exist_or_not",array($this,"check_daily_deposit_exist_or_not"));

		add_action("wp_ajax_update_reimbursement_status",array($this,"update_reimbursement_status"));
		add_action("wp_ajax_nopriv_update_reimbursement_status",array($this,"update_reimbursement_status"));

		add_action("admin_post_amount_paid_to_technnician",array($this,"amount_paid_to_technnician"));
		add_action("admin_post_nopriv_amount_paid_to_technnician",array($this,"amount_paid_to_technnician"));

		add_action("admin_post_edit_amount_paid_to_technnician",array($this,"edit_amount_paid_to_technnician"));
		add_action("admin_post_nopriv_edit_amount_paid_to_technnician",array($this,"edit_amount_paid_to_technnician"));

		add_action("admin_post_edit_daily_proof_of_deposit",array($this,"edit_daily_proof_of_deposit"));
		add_action("admin_post_nopriv_edit_daily_proof_of_deposit",array($this,"edit_daily_proof_of_deposit"));

		add_action("admin_post_upload_proof_of_reimbursement",array($this,"upload_proof_of_reimbursement"));
		add_action("admin_post_nopriv_upload_proof_of_reimbursement",array($this,"upload_proof_of_reimbursement"));
		
	}

	public function getTechnicianDepositProofByWeek( int $technician_id, string $week, $columns){
		global $wpdb;

		$week_monday = date('Y-m-d',strtotime('this monday',strtotime($week)));
		$week_sunday = date('Y-m-d',strtotime('this sunday',strtotime($week)));

		$columns = count($columns) > 0 ? implode(',', $columns) : '*';
		
		return $wpdb->get_results("
			select $columns
			from {$wpdb->prefix}daily_deposit
			where DATE(date) >= '$week_monday'
			andd DATE(date) <= '$week_sunday'
			and technician_id = '$technician_id'			
		");
	}

	// REIMBURSEMENT METHODS
	public function update_reimbursement_status(){
		global $wpdb;
		if(isset($_POST['reimbursement_id']) && !empty($_POST['reimbursement_id'])){
			$data=[
				'status'			=>	$_POST['checked']=='true' ? 'paid' : 'not_paid',
				'date_reimbursed'	=>	date('Y-m-d')
			];

			$res=$wpdb->update($wpdb->prefix."reimbursement_proof",$data,['id'=>$_POST['reimbursement_id']]);

			if($res){
				$this->response('success','Reimbursement marked as paid');
			}
			else{
				$this->response('error','something went wrong');
			}
		}
		else{
			$this->response('error','something went wrong');
		}

	}

	public function upload_proof_of_reimbursement(){
		$this->verify_nonce_field('upload_proof_of_reimbursement');
		global $wpdb;
		$data=[];

		if(isset($_POST['reimbursement_id']) && !empty($_POST['reimbursement_id'])){

			$new_docs=[];

			$old_docs=[];

			$old_data=$wpdb->get_row("select proof_of_reimbursement from {$wpdb->prefix}reimbursement_proof where id='{$_POST['reimbursement_id']}'");

			if($old_data && !empty($old_data->proof_of_reimbursement)){
				$old_docs=json_decode($old_data->proof_of_reimbursement);
			}

			if (isset($_FILES['docs']['name']) &&  count($_FILES['docs']['name']) > 0){
				$new_docs = $this->uploadFiles($_FILES['docs']);
			}else{
				wp_die('Something went wrong, please try again later (file)');
			}

			$new_docs=array_merge($old_docs,$new_docs);

			if(count($new_docs)>0){

				$data['proof_of_reimbursement']=json_encode($new_docs);
				$res=$wpdb->update($wpdb->prefix."reimbursement_proof",$data,['id'=> $_POST['reimbursement_id']]);

				if(!$res){
					wp_die('Something went wrong, please try again later (db)');
				}else{
					$message = 'Proof of reimbursement submitted successfully';
					$this->setFlashMessage($message,'success');
					wp_redirect($_POST['page_url']);
				}

				if(isset($_POST['page_url'])){
					wp_redirect($_POST['page_url']);
				}
				else{
					wp_redirect(admin_url('admin.php?page=reimbursement'));
				}

			}
			else{
				wp_die('Something went wrong, please try again later (doc)');
			}

		}
		else{
			wp_die('Something went wrong, please try again later (post not se)');
		}


	}

	// DEPOSIT METHODS
	public function edit_admin_daily_proof_of_deposit(){
		$this->verify_nonce_field('edit_admin_daily_proof_of_deposit');
		if(isset($_POST['admin_proof_id']) && !empty($_POST['admin_proof_id'])){
			global $wpdb;
			$data=[
				'total_amount'	=>	$_POST['total_amount'],
				'dscrepancy_amount'	=>	$_POST['dscrepancy_amount'],
				'describe_discrepancy'=>	$_POST['describe_discrepancy'],
			];
			$res=$wpdb->update($wpdb->prefix."daily_deposit",$data,['id'=>$_POST['admin_proof_id']]);
			if ($res) {
				echo json_encode(['status'=>'true']);
			}
			wp_die();
		}
	}

	public function delete_daily_deposit(){
	$this->verify_nonce_field('delete_daily_deposit');
		if(isset($_POST['deposit_id']) && !empty($_POST['deposit_id'])){
			global $wpdb;

			$res=$wpdb->delete($wpdb->prefix."daily_deposit",['id'=>$_POST['deposit_id']]);

			if($res){
				$this->response('success','record deleted successfully');
			}else{
				$this->response('error','Something went wrong , please try again later');
			}
		}
	}

	public function edit_daily_proof_of_deposit(){

		if(isset($_POST['proof_id']) && !empty($_POST['proof_id'])){

			global $wpdb;

			$data=[
				'total_amount'			=>	$_POST['total_amount'],
				'dscrepancy_amount'		=>	$_POST['dscrepancy_amount'],
				'describe_discrepancy'	=>	$_POST['describe_discrepancy'],
				'status'				=>	'pending',
			];
			// echo "<pre>";print_r($_FILES);wp_die();
			$deposit_file=count($_FILES['deposit_docs']['name']);

			$db_docs=[];
			$saved_docs=$wpdb->get_row("select deposit_proof from {$wpdb->prefix}daily_deposit where id='{$_POST['proof_id']}'");

			if($saved_docs){
				if(!empty($saved_docs->deposit_proof)){
					$db_docs=json_decode($saved_docs->deposit_proof);
				}
			}

			

			$new_docs=[];
			if($deposit_file>0){
				for($i=0;$i<$deposit_file;$i++){
					if($_FILES['deposit_docs']['tmp_name'][$i]!=""){
						$tmp_name=$_FILES['deposit_docs']['tmp_name'][$i];
						$file_name=$_FILES['deposit_docs']['name'][$i];
						$new_docs[$i]['file_name']=$file_name;
						$upload=wp_upload_bits($file_name,null,file_get_contents($tmp_name));
						if(array_key_exists('url',$upload)){
							$new_docs[$i]['file_url']=$upload['url'];
						}	
					}
				}
			}

			$total_docs=array_merge($db_docs,$new_docs);
			// echo $total_docs;wp_die();
			if(count($total_docs)>0){
				$data['deposit_proof']=json_encode($total_docs);
			}

			// Discprepancy file
		$dscrepancy_file=count($_FILES['dscrepancy_proof']['name']);
		$db_d_docs=[];
			$saved_dscrepancy_docs=$wpdb->get_row("select dscrepancy_proof from {$wpdb->prefix}daily_deposit where id='{$_POST['proof_id']}'");

			if($saved_docs){
				if(!empty($saved_dscrepancy_docs->dscrepancy_proof)){
					$db_d_docs=json_decode($saved_dscrepancy_docs->dscrepancy_proof);
				}
			}

			

			$new_d_docs=[];
		if($dscrepancy_file>0){
			for($i=0;$i<$dscrepancy_file;$i++){
				if($_FILES['dscrepancy_proof']['tmp_name'][$i]!=""){
					$tmp_name=$_FILES['dscrepancy_proof']['tmp_name'][$i];
					$file_name=$_FILES['dscrepancy_proof']['name'][$i];
					$discrepancy_proofs[$i]['file_name']=$file_name;
					$discrepancy_proof_upload=wp_upload_bits($file_name,null,file_get_contents($tmp_name));
					if(array_key_exists('url',$discrepancy_proof_upload)){
						$discrepancy_proofs[$i]['file_url']=$discrepancy_proof_upload['url'];
					}	
				}
			}
		}
		$total_d_docs=array_merge($db_d_docs,$new_d_docs);
			// echo $total_docs;wp_die();
			if(count($total_d_docs)>0){
				$data['dscrepancy_proof']=json_encode($total_d_docs);
			}

			// End Discprepancy file
			$res=$wpdb->update($wpdb->prefix."daily_deposit",$data,['id'=>$_POST['proof_id']]);

			wp_redirect('/technician-dashboard');
		}

	}

	public function check_daily_deposit_exist_or_not(){
		global $wpdb;

		$this->verify_nonce_field('check_daily_deposit_exist_or_not');

		if(empty($_POST['deposit_date'])) {echo "true"; wp_die();}

		$date = $this->sanitizeEscape($_POST['deposit_date']);
		$technician_id = (new Technician_details)->get_technician_id();

		$response = $wpdb->get_var("
			select count(*) from
			{$wpdb->prefix}daily_deposit
			where technician_id='$technician_id' 
			and DATE(date) = '$date'
		");

		if(!$response) {echo "true"; wp_die();}

		echo "false"; wp_die();
	}

	public function approve_disaprove_deposit(){
	$this->verify_nonce_field('approve_disaprove_deposit');
		if(isset($_POST['checked']) && isset($_POST['deposit_id'])){

			global $wpdb;
			if($_POST['checked']=='true'){
				$status="approved";
				// if deposit is approved, then remove the notice from technician end as well
				
				$conditions=[
					'date'			=>	$_POST['date'],
					'technician_id'	=>	$_POST['technician_id'],
					'type'			=>	'pending_deposit_approval'
				];

				$wpdb->delete($wpdb->prefix."technician_account_status",$conditions);


			}else{
				$status="pending";
			}

			$wpdb->update($wpdb->prefix."daily_deposit",['status'=>$status],['id'=>$_POST['deposit_id']]);

			$this->response('success','deposit status updated successfully');
		}
		else{
			$this->response('error','field not found');
		}
	}

	public function get_daily_deposit_doc_proofs(){
		global $wpdb;

		$this->verify_nonce_field('get_daily_deposit_doc_proofs');

		if(empty($_POST['deposit_id'])) $this->response('error');

		$deposit_id = $this->sanitizeEscape($_POST['deposit_id']);

		$deposit_proof = $wpdb->get_var("select deposit_proof from {$wpdb->prefix}daily_deposit where id='$deposit_id'");

		if(empty($deposit_proof)){
			echo "No Record Found"; wp_die();
		}

		get_template_part('include/admin/proof-of-deoposit/template-docs', null, ['data' => json_decode($deposit_proof)]);
		wp_die();
	}

	public function get_daily_deposit_dscrepancy_doc_proofs(){
		global $wpdb;

		$this->verify_nonce_field('get_daily_deposit_dscrepancy_doc_proofs');

		if(empty($_POST['deposit_id'])) $this->response('error');

		$deposit_id = $this->sanitizeEscape($_POST['deposit_id']);

		$deposit_proof = $wpdb->get_var("select dscrepancy_proof from {$wpdb->prefix}daily_deposit where id='$deposit_id'");

		if(empty($deposit_proof)){
			echo "No Record Found"; wp_die();
		}

		get_template_part('include/admin/proof-of-deoposit/discrepancy-template-docs', null, ['data' => json_decode($deposit_proof)]);
		wp_die();
	}

	public function daily_deposit_proof(){
		global $wpdb;

		$this->verify_nonce_field('daily_deposit_proof');

		$page_url = esc_url_raw($_POST['page_url']);

		$required_fields = [
			'total_amount',
			'deposit_date',
			'any_discrepancy'
		];

		list($response, $message) = $this->requiredValidation($required_fields, $_POST);
		if(!$response) $this->sendErrorMessage($page_url, $message);

		list($response, $message) = $this->singleFileValidation($_FILES, 'desposit_proof');
		if(!$response) $this->sendErrorMessage($page_url, $message);

		$deposit_proof = $this->uploadSingleFile($_FILES['desposit_proof']);
		if(empty($deposit_proof['url'])) $this->sendErrorMessage($page_url, 'Unable to upload proof file');

		$total_amount = $this->sanitizeEscape($_POST['total_amount']);
		$deposit_date = $this->sanitizeEscape($_POST['deposit_date']);
		$any_discrepancy = $this->sanitizeEscape($_POST['any_discrepancy']);
		$technician_id = (new Technician_details)->get_technician_id();

		$deposit_data=[
			'technician_id'			=>	$technician_id,
			'total_amount'			=>	$total_amount,
			'date'					=>	$deposit_date,
			'deposit_proof'			=>	$deposit_proof['url']
		];

		if($any_discrepancy == "yes"){   

			$discrepancy_required_fields = [
				'dscrepancy_amount',
				'describe_discrepancy',				
			];
			list($response, $message) = $this->requiredValidation($discrepancy_required_fields, $_POST);
			if(!$response) $this->sendErrorMessage($page_url, $message);

			list($response, $message) = $this->singleFileValidation($_FILES, 'dscrepancy_proof');
			if(!$response) $this->sendErrorMessage($page_url, $message);			

			$deposit_data['dscrepancy_amount'] = $this->sanitizeEscape($_POST['dscrepancy_amount']);
			$deposit_data['describe_discrepancy'] = $this->sanitizeEscape($_POST['describe_discrepancy']);

			$discrepancy_proof = $this->uploadSingleFile($_FILES['dscrepancy_proof']);
			if(empty($discrepancy_proof['url'])) $this->sendErrorMessage($page_url, 'Unable to upload proof file');

			$deposit_data['dscrepancy_proof'] = $discrepancy_proof['url'];
		}

		$this->beginTransaction();

		$response = $wpdb->insert($wpdb->prefix."daily_deposit",$deposit_data);
		if(!$response) $this->rollBackTransaction($page_url);


		$conditions=[
			'date'			=>	$deposit_date,
			'technician_id'	=>	$technician_id,
			'type'			=>	'pending_deposit'
		];
		$response = $wpdb->delete($wpdb->prefix."technician_account_status",$conditions);	
		if($response === false) $this->rollBackTransaction($page_url);

		$this->commitTransaction();
		
		$message = 'Proof of deposit submitted successfully';

		$this->setFlashMessage($message,'success');

		wp_redirect($page_url);
	}

	public function get_week_dates(){
		if(isset($_POST['week']) && !empty($_POST['week'])){

			list($start,$end)=(new GamFunctions)->x_week_range($_POST['week'],'sunday');

			$full_date="<b>Date:</b> From ".date('d M',strtotime($start))." to ".date('d M',strtotime($end));

			$this->response('success',"date is $full_date",$full_date);

		}else{
			$this->response('error','week not set');
		}
	}

	public function getReimbursementTotalRecord(array $data){
		global $wpdb;
		return $wpdb->get_var("
			select count(*) 
			from {$wpdb->prefix}{$data['rp']}
			right join {$wpdb->prefix}{$data['emp']} 
			{$data['on']}
			{$data['conditions']} 
   	 	");
	}

	public function getReimbursementRecords(array $data){
		global $wpdb;
		return $wpdb->get_results("
			select RP.*,{$data['col']}
			from {$wpdb->prefix}{$data['rp']}
			right join {$wpdb->prefix}{$data['emp']}
			{$data['on']}
			{$data['conditions']} 
			order by RP.created_at desc
			LIMIT {$data['offset']}, {$data['per_page']}
		");
	}
}
$proof=new TechnicianDepositProof();
