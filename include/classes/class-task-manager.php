<?php

class Task_manager extends GamFunctions{

	use GamValidation;

	private $table_name = "tasks";

	function __construct(){
		
		add_action("admin_post_create_task",array($this,"create_task"));
		add_action("admin_post_nopriv_create_task",array($this,"create_task"));
		
		add_action( 'wp_ajax_delete_task', array($this,'delete_task'));
		add_action( 'wp_ajax_nopriv_delete_task', array($this,'delete_task'));

		add_action( 'admin_post_tm_update_task_by_employee', array($this,'tm_update_task_by_employee'));
		add_action( 'admin_post_nopriv_tm_update_task_by_employee', array($this,'tm_update_task_by_employee'));

		add_action( 'admin_post_tm_update_task_status_by_office', array($this,'tm_update_task_status_by_office'));
		add_action( 'admin_post_nopriv_tm_update_task_status_by_office', array($this,'tm_update_task_status_by_office'));

		add_action( 'admin_post_tm_assign_task_to_employees', array($this,'tm_assign_task_to_employees'));
		add_action( 'admin_post_nopriv_tm_assign_task_to_employees', array($this,'tm_assign_task_to_employees'));

		add_action( 'wp_ajax_tm_upload_notes_by_employee', array($this,'tm_upload_notes_by_employee'));
		add_action( 'wp_ajax_nopriv_tm_upload_notes_by_employee', array($this,'tm_upload_notes_by_employee'));

	}

	public function tm_upload_notes_by_employee(){
		global $wpdb;

		$required_fields = ['task_id', 'notes'];

		list($response, $message) = $this->requiredValidation($required_fields, $_POST);
		if(!$response) $this->response('error', $message);

		$task_id = $this->sanitizeEscape($_POST['task_id']);
		$notes = $this->sanitizeEscape($_POST['notes'], 'textarea');

		list($response, $message) = DB::table($this->table_name)->update($task_id, ['notes' => $notes]);
		if(!$response) $this->response('error', $message);

		$this->response('success');
	}

	public function tm_assign_task_to_employees(){
		global $wpdb;

		$this->verify_nonce_field('tm_assign_task_to_employees');

		$page_url = esc_url_raw($_POST['page_url']);

		if(
			empty($_POST['task_id']) ||
			!isset($_POST['employees_ids']) || !is_array($_POST['employees_ids']) || count($_POST['employees_ids']) <= 0
		) $this->sendErrorMessage($page_url);

		$task_id = $this->sanitizeEscape($_POST['task_id']);

		$employee_ids = $_POST['employees_ids'];
		
		$this->beginTransaction();

		foreach($employee_ids as $employee_id){
			if(!$this->linkEmployeeToTask($task_id, $employee_id)) $this->rollBackTransaction($page_url, "Unable to link employees to task");
		}

		$this->commitTransaction();

		$message = "Employees linked to task successfully";
		$this->setFlashMessage($message, 'success');

		wp_redirect($page_url);
	}

	public function tm_update_task_status_by_office(){
		global $wpdb;

		$this->verify_nonce_field('tm_update_task_status_by_office');

		$page_url = esc_url_raw($_POST['page_url']);

		$required_fields = [
			'task_id',
			'notes',
			'status'
		];

		list($response, $message) = $this->requiredValidation($required_fields, $_POST);
		if(!$response) $this->sendErrorMessage($page_url, $message);

		$task_id = $this->sanitizeEscape($_POST['task_id']);
		$notes = $this->sanitizeEscape($_POST['notes'], 'textarea');
		$status = $this->sanitizeEscape($_POST['status']);

		$data = [
			'notes'			=>	$notes,
			'task_status'	=>	$status
		];

		if(
			isset($_FILES['task_proof_doc']['name']) &&
			!empty($_FILES['task_proof_doc']['name'][0])
		){
			$task_proof_docs = $this->uploadFiles($_FILES['task_proof_doc']);
			if(!$task_proof_docs) $this->sendErrorMessage($page_url, "Unable to upload task proof docs");
			$data['task_proof_doc'] = json_encode($task_proof_docs);
		}

		$response = $this->updateTask($task_id, $data);
		if(!$response) $this->sendErrorMessage($page_url, "Unable to udpate task status");

		$message = "Task status updated successfully";
		$this->setFlashMessage($message, 'success');

		wp_redirect($page_url);
	}

	public function tm_update_task_by_employee(){
		global $wpdb;

		$this->verify_nonce_field('tm_update_task_by_employee');

		$page_url = esc_url_raw($_POST['page_url']);

		$required_fields = [
			'task_id',
			'notes'
		];

		list($response, $message) = $this->requiredValidation($required_fields, $_POST);
		if(!$response) $this->sendErrorMessage($page_url, $message);

		$task_id = $this->sanitizeEscape($_POST['task_id']);
		$notes = $this->sanitizeEscape($_POST['notes'], 'textarea');

		$data = [
			'notes'	=>	$notes,
			'task_status'	=>	'completed'
		];
		
		if(
			isset($_FILES['task_proof_doc']['name']) &&
			!empty($_FILES['task_proof_doc']['name'][0])
		){
			$task_proof_docs = $this->uploadFiles($_FILES['task_proof_doc']);
			if(!$task_proof_docs) $this->sendErrorMessage($page_url, "Unable to upload task proof docs");
			$data['task_proof_doc'] = json_encode($task_proof_docs);
		}

		$response = $this->updateTask($task_id, $data);
		if(!$response) $this->sendErrorMessage($page_url, "Unable to udpate task status");

		$message = "Task status updated successfully";
		$this->setFlashMessage($message, 'success');

		wp_redirect($page_url);
	}

	public function updateTask(int $task_id, array $data){
		global $wpdb;
		$response = $wpdb->update($wpdb->prefix."tasks", $data, ['id' => $task_id]);
		return $response === false ? false : true;
	}

	

	public function create_task(){
		global $wpdb;

		$this->verify_nonce_field('create_task');

		$page_url = esc_url_raw($_POST['page_url']);

		if(empty($_POST['task_description'])) $this->sendErrorMessage($page_url, "Task description is required");

		$task_description = $this->sanitizeEscape($_POST['task_description'], 'textarea');

		$data = ['task_description' 	=> $task_description ];

		if (isset($_FILES['files']['name']) &&  count($_FILES['files']['name']) > 0){
			$docs = $this->uploadFiles($_FILES['files']);
			$data['task_document']=json_encode($docs);
		}

		$this->beginTransaction();

		$response = $this->createTask($data);
		if(!$response) $this->rollBackTransaction($page_url);

		$task_id = $wpdb->insert_id;

		if(!empty($_POST['performer'])){
			$performer_id = $this->sanitizeEscape($_POST['performer']);	
			$response = $this->linkEmployeeToTask($task_id, $performer_id);
			if(!$response) $this->rollBackTransaction($page_url, "Unable to link task to employee");
		}

		$this->commitTransaction();

		$message = "Task created successfully";
		$this->setFlashMessage($message, 'success');

		wp_redirect($page_url);
	}

	public function getTaskEmployees(int $task_id){
		global $wpdb;
		return $wpdb->get_col("
			select E.name
			from {$wpdb->prefix}task_employee TE
			left join {$wpdb->prefix}employees E
			on TE.performer_id = E.id
			where TE.task_id = '$task_id'
		");
	}

	public function isEmployeeAlreaedyAssignedTask(int $task_id, int $employee_id){
		global $wpdb;

		return $wpdb->get_var("
			select count(*)
			from {$wpdb->prefix}task_employee
			where task_id = '$task_id'
			and performer_id = '$employee_id'
		");
	}

	public function linkEmployeeToTask(int $task_id, int $employee_id, int $requestor_id = 0){
		global $wpdb;

		// first check if already linked with task
		if($this->isEmployeeAlreaedyAssignedTask($task_id, $employee_id)) return true;

		$data = [
			'performer_id' 		=> $employee_id,
			'task_id' 			=> $task_id,
		];

		if(!empty($requestor_id)) $data['emp_requester_id'] = $requestor_id;

		return $wpdb->insert($wpdb->prefix."task_employee", $data);
	}

	public function delete_task(){
		global $wpdb;

		$this->verify_nonce_field('delete_task');

		if(empty($_POST['task_id'])) $this->response('error');

		$task_id = $this->sanitizeEscape($_POST['task_id']);

		if(!$this->deleteTask($task_id)) $this->response('error');
		
		$this->response('success');
	}

	public function deleteTask(int $task_id){
		global $wpdb;

		$this->beginTransaction();

		$response = $wpdb->delete($wpdb->prefix."tasks", ['id' => $task_id]);
		if($response === false) { $wpdb->query('ROLLBACK'); return false; }

		$response = $wpdb->delete($wpdb->prefix."task_employee", ['task_id' => $task_id]);
		if($response === false) { $wpdb->query('ROLLBACK'); return false; }

		$response = $wpdb->delete($wpdb->prefix."task_meta", ['task_id' => $task_id]);
		if($response === false) { $wpdb->query('ROLLBACK'); return false; }

		$this->commitTransaction();
		
		return true;
	}

	public function createTask(array $task){
		global $wpdb;

		if(empty($task['task_status'])) $task['task_status'] = 'pending';

		$response = $wpdb->insert($wpdb->prefix."tasks", $task);
		return $response ? $wpdb->insert_id : false;
	}

	public function isTaskByRoleExist(int $role_id, int $targeted_employee_id = 0){
		global $wpdb;

		$conditions = [];

		$conditions[] = " T.task_status = 'pending' ";
		$conditions[] = " TM.role_id = '$role_id'";

		if(!empty($targeted_employee_id)) $conditions[] = " TM.targeted_employee_id = '$targeted_employee_id'";

		$conditions = $this->generate_query($conditions);
		
		return $wpdb->get_var("
			select count(*)
			from {$wpdb->prefix}task_meta TM
			left join {$wpdb->prefix}tasks T
			on TM.task_id = T.id
			$conditions
		");
	}

	public function assignTaskByRole(string $role_slug, string $task_description, int $targeted_employee_id = 0, bool $checkIfExist = false, string $calendar_id=''){
		global $wpdb;

		$response = $this->createTask(compact('task_description'));
		if(!$response) return false;

		// get task id
		$task_id = $wpdb->insert_id;		
		
		// check if role exist in system
		$role = (new Roles)->getRoleBySlug($role_slug);
		if(!$role) return true;

		// optionally check if task by this role already exist 
		if($checkIfExist && $this->isTaskByRoleExist($role->id, $targeted_employee_id)) return true;

		// link employee to task if employee if was found
		$role_employees = (new Roles)->getLinkedEmployees($role->id);
		foreach($role_employees as $role_employee){
			$response = $this->linkEmployeeToTask($task_id, $role_employee->employee_id);
			if(!$response) return false;
		}

		// generate record in task meta table
		$response = $this->createTaskMeta($task_id, $role->id, $targeted_employee_id,$calendar_id);
		if(!$response) return false;

		return true;
	}

	public function createTaskMeta(int $task_id, int $role_id, int $targeted_employee_id = 0, string $calendar_id=''){
		global $wpdb;

		$data = [
			'task_id'		=>	$task_id,
			'role_id'		=>	$role_id,
		];

		if(!empty($targeted_employee_id)) $data['targeted_employee_id'] = $targeted_employee_id;
		if(!empty($calendar_id)) $data['event_calendar_id'] = $calendar_id;

		return $wpdb->insert($wpdb->prefix."task_meta", $data);
	}

	public function clearTaskByRole(string $role_slug, int $targeted_employee_id = 0){
		global $wpdb;

		$role_id = (new Roles)->getRoleIdBySlug($role_slug);
		if(!$role_id) return true;

		// first check record in task meta table and get the task id, if no task then return true
		$task_id = $this->getTaskIdFromTaskMeta($role_id, $targeted_employee_id);
		if(!$task_id) return true;

		// mark the task as completed by task id
		$response = $this->markAsCompleted($task_id, 'System cleared the task automatically');
		if(!$response) return false;

		return true;
	}

	public function getTaskIdFromTaskMeta(int $role_id, int $targeted_employee_id = 0){
		global $wpdb;

		$conditions = [];

		$conditions[] = " role_id = '$role_id'";

		if(!empty($targeted_employee_id)) $conditions[] = " targeted_employee_id = '$targeted_employee_id'";
		
		$conditions = $this->generate_query($conditions);

		return $wpdb->get_var("
			select task_id
			from {$wpdb->prefix}task_meta
			$conditions
		");
	}

	public function markAsCompleted(int $task_id, string $task_notes){
		global $wpdb;

		$data = ['task_status'	=>	'completed', 'notes' => $task_notes];
		$response = $wpdb->update($wpdb->prefix."tasks", $data, ['id' => $task_id]);
		return $response === false ? false : true;
	}
	
}
$task=new Task_manager();