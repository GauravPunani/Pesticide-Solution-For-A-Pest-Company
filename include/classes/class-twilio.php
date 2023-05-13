<?php

// Use the REST API Client to make requests to the Twilio REST API        
use Twilio\Rest\Client;

class Twilio extends GamFunctions{
    
    private $client;
    private $base_url;

    function __construct(){

        // load twillio php sdk from vendor 
        self::loadVendor();

        // Your Account SID and Auth Token from twilio.com/console
        $sid = 'ACc37564f275bb841d981fbc9cd5568a03';
        $token = '4718072f5f7252eebd19ad160006e607';
        $this->client = new Client($sid, $token);
        $this->base_url = wp_upload_dir()['baseurl'];
    }

    public function send_text_message($phone_no='',$message=''){

        $phone_no = $this->sanitizeUSPhoneNo($phone_no);
        //$phone_no = $phone_no;

        try{
            // Use the client to do fun stuff like send text messages!
            $this->client->messages->create(
                // the number you'd like to send the message to
                $phone_no,
                [
                    // A Twilio phone number you purchased at twilio.com/console
                    'from' => '+17738230870',
                    // the body of the text message you'd like to send
                    'body' => $message
                ]
            );

            return true;
        }
        catch(Exception $e){
            echo $e->getCode() . ' : ' . $e->getMessage()."<br>";
            return false;
        }
    }

    public function sendInvoiceLink(int $invoice_id, string $phone_no){

        $invoice = (new Invoice)->getInvoiceById($invoice_id, ['email']);

        $document_link = $this->clientDocumentUrl('invoice', $invoice_id, $invoice->email);

        $shorten_link = (new Bitly)->shortenLink($document_link);
        if(!$shorten_link) return false;

        $message = "Thank you for your business with GAM EXTERMINATING. Please click here $document_link to view your invoice";

        // check client email is multiple or single
		if(strpos($phone_no, ',') !== false) {
			foreach(explode(',',$phone_no) as $phone){
				$response = $this->send_text_message($phone, $message);
			}
            return $response;
		} else {
            return $this->send_text_message($phone_no, $message);
		}
    }

    public function sendResidentialQuoteLink(int $quote_id, string $phone_no){

        $quote = (new Quote)->getResidentialQuoteById($quote_id, ['clientEmail']);

        $document_link = $this->clientDocumentUrl('residential_quote', $quote_id, $quote->clientEmail);

        $shorten_link = (new Bitly)->shortenLink($document_link);
        if(!$shorten_link) return false;

        $message = "Thank you for your business with GAM EXTERMINATING. Please click here $shorten_link to view your residential quotesheet";

        return $this->send_text_message($phone_no, $message);
    }

    public function sendCommercialQuoteLink(int $quote_id, string $phone_no){

        $quote = (new Quote)->getCommercialQuoteById($quote_id, ['clientEmail']);

        $document_link = $this->clientDocumentUrl('commercial_quote', $quote_id, $quote->clientEmail);

        $shorten_link = (new Bitly)->shortenLink($document_link);
        if(!$shorten_link) return false;

        $message = "Thank you for your business with GAM EXTERMINATING. Please click here $shorten_link to view your commercial quotesheet";

        return $this->send_text_message($phone_no, $message);
    }

    // FOR MAINTENANCE CONTRACTS - SEND ACTUALL PDF LINK OF CONTRACT
    public function sendMonthlyMaintenanceLink(int $contract_id, string $phone_no){
        global $wpdb;

        $contract = (new MonthlyQuarterlyMaintenance)->getContractById($contract_id, ['pdf_path']);
        if(!$contract || empty($contract->pdf_path)) return false;

        $pdf_url = $this->base_url.$contract->pdf_path;
        
        $shorten_link = (new Bitly)->shortenLink($pdf_url);
        if(!$shorten_link) return false;

        $message = "Thank you for your business with GAM EXTERMINATING. Please click here $shorten_link to view your Monthly Maintneance Contract.";
        
        return $this->send_text_message($phone_no, $message);
    }

    public function sendQuarterlyMaintenanceLink(int $contract_id, string $phone_no){

        $contract = (new MonthlyQuarterlyMaintenance)->getContractById($contract_id, ['pdf_path']);
        if(!$contract || empty($contract->pdf_path)) return false;

        $pdf_url = $this->base_url.$contract->pdf_path;
        
        $shorten_link = (new Bitly)->shortenLink($pdf_url);
        if(!$shorten_link) return false;

        $message = "Thank you for your business with GAM EXTERMINATING. Please click here $shorten_link to view your Quarterly Maintenance Contract.";

        return $this->send_text_message($phone_no, $message);
    }

    public function sendSpecialMaintenanceLink(int $contract_id, string $phone_no){

        $contract = (new SpecialMaintenance)->getContractById($contract_id, ['pdf_path']);
        if(!$contract || empty($contract->pdf_path)) return false;

        $pdf_url = $this->base_url.$contract->pdf_path;
        
        $shorten_link = (new Bitly)->shortenLink($pdf_url);
        if(!$shorten_link) return false;

        $message = "Thank you for your business with GAM EXTERMINATING. Please click here $shorten_link to view your Special Maintenance Contract.";

        return $this->send_text_message($phone_no, $message);
    }

    public function sendCommercialMaintenanceLink(int $contract_id, string $phone_no){

        $contract = (new CommercialMaintenance)->getContractById($contract_id, ['pdf_path']);
        if(!$contract || empty($contract->pdf_path)) return false;

        $pdf_url = $this->base_url.$contract->pdf_path;

        $shorten_link = (new Bitly)->shortenLink($pdf_url);
        if(!$shorten_link) return false;

        $message = "Thank you for your business with GAM EXTERMINATING. Please click here $shorten_link to view your Commercial Maintenance Contract.";

        return $this->send_text_message($phone_no, $message);
    }

    public function clientDocumentUrl(string $type, int $id, string $email){

        $encrypted_id = $this->encrypt_data($id);

        $document_args = [
            'type'  =>  $type,
            'id'    =>  $encrypted_id,
            'email' =>  $email
        ];

        $document_args = http_build_query($document_args);

        $document_link = site_url()."/client-documents?".$document_args;

        return $document_link;
    }

    public function sendThankYouMessage(int $invoice_id){

        $invoice = (new Invoice)->getInvoiceById($invoice_id, ['client_name', 'phone_no', 'branch_id']);
		// SEND TEXT MESSAGE ON CLIENT PHONE AS WELL
		$review_link = $this->getReviewLink($invoice->branch_id);

		$client_name = explode(" ",$invoice->client_name)[0];
		$client_name = strtoupper(substr($invoice->client_name, 0, 10));
        $phone_no = "+1".str_replace("-", "", $invoice->phone_no);

		$message = "Thank you $client_name for your business with GAM Exterminating, please leave us a 5 star review by clicking here $review_link then scroll to the review section";
        
		$this->send_text_message($phone_no, $message);
    }

}

new Twilio();