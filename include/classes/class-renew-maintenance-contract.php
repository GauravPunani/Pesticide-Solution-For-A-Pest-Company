<?php

class RenewMaintenanceContract extends GamFunctions{

    use GamValidation;

    function __construct(){

		add_action("wp_ajax_renew_maintenance_contract",array($this,'update_client_maintenance_contract'));
		add_action("wp_ajax_nopriv_renew_maintenance_contract",array($this,'update_client_maintenance_contract'));
        
    }

    // This method update client new contract signature and set contract start and end date
    public function update_client_maintenance_contract(){
        global $wpdb;

		$this->verify_nonce_field('renew_maintenance_contract');

        $required_fields = [
            'contract_start_date',
            'contract_end_date',
            'signimgurl',
            'contract_id',
            'contract_type',
            'contract_name',
            'contract_sign'
        ];
        
        foreach($required_fields as $required_field){
            if(empty($_POST[$required_field])) $this->response('error', $required_field." is required");
        }

        $contract_start_date = $this->sanitizeEscape($_POST['contract_start_date']);
        $contract_end_date = $this->sanitizeEscape($_POST['contract_end_date']);
        $client_name = $this->sanitizeEscape($_POST['contract_name']);
        $type = $this->sanitizeEscape($_POST['contract_type']);
        $contract_id = $this->sanitizeEscape($_POST['contract_id']);
        $contract_old_sign = $this->sanitizeEscape($_POST['contract_sign']);
        $signimgurl = $_POST['signimgurl'];

        $this->beginTransaction();

        list($signature_img, $image_file) = $this->save_signature($signimgurl, 'maintenance', $client_name);

        if($type == 'commercial'){
            $db_tbl = 'commercial_maintenance';
            $update_data = array('contract_start_date' => $contract_start_date, 'contract_end_date' => $contract_end_date, 'signature' => $signature_img, 'renew_status' => 1);
        }elseif($type == 'special'){
            $db_tbl = 'special_contract';
            $update_data = array('from_date' => $contract_start_date, 'to_date' => $contract_end_date, 'signature' => $signature_img, 'renew_status' => 1);
        }elseif($type == 'termite'){
            $db_tbl = 'yearly_termite_contract';
            $update_data = array('start_date' => $contract_start_date, 'end_date' => $contract_end_date, 'signature' => $signature_img, 'renew_status' => 1);
        }else{
            $db_tbl = 'maintenance_contract';
            $update_data = array('contract_start_date' => $contract_start_date, 'contract_end_date' => $contract_end_date, 'signature' => $signature_img, 'renew_status' => 1);
        }
        
        list($response, $message) = $this->updateContract($contract_id, $update_data, $db_tbl);

        if (!empty($contract_old_sign)) $this->deleteFileByUrl($contract_old_sign);

        (new Emails)->renewContractCcEmail($contract_id, $type, true);
        
        if(!$response) $this->rollBackResponse('error', $message);

        $this->commitTransaction();  

        $this->response('success', 'Contract '.$type.' updated successfully');
    }

    public function updateContract(int $contract_id, array $data, string $db_tbl){
        global $wpdb;
        $response = $wpdb->update($wpdb->prefix.$db_tbl, $data, ['id' => $contract_id]);
        if($response === false) return [false, $wpdb->last_error];
        return [true, null];
    }

}
new RenewMaintenanceContract();