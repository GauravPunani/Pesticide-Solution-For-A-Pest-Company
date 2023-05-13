<?php

class CommercialMaintenance extends Maintenance
{

    use GamValidation;

    function __construct()
    {

        add_action('admin_post_nopriv_commercial_maintenance_form_by_staff', array($this, 'commercial_maintenance_form_by_staff'));
        add_action('admin_post_commercial_maintenance_form_by_staff', array($this, 'commercial_maintenance_form_by_staff'));

        add_action('admin_post_nopriv_update_commercial_contract', array($this, 'update_commercial_contract'));
        add_action('admin_post_update_commercial_contract', array($this, 'update_commercial_contract'));

        add_action("admin_post_commercial_contract", array($this, "commercial_contract"));
        add_action("admin_post_nopriv_commercial_contract", array($this, "commercial_contract"));

        add_action("admin_post_commercial_contract_client_cc_part", array($this, "commercial_contract_client_cc_part"));
        add_action("admin_post_nopriv_commercial_contract_client_cc_part", array($this, "commercial_contract_client_cc_part"));

        add_action('admin_post_nopriv_commercial_contract_download', array($this, 'download_commercial_contract'));
        add_action('admin_post_commercial_contract_download', array($this, 'download_commercial_contract'));
    }

    public function download_commercial_contract()
    {
        global $wpdb;

        $this->verify_nonce_field('commercial_contract_download');

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['contract_id'])
        ) {
            $this->sendErrorMessage($page_url);
        }

        $contract_id = $this->sanitizeEscape($_POST['contract_id']);
        $downloadContract = $this->commercial_contract_template($contract_id);
        // load sendgrid php sdk from vendor
        self::loadVendor();

        $mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
        $mpdf->WriteHTML($downloadContract);
        $title = sprintf('commercial_contract_%s', $contract_id);
        $mpdf->Output("$title.pdf", "D");
        return;
    }

    public function commercial_contract_client_cc_part()
    {
        global $wpdb;

        $this->verify_nonce_field('commercial_contract');

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['contract_id']) ||
            empty($_POST['signimgurl']) ||
            empty($_POST['card_details']) || count((array)$_POST['card_details']) <= 0
        ) {
            $this->sendErrorMessage($page_url);
        }

        $contract_id = $this->sanitizeEscape($_POST['contract_id']);
        $signimgurl = $_POST['signimgurl'];
        $card_details = $_POST['card_details'];

        $this->beginTransaction();

        $contract = $this->getContractById($contract_id, ['establishement_name', 'client_email']);
        if (!$contract) $this->rollBackTransaction($page_url);

        // save signature & credit card details 
        list($image_path, $image_file) = $this->save_signature($signimgurl, 'maintenance', $contract->establishement_name);
        $update_data = [
            'card_details'  =>  json_encode($card_details),
            'signature'     =>  $image_path,
            'form_status'   =>  'form_completed_by_client'
        ];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        // update contract pdf 
        $email_content = $this->commercial_contract_template($contract_id);
        list($file_path, $pdf_path) = $this->save_pdf($email_content, 'maintenance_commercial', $contract->establishement_name);

        // update pdf path 
        $update_data = ['pdf_path' => $pdf_path];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        // email commercial contract and upate email sent status
        $response = (new Emails)->emailCommercialContract($contract_id);
        if (!$response) $this->rollBackTransaction($page_url);

        $update_data = ['email_status' => $response ? 1 : 0];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        $this->commitTransaction();

        wp_redirect($this->thankyouPageUrl());
    }

    public function commercial_maintenance_form_by_staff()
    {
        global $wpdb;

        $this->verify_nonce_field('commercial_contract');

        $page_url = esc_url_raw($_POST['page_url']);

        $required_fields = [
            'establishement_name',
            'person_in_charge',
            'client_address',
            'branch_id',
            'establishment_phoneno',
            'res_person_in_charge_phone_no',
            'client_email',
            'frequency_of_visit',
            'frequency_per',
            'prefered_days',
            'prefered_time',
            'contract_start_date',
            'contract_end_date',
        ];

        list($response, $message) = $this->requiredValidation($required_fields, $_POST);
        if (!$response) $this->response('error', $message);

        $array_fields = ['pests_included'];
        list($response, $message) = $this->arrayValidation($array_fields, $_POST);
        if (!$response) $this->response('error', $message);

        $numeric_fields = ['cost_per_visit'];
        list($response, $message) = $this->isNumericValidation($numeric_fields, $_POST);
        if (!$response) $this->response('error', $message);

        $this->beginTransaction();

        $client_email = sanitize_email($_POST['client_email']);
        $establishement_name = $this->sanitizeEscape($_POST['establishement_name']);

        $cost_per_visit = $this->sanitizeEscape($_POST['cost_per_visit']);

        // check for code verification if maintenance amount is less than or equal to $59
        if ($cost_per_visit <= 59) $this->__codeValidation();

        $rodent_included = 0;
        if (isset($_POST['rodent_included']) && $_POST['rodent_included'] == "yes") $rodent_included = 1;

        $data = [
            'establishement_name'                =>  $establishement_name,
            'person_in_charge'                    =>  $this->sanitizeEscape($_POST['person_in_charge']),
            'client_address'                    =>  $this->sanitizeEscape($_POST['client_address']),
            'branch_id'                            =>  $this->sanitizeEscape($_POST['branch_id']),
            'establishment_phoneno'                =>  $this->sanitizeEscape($_POST['establishment_phoneno']),
            'res_person_in_charge_phone_no'        =>  $this->sanitizeEscape($_POST['res_person_in_charge_phone_no']),
            'client_email'                        =>  $client_email,
            'cost_per_visit'                    =>  $cost_per_visit,
            'frequency_of_visit'                =>  $this->sanitizeEscape($_POST['frequency_of_visit']),
            'frequency_per'                        =>  $this->sanitizeEscape($_POST['frequency_per']),
            'prefered_days'                        =>  $this->sanitizeEscape($_POST['prefered_days']),
            'prefered_time'                        =>  $this->sanitizeEscape($_POST['prefered_time']),
            'contract_start_date'                =>  $this->sanitizeEscape($_POST['contract_start_date']),
            'contract_end_date'                    =>  $this->sanitizeEscape($_POST['contract_end_date']),
            'form_status'                        =>  'form_filled_by_staff',
            'rodent_included'                   =>  $rodent_included,
            'technician_id'                     => (new Technician_details)->get_technician_id(),
            'pests_included'                    =>  implode(',', $_POST['pests_included'])
        ];

        if (!empty($_POST['client_notes'])) {
            $data['client_notes'] = $this->sanitizeEscape($_POST['client_notes'], 'textarea');
        }

        list($contract_id, $message) = $this->createContract($data);
        if (!$contract_id) $this->rollBackTransaction($page_url, $message);

        // create task for office to remind client to sign contract
        $response = (new OfficeTasks)->remindClientToSignContract('commercial', $contract_id);
        if (!$response) $this->rollBackTransaction($page_url);

        // sent cc and sign email and update email status
        $response = (new Emails)->contractCcEmail($contract_id, 'commercial');
        if (!$response) $this->rollBackTransaction($page_url);

        $update_data = ['email_status' => $response ? 1 : 0];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        $this->commitTransaction();

        if ($response)
            $message = 'An email to proceed with contract has been sent to the client';
        else
            $message = 'Form submitted successfully but there was error sending email to client';

        $this->setFlashMessage($message, 'success');
        $redirect_url = '';

        // if it was a part of invoice flow then redirect to invoice page on invoice flow 
        if (
            isset($_POST['invoice_step']) &&
            $_POST['invoice_step'] == "maintenance_plan" &&
            @$_SESSION['invoice_step'] == "maintenance_plan"
        ) {

            (new InvoiceFLow)->callNextPageInFlow();
        } else {
            // set redirect url 
            $redirect_url = $page_url;
        }

        wp_redirect($redirect_url);
    }

    public function update_commercial_contract()
    {
        global $wpdb;

        $this->verify_nonce_field('update_commercial_contract');

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['branch_id']) ||
            empty($_POST['establishement_name']) ||
            empty($_POST['person_in_charge']) ||
            empty($_POST['client_address']) ||
            empty($_POST['establishment_phoneno']) ||
            empty($_POST['res_person_in_charge_phone_no']) ||
            empty($_POST['client_email']) ||
            !isset($_POST['cost_per_visit']) || !is_numeric($_POST['cost_per_visit']) ||
            empty($_POST['frequency_of_visit']) ||
            empty($_POST['frequency_per']) ||
            empty($_POST['prefered_time']) ||
            empty($_POST['contract_start_date']) ||
            empty($_POST['contract_end_date']) ||
            empty($_POST['contract_id']) ||
            empty($_POST['notes_for_client'])
        ) $this->sendErrorMessage($page_url);

        $data = [
            'branch_id'                            =>    $this->sanitizeEscape($_POST['branch_id']),
            'establishement_name'                =>    $this->sanitizeEscape($_POST['establishement_name']),
            'person_in_charge'                    =>    $this->sanitizeEscape($_POST['person_in_charge']),
            'client_address'                    =>    $this->sanitizeEscape($_POST['client_address']),
            'establishment_phoneno'                =>    $this->sanitizeEscape($_POST['establishment_phoneno']),
            'res_person_in_charge_phone_no'        =>    $this->sanitizeEscape($_POST['res_person_in_charge_phone_no']),
            'client_email'                        =>    sanitize_email($_POST['client_email']),
            'cost_per_visit'                    =>    $this->sanitizeEscape($_POST['cost_per_visit']),
            'frequency_of_visit'                =>    $this->sanitizeEscape($_POST['frequency_of_visit']),
            'frequency_per'                        =>    $this->sanitizeEscape($_POST['frequency_per']),
            'prefered_time'                        =>    $this->sanitizeEscape($_POST['prefered_time']),
            'contract_start_date'                =>    $this->sanitizeEscape($_POST['contract_start_date']),
            'contract_end_date'                    =>    $this->sanitizeEscape($_POST['contract_end_date']),
            'client_notes'                      => $this->sanitizeEscape($_POST['notes_for_client'], 'textarea')
        ];

        $contract_id = $this->sanitizeEscape($_POST['contract_id']);
        list($response, $message) = $this->updateContract($contract_id, $data);
        if ($response === false) $this->sendErrorMessage($page_url, $message);

        $message = 'Contract has been updated successfully';
        $this->setFlashMessage($message, 'success');

        if (isset($_SESSION['commercial_maintenance_editable'])) unset($_SESSION['commercial_maintenance_editable']);

        wp_redirect($page_url);
    }

    public function commercial_contract()
    {
        global $wpdb;

        $this->verify_nonce_field('commercial_contract');

        $page_url = esc_url_raw($_POST['page_url']);

        $this->beginTransaction();

        $required_fields = [
            'establishement_name',
            'person_in_charge',
            'client_address',
            'establishment_phoneno',
            'res_person_in_charge_phone_no',
            'client_email',
            'frequency_of_visit',
            'frequency_per',
            'prefered_days',
            'prefered_time',
            'contract_start_date',
            'contract_end_date',
            'branch_id',
        ];

        list($response, $message) = $this->requiredValidation($required_fields, $_POST);
        if (!$response) $this->response('error', $message);

        $array_fields = ['pests_included', 'card_details'];
        list($response, $message) = $this->arrayValidation($array_fields, $_POST);
        if (!$response) $this->response('error', $message);

        $numeric_fields = ['cost_per_visit'];
        list($response, $message) = $this->isNumericValidation($numeric_fields, $_POST);
        if (!$response) $this->response('error', $message);

        $establishement_name = $this->sanitizeEscape($_POST['establishement_name']);
        $person_in_charge = $this->sanitizeEscape($_POST['person_in_charge']);
        $client_address = $this->sanitizeEscape($_POST['client_address']);
        $establishment_phoneno = $this->sanitizeEscape($_POST['establishment_phoneno']);
        $res_person_in_charge_phone_no = $this->sanitizeEscape($_POST['res_person_in_charge_phone_no']);
        $client_email = $this->sanitizeEscape($_POST['client_email']);
        $cost_per_visit = $this->sanitizeEscape($_POST['cost_per_visit']);
        $frequency_of_visit = $this->sanitizeEscape($_POST['frequency_of_visit']);
        $frequency_per = $this->sanitizeEscape($_POST['frequency_per']);
        $prefered_days = $this->sanitizeEscape($_POST['prefered_days']);
        $prefered_time = $this->sanitizeEscape($_POST['prefered_time']);
        $contract_start_date = $this->sanitizeEscape($_POST['contract_start_date']);
        $contract_end_date = $this->sanitizeEscape($_POST['contract_end_date']);

        $card_details = $_POST['card_details'];

        // check for code verification if maintenance amount is less than or equal to $59
        if ($cost_per_visit <= 59) $this->__codeValidation();

        $branch_id = $this->sanitizeEscape($_POST['branch_id']);
        $branch_slug = (new Branches)->getBranchSlug($branch_id);

        $rodent_included = 0;
        if (isset($_POST['rodent_included']) && $_POST['rodent_included'] == "yes") $rodent_included = 1;

        list($signature_img, $image_file) = $this->save_signature($_POST["signimgurl"], 'maintenance', $person_in_charge);

        $data = [
            'establishement_name'                =>  $establishement_name,
            'person_in_charge'                    =>  $person_in_charge,
            'client_address'                    =>  $client_address,
            'establishment_phoneno'                =>  $establishment_phoneno,
            'res_person_in_charge_phone_no'        =>  $res_person_in_charge_phone_no,
            'client_email'                        =>  $client_email,
            'cost_per_visit'                    =>  $cost_per_visit,
            'frequency_of_visit'                =>  $frequency_of_visit,
            'frequency_per'                        =>  $frequency_per,
            'prefered_days'                        =>  $prefered_days,
            'prefered_time'                        =>  $prefered_time,
            'contract_start_date'                =>  $contract_start_date,
            'contract_end_date'                    =>  $contract_end_date,
            'form_status'                        =>  "form_completed_by_client",
            'card_details'                       =>  json_encode($card_details),
            'signature'                           =>  $signature_img,
            'branch_id'                            =>  $branch_id,
            'rodent_included'                   =>  $rodent_included,
            'technician_id'                     => (new Technician_details)->get_technician_id(),
            'pests_included'                    =>  implode(',', $_POST['pests_included'])
        ];

        if (!empty($_POST['callrail_id'])) $data['callrail_id'] = $this->sanitizeEscape($_POST['callrail_id']);
        if (!empty($_POST['client_notes'])) $data['client_notes'] = $this->sanitizeEscape($_POST['client_notes'], 'textarea');

        list($contract_id, $message) = $this->createContract($data);
        if (!$contract_id) $this->rollBackTransaction($page_url, $message);

        $message = $this->commercial_contract_template($contract_id);
        list($file_path, $pdf_path) = $this->save_pdf($message, 'maintenance_commercial', $establishement_name);

        $update_data = ['pdf_path' => $pdf_path];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        // save new email in email database 
        $email_data = [
            'branch_id'    =>    $branch_id,
            'email'        =>    $establishement_name,
            'name'        =>    $establishement_name . " - " . $person_in_charge,
            'address'    =>    $client_address,
            'phone'        =>    $res_person_in_charge_phone_no,
            'date'        =>    date('Y-m-d'),
        ];

        $email_data['status'] = (isset($_POST['callrail_id']) && $_POST['callrail_id']) == "reoccuring_customer" ? "reocurring" : "non_reocurring";
        $response = (new Emails)->save_email($email_data);
        if (!$response) $this->rollBackTransaction($page_url, "Unable to save client details in system");

        // email commercial contract and upate email sent status
        $response = (new Emails)->emailCommercialContract($contract_id);
        $update_data = ['email_status' => $response ? 1 : 0];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        // if client comes from receipt page , then update that offer is made
        if (isset($_POST['show_receipt']) && !empty($_POST['invoice_id'])) {
            $invoice_id = $this->encrypt_data($_POST['invoice_id'], 'd');
            $response = (new Invoice)->updateInvoice($invoice_id, ['maintenance_offered' => 'offered']);
            if (!$response) $this->rollBackTransaction($page_url, "Unable to link contract id with invoice");
        }

        $this->commitTransaction();

        $message = "Commercial Contract data has been submitted, You'll recieve contract details on your email.";
        $this->setFlashMessage($message, 'success');

        // if it was a part of invoice flow then redirect to invoice page on invoice flow 
        $redirect_url = '';

        if (
            isset($_POST['invoice_step']) &&
            $_POST['invoice_step'] == "maintenance_plan" &&
            @$_SESSION['invoice_step'] == "maintenance_plan"
        ) {

            (new InvoiceFLow)->callNextPageInFlow();
        } else {
            // set redirect url 
            $redirect_url = $page_url;
        }

        wp_redirect($redirect_url);
    }

    public function createContract(array $data)
    {
        global $wpdb;

        $response = $wpdb->insert($wpdb->prefix . "commercial_maintenance", $data);
        if (!$response) return [false, $wpdb->last_error];

        $contract_id = $wpdb->insert_id;

        $status = $data['form_status'] == "form_completed_by_client" ? 'closed' : 'pending';

        $data =  [
            'address'   =>  $data['client_address'],
            'phone'     =>  $data['establishment_phoneno'],
            'email'     =>  $data['client_email'],
            'status'    =>  $status,
            'source'    =>  'commercial_maintenance',
            'source_id' =>  $contract_id
        ];

        list($response, $message) = (new Leads)->__quoteLeadStatusMiddleware($data);
        if (!$response) return [false, $message];

        return [$contract_id, null];
    }

    public function commercial_contract_template(int $contract_id)
    {

        $contract = $this->getContractById($contract_id);
        $card_details = json_decode($contract->card_details);
        $upload_dir = wp_upload_dir();

        $branch_name = (new Branches)->getBranchName($contract->branch_id);

        $rodent_included = !empty($contract->rodent_included) ? true : false;

        $message = (new Emails)->emailTemplateHeader();

        $message .= "
            <table>
                <tbody>
                    <tr>
                        <th colspan='2' style='text-align:center'>
                            COMMERCIAL MAINTENANCE CONTRACT
                        </th>
                    </tr>
        ";

        $message .= "<tr>
                        <th>Establishment NAME</th>
                        <td>$contract->establishement_name</td>
                    </tr>";

        $message .= "<tr>
                        <th>Responsible person in charge name</th>
                        <td>$contract->person_in_charge</td>
                    </tr>";

        $message .= "<tr>
                        <th>Location</th>
                        <td>$branch_name</td>
                    </tr>";

        $message .= "<tr>
                        <th>Address</th>
                        <td>$contract->client_address</td>
                    </tr>";

        $message .= "<tr>
                        <th>Establishment phone number</th>
                        <td>$contract->establishment_phoneno</td>
                    </tr>";

        $message .= "<tr>
                        <th>Responsible person in charge phone number</th>
                        <td>$contract->res_person_in_charge_phone_no</td>
                    </tr>";

        $message .= "<tr>
                        <th>Email</th>
                        <td>$contract->client_email</td>
                    </tr>";

        $message .= "<tr>
                        <th>Cost per visit</th>
                        <td>\$$contract->cost_per_visit</td>
                    </tr>";

        $message .= "<tr>
                        <th>Frequency of visit</th>
                        <td>$contract->frequency_of_visit</td>
                    </tr>";

        $message .= "<tr>
                        <th>Per</th>
                        <td>$contract->frequency_per</td>
                    </tr>";

        $message .= "<tr>
                        <th>Prefered Day & Time</th>
                        <td>".sprintf('%d day & %s',$contract->prefered_days, $contract->prefered_time)."</td>
                    </tr>";
        $message .= "<tr>
                        <th>Notes</th>
                        <td>" . nl2br($contract->client_notes) . "</td>
                    </tr>";

        $message .= "<tr>
                        <th>Contract Start Date</th>
                        <td>" . date('d M Y', strtotime($contract->contract_start_date)) . "</td>
                    </tr>";

        $message .= "<tr>
                        <th>Contract End Date</th>
                        <td>" . date('d M Y', strtotime($contract->contract_end_date)) . "</td>
                    </tr>";

        $message .= "
                </tbody>
            </table>
        ";

        $message .= "<table>
                        <tr>
                            <th>Credit Card No</th>
                            <th>Expiration</th>
                            <th>Security Code</th>
                        </tr>
                        <tr>
                            <td>$card_details->creditcardnumber</td>
                            <td>$card_details->cc_month / $card_details->cc_year</td>
                            <td>$card_details->cccode</td>
                        </tr>
                    </table>";

        // get the included / excluded pests html
        $message .= $this->includedExludedPests($contract->pests_included);

        $message .= $this->mail_template($rodent_included);

        $message .= "<div style='float:left;width: 40%;margin: 8% 5% auto;font-size:22px;'>$contract->person_in_charge<br/><img src='" . $contract->signature . "'/></div>";

        $message .= "</body>";
        $message .= "</html>";

        return $message;
    }

    public function getCommercialContracts(string $date, array $columns = [])
    {
        global $wpdb;
        $date = date('Y-m-d', strtotime($date));

        $columns = (count($columns) > 0) ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}commercial_maintenance
            where DATE(date_created) = '$date'
        ");
    }

    public function getCount(string $from_date, string $to_date, int $employee_id = null, bool $tech_owned)
    {
        global $wpdb;

        $conditions = [];

        $from_date = date('Y-m-d', strtotime($from_date));
        $to_date = date('Y-m-d', strtotime($to_date));

        $conditions[] = " DATE(date_created) >= '$from_date' ";
        $conditions[] = " DATE(date_created) <= '$to_date' ";

        if ($tech_owned) {
            $conditions[] = " credit = 'technician' ";
        }

        if (!is_null($employee_id)) {

            $technician_id = (new Employee\Employee)->getReferenceIdByEmployeeId($employee_id);
            if (!$technician_id) throw new Exception('Employee ref id not found');

            $conditions[] = " technician_id = '$technician_id'";
        }

        if (!is_null($technician_id)) $conditions[] = " technician_id = '$technician_id'";

        $conditions = count($conditions) > 0 ? $this->generate_query($conditions) : '';

        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}commercial_maintenance
            $conditions
        ");
    }

    public function getContractById(int $contract_id, array $columns = [])
    {
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns
            from {$wpdb->prefix}commercial_maintenance
            where id = '$contract_id'
        ");
    }

    public function updateContract(int $contract_id, array $data)
    {
        global $wpdb;

        $response = $wpdb->update($wpdb->prefix . "commercial_maintenance", $data, ['id' => $contract_id]);
        if ($response === false) return [false, $wpdb->last_error];

        //if status is changed to completed then set lead/quote as closed too
        if (!empty($data['form_status']) && $data['form_status'] == 'form_completed_by_client') {

            $contract = $this->getContractById($contract_id);

            $data =  [
                'address'   =>  $contract->client_address,
                'phone'     =>  $contract->establishment_phoneno,
                'email'     =>  $contract->client_email,
                'status'    =>  'closed',
                'source'    =>  'commercial_maintenance',
                'source_id' =>  $contract_id
            ];

            list($response, $message) = (new Leads)->__quoteLeadStatusMiddleware($data);
            if (!$response) return [false, $message];
        }

        return [true, null];
    }
}

new CommercialMaintenance();
