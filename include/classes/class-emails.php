<?php

use phpDocumentor\Reflection\Types\Boolean;

class Emails extends Sendgrid_child
{


    function __construct()
    {

        add_action("wp_ajax_check_if_email_exist", array($this, 'check_if_email_exist'));
        add_action("wp_ajax_nopriv_check_if_email_exist", array($this, 'check_if_email_exist'));

        add_action("wp_ajax_check_for_banned_email", array($this, 'check_for_banned_email'));
        add_action("wp_ajax_nopriv_check_for_banned_email", array($this, 'check_for_banned_email'));

        add_action("admin_post_create_email", array($this, 'create_email'));
        add_action("admin_post_nopriv_create_email", array($this, 'create_email'));

        add_action("admin_post_download_emails_csv", array($this, 'download_emails_csv'));
        add_action("admin_post_nopriv_download_emails_csv", array($this, 'download_emails_csv'));

        add_action("admin_post_edit_email_record", array($this, 'edit_email_record'));
        add_action("admin_post_nopriv_edit_email_record", array($this, 'edit_email_record'));

        add_action("admin_post_create_cold_calls_log", array($this, 'create_cold_calls_log'));
        add_action("admin_post_nopriv_create_cold_calls_log", array($this, 'create_cold_calls_log'));

        add_action("admin_post_unsubscribe_from_satisfaction_email", array($this, 'unsubscribe_client_from_campaign'));
        add_action("admin_post_nopriv_unsubscribe_from_satisfaction_email", array($this, 'unsubscribe_client_from_campaign'));

        add_action('admin_post_update_cold_calls_status', array($this, 'update_cold_calls_status'));
        add_action('admin_post_nopriv_update_cold_calls_status', array($this, 'update_cold_calls_status'));

        add_action("wp_ajax_view_cold_call_logs", array($this, 'view_cold_call_logs'));
        add_action("wp_ajax_nopriv_view_cold_call_logs", array($this, 'view_cold_call_logs'));

        add_action("wp_ajax_view_unsubscribe_campaign_list", array($this, 'unsubscribe_client_campaign_list'));
        add_action("wp_ajax_nopriv_view_unsubscribe_campaign_list", array($this, 'unsubscribe_client_campaign_list'));
    }

    public function sendColdCallerRealtorEmail(int $lead_id)
    {
        global $wpdb;
        $lead = (new Leads)->getLead($lead_id);
        if (!$lead && empty($lead->email)) return false;

        $cold_caller = (new ColdCaller)->getColdCallerById($lead->cold_caller_id, ['name']);
        if (!$cold_caller) return false;

        // get the email template for realtor email
        $file_path = get_template_directory() . "/template/emails/realtors-email.html";
        $email_template = file_get_contents($file_path);

        // replace client name and cold caller name in email template
        $email_template = str_replace('[realtor-name]', $lead->name, $email_template);
        $email_template = str_replace('[cold-caller-name]', $cold_caller->name, $email_template);

        // send email to client
        $subject = "Pest Control/Termite Inspections";
        $tos = [];

        $tos[] = [
            'email' =>  $lead->email,
            'name'  =>  $lead->name
        ];

        // send email 
        $response = (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $email_template, '', '', 'realtor_email');
        if ($response['status'] == false) return false;

        return true;
    }

    public function getBannedEmails()
    {
        global $wpdb;
        return $wpdb->get_results("
            select * 
            from {$wpdb->prefix}banned_emails
        ");
    }

    public function getClientsCampaign()
    {
        global $wpdb;
        return $wpdb->get_results("
            select * 
            from {$wpdb->prefix}clients_campaign
        ");
    }

    public function getUnsubscribeClientsList()
    {
        global $wpdb;
        return $wpdb->get_results("
            select client_id
            from {$wpdb->prefix}clients_unsubscribe_campaign
        ", ARRAY_A);
    }

    public function getEmailSystemsStatus(string $email)
    {
        global $wpdb;
        return $wpdb->get_var("
            select is_valid
            from {$wpdb->prefix}emails
            where email = '$email'
        ");
    }

    public function isBannedEmail(string $email)
    {
        global $wpdb;

        // first check if banned email
        $response = $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}banned_emails
            where email like '%$email%'
        ");

        if ($response) return true;

        // second check if email is suspicious using regex keywords
        $banned_keywords = '/(noemail|test|unknown|quote|wouldnt|clientdidnotprovideone|client|fake|norecord|sample|@none|duplicate|noname|@n.com|clientdidnotwanttoprovideemail|invalidemail|Email-|wouldntgiveemail|niemail|@example|@test|@noemail|@notprovided|@gamexterminating.com|@gamexterminatingservices.com)/i';

        preg_match($banned_keywords, $email, $matches);
        if (count($matches)) return true;

        // second check if email is not valide in system
        $email_status = $this->getEmailSystemsStatus($email);
        if ($email_status == "yes") return false;
        if ($email_status == "no") return true;

        // if not valid from sendgrid return true as not valid
        if (!(new Sendgrid_child)->isValidEmail($email)) return true;

        return false;
    }


    public function check_for_banned_email()
    {
        global $wpdb;
        if (empty($_POST['email'])) {
            echo "false";
            wp_die();
        }
        $email = sanitize_email($_POST['email']);

        if (!empty($_POST['checkForOfficeEmail'])) {
            $isEmailExist = DB::table('office_emails')->where('email', $email)->first()->get();
            if ($isEmailExist) {
                echo "false";
                wp_die();
            }
        }

        if ($this->isBannedEmail($email)) {
            echo "false";
            wp_die();
        }

        echo "true";
        wp_die();
    }

    public function emailResidentialQuote(int $quote_id, string $email = '')
    {

        $quote = (new Quote)->getResidentialQuoteById($quote_id, ['quote_no', 'clientName', 'clientEmail', 'branch_id']);
        $quotesheet_template = (new Quote)->residentialQuotePDFContent($quote_id);
        list($file_path, $pdf_path) = $this->save_pdf($quotesheet_template, 'quotesheet', $quote->clientName);

        if (empty($file_path)) return false;

        $subject = "Residential Quote Sheet for {$quote->quote_no}";
        $mail_message = "
            <p>Hello <b>$quote->clientName</b></p>
            <p>Your residential quote sheet is attached with this email in PDF form.</p>
            " . $this->getReviewLine($quote->branch_id) . "
            <p>Thanks</p>
        ";

        $email = !empty($email) ? $email : $quote->clientEmail;
        if (empty($email)) return false;
        $tos = [];
        $tos[] = [
            'email' =>  $email,
            'name'  =>  $quote->clientName
        ];

        // send email 
        $response = (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $mail_message, $file_path, 'Residential Quote.pdf', 'quote');

        // delete quote pdf 
        unset($file_path);

        return $response['status'] == "success" ? true : false;
    }

	public function emailCommercialQuote(int $quote_id, string $email = ''){

		$quote = (new Quote)->getCommercialQuoteById($quote_id, ['quote_no','client_name', 'clientEmail', 'branch_id']);
		if(!$quote) return false;
        $quote_template = (new Quote)->commercialQuotesheetPDFContent($quote_id);        
        list($file_path,$pdf_path) = $this->save_pdf($quote_template,'quotesheet',$quote->client_name);
		if(empty($file_path)) return false;
    
        $subject = "Commercial Quote Sheet for {$quote->quote_no}";
        $mail_message = "
            <p>Hello $quote->client_name</p>
            <p>Your commercial quote sheet is attached with this email in PDF form.</p>
            " . $this->getReviewLine($quote->branch_id) . "
            <p>Thanks</p>
        ";

        $email = !empty($email) ? $email : $quote->clientEmail;
        if (empty($email)) return false;

        $tos = [];
        $tos[] = [
            'email' =>  $email,
            'name'  =>  $quote->client_name
        ];

        // send email 
        $response = (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $mail_message, $file_path, 'Commerical Quote.pdf', 'quote');
        // delete quote pdf 
        unset($file_path);

        return $response['status'] == "success" ? true : false;
    }

    public function emailMonthlyContract(int $contract_id)
    {

        $contract = (new MonthlyQuarterlyMaintenance)->getContractById($contract_id, ['client_name', 'client_email', 'pdf_path', 'branch_id']);
        $upload_dir = wp_upload_dir();
        $message = "
            <p>Thanks for your interest in Gam exterminating maintenance service.</p>
            <p>Your monthly maintenance contract is attached with this email in PDF form.</p>
            " . $this->getReviewLine($contract->branch_id) . "            
			<p>Thanks</p>
        ";

        $subject = "Monthly Maintenance Contract";
        $tos = [];
        $tos[] = [
            'email' =>  $contract->client_email,
            'name'  =>  $contract->client_name
        ];
        $response = (new Sendgrid_child)->sendTemplateEmail(
            $tos,
            $subject,
            $message,
            $upload_dir['basedir'] . "/" . $contract->pdf_path,
            'Monthly Maintenance Contract.pdf',
            'maintenance'
        );

        return $response['status'] == "success" ? true : false;
    }

    public function emailQuarterlyContract(int $contract_id)
    {

        $contract = (new MonthlyQuarterlyMaintenance)->getContractById($contract_id, ['client_name', 'client_email', 'pdf_path', 'branch_id']);
        $upload_dir = wp_upload_dir();
        $message = "
            <p>Thanks for your interest in Gam exterminating maintenance service.</p>
            <p>Your quarterly maintenance contract is attached with this email in PDF form.</p>
            " . $this->getReviewLine($contract->branch_id) . "
			<p>Thanks</p>
        ";

        $subject = "Quarterly Maintenance Contract";
        $tos = [];
        $tos[] = [
            'email' =>  $contract->client_email,
            'name'  =>  $contract->client_name
        ];

        $response = (new Sendgrid_child)->sendTemplateEmail(
            $tos,
            $subject,
            $message,
            $upload_dir['basedir'] . "/" . $contract->pdf_path,
            'Quarterly Maintenance Contract.pdf',
            'maintenance'
        );

        return $response['status'] == "success" ? true : false;
    }

    public function emailSpecialContract(int $contract_id)
    {
        $contract = (new SpecialMaintenance)->getContractById($contract_id, ['client_name', 'client_email', 'pdf_path', 'branch_id']);
        $upload_dir = wp_upload_dir();

        $message = "
            <p>Thanks for your interest in Gam exterminating maintenance service.</p>
            <p>Your special maintenance contract is attached with this email in PDF form.</p>
            " . $this->getReviewLine($contract->branch_id) . "            
			<p>Thanks</p>
        ";

        $subject = "Special Maintenance Contract";
        $tos = [];
        $tos[] = [
            'email' =>  $contract->client_email,
            'name'  =>  $contract->client_name
        ];

        $response = (new Sendgrid_child)->sendTemplateEmail(
            $tos,
            $subject,
            $message,
            $upload_dir['basedir'] . "/" . $contract->pdf_path,
            'Special Maintenance Contract.pdf',
            'maintenance'
        );
        return $response['status'] == "success" ? true : false;
    }

    public function emailCommercialContract(int $contract_id)
    {

        $contract = (new CommercialMaintenance)->getContractById($contract_id, ['establishement_name', 'client_email', 'pdf_path', 'branch_id']);
        $upload_dir = wp_upload_dir();
        $message = "
            <p>Thanks for your interest in Gam exterminating maintenance service.</p>
            <p>Your commercial maintenance contract is attached with this email in PDF form.</p>
            " . $this->getReviewLine($contract->branch_id) . "
			<p>Thanks</p>
        ";

        $subject = "Commercial Maintenance Contract";
        $tos = [];
        $tos[] = [
            'email' =>  $contract->client_email,
            'name'  =>  $contract->establishement_name
        ];

        $response = (new Sendgrid_child)->sendTemplateEmail(
            $tos,
            $subject,
            $message,
            $upload_dir['basedir'] . "/" . $contract->pdf_path,
            'Commercial Maintenance Contract.pdf',
            'maintenance'
        );
        return $response['status'] == "success" ? true : false;
    }

    public function contractCcEmail(int $contract_id, string $contract_type, string $email = '')
    {

        if ($contract_type == "monthly" || $contract_type == "quarterly") {
            $contract = (new MonthlyQuarterlyMaintenance)->getContractById($contract_id, ['client_email', 'branch_id']);
        } elseif ($contract_type == "special") {
            $contract = (new SpecialMaintenance)->getContractById($contract_id, ['client_email', 'branch_id']);
        } elseif ($contract_type == "commercial") {
            $contract = (new CommercialMaintenance)->getContractById($contract_id, ['client_email', 'branch_id']);
        } else {
            return false;
        }

        if (empty($contract->client_email)) return false;
        $contract_id = $this->encrypt_data($contract_id);
        $client_email = !empty($email) ? $email : $contract->client_email;
        $page_url = "";
        switch ($contract_type) {
            case 'monthly':
                $page_url = (new Maintenance)->monthlyPageUrl();
                break;
            case 'quarterly':
                $page_url = (new Maintenance)->quarterlyPageUrl();
                break;
            case 'commercial':
                $page_url = (new Maintenance)->commercialPageUrl();
                break;
            case 'special':
                $page_url = (new Maintenance)->specialPageUrl();
                break;

            default:
                return false;
                break;
        }

        $page_url .= "?contract-id=$contract_id";
        $subject = "Complete Your Maintenance Process";
        $emailContent = "
            <p> Thanks for your interest in Gam exterminating maintenance service.</p>
            <p>Please <a href='$page_url'>click here</a> in order to sign your new contract</p>
            " . $this->getReviewLine($contract->branch_id) . "
            <p>Thanks</p>
        ";

        $tos = [];
        $tos[] = [
            'email' =>  $client_email,
            'name'  =>  'GAM Client'
        ];
        $response = (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $emailContent, null, null, 'maintenance');
        return $response['status'] == "success" ? true : false;
    }

    public function emailClientCageReturn(object $cage)
    {
        if (is_object($cage) && !empty($cage)) {
            $subject = "Return of animal cage";
            $cage_temp = get_page_by_path('return-of-animal-cage', OBJECT, 'emailtemplates');
        	$content = $cage_temp->post_content;
       		$content = apply_filters('the_content', $content);
            $emailContent = $content;

            $tos = [];
            $tos[] = [
                'email' =>  $cage->email,
                'name'  =>  $cage->name
            ];

            $response = (new Sendgrid_child)->sendTemplateEmail(
                $tos,
                $subject,
                $emailContent,
                null,
                null,
                'maintenance'
            );
            return $response['status'] == "success" ? true : false;
        }
    }

    public function emailSpringTreatment(object $cage)
    {
        if (is_object($cage) && !empty($cage)) {
            $query_yes_url = add_query_arg(['id' => $this->encrypt_data($cage->id),'action' => $this->encrypt_data('yes')], (new Maintenance)->springTreatmentPageUrl());
            $query_no_url = add_query_arg(['id' => $this->encrypt_data($cage->id),'action' => $this->encrypt_data('no')], (new Maintenance)->springTreatmentPageUrl());

            $yes_url = '<a href='.$query_yes_url.'>Yes</a>';
            $no_url = '<a href='.$query_no_url.'>No</a>';

            $subject = "Spring treatment for your home ?";
            $spring_temp = get_page_by_path( 'spring-treatment-for-your-home', OBJECT, 'emailtemplates');
        	$content = $spring_temp->post_content;
       		$content = apply_filters('the_content', $content);
            $emailContent = str_replace(
                array('[name_of_client]', '[yes]', '[no]', '[review_link]'), 
                array($cage->name, $yes_url, $no_url , $this->getReviewLine($cage->branch_id)), 
                $content
                );
            $tos = [];
            $tos[] = [
                'email' =>  $cage->email,
                'name'  =>  $cage->name
            ];

            $response = (new Sendgrid_child)->sendTemplateEmail(
                $tos,
                $subject,
                $emailContent,
                null,
                null,
                'maintenance'
            );
            return $response['status'] == "success" ? true : false;
        }
    }

    public function emailClientForServiceFeedback(array $clients)
    {
        if (is_array($clients) && !empty($clients)) {
            $service_temp = get_page_by_path('client-service-satisfaction-feedback', OBJECT, 'emailtemplates');
            $subject = sprintf('%s for %s', $service_temp->post_title, $clients['inv_no']);
        	$content = $service_temp->post_content;
       		$content = apply_filters('the_content', $content);
            $emailContent = str_replace(
                array('[company_phone_no]'), 
                array(esc_attr(get_option('gam_company_phone_no'))), 
                $content
            );

            $invoice_upload_path = (new Mpdf)->generateInvoicePdf($clients['inv_id']);

            $tos = $pdf_files = [];

            $pdf_files[] = [
                'file'	=>	$invoice_upload_path,
                'type'	=>	'application/pdf',
                'name'	=>	"Invoice {$clients['inv_no']}.pdf"
            ];

            $tos[] = [
                'email' => $clients['email'],
                'name'  =>  $clients['name']
            ];
        
            $response = (new Sendgrid_child)->sendTemplateEmail(
                $tos,
                $subject,
                $emailContent,
                $pdf_files,
                null,
                'service_feedback'
            );
            return $response['status'] == "success" ? true : false;
        }
    }

    public function renewContractCcEmail(int $contract_id, string $contract_type, bool $renew = false, string $email = '')
    {
        $contract_end = '';
        if ($contract_type == "monthly" || $contract_type == "quarterly") {
            $contract = (new MonthlyQuarterlyMaintenance)->getContractById($contract_id, ['client_email', 'branch_id', 'contract_end_date']);
            $contract_end = $contract->contract_end_date;
        } elseif ($contract_type == "special") {
            $contract = (new SpecialMaintenance)->getContractById($contract_id, ['client_email', 'branch_id', 'to_date']);
            $contract_end = $contract->to_date;
        } elseif ($contract_type == "commercial") {
            $contract = (new CommercialMaintenance)->getContractById($contract_id, ['client_email', 'branch_id', 'contract_end_date']);
            $contract_end = $contract->contract_end_date;
        } elseif ($contract_type == "termite") {
            $contract = (new YearlyTermite)->getContractById($contract_id, ['email', 'end_date']);
            $contract_end = $contract->end_date;
        } else {
            return false;
        }

        if (empty($contract->client_email) && empty($contract->email)) return false;
        $contract_id = $this->encrypt_data($contract_id);
        $client_email = !empty($email) ? $email : (isset($contract->client_email) ? $contract->client_email : $contract->email);
        $end_date = date("D, M j Y", strtotime($contract_end)) . '<br>';
        $ctype = $contract_type;
        $page_url = esc_url(add_query_arg(
            [
                'contract-id' => $contract_id,
                'type' => $this->encrypt_data($ctype)
            ],
            (new Maintenance)->renewContractPageUrl()
        ));

        if ($renew) {
            $subject = "Your ". ucwords($ctype) ." Maintenance Contract Renew Successfully";
            $emailContent = "
            <p> Thanks for your interest in Gam exterminating maintenance service.</p>
            <p>Your {$ctype} maintenance contract date extend to <b>{$end_date}</b> we will remind you 2 days before expiring.</p>
            " . (isset($contract->branch_id) ? $this->getReviewLine($contract->branch_id) : '') . "
            <p>Thanks</p>
        ";
        } else {
            $subject = "Renew Your " . ucwords($ctype) . " Maintenance Contract";
            $emailContent = "
            <p> Thanks for your interest in Gam exterminating maintenance service.</p>
            <p>Your {$ctype} maintenance contract is expiring on <b>{$end_date}</b> please <a href='$page_url'>click here</a> in order to renew your maintenance contract</p>
            " . (isset($contract->branch_id) ? $this->getReviewLine($contract->branch_id) : '') . "
            <p>Thanks</p>
        ";
        }

        $tos = [];
        $tos[] = [
            'email' =>  $client_email,
            'name'  =>  'GAM Client'
        ];
        $response = (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $emailContent, null, null, 'maintenance');
        return $response['status'] == "success" ? true : false;
    }

    public function edit_email_record()
    {
        global $wpdb;

        $this->verify_nonce_field('edit_email_record');

        $page_url = $_POST['page_url'];

        // $data=['status'	=>	$_POST['type']];
        // if(!empty($_POST['name'])) $data['name']=$_POST['name'];
        // if(!empty($_POST['address'])) $data['address']=$_POST['address'];
        // if(!empty($_POST['phone_no']))$data['phone']=$_POST['phone_no'];

        $email = esc_html($_POST['email']);
        $name = esc_html($_POST['name']);
        $address = esc_html($_POST['address']);
        $phone = esc_html($_POST['phone']);
        $status = esc_html($_POST['status']);
        $book_appointment = esc_html($_POST['book_appointment']);
        $note = esc_html($_POST['note']);
        $is_valid_email = esc_html($_POST['is_client_email_valid']);

        $data = [
            'email'        =>    $email,
            'name'    =>    $name,
            'address'    =>    $address,
            'phone'    =>    $phone,
            'status'    =>    $status,
            'book_appointment'        =>    $book_appointment,
            'note' => $note,
            'is_valid' => 'yes',
            'date'        =>    date('Y-m-d')
        ];

        $res = $wpdb->update($wpdb->prefix . "emails", $data, ['id' => $_POST['email_id']]);
        print_r($res);
        if (!$res) $this->sendErrorMessage($page_url);
        $meessage = "Email Updated Successfully";
        $this->setFlashMessage($meessage, "success");
        wp_redirect($page_url);
    }

    public function download_emails_csv()
    {
        global $wpdb;

        $page_url = $_POST['page_url'];
        if (empty($_POST['from_date'])) $this->sendErrorMessage($page_url);
        if (empty($_POST['to_date'])) $this->sendErrorMessage($page_url);
        if (empty($_POST['type'])) $this->sendErrorMessage($page_url);
    
        $from_date = sanitize_text_field($_POST['from_date']);
        $to_date = sanitize_text_field($_POST['to_date']);
        $type = sanitize_text_field($_POST['type']);
        $branch_id = sanitize_text_field($_POST['branch_id']);
        $branch = $status = '';
        if (!empty($branch_id)){
            $branch = "and branch_id = '$branch_id'";
        }

        if($type != 'all_clients'){
            $status = "and status = '$type'";
        }
        $emails = $wpdb->get_results("
			select *
			from {$wpdb->prefix}emails
			where DATE(date) >= '$from_date'
			and DATE(date) <= '$to_date'
			$status
            and email <> ''
            $branch
		");
       
        header("Content-Disposition: attachment; filename=\"Client Emails.xls\"");
        header("Content-Type: application/vnd.ms-excel;");
        header("Pragma: no-cache");
        header("Expires: 0");
        $out = fopen("php://output", 'w');

        $header_line = [
            'Name',
            'Street Address',
            'City',
            'State',
            'Zip Code',
            'Phone No.',
            'Email',
            'Type',
            'Date',
        ];

        fputcsv($out, $header_line, "\t");
            foreach ($emails as $email) {
                $street_address = $city = $state = $postal_code = '';
                $location = (new GamFunctions)->getLocationFromAddress([
                    'address' => $email->address,
                    'apiKey' => 'AIzaSyBkVmhrScUM6KYaexQDQY8Colf1bnwZ380'
                ]);
                if(is_array($location)){
                    $street_address = sprintf('%s %s, %s, %s', $location['street_number'],$location['route'],$location['neighborhood'],$location['locality']);
                    $city = $location['administrative_area_level_2'];
                    $state = $location['administrative_area_level_1'];
                    $postal_code = $location['postal_code'];
                }else{
                    $street_address = $email->address;
                }
                $line = [
                    $email->name,
                    $street_address,
                    $city,
                    $state,
                    $postal_code,
                    $email->phone,
                    $email->email,
                    $email->status,
                    date("d M Y", strtotime($email->date)),
                ];

                fputcsv($out, $line, "\t");
            }
        fclose($out);
    }

    public function create_email()
    {
        global $wpdb;

        $this->verify_nonce_field('create_email');

        $page_url = $_POST['page_url'];
        $email = esc_html($_POST['email']);
        $name = esc_html($_POST['name']);
        $address = esc_html($_POST['address']);
        $phone = esc_html($_POST['phone']);
        $status = esc_html($_POST['status']);
        $book_appointment = esc_html($_POST['book_appointment']);
        $note = esc_html($_POST['note']);

        $data = [
            'email'        =>    $email,
            'name'    =>    $name,
            'address'        =>    $address,
            'phone'    =>    $phone,
            'status'    =>    $status,
            'book_appointment'        =>    $book_appointment,
            'note'        =>    $note,
            'date'        =>    date('Y-m-d')
        ];

        $res = $wpdb->insert($wpdb->prefix . "emails", $data);
        print_r($data);

        if ($res) {
            $message = "Cold Calls created successfully";
            $this->setFlashMessage($message, 'success');
        } else {
            $message = "Something went wrong , please try again later";
            $this->setFlashMessage($message, 'danger');
        }
        wp_redirect($page_url);
    }

    public function check_if_email_exist()
    {
        global $wpdb;
        if (empty($_POST['email'])) echo 'true';
        wp_die();
        $email = sanitize_email($_POST['email']);
        $res = $wpdb->get_var("select count(*) from {$wpdb->prefix}emails where email='$email'");
        echo $res ? 'false' : 'true';
        wp_die();
    }

    public function save_email($data)
    {
        global $wpdb;
        if (empty($data['date'])) $data['date'] = date('Y-m-d h:i:s');
        $data['email'] = sanitize_email($data['email']);
        if (!empty($data['email']) && $this->emailExist($data['email'])) return true;
        return $wpdb->insert($wpdb->prefix . "emails", $data);
    }

    public function emailExist(string $email)
    {
        global $wpdb;
        $email = sanitize_email($email);
        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}emails
            where email = '$email'		
        ");
    }

    public function emailTemplateHeader()
    {
        $upload_dir = wp_upload_dir();
        return "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Document</title>
                <style>
                    body{
                        font-family: arial, sans-serif;
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
                </style>
            </head>
            <body>
            <table>
                <tr>
                    <td>
                        <img width='100%' src='" . (new GamFunctions)->getBannerImage() . "'/>
                    </td>
                </tr>
            </table>
        ";
    }

    // Create Cold Calls Log 
    public function unsubscribe_client_from_campaign()
    {
        global $wpdb;
        $this->verify_nonce_field('unsubscribe_from_satisfaction_email');

        // set variables
        $campaign_id = esc_html($_POST['email_campaign_id']);
        $client_id = esc_html($_POST['client_id']);
        $client_email = esc_html($_POST['client_email']);
        $page_url = $_POST['page_url'];

        if(!empty($campaign_id) && !empty($client_id)){
            $data = [
                'campaign_id'   =>  $campaign_id,
                'client_id'    =>  $client_id
            ];
    
            $res = $wpdb->insert($wpdb->prefix . "clients_unsubscribe_campaign", $data);
            if ($res) {
                $message = "Client <b>$client_email</b> unsubscribe successfully.";
                $this->setFlashMessage($message, 'success');
            } else {
                $message = "Something went wrong , please try again later";
                $this->setFlashMessage($message, 'danger');
            }
            wp_redirect($page_url);
        }else{
            $this->sendErrorMessage($page_url);
        }
    }

    // Create Cold Calls Log 
    public function create_cold_calls_log()
    {
        global $wpdb;
        $this->verify_nonce_field('create_cold_calls_log');

        // set variables
        $cold_date = esc_html($_POST['cold_date']);
        $description = esc_html($_POST['description']);
        $cold_call_id = esc_html($_POST['cold_call_id']);
        $data = [
            'cold_date'    =>  $cold_date,
            'description'   =>  $description,
            'cold_call_id'   =>  $cold_call_id
        ];

        //    print_r($data);
        $res = $wpdb->insert($wpdb->prefix . "cold_calls_log", $data);
        if ($res) {
            $message = "Cold Calls Log created successfully";
            $this->setFlashMessage($message, 'success');
        } else {
            $message = "Something went wrong , please try again later";
            $this->setFlashMessage($message, 'danger');
        }
        wp_redirect($_POST['page_url']);
    }

    // get email record
    public function getClientEmailRecord(int $client_id, array $columns = [])
    {
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns
            from {$wpdb->prefix}emails
            where id = '$client_id'
        ");
    }

    // View Cold Calls Log 
    public function getColdcallstatus(int $cold_calls_id)
    {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM wp_cold_calls_log
            RIGHT JOIN wp_emails
            ON wp_cold_calls_log.cold_call_id = wp_emails.id
            WHERE wp_cold_calls_log.cold_call_id = '$cold_calls_id' ORDER BY wp_emails.id desc
        "
        );
    }

    // Update status
    public function update_cold_calls_status()
    {
        $this->verify_nonce_field('update_cold_calls_status');
        if (isset($_POST['update_status_id']) && !empty($_POST['update_status_id'])) {
            global $wpdb;
            // set variables
            $cold_status = esc_html($_POST['cold_status']);
            $note = esc_html($_POST['note']);
            $data = [
                'cold_status'   =>  $_POST['cold_status'],
                'note'  =>  $_POST['note'],
            ];
            $status = $wpdb->update($wpdb->prefix . "emails", $data, ['id' => $_POST['update_status_id']]);
            if ($status == 1) {
                $message = "Cold Calls Status updated successfully";
                $this->setFlashMessage($message, 'success');
            } else {
                $message = "Something Went wrong, please try again later";
                $this->setFlashMessage($message, 'warning');
            }
        } else {
            $message = "Something Went wrong, please try again later";
            $this->setFlashMessage($message, 'warning');
        }
        wp_redirect($_POST['page_url']);
    }


    // Display data in Pop-up
    public function view_cold_call_logs()
    {
        global $wpdb;
        $this->verify_nonce_field('view_cold_call_logs');
        $s_id = $_POST['cold_call_id'];
        $deposit_proof =  $wpdb->get_results("SELECT * FROM wp_cold_calls_log
                RIGHT JOIN wp_emails
                ON wp_cold_calls_log.cold_call_id = wp_emails.id
                WHERE wp_cold_calls_log.cold_call_id = '$s_id' ORDER BY wp_emails.id desc
            ");

        if (!empty($deposit_proof)) {
            $output = '<table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>';
            foreach ($deposit_proof as $row) :
                $output .= '<tr>';
                $output .= '<td>' . (!empty($row->cold_date) ? $row->cold_date : '') . '</td>';
                $output .= '<td>' . (!empty($row->description) ? $row->description : '') . '</td>';
                $output .= '</tr>';
            endforeach;
            $output .= '</tbody></table>';
            echo $output;
        } else {

            echo '<h3 class="text-center text-danger">No Cold-Call Logs Found</h3>';
        }
        wp_die();
    }

    // Display data in Pop-up
    public function unsubscribe_client_campaign_list()
    {
        global $wpdb;
        $this->verify_nonce_field('view_unsubscribe_campaign_list');

        $client_id = $_POST['client_id'];
        $unsubscribe_list =  $wpdb->get_results("
                SELECT em.name,em.email,clc.campaign_name FROM {$wpdb->prefix}clients_unsubscribe_campaign as cmp
                JOIN {$wpdb->prefix}emails as em
                ON cmp.client_id = em.id
                JOIN {$wpdb->prefix}clients_campaign as clc
                ON cmp.campaign_id = clc.id
                WHERE cmp.client_id = '$client_id' ORDER BY cmp.created_at desc
            ");
        if (count($unsubscribe_list) > 0) {
            $output = '<table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>';
                $output .= '<tr>';
                $output .= '<td>' . (!empty($unsubscribe_list[0]->name) ?$unsubscribe_list[0]->name : '') . '</td>';
                $output .= '<td>' . (!empty($unsubscribe_list[0]->email) ? $unsubscribe_list[0]->email : '') . '</td>';
                $output .= '</tr>';
            $output .= '</tbody></table>';
            $output .= '<table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Notification Type</th>
                </tr>
            </thead>
            <tbody>';
            foreach ($unsubscribe_list as $row) :
                $output .= '<tr>';
                $output .= '<td>' . (!empty($row->campaign_name) ? $row->campaign_name : '') . '</td>';
                $output .= '</tr>';
            endforeach;
            $output .= '</tbody></table>';
            echo $output;
        } else {
            echo '<h3 class="text-center text-danger">No Unsubscribe list Found.</h3>';
        }
        wp_die();
    }
}
new Emails();
