<?php

use function PHPSTORM_META\type;

class Autobilling extends GamFunctions
{

    public $grouped_invoices = [];
    public $grouped_by_client_data = [];

    function __construct()
    {

        add_action('admin_post_generate_mini_statement', array($this, 'generate_mini_statement'));
        add_action('admin_post_nopriv_generate_mini_statement', array($this, 'generate_mini_statement'));

        add_action('wp_ajax_update_grouped_invoices_email', array($this, 'update_grouped_invoices_email'));
        add_action('wp_ajax_nopriv_update_grouped_invoices_email', array($this, 'update_grouped_invoices_email'));

        add_action('wp_ajax_update_grouped_invoices_address', array($this, 'update_grouped_invoices_address'));
        add_action('wp_ajax_nopriv_update_grouped_invoices_address', array($this, 'update_grouped_invoices_address'));

        add_action('wp_ajax_send_mini_statements_to_clients', array($this, 'send_mini_statements_to_clients'));
        add_action('wp_ajax_nopriv_send_mini_statements_to_clients', array($this, 'send_mini_statements_to_clients'));

        add_action('wp_ajax_move_client_in_collection', array($this, 'move_client_to_collection_folder'));
        add_action('wp_ajax_nopriv_move_client_in_collection', array($this, 'move_client_to_collection_folder'));

        add_action('wp_ajax_client_paid_collection_status', array($this, 'update_collection_client_status'));
        add_action('wp_ajax_nopriv_client_paid_collection_status', array($this, 'update_collection_client_status'));

        add_action('admin_post_update_collection_debt_note', array($this, 'add_debt_note_for_collection_client'));
        add_action('admin_post_nopriv_update_collection_debt_note', array($this, 'add_debt_note_for_collection_client'));

        add_action('admin_post_collection_proof_of_payment', array($this, 'upload_collection_client_proof_of_payment'));
        add_action('admin_post_nopriv_collection_proof_of_payment', array($this, 'upload_collection_client_proof_of_payment'));
    }


    public function get_unpaid_invoices($branch_id = '', $email = true, $past_30_days = false, $type = '', $remove_moved_client = '')
    {
        global $wpdb;

        $conditions = [];

        $conditions[] = " (status IS NULL or status='' or status='not_paid')";
        $conditions[] = "total_amount <> 0";

        if (!current_user_can('other_than_upstate')) {
            $accessible_branches = (new Branches)->partner_accessible_branches(true);
            $accessible_branches = "'" . implode("', '", $accessible_branches) . "'";

            $conditions[] = " branch_id IN ($accessible_branches)";
        }


        if (!empty($branch_id) && $branch_id != "all") {
            $conditions[] = " branch_id = '$branch_id'";
        }

        // if email is true means we need email in bills, else it's for empty email bills 
        if ($email) {
            $conditions[] = " email IS NOT NULL and email!=''";
        } else {
            $conditions[] = " (email IS NULL or email='')";
        }

        if ($past_30_days == true || !empty($type)) {
            $before_30_days = date('Y-m-d', strtotime('-30 days'));
            $conditions[] = " DATE(date)<='$before_30_days'";
        }

        if ($remove_moved_client == true) {
            $conditions[] = "in_collections IS NULL";
        }

        $in_collection = false;
        if (!empty($type)) {
            $in_collection = true;
            $conditions[] = "in_collections IS NOT NULL";
        }

        if (count($conditions) > 0) {
            $conditions = $this->generate_query($conditions);
        } else {
            $conditions = '';
        }

        if (!empty($_GET['search'])) {
            $whereSearch = $this->get_table_coloumn($wpdb->prefix . 'invoices');
            if (!empty($conditions)) {
                $conditions .= " " . $this->create_search_query_string($whereSearch, trim($_GET['search']), 'and');
            } else {
                $conditions = $this->create_search_query_string($whereSearch, trim($_GET['search']));
            }
        }

        if($in_collection){
            $sql_query = "
                select inv.*,cic.collection_agency,cic.paid_status,cic.proof_of_payment
                from {$wpdb->prefix}invoices as inv
                LEFT JOIN {$wpdb->prefix}clients_in_collection as cic
                ON inv.id = cic.invoice_id
                $conditions 
                order by client_name
            ";
        }else{
            $sql_query = "
                select * from 
                {$wpdb->prefix}invoices 
                $conditions 
                order by date
            ";
        }
        $invoices = $wpdb->get_results($sql_query);
        if (is_array((array) $invoices) && count((array)$invoices) > 0) {
            foreach ($invoices as $key => $value) {
                foreach ($invoices as $k => $v) {
                    if (trim($value->address) == trim($v->address)) {
                        $this->grouped_invoices[$key][] = $invoices[$k];
                        unset($invoices[$k]);
                    }
                }
            }
            $this->grouped_invoices = array_values($this->grouped_invoices);
        }

        return $this;
    }

    public function group_unpaid_invoices()
    {
        global $wpdb;

        if (!is_array($this->grouped_invoices) || count($this->grouped_invoices) <= 0) return [];

        foreach ($this->grouped_invoices as $key => $value) {
            $data = [];
            $total_amount = 0;
            $data['id'] = $value[0]->id;
            $data['branch_id'] = $value[0]->branch_id;
            $data['client_address'] = $value[0]->address;
            $data['client_email'] = $value[0]->email;
            $data['client_name'] = $value[0]->client_name;
            $data['phone_no'] = $value[0]->phone_no;
            $data['debt_agency_name'] = $value[0]->collection_agency ?? '';
            $data['paid_status'] = $value[0]->paid_status ?? '';
            $data['payment_proof'] = $value[0]->proof_of_payment ?? '';

            foreach ($value as $k => $v) {
                $total_amount += (float)$v->total_amount;
                $data['date_of_service'][$k]['id'] = $v->id;
                $data['date_of_service'][$k]['invoice_no'] = $v->invoice_no;
                $data['date_of_service'][$k]['date'] = date('m/d/y', strtotime($v->date));
                $data['date_of_service'][$k]['amount'] = (float)$v->total_amount;
                $data['invoice_id'][$k] = $v->id;
            }
            $data['total_amount_owed'] = $total_amount;

            $this->grouped_by_client_data[] = $data;
        }

        return $this->grouped_by_client_data;
    }

    public function createMiniStatementLog(array $data)
    {
        global $wpdb;

        $response =  $wpdb->insert($wpdb->prefix . "mini_statements", $data);
        if (!$response) return false;

        return $wpdb->insert_id;
    }

    public function generateMultipleDbTransactions(array $data)
    {
        $invoice_ids = $data['invoice_ids'];
        if (is_array($invoice_ids) && count($invoice_ids) > 0) {
            $case_sqls = $all_ids = array();
            for ($x = 0; $x < count($invoice_ids); $x++) {
                $all_ids[] = intval($invoice_ids[$x]);
                $case_sqls[] = "WHEN " . intval($invoice_ids[$x]) . " THEN '{$data['value']}'";
            }
            $case_sql = implode(" ", $case_sqls);
            return [$all_ids, $case_sql];
        }
    }

    public function upload_collection_client_proof_of_payment()
    {
        global $wpdb;

        $this->verify_nonce_field('collection_proof_of_payment_nonce');

        $page_url = esc_url_raw($_POST['page_url']);

        if (!isset($_FILES['collection_payment_proof']) || empty($_FILES['collection_payment_proof']['name'])) $this->sendErrorMessage($page_url);

        if (empty($_POST['invoice_all_ids'])) $this->sendErrorMessage($page_url);

        $file = $_FILES['collection_payment_proof'];

        $upload = $this->uploadSingleFile($file);

        if (!$upload) $this->sendErrorMessage($page_url);

        $data = $_POST['invoice_all_ids'];

        // updating record in database
        $invoice_ids = (array) json_decode(stripslashes($data));

        list($all_ids, $case_sql) = $this->generateMultipleDbTransactions([
            'invoice_ids' => $invoice_ids,
            'value' => $upload['url']
        ]);

        $response = (new GamFunctions)->updateMultipleRecordInDbTable([
            'tbl' => 'clients_in_collection',
            'col' => 'proof_of_payment',
            'where_col' => 'invoice_id',
            'case_sql' => $case_sql,
            'all_ids' => $all_ids
        ]);

        if (!$response) $this->sendErrorMessage($page_url);

        $message = "Payment Proof Saved Successfully";
        $this->setFlashMessage($message, 'success');
        wp_redirect($page_url);
    }

    public function move_client_to_collection_folder()
    {
        global $wpdb;

        if (!isset($_POST['data']) || count($_POST['data']) <= 0) $this->response('error');

        $data = $_POST['data'];

        // updating record in database
        $invoice_ids = $data['invoice_id'];

        list($all_ids, $case_sql) = $this->generateMultipleDbTransactions([
            'invoice_ids' => $invoice_ids,
            'value' => 1
        ]);

        $response = (new GamFunctions)->updateMultipleRecordInDbTable([
            'tbl' => 'invoices',
            'col' => 'in_collections',
            'where_col' => 'id',
            'case_sql' => $case_sql,
            'all_ids' => $all_ids
        ]);

        if (!$response) $this->response('error', $response);
        $this->response('success');
    }

    public function update_collection_client_status()
    {
        global $wpdb;

        if (!isset($_POST['data']) || count($_POST['data']) <= 0) $this->response('error');

        // updating record in database
        $invoice_ids = $_POST['data'];
        list($all_ids, $case_sql) = $this->generateMultipleDbTransactions([
            'invoice_ids' => $invoice_ids,
            'value' => 1
        ]);

        $response = (new GamFunctions)->updateMultipleRecordInDbTable([
                'tbl' => 'clients_in_collection',
                'col' => 'paid_status',
                'where_col' => 'invoice_id',
                'case_sql' => $case_sql,
                'all_ids' => $all_ids
        ]);

        if (!$response) $this->response('error', $response);
        $this->response('success', 'Client marked as paid successfully');
    }

    public function add_debt_note_for_collection_client()
    {
        global $wpdb;

        $this->verify_nonce_field('update_collection_debt_note');
        $referer = $_POST['page_url'];
        if (
            empty($_POST['invoice_all_ids']) ||
            empty($_POST['collection_agency_name'])
        ) $this->sendErrorMessage($referer);

        // insert record in database
        $invoice_ids = (array) json_decode(stripslashes($_POST['invoice_all_ids']));
        if (is_array($invoice_ids) && count($invoice_ids) > 0) {
            $case_sqls = array();
            for ($x = 0; $x < count($invoice_ids); $x++) {
                $insert_data = [
                    'invoice_id' => "'" . $invoice_ids[$x] . "'",
                    'collection_agency' => "'" . $this->sanitizeEscape($_POST['collection_agency_name']) . "'"
                ];
                $case_sqls[] = "(" . implode(',', $insert_data) . ")";
            }
            $sql_values = implode(',', $case_sqls);
            $response = (new GamFunctions)->InsertMultipleRecordInDbTable([
                'tbl' => 'clients_in_collection',
                'col' => 'invoice_id,collection_agency',
                'values' => $sql_values
            ]);

            if ($response) {
                // set the message
                $message = "Collection agency added successfully";
                $this->setFlashMessage($message, 'success');
                wp_redirect($referer);
                exit;
            } else {
                $this->setFlashMessage($response, 'error');
                wp_redirect($referer);
                exit;
            }
        }
    }

    public function send_mini_statements_to_clients()
    {
        global $wpdb;

        if (!isset($_POST['data']) || count($_POST['data']) <= 0) $this->response('error');

        $data = $_POST['data'];

        $client_name = $this->sanitizeEscape($data['client_name']);
        $address = $this->sanitizeEscape($data['client_address']);
        $total_amount_owed = $this->sanitizeEscape($data['total_amount_owed']);
        $branch_id = $this->sanitizeEscape($data['branch_id']);
        $client_email = $this->sanitizeEscape($data['client_email']);
        $invoices = $data['date_of_service'];
        $branch_address = $this->get_company_address($branch_id);

        $pdf_files = [];

        $mini_statment_html = $this->miniStatementHtml($client_name, $address, $total_amount_owed, $branch_id, $invoices);

        // save mini statement in database
        list($db_dir_path, $file_path) = (new Mpdf)->saveMiniStatementPdf($mini_statment_html);

        $pdf_files[] = [
            'file'  =>  $file_path,
            'type'  =>  'application/pdf',
            'name'  =>  'Invoices Mini Statement'
        ];

        // saving record in database
        if (is_array($data['invoice_id']) && count($data['invoice_id']) > 0) {

            // create mini statement log
            $mini_statement_log = [
                'invoice_id'    =>    $data['invoice_id'][0],
                'amount'        =>    $total_amount_owed,
                'pdf_path'        =>    $db_dir_path,
                'status'        =>  'email_sent'
            ];
            $this->createMiniStatementLog($mini_statement_log);
        }

        foreach ($invoices as $invoice) {

            $invoice_upload_path = (new Mpdf)->generateInvoicePdf($invoice['id']);

            $pdf_files[] = [
                'file'    =>    $invoice_upload_path,
                'type'    =>    'application/pdf',
                'name'    =>    "Invoice {$invoice['invoice_no']}.pdf"
            ];
        }

        // sending email part 
        $subject = "Payment Reminder";
        $mail_message = "
            <p> Hello <b>$client_name</b> </p>
            <p>Attached is a copy of all open services invoices you currently have with GAM Exterminating. Additionally we have attached a statement of all open invoices on one document for easier viewership. </p>
            <p>Kindly remit full balances owed to $branch_address</p>
            " . $this->getReviewLine($branch_id) . "            
            <p>Thanks</p>
        ";

        $tos = [];

        $tos[] = [
            'email'    =>    $client_email,
            'name'    =>    $client_name
        ];

        $sent = (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $mail_message, $pdf_files, '', 'invoice');

        if ($sent['status'] == "error") {
            if (is_array($data['invoice_id']) && count($data['invoice_id']) > 0) {
                $res = $wpdb->update($wpdb->prefix . "mini_statements", ['status' => 'error_sending_email'], ['invoice_id' => $data['invoice_id'][0]]);
            }
        }

        $this->response('success');
    }

    public function update_grouped_invoices_email()
    {
        global $wpdb;

        $this->verify_nonce_field('update_grouped_invoices_email');

        if (
            empty($_POST['invoice_address']) ||
            empty($_POST['email'])
        ) $this->response('error');

        $invoice_address = $this->sanitizeEscape($_POST['invoice_address']);
        $email = $this->sanitizeEscape($_POST['email']);

        $update_data = ['email' => $email];
        $where_data = ['address' => $invoice_address];
        $result = $wpdb->update($wpdb->prefix . "invoices", $update_data, $where_data);
        if (!$result) $this->response('error');

        $this->response('success', 'email updated for the invoices');
    }

    public function update_grouped_invoices_address()
    {
        global $wpdb;

        $this->verify_nonce_field('update_grouped_invoices_address');

        if (
            empty($_POST['new_address']) ||
            empty($_POST['actual_address'])
        ) $this->response('error');

        $new_address = $this->sanitizeEscape($_POST['new_address']);
        $actual_address = $this->sanitizeEscape($_POST['actual_address']);

        $update_data = ['address' => $new_address];
        $where_data = ['address' => $actual_address];

        $result = $wpdb->update($wpdb->prefix . "invoices", $new_address, $where_data);
        if (!$result) $this->response('error');

        $this->response('success', 'Address updated for the invoices');
    }

    public function generate_mini_statement()
    {
        global $wpdb;

        $this->verify_nonce_field('generate_mini_statement');

        if (
            empty($_POST['name']) ||
            empty($_POST['address']) ||
            empty($_POST['total_amount']) ||
            empty($_POST['branch_id']) ||
            !is_array($_POST['invoice']) || count($_POST['invoice']) <= 0
        ) $this->sendErrorMessage($_POST['_wp_http_referer']);

        $name = $this->sanitizeEscape($_POST['name']);
        $address = $this->sanitizeEscape($_POST['address']);
        $total_amount = $this->sanitizeEscape($_POST['total_amount']);
        $branch_id = $this->sanitizeEscape($_POST['branch_id']);
        $invoices = $_POST['invoice'];

        $upload_dir = wp_upload_dir();

        $mini_statment_html = $this->miniStatementHtml($name, $address, $total_amount, $branch_id, $invoices);

        list($db_dir_path, $file_path) = (new Mpdf)->saveMiniStatementPdf($mini_statment_html);

        // saving mini statement path in db corrosponding to invoice for futher reference 

        if (!empty($_POST['invoice_ids'])) {
            $invoice_ids = explode(',', $_POST['invoice_ids']);
            $data = [
                'invoice_id'    =>    $invoice_ids[0],
                'amount'        =>    $total_amount,
                'pdf_path'        =>    $db_dir_path
            ];
            $this->createMiniStatementLog($data);
        }

        (new Mpdf)->downloadMiniStatement($mini_statment_html);
    }

    public function miniStatementHtml(string $name, string $address, int $total_amount, int $branch_id, $data)
    {

        $date = date('m/d/y');

        $message = (new Emails)->emailTemplateHeader();

        $message .= "
			<p>Request for payment</p>
			<p><b>Name: $name</b></p>
			<p><b>Address: $address</b></p>
			<p><b>Date: $date</b></p>
			<p><b>Total amount owed: \$$total_amount</b></p>
		";

        $message .= "
            <table class='table table-striped table-hover'>
                <thead>
                    <tr>
                        <th>Invoice No.</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>";

        if (is_array($data) && count($data) >= 1) {
            foreach ($data as $key => $value) {

                $message .= "
					<tr>
						<td>{$value['invoice_no']}</td>
						<td>\${$value['amount']}</td>
						<td>".date('m/d/Y',strtotime($value['date']))."</td>
					</tr>
                ";
            }
        }

        $message .= "
                    </tbody>
                </table>";


        if (!empty($branch_id)) {
            $branch_address = $this->get_company_address($branch_id);
        } else {
            $branch_address = $this->getMainAddress();
        }

        $message .= "<p>Please remit payment to $branch_address </p>";
        $message .= "<p style='text-align:right;'>Thank you</p>";

        $message .= "
			</body>
			</html>
		";

        return $message;
    }
}

new Autobilling();
