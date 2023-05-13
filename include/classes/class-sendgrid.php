<?php

use SendGrid\Mail\Asm;
use SendGrid\Mail\From;
use SendGrid\Mail\To;
use SendGrid\Mail\Mail;
use SendGrid\Mail\GroupId;
use SendGrid\Mail\GroupsToDisplay;
use SendGrid\Mail\Content;
use SendGrid\Mail\Personalization;
use SendGrid\Mail\CustomArg;

class Sendgrid_child extends GamFunctions{

    private $email_from='service@gamexterminating.com';
    private $email_from_title='Gam Exterminating Services';

    function __construct(){
        $this->template_id = esc_attr(get_option('gam_sg_template_id'));
        $this->email_sending_api_key = esc_attr(get_option('gam_email_api_key'));
        $this->email_validation_api_key = esc_attr(get_option('gam_email_validation_api_key'));
    }

    // depricated 
    public function send_email($to=[], $subject, $message, $attachement_path='', $file_name='', $type=''){

        $mail = new \SendGrid\Mail\Mail();

        if($type=="invoice" || $type=="stripe_receipt"){
            $this->email_from="billing@gamexterminating.com";
        }
        elseif($type=="quote" || $type="maintenance"){
            $this->email_from="service@gamexterminating.com";
        }
        $mail->setFrom($this->email_from, $this->email_from_title);
        $mail->setSubject($subject);

        foreach($to as $email){
            if(!empty($email)){
                $mail->addTo($email,"Gam User");
            }
        }
        $mail->addContent("text/html",$message);

        if(is_array($attachement_path) && count($attachement_path) > 0){
            foreach ($attachement_path as $attachement) {
                $file_encoded = base64_encode(file_get_contents($attachement['file']));
                $mail->addAttachment($file_encoded,$attachement['type'],$attachement['name'],"attachment");    
            }
        }
        elseif(!empty($attachement_path)){
            $file_encoded = base64_encode(file_get_contents($attachement_path));
            $mail->addAttachment($file_encoded,"application/pdf",$file_name,"attachment");    
        }

        $sendgrid = new \SendGrid($this->email_sending_api_key);

        try {
            $response = $sendgrid->send($mail);
            $body=json_decode($response->body());

            if(array_key_exists('errors',(array)$body)){
                return ['status'=>'error','message'=>$body->errors[0]->message,'code'=>$response->statusCode(),'body'=>(array)$body];
            }
            else{
                return ['status'=>'success','body'=>(array)$body];
            }
        }
        catch (Exception $e) {
            return ['status'=>'error','code'=>$e->getMessage()];
        }
    }

    public function sendTemplateEmail(array $tos, $subject, $message, $attachement_path='', $file_name='',$type='', int $branch_id = 2){
        
        // load sendgrid php sdk from vendor
        self::loadVendor();

        // check for blocked emails or fake emails first
        foreach($tos as $key => $to){
            if((new Emails)->isBannedEmail($to['email'])) unset($tos[$key]);
        }

        if(count($tos) <= 0) return ['status' => 'error', 'code' => 'blocked emails provided'];

        $mail = new Mail();

        if($type == "invoice" || $type == "stripe_receipt") $this->email_from="billing@gamexterminating.com";
        elseif($type == "quote" || $type =="maintenance" || $type =="service_report") $this->email_from="service@gamexterminating.com";
        elseif($type == "realtor_email") $this->email_from = "nicole@gamexterminating.com";
        elseif($type == "service_feedback") $this->email_from = "client.satisfaction@gamexterminating.com";

        $mail->setFrom($this->email_from, $this->email_from_title);
        $mail->setSubject($subject);
        $mail->setTemplateId($this->template_id);
        $mail->setAsm(16644, [16644]);

        $branch_address = (new GamFunctions)->get_company_address($branch_id);

        foreach($tos as $to){
            // check if message exist in $tos array
            $email_message = ($message == 'personalization_msg' ? $to['email_content'] : $message);
            $personalization = new Personalization();
            $personalization->addTo(new To($to['email'], $to['name']));
            $personalization->addSubstitution("subject", $subject);
            $personalization->addSubstitution("client_email", $to['email']);
            $personalization->addSubstitution("email_content", $email_message);
            $personalization->addSubstitution("branch_address", $branch_address);

            $mail->addPersonalization($personalization);
        }        
        
        $mail->addContent("text/html",$email_message);

        if(is_array($attachement_path) && count($attachement_path) > 0){
            foreach ($attachement_path as $attachement) {
                $file_encoded = base64_encode(file_get_contents($attachement['file']));
                $mail->addAttachment($file_encoded,$attachement['type'],$attachement['name'],"attachment");    
            }
        }
        elseif(!empty($attachement_path)){
            $file_encoded = base64_encode(file_get_contents($attachement_path));
            $mail->addAttachment($file_encoded,"application/pdf",$file_name,"attachment");    
        }


        $sendgrid = new \SendGrid($this->email_sending_api_key);

        try {
            $response = $sendgrid->send($mail);

            $body = json_decode($response->body());

            if(array_key_exists('errors',(array)$body)){
                return [
                    'status'    =>  'error',
                    'message'   =>  $body->errors[0]->message,
                    'code'      =>  $response->statusCode(),
                    'body'      =>  (array)$body
                ];
            }
            else{
                return ['status' => 'success', 'body' => (array)$body];
            }

        } catch (Exception $e) {
            return ['status'=>'error','code'=>$e->getMessage()];
        }
    }

    public function isValidEmail(string $email){

        // load sendgrid php sdk from vendor
        self::loadVendor();

        $sg = new \SendGrid($this->email_validation_api_key);

        $request_body = [
            'email'     =>  $email,
            'source'    =>  'signup'
        ];

        try {
            $response = $sg->client->validations()->email()->post($request_body);
            $response = json_decode($response->body());
            $email_status = $response->result->verdict == "Invalid" ? "no" : "yes";

            // update email status in database
            $this->updateEmailStatus($email, $email_status);

            // return email status to calling function
            return $response->result->verdict == "Invalid" ? false : true;

        } catch (Exception $ex) {
            return false;
        }
    }

    public function updateEmailStatus(string $email, string $status){
        global $wpdb;

        $response = $wpdb->update($wpdb->prefix."emails", ['is_valid' => $status], ['email' => $email]);
        return $response === false ? false : true;
    }

}