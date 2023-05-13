<?php

class YearlyTermite extends Maintenance{
    function __construct(){
        add_action( 'admin_post_nopriv_yearly_termite_contract', array($this,'yearly_termite_contract'));
        add_action( 'admin_post_yearly_termite_contract', array($this,'yearly_termite_contract'));

        add_action( 'admin_post_nopriv_yearly_termite_filled_by_staff', array($this,'yearly_termite_filled_by_staff'));
        add_action( 'admin_post_yearly_termite_filled_by_staff', array($this,'yearly_termite_filled_by_staff'));

    }

    public function getContractById(int $contract_id, array $columns = []){
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns
            from {$wpdb->prefix}yearly_termite_contract
            where id = '$contract_id'
        ");
    }

    public function yearly_termite_contract(){

        global $wpdb;
		$this->verify_nonce_field('yearly_termite_contract');
        $data=[];

        if($_POST['method']=="update"){
            $data=$wpdb->get_row("select * from {$wpdb->prefix}yearly_termite_contract where id='{$_POST['client_id']}'",'ARRAY_A');
        }
        else{
            $data=$_POST;
        }

        list($message,$signature_img)=$this->yearly_termite_template($data);
        list($file_path,$pdf_path)=$this->save_pdf($message,'yearly_termite_contract',$_POST['name']);
    
        // if insert method, then inesrte and send contract
        if($_POST['method']=="insert"){

            // insert data into database
            $data=[
                'name'                      =>  $_POST['name'],
                'address'                   =>  $_POST['address'],
                'phone_no'                  =>  $_POST['phone_no'],
                'email'                     =>  $_POST['email'],
                'description_of_structure'  =>  $_POST['description_of_structure'],
                'area_treated'              =>  $_POST['area_treated'],
                'type_of_termite'           =>  $_POST['type_of_termite'],
                'amount'                    =>  $_POST['amount'],
                'start_date'                =>  $_POST['start_date'],
                'end_date'                  =>  $_POST['end_date'],
                'callrail_id'               =>  $_POST['callrail_id'],
                'date_cretaed'              =>  date('Y-m-d'),
                'card_details'              =>  json_encode($_POST['card_details']),
                'signature'                 =>  $signature_img,
                'pdf_path'                  =>  $pdf_path

            ];

            // building treated will be checked for other field value
            if($_POST['buildings_treated']=="other"){
                $data['buildings_treated']=$_POST['buildings_treated_other'];
            }
            else{
                $data['buildings_treated']=$_POST['buildings_treated'];
            }

            if(isset($_POST['technician_id']) && !empty($_POST['technician_id'])){
                $data['technician_id']=$_POST['technician_id'];
            }
    
            $wpdb->insert($wpdb->prefix."yearly_termite_contract",$data);
        }
    
        // if update method, update card details and send contract
        if($_POST['method']=="update"){

            // delete the token and update the pdf path 
            $wpdb->delete($wpdb->prefix."tokens",['client_id'=>$_POST['client_id']]);

            $contract_data=[
                'pdf_path'          =>  $pdf_path,
                'card_details'   	=>  json_encode($_POST['card_details']),
                'signature'   	    =>  $signature_img,
            ];

            $wpdb->update($wpdb->prefix."yearly_termite_contract",$contract_data,['id' => $_POST['client_id']]);
        }

        $subject = "Yearly Termite Contract Contract";
        $notificationMsg="Here is your copy of Yearly Termite Contract in PDF form";
        $tos = [];
        $tos[] = [
            'email' =>  $data['email'],
            'name'  =>  'GAM Client'
        ];
        
        $res=(new Sendgrid_child)->sendTemplateEmail($tos, $subject, $notificationMsg, $file_path, 'Yearly Termite Contract.pdf', 'maintenance');

        $message="Yearly Termite Contract data has been submitted, You'll recieve contract details on your email.";
        $this->setFlashMessage($message,'success');    



        // if client comes from receipt page , then update that offer is made
        if(isset($_POST['show_receipt']) && isset($_POST['invoice_id']) && !empty($_POST['invoice_id'])){
            $invoice_id=$this->encrypt_data($_POST['invoice_id'],'d');
            $wpdb->update($wpdb->prefix."invoices",['maintenance_offered'=>'offered'],['id'=>$invoice_id]);
        }

        // if it was a part of invoice flow then redirect to invoice page on invoice flow 
        $redirect_url='';
        
        if(isset($_POST['invoice_step']) && $_POST['invoice_step']=="maintenance_plan" && @$_SESSION['invoice_step']=="maintenance_plan"){

            // set the invoice step to invoice 
            $_SESSION['invoice_step']="invoice";

            // set redirect url 
            $redirect_url='/invoice';
        }
        elseif(isset($_POST['page_url']) && !empty($_POST['page_url'])){
            // set redirect url 
            $redirect_url=$_POST['page_url'];
        }
        else{
            // set redirect url 
            $redirect_url=home_url();
        }

        wp_redirect($redirect_url);
 
    }

    public function yearly_termite_filled_by_staff(){

        global $wpdb;

        $data=[

            'name'                      =>  $_POST['name'],
            'address'                   =>  $_POST['address'],
            'phone_no'                  =>  $_POST['phone_no'],
            'email'                     =>  $_POST['email'],
            'description_of_structure'  =>  $_POST['description_of_structure'],
            'area_treated'              =>  $_POST['area_treated'],
            'type_of_termite'           =>  $_POST['type_of_termite'],
            'amount'                    =>  $_POST['amount'],
            'start_date'                =>  $_POST['start_date'],
            'end_date'                  =>  $_POST['end_date'],
            'callrail_id'               =>  $_POST['callrail_id'],
            'date_cretaed'              =>  date('Y-m-d'),
        ];

        // building treated will be checked for other field value
        if($_POST['buildings_treated']=="other"){
            $data['buildings_treated']=$_POST['buildings_treated_other'];
        }
        else{
            $data['buildings_treated']=$_POST['buildings_treated'];
        }
        
		
        $client_data=$wpdb->insert($wpdb->prefix."yearly_termite_contract",$data);

        if(!$client_data){
            $message="Something went wrong, please try again later";
            $this->setFlashMessage($message,'danger');
            wp_redirect($_POST['page_url']);
            return;
        }

		$token=$this->genereate_token(20);

		// insert the token in database 
		$token_data=[
						'client_id' =>$wpdb->insert_id,
						'token'     =>$token
                    ];
        
        $wpdb->insert($wpdb->prefix."tokens",$token_data);

		$page_url=$_POST['page_url'];

        $auth_url=$page_url."?".http_build_query($token_data);
        
        $to=[$_POST['email']];
        $subject="Complete Yearly Termite Contract";
        $emailContent="<p>Please <a href='$auth_url'>click here</a> in order to complete your Yearly Termite Contract</p>";
        $tos = [];
        $tos[] = [
            'email' =>  $_POST['email'],
            'name'  =>  $_POST['name']
        ];
        
        (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $emailContent, null, null, 'maintenance');
        
        $message="An email to continue with yearly termite maintenance contrace is sent to client";
        $this->setFlashMessage($message,'success');

        $redirect_url='';

        // if it was a part of invoice flow then redirect to invoice page on invoice flow 
        if(isset($_POST['invoice_step']) && $_POST['invoice_step']=="maintenance_plan" && @$_SESSION['invoice_step']=="maintenance_plan"){

            // set the invoice step to invoice 
            $_SESSION['invoice_step']="invoice";

            // set redirect url 
            $redirect_url='/invoice';
        }
        elseif(isset($_POST['page_url']) && !empty($_POST['page_url'])){
            // set redirect url 
            $redirect_url=$_POST['page_url'];
        }
        else{
            // set redirect url 
            $redirect_url=home_url();
        }

        wp_redirect($redirect_url);
    }
    
}

new YearlyTermite();