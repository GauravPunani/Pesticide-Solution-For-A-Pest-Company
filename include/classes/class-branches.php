<?php

class Branches extends GamFunctions{

    use GamValidation;

    function __construct(){
        add_action('admin_post_edit_branch',array($this,'edit_branch'));
        add_action('admin_post_nopriv_edit_branch',array($this,'edit_branch'));

        add_action('admin_post_create_branch',array($this,'create_branch'));
        add_action('admin_post_nopriv_create_branch',array($this,'create_branch'));
    }

    public function create_branch(){
        global $wpdb;

        // verifiy nonce field first
        $this->verify_nonce_field('create_branch');

        // set variables
        $branch_name = esc_html($_POST['branch_name']);
        $callrail_ac_no = esc_html($_POST['callrail_ac_no']);
        $tekcard_key = esc_html($_POST['tekcard_key']);
        $calendar_id = esc_html($_POST['calendar_id']);
        $callrail_id = esc_html($_POST['callrail_id']);
        $address = esc_html($_POST['address']);

        // set branch slug
        $branch_slug = str_replace(' ','_',strtolower($branch_name));

        $data = [
            'location_name' =>  $branch_name,
            'slug'          =>  $branch_slug,
            'tekcard_key'   =>  $tekcard_key,
            'callrail_id'   =>  $callrail_id,
            'calendar_id'   =>  $calendar_id,
            'address'       =>  $address
        ];

        if(!empty($_POST['review_link'])) $data['review_link'] = $this->sanitizeEscape($_POST['review_link']);

        $res = $wpdb->insert($wpdb->prefix."branches", $data);

        if($res){
            $message="Branch created successfully";
            $this->setFlashMessage($message,'success');
        }
        else{
            $message="Something went wrong , please try again later";
            $this->setFlashMessage($message,'danger');
        }

        wp_redirect($_POST['page_url']);
    }

    public function edit_branch(){
        global $wpdb;

		$this->verify_nonce_field('edit_branch_form');

        $page_url = esc_url_raw($_POST['page_url']);

        $required_fields = ['branch_id', 'name', 'status'];

        list($response, $message) = $this->requiredValidation($required_fields, $_POST);
        if(!$response) $this->sendErrorMessage($page_url, $message);

        $branch_id = $this->sanitizeEscape($_POST['branch_id']); 
        $name = $this->sanitizeEscape($_POST['name']); 
        $status = $this->sanitizeEscape($_POST['status']);
        $status = $status == "true" ? 1 : 0;

        $data=[
            'location_name'     =>  $name,
            'status'       		=>  $status,
        ];

        if(!empty($_POST['review_link'])){
            $data['review_link'] = $this->sanitizeEscape($_POST['review_link']);
        }

        $status = $wpdb->update($wpdb->prefix."branches", $data, ['id' => $branch_id]);
        if($status === false) $this->sendErrorMessage($page_url);

        $message="Branch data updated successfully";
        $this->setFlashMessage($message,'success');    

        wp_redirect($page_url);
    }

    public function getAllBranches($office=true){
		global $wpdb;

        $conditions=[];

        if(is_admin() && !current_user_can('other_than_upstate')){
            $accessible_branches=(new Branches)->partner_accessible_branches(true);
            $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";
        
            $conditions[]=" id IN ($accessible_branches)";
        }
    
		if($office==false){
			$conditions[]=" slug <> 'global'";
		}

        $conditions[]=" status=1";

        if(count($conditions)>0){
            $conditions=(new GamFunctions)->generate_query($conditions);
        }
        else{
            $conditions="";
        }
    
		$locations=$wpdb->get_results("
            select * from
            {$wpdb->prefix}branches
            $conditions
        ");

		return $locations;
	}

	public function partner_accessible_branches( bool $branch_id = false){
        global $wpdb;

        if(!$branch_id) return ['upstate','buffalo','rochester'];

        $branches = $wpdb->get_results("
            select id
            from {$wpdb->prefix}branches
            where slug IN ('upstate','buffalo','rochester')
        ");

        if(count($branches) <= 0) return [];

        $branch_ids = array_map(function($branch){
            return $branch->id;
        },$branches);

        return $branch_ids;
	}

    public function getBranchSlug( int $branch_id){
        global $wpdb;
        return $wpdb->get_var("
            select slug
            from {$wpdb->prefix}branches
            where id = '$branch_id'
        ");
    }

    public function getBranchName($branch_id){
        global $wpdb;

        $branch_id = empty($branch_id) ? 2 : $branch_id;

        return $wpdb->get_var("
            select location_name
            from {$wpdb->prefix}branches
            where id = '$branch_id'
        ");
    }

    public function getBranchIdBySlug( string $slug ){
        global $wpdb;
        return $wpdb->get_var("
            select id
            from {$wpdb->prefix}branches
            where slug = '$slug'
        ");
    }

    public function getBranch( int $branch_id ){
        global $wpdb;
        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}branches
            where id = '$branch_id'
        ");
    }

    public function getParentBranchId(int $branch_id){
        global $wpdb;

        return $wpdb->get_var("
            select parent
            from {$wpdb->prefix}branches
            where id = '$branch_id'
        ");
    }

    public function getParentBranchSlug(int $branch_id){
        global $wpdb;

        $parent_branch_id = $this->getParentBranchId($branch_id);

        if(!$parent_branch_id) return null;

        return $wpdb->get_var("
            select slug
            from {$wpdb->prefix}branches
            where id = '$parent_branch_id'
        ");
    }
}

new Branches();