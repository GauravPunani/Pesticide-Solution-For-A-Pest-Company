<?php

class SpecialMaintenance extends Maintenance
{

    use GamValidation;

    function __construct()
    {

        add_action('admin_post_nopriv_special_maintenance_by_staff', array($this, 'special_maintenance_by_staff'));
        add_action('admin_post_special_maintenance_by_staff', array($this, 'special_maintenance_by_staff'));

        add_action('admin_post_nopriv_update_special_contract', array($this, 'update_special_contract'));
        add_action('admin_post_update_special_contract', array($this, 'update_special_contract'));

        add_action('admin_post_nopriv_download_maintenance_plan_special', array($this, 'download_maintenance_plan_special'));
        add_action('admin_post_download_maintenance_plan_special', array($this, 'download_maintenance_plan_special'));

        add_action('admin_post_nopriv_special_contract', array($this, 'special_contract'));
        add_action('admin_post_special_contract', array($this, 'special_contract'));

        add_action('admin_post_special_contract_client_cc_part', array($this, 'special_contract_client_cc_part'));
        add_action('admin_post_nopriv_special_contract_client_cc_part', array($this, 'special_contract_client_cc_part'));

        add_action('admin_post_nopriv_special_contract_download', array($this, 'download_special_contract'));
        add_action('admin_post_special_contract_download', array($this, 'download_special_contract'));
    }

    public function download_special_contract(){
		global $wpdb;

		$this->verify_nonce_field('special_contract_download');

		$page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['contract_id'])
        ) {
            $this->sendErrorMessage($page_url);
        }

        $contract_id = $this->sanitizeEscape($_POST['contract_id']);
        $downloadContract = $this->special_contract_template($contract_id);
        // load sendgrid php sdk from vendor
		self::loadVendor();

		$mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
		$mpdf->WriteHTML($downloadContract);
		$title = sprintf('special_contract_%s', $contract_id);
		$mpdf->Output("$title.pdf", "D");
		return;
    }

    public function special_contract_client_cc_part()
    {
        global $wpdb;

        $this->verify_nonce_field('special_contract');

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

        $contract = $this->getContractById($contract_id, ['client_name', 'client_email']);
        if (!$contract) $this->rollBackTransaction($page_url);

        // save signature & credit card details 
        list($image_path, $image_file) = $this->save_signature($signimgurl, 'maintenance', $contract->client_name);
        $update_data = [
            'card_details'  =>  json_encode($card_details),
            'signature'     =>  $image_path,
            'form_status'   =>  'form_completed_by_client'
        ];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        // update contract pdf 
        $email_content = $this->special_contract_template($contract_id);
        list($file_path, $pdf_path) = $this->save_pdf($email_content, 'maintenance_special', $contract->client_name);

        $update_data = ['pdf_path' => $pdf_path];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        $response = (new Emails)->emailSpecialContract($contract_id);

        $update_data = ['email_status'  =>  $response ? 1 : 0];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        $this->commitTransaction();

        wp_redirect($this->thankyouPageUrl());
    }

    public function special_maintenance_by_staff()
    {
        global $wpdb;

        $page_url = esc_url_raw($_POST['page_url']);

        $this->beginTransaction();

        if (
            empty($_POST['service_type']) ||
            !isset($_POST['cost']) || !is_numeric($_POST['cost']) ||
            empty($_POST['days']) ||
            empty($_POST['from_date']) ||
            empty($_POST['to_date']) ||
            empty($_POST['client_name']) ||
            empty($_POST['client_address']) ||
            empty($_POST['client_phone']) ||
            empty($_POST['client_email']) ||
            empty($_POST['branch_id'])
        ) $this->sendErrorMessage($page_url);

        $array_fields = ['pests_included'];
        list($response, $message) = $this->arrayValidation($array_fields, $_POST);
        if (!$response) $this->sendErrorMessage($page_url, $message);

        $client_email = sanitize_email($_POST['client_email']);
        $client_name = $this->sanitizeEscape($_POST['client_name']);

        $cost = $this->sanitizeEscape($_POST['cost']);

        // check for code verification if maintenance amount is less than or equal to $59
        if ($cost <= 59) $this->__codeValidation();

        $data = [
            'service_type'          =>      $this->sanitizeEscape($_POST['service_type']),
            'cost'                  =>      $cost,
            'days'                  =>      $this->sanitizeEscape($_POST['days']),
            'from_date'             =>      $this->sanitizeEscape($_POST['from_date']),
            'to_date'               =>      $this->sanitizeEscape($_POST['to_date']),
            'client_name'           =>      $client_name,
            'client_address'        =>      $this->sanitizeEscape($_POST['client_address']),
            'client_phone'          =>      $this->sanitizeEscape($_POST['client_phone']),
            'client_email'          =>      $client_email,
            'branch_id'             =>      $this->sanitizeEscape($_POST['branch_id']),
            'date_created'          =>      date('y-m-d'),
            'form_status'           =>      "form_filled_by_staff",
            'technician_id'         =>      (new Technician_details)->get_technician_id(),
            'pests_included'        =>      implode(',', $_POST['pests_included'])
        ];

        if (!empty($_POST['notes'])) {
            $data['notes'] = $this->sanitizeEscape($_POST['notes'], 'textarea');
        }

        list($contract_id, $message) = $this->createContract($data);
        if (!$contract_id) $this->rollBackTransaction($page_url, $message);

        // create task for office to remind client to sign contract
        $response = (new OfficeTasks)->remindClientToSignContract('special', $contract_id);
        if (!$response) $this->rollBackTransaction($page_url);

        $response = (new Emails)->contractCcEmail($contract_id, 'special');

        $update_data = ['email_status'  =>  $response ? 1 : 0];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        $this->commitTransaction();

        $this->setFlashMessage('An email to proceed with contract has been sent to the client', 'success');

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

    public function update_special_contract()
    {
        global $wpdb;

        $this->verify_nonce_field('update_special_contract');

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['branch_id']) ||
            empty($_POST['service_type']) ||
            !isset($_POST['cost']) || !is_numeric($_POST['cost']) ||
            empty($_POST['days']) ||
            empty($_POST['client_name']) ||
            empty($_POST['client_address']) ||
            empty($_POST['client_phone']) ||
            empty($_POST['client_email']) ||
            empty($_POST['from_date']) ||
            empty($_POST['to_date']) ||
            empty($_POST['contract_id']) ||
            empty($_POST['notes_for_client'])
        ) $this->sendErrorMessage($page_url);

        $data = [
            'branch_id'                =>    $this->sanitizeEscape($_POST['branch_id']),
            'service_type'            =>    $this->sanitizeEscape($_POST['service_type']),
            'cost'                    =>    $this->sanitizeEscape($_POST['cost']),
            'days'                    =>    $this->sanitizeEscape($_POST['days']),
            'client_name'            =>    $this->sanitizeEscape($_POST['client_name']),
            'client_address'        =>    $this->sanitizeEscape($_POST['client_address']),
            'client_phone'            =>    $this->sanitizeEscape($_POST['client_phone']),
            'client_email'            =>    $this->sanitizeEscape($_POST['client_email']),
            'from_date'             =>  $this->sanitizeEscape($_POST['from_date']),
            'to_date'               =>  $this->sanitizeEscape($_POST['to_date']),
            'notes'                 => $this->sanitizeEscape($_POST['notes_for_client'], 'textarea')
        ];

        $contract_id = $this->sanitizeEscape($_POST['contract_id']);
        list($response, $message) = $this->updateContract($contract_id, $data);
        if (!$response) $this->sendErrorMessage($page_url, $message);

        $this->setFlashMessage("Contract has been updated successfully", "success");

        if (isset($_SESSION['special_maintenance_editable'])) unset($_SESSION['special_maintenance_editable']);
        wp_redirect($page_url);
    }

    public function special_contract()
    {
        global $wpdb;

        $this->verify_nonce_field('special_contract');

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['service_type']) ||
            !isset($_POST['cost']) || !is_numeric($_POST['cost']) ||
            empty($_POST['days']) ||
            empty($_POST['client_name']) ||
            empty($_POST['client_address']) ||
            empty($_POST['client_phone']) ||
            empty($_POST['client_email']) ||
            empty($_POST['from_date']) ||
            empty($_POST['to_date']) ||
            empty($_POST['branch_id'])
        ) $this->sendErrorMessage($page_url);

        // pdie($_POST);

        $array_fields = ['pests_included', 'card_details'];
        list($response, $message) = $this->arrayValidation($array_fields, $_POST);
        if (!$response) $this->sendErrorMessage($page_url, $message);

        $service_type = $this->sanitizeEscape($_POST['service_type']);
        $cost = $this->sanitizeEscape($_POST['cost']);
        $days = $this->sanitizeEscape($_POST['days']);
        $client_name = $this->sanitizeEscape($_POST['client_name']);
        $client_address = $this->sanitizeEscape($_POST['client_address']);
        $client_phone = $this->sanitizeEscape($_POST['client_phone']);
        $client_email = $this->sanitizeEscape($_POST['client_email']);
        $from_date = $this->sanitizeEscape($_POST['from_date']);
        $to_date = $this->sanitizeEscape($_POST['to_date']);
        $branch_id = $this->sanitizeEscape($_POST['branch_id']);

        $card_details = $_POST['card_details'];

        $callrail_id = "";
        if (!empty($_POST['callrail_id'])) $callrail_id = $this->sanitizeEscape($_POST['callrail_id']);

        // check for code verification if maintenance amount is less than or equal to $59
        if ($cost <= 59) $this->__codeValidation();

        $branch_slug = (new Branches)->getBranchSlug($branch_id);

        list($signature_img, $image_file) = $this->save_signature($_POST["signimgurl"], 'maintenance', $client_name);

        // save new email in email database 
        $email_data = [
            'branch_id'    =>    $branch_id,
            'email'        =>    $client_email,
            'name'        =>    $client_name,
            'address'    =>    $client_address,
            'phone'        =>    $client_phone,
            'date'        =>    date('Y-m-d'),
        ];
        $email_data['status'] = $callrail_id == "reoccuring_customer" ? "reocurring" : "non_reocurring";
        $response = (new Emails)->save_email($email_data);
        if (!$response) $this->rollBackTransaction($page_url);

        $data = [
            'service_type'        =>    $service_type,
            'cost'                =>    $cost,
            'days'                =>    $days,
            'client_name'        =>    $client_name,
            'client_address'    =>    $client_address,
            'client_phone'        =>    $client_phone,
            'client_email'        =>    $client_email,
            'from_date'            =>    $from_date,
            'to_date'            =>    $to_date,
            'form_status'        =>  "form_completed_by_client",
            'branch_id'            =>  $branch_id,
            'callrail_id'       =>  $callrail_id,
            'card_details'       =>  json_encode($card_details),
            'signature'           =>  $signature_img,
            'technician_id'     => (new Technician_details)->get_technician_id(),
            'pests_included'    =>  implode(',', $_POST['pests_included'])
        ];

        if (!empty($_POST['notes'])) {
            $data['notes'] = $this->sanitizeEscape($_POST['notes'], 'textarea');
        }

        list($contract_id, $message) = $this->createContract($data);
        if (!$contract_id) $this->rollBackTransaction($page_url, $message);

        $email_content = $this->special_contract_template($contract_id);
        list($file_path, $pdf_path) = $this->save_pdf($email_content, 'maintenance_special', $client_name);

        $update_data = ['pdf_path' => $pdf_path];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        $response = (new Emails)->emailSpecialContract($contract_id);

        $update_data = ['email_status'  =>  $response ? 1 : 0];
        list($response, $message) = $this->updateContract($contract_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        $message = "Special Maintenance Contract Submitted Successfully";
        $this->setFlashMessage($message, 'success');

        // if client comes from receipt page , then update that offer is made
        if (isset($_POST['show_receipt']) && !empty($_POST['invoice_id'])) {
            $invoice_id = $this->encrypt_data($_POST['invoice_id'], 'd');
            $response = (new Invoice)->updateInvoice($invoice_id, ['maintenance_offered' => 'offered']);
            if (!$response) $this->rollBackTransaction($page_url);
        }

        $this->commitTransaction();
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

    public function createContract(array $data)
    {
        global $wpdb;

        $response = $wpdb->insert($wpdb->prefix . "special_contract", $data);
        if (!$response) return [false, $wpdb->last_error];

        $contract_id = $wpdb->insert_id;

        $status = $data['form_status'] == "form_completed_by_client" ? 'closed' : 'pending';

        $data =  [
            'address'   =>  $data['client_address'],
            'phone'     =>  $data['client_phone'],
            'email'     =>  $data['client_email'],
            'status'    =>  $status,
            'source'    =>  'special_maintenance',
            'source_id' =>  $contract_id
        ];

        list($response, $message) = (new Leads)->__quoteLeadStatusMiddleware($data);
        if (!$response) return [false, $message];

        return [$contract_id, null];
    }

    public function special_contract_template(int $contract_id)
    {

        $contract = $this->getContractById($contract_id);
        $card_details = json_decode($contract->card_details);
        $upload_dir = wp_upload_dir();

        $branch_name = (new Branches)->getBranchName($contract->branch_id);

        $email_content = (new Emails)->emailTemplateHeader();

        $email_content .= '
                <table>
                    <tr>
                        <th colspan="2" style="text-align:center">
                            SPECIAL MAINTENANCE CONTRACT
                        <th>
                    </tr>';

        $email_content .= "
                    <tr>
                        <th>Service Type</th>
                        <td>$contract->service_type</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>$contract->client_name</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>$contract->client_address</td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td>" . (new Branches)->getBranchName($contract->branch_id) . "</td>
                    </tr>
                    <tr>
                        <th>Phone No.</th>
                        <td>$contract->client_phone</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>$contract->client_email</td>
                    </tr>
                    <tr>
                        <th>Cost</th>
                        <td>\$$contract->cost</td>
                    </tr>
                    <tr>
                        <th>For</th>
                        <td>$contract->days ".(new GamFunctions)->getFormattedServiceDuration($contract->service_type)."</td>
                    </tr>
                    <tr>
                        <th>From Date</th>
                        <td>" . date('d M Y', strtotime($contract->from_date)) . "</td>
                    </tr>
                    <tr>
                        <th>To Date</th>
                        <td>" . date('d M Y', strtotime($contract->to_date)) . "</td>                        
                    </tr>
                    <tr>
                        <th>Notes</th>
                        <td>" . nl2br($contract->notes) . "</td>
                    </tr>
                </table>";

        $email_content .= "
                <table>
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

        $email_content .= $this->includedExludedPests($contract->pests_included);

        // add mail template method here
        $email_content .= $this->mail_template();

        $email_content .= "\n <p style='font-size:12px; font-style:italic;'><img style='width:20px;height:20px;' src='" . $upload_dir['baseurl'] . "/2019/11/checkmark.png' />* I understand I am responsible for the maintenance plan i have selected for the property I have listed, and I am responsible for the full value of this contract. I understand my card will be billed in accordance to the terms of this agreement for the amount stated above.</p>";

        $email_content .= "<div style='float:left;width: 40%;margin: 8% 5% auto;font-size:22px;'><img src='" . $contract->signature . "'/>";

        $email_content .= "
                </body>
            </html>";


        return $email_content;
    }

    public function getSpecialContracts(string $date, array $columns = [])
    {
        global $wpdb;
        $date = date('Y-m-d', strtotime($date));

        $columns = (count($columns) > 0) ? implode(',', $columns) : '*';

        return $wpdb->get_results("
            select $columns
            from {$wpdb->prefix}special_contract
            where DATE(date_created) = '$date'
        ");
    }

    public function getCount(string $from_date, string $to_date, int $employee_id = NULL, bool $tech_owned)
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
            from {$wpdb->prefix}special_contract
            $conditions
        ");
    }

    public function getContractById(int $contract_id, array $columns = [])
    {
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns
            from {$wpdb->prefix}special_contract
            where id = '$contract_id'
        ");
    }

    public function updateContract(int $contract_id, array $data)
    {
        global $wpdb;

        $response = $wpdb->update($wpdb->prefix . "special_contract", $data, ['id' => $contract_id]);
        if ($response === false) return [false, $wpdb->last_error];

        //if status is changed to completed then set lead/quote as closed too
        if (!empty($data['form_status']) && $data['form_status'] == 'form_completed_by_client') {

            $contract = $this->getContractById($contract_id);

            $data =  [
                'address'   =>  $contract->client_address,
                'phone'     =>  $contract->client_phone,
                'email'     =>  $contract->client_email,
                'status'    =>  'closed',
                'source'    =>  'special_maintenance',
                'source_id' =>  $contract_id
            ];

            list($response, $message) = (new Leads)->__quoteLeadStatusMiddleware($data);
            if (!$response) return [false, $message];
        }

        return [true, null];
    }
}

new SpecialMaintenance();
