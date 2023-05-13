<?php

class AnimalCageTracker extends GamFunctions
{

    function __construct()
    {
        $this->debug = constant("WP_DEBUG");

        add_action('admin_post_create_cage_record', array($this, "create_cage_record"));
        add_action('admin_post_nopriv_create_cage_record', array($this, "create_cage_record"));

        add_action('admin_post_skip_cage_form', array($this, "skip_cage_form"));
        add_action('admin_post_nopriv_skip_cage_form', array($this, "skip_cage_form"));

        add_action('admin_post_extend_pickup_date', array($this, "extend_pickup_date"));
        add_action('admin_post_nopriv_extend_pickup_date', array($this, "extend_pickup_date"));

        add_action('wp_ajax_act_mark_everything_retrieved', array($this, "act_mark_everything_retrieved"));
        add_action('wp_ajax_nopriv_act_mark_everything_retrieved', array($this, "act_mark_everything_retrieved"));

        add_action('admin_post_act_edit_address_record', array($this, "act_edit_address_record"));
        add_action('admin_post_nopriv_act_edit_address_record', array($this, "act_edit_address_record"));

        add_action('wp_ajax_notify_client_return_cage', array($this, 'send_bulk_notification'));
        add_action('wp_ajax_nopriv_notify_client_return_cage', array($this, 'send_bulk_notification'));

        add_action('wp_ajax_view_contact_detail', array($this, "view_contact_detail"));
        add_action('wp_ajax_nopriv_view_contact_detail', array($this, "view_contact_detail"));
    }

    public function send_bulk_notification()
    {
        global $wpdb;
        $this->verify_nonce_field('client_bulk_notify');

        $selected_client_list = $_POST['selected_clients'];

        $address = [];
        if (is_array($selected_client_list) && count($selected_client_list) > 0) {
            for ($x = 0; $x < count($selected_client_list); $x++) {
                $address[] = $selected_client_list[$x];
            }
            $where = ' WHERE ca.id IN(' . implode(',', $address) . ')';
            $clients = $this->getCageInstallClientData($where);
            if (count($clients) > 0) {
                for ($x = 0; $x < count($clients); $x++) {
                    $data = array(
                        'id' => $clients[$x]->id,
                        'branch_id' => $clients[$x]->branch_id,
                        'email' => $clients[$x]->email,
                        'name' =>  $clients[$x]->name
                    );
                    $mail_result = (new Emails)->emailClientCageReturn((object) $data);
                }
            }
            if ($mail_result) {
                $this->response('success', 'Email sent succesfully');
            } else {
                $this->response('error', 'something went wrong');
            }
        }
    }

    public function getCageInstallClientData(string $where = '')
    {
        global $wpdb;
        return $wpdb->get_results("
            select ca.*,inv.client_name,inv.email,inv.phone_no
            from {$wpdb->prefix}cage_address as ca
            JOIN {$wpdb->prefix}invoices as inv 
            on ca.invoice_id = inv.id
            {$where}
        ");
    }

    public function act_edit_address_record()
    {
        global $wpdb;

        $this->verify_nonce_field('act_edit_address_record');

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['address_record_id']) ||
            !isset($_POST['racoon_cages']) || !is_numeric($_POST['racoon_cages']) ||
            !isset($_POST['squirrel_cages']) || !is_numeric($_POST['squirrel_cages']) ||
            empty($_POST['notes'])
        ) $this->sendErrorMessage($page_url);

        $address_data_id = $this->sanitizeEscape($_POST['address_record_id']);
        $racoon_cages = $this->sanitizeEscape($_POST['racoon_cages']);
        $squirrel_cages = $this->sanitizeEscape($_POST['squirrel_cages']);
        $notes = $this->sanitizeEscape($_POST['notes'], 'textarea');

        $update_data = [
            'racoon_cages'      =>  $racoon_cages,
            'squirrel_cages'    =>  $squirrel_cages,
            'notes'             =>  $notes,
        ];

        $this->beginTransaction();

        list($response, $message) = $this->updateAddressDataRecord($address_data_id, $update_data);
        if (!$response) $this->rollBackTransaction($page_url, $message);

        $this->commitTransaction();

        $message = "Address record data updated successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function updateAddressDataRecord(int $address_data_id, array $data)
    {
        global $wpdb;


        // if all cages are set to zero, then mark the cage as retrieved as well
        if (
            isset($data['racoon_cages']) &&
            $data['racoon_cages'] == 0 &&
            isset($data['squirrel_cages']) &&
            $data['squirrel_cages'] == 0 &&
            $this->isLastCageRecord($address_data_id) // check if the record is the latest record then set the retrieved status as well
        ) {
            $addres_record = $this->getCageDataRecordById($address_data_id);

            $update_data = ['retrieved' =>  1];
            $response = $this->updateAddressRecord($addres_record->address_id, $update_data);
            if (!$response) return [false, 'Unable to udpate reterived status for the address'];
        }

        $response = $wpdb->update($wpdb->prefix . "cage_data", $data, ['id' => $address_data_id]);
        return $response === false ? [false, $wpdb->last_error] : [true, null];
    }

    public function isLastCageRecord(int $record_id)
    {

        $record = $this->getCageDataRecordById($record_id);
        if (!$record || empty($record->address_id)) return false;

        $latest_record = $this->getLatestCageRecord($record->address_id);
        if ($record_id != $latest_record->id) return false;

        return true;
    }

    public function getLatestCageRecord(int $address_id)
    {
        global $wpdb;
        return $wpdb->get_row("select * from {$wpdb->prefix}cage_data where address_id = '$address_id' order by created_at desc");
    }

    public function getCageDataRecordById(int $record_id)
    {
        global $wpdb;
        return $wpdb->get_row("select * from {$wpdb->prefix}cage_data where id = '$record_id'");
    }

    public function act_mark_everything_retrieved()
    {

        $this->verify_nonce_field('act_mark_everything_retrieved');

        if (empty($_POST['address_id'])) $this->response('error');

        $address_id = $this->sanitizeEscape($_POST['address_id']);

        $this->beginTransaction();

        // create new record marking all cages as reterived
        $data = [
            'address_id'        =>  $address_id,
            'racoon_cages'      =>  0,
            'squirrel_cages'    =>  0,
            'notes'             =>  "*Marked as reterived by office manually",
        ];
        $response = $this->createSingleCageRecord($data);
        if (!$response) $this->rollbackResponse('error');

        // set address record as reterived in address table
        $update_data = ['retrieved' =>  1];
        $response = $this->updateAddressRecord($address_id, $update_data);
        if (!$response) $this->rollbackResponse('error');

        $this->commitTransaction();

        $this->response('success');
    }

    public function extend_pickup_date()
    {
        global $wpdb;

        $this->verify_nonce_field('extend_pickup_date');

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['pickup_date']) ||
            empty($_POST['address_id'])
        ) $this->sendErrorMessage($page_url, "Please fill all the required fields");

        $pickup_date = $this->sanitizeEscape($_POST['pickup_date']);
        $address_id = $this->sanitizeEscape($_POST['address_id']);

        // first check if pickup date is not less then current pickup date
        $address_record = $this->getAddressRecord($address_id, ['pickup_date']);
        if (!$address_record) $this->sendErrorMessage($page_url, $this->debug ? $wpdb->last_error : '');

        if (strtotime($address_record->pickup_date) > strtotime($pickup_date))
            $this->sendErrorMessage($page_url, "New pickup date can not be less than old pickup date");

        $update_data = ['pickup_date' => $pickup_date];
        $response = $this->updateAddressRecord($address_id, $update_data);
        if (!$response) $this->sendErrorMessage($page_url, $this->debug ? $wpdb->last_error : "Unable to update pickup date");

        $message = "Pickup date updated successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function skip_cage_form()
    {

        $this->verify_nonce_field('create_cage_record');

        $page_url  = esc_url_raw($_POST['page_url']);

        // allow to skip page only if there are no cages on site
        $address = (new InvoiceFlow)->getClientAddress();
        if ($this->isCageOnAddress($address)) $this->sendErrorMessage($page_url, "You can't skip animal cage form page as there are cages on client address");

        (new InvoiceFlow)->callNextPageInFlow();
    }

    public function create_cage_record()
    {

        $this->verify_nonce_field('create_cage_record');

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            !isset($_POST['racoon_cages']) || !is_numeric($_POST['racoon_cages']) ||
            !isset($_POST['squirrel_cages']) || !is_numeric($_POST['squirrel_cages']) ||
            empty($_POST['notes'])
        ) $this->sendErrorMessage($page_url, "Please fill all the required fields");

        $technician_id = (new Technician_details)->get_technician_id();
        $branch_id = (new Technician_details)->getTechnicianBranchId($technician_id);

        $data = [
            'racoon_cages'      =>  $this->sanitizeEscape($_POST['racoon_cages']),
            'squirrel_cages'    =>  $this->sanitizeEscape($_POST['squirrel_cages']),
            'notes'             =>  $this->sanitizeEscape($_POST['notes'], 'textarea'),
            'name'              => (new InvoiceFlow)->getClientName(),
            'address'           => (new InvoiceFlow)->getClientAddress(),
            'technician_id'     =>  $technician_id,
            'branch_id'         =>  $branch_id
        ];

        $this->beginTransaction();

        list($response, $message) = $this->addCageRecord($data);
        if (!$response) $this->sendErrorMessage($page_url, $message);

        $this->commitTransaction();

        $response = "Animal cage record updated successfully";
        $this->setFlashMessage($response, "success");

        (new InvoiceFlow)->callNextPageInFlow();
    }

    public function getAllRecordsForAddress(string $address)
    {
        global $wpdb;

        return $wpdb->get_results("
            select CD.*
            from {$wpdb->prefix}cage_data CD
            left join {$wpdb->prefix}cage_address CA
            on CD.address_id = CA.id
            where CA.address = '$address'
            order by created_at desc
        ");
    }

    public function getAddressRecord(int $address_id, array $columns = [])
    {
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        return $wpdb->get_row("
            select $columns
            from {$wpdb->prefix}cage_address
            where id = '$address_id'
        ");
    }

    public function getCagesOnAddress(string $address)
    {
        global $wpdb;

        return $wpdb->get_row("
            select CD.*
            from {$wpdb->prefix}cage_data CD
            left join {$wpdb->prefix}cage_address CA
            on CD.address_id = CA.id
            where CA.address = '$address'
            order by created_at desc
        ");
    }

    public function isCageOnAddress(string $address)
    {
        global $wpdb;

        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}cage_address
            where address = '$address'
            and retrieved = 0
        ");
    }

    public function isAddressExist(string $address)
    {
        global $wpdb;

        return $wpdb->get_var("
            select id
            from {$wpdb->prefix}cage_address
            where address = '$address'
        ");
    }

    public function createAddressRecord(string $address, string $name, int $branch_id)
    {
        global $wpdb;

        $data = [
            'name'          =>  $name,
            'address'       =>  $address,
            'retrieved'     =>  0,
            'pickup_date'   =>  date('Y-m-d', strtotime('+31 days')),
            'branch_id'     =>  $branch_id
        ];

        $response = $wpdb->insert($wpdb->prefix . "cage_address", $data);
        return $response ? $wpdb->insert_id : false;
    }

    public function updateAddressRecord(int $address_id, array $data)
    {
        global $wpdb;
        $response = $wpdb->update($wpdb->prefix . "cage_address", $data, ['id' => $address_id]);
        return $response === false ? false : true;
    }

    public function addCageRecord(array $data)
    {
        global $wpdb;

        if (empty($data['address'])) return [false, 'Please provide the address'];
        if (empty($data['name'])) return [false, 'Please provide the name'];
        if (!isset($data['racoon_cages']) || !is_numeric($_POST['racoon_cages'])) return [false, 'Racoon cages quantity is required'];
        if (!isset($data['squirrel_cages']) || !is_numeric($_POST['squirrel_cages'])) return [false, 'Squirrel cages quantity is required'];
        if (empty($data['notes'])) return [false, 'Notes are required'];
        if (empty($data['technician_id'])) return [false, 'Technician id is required'];
        if (empty($data['branch_id'])) return [false, 'Branch id is required'];

        if (!$data['address_id'] = $this->isAddressExist($data['address'])) {
            $data['address_id'] = $this->createAddressRecord($data['address'], $data['name'], $data['branch_id']);
            (new InvoiceFlow)->setVariableInSession(['key' => 'cage_address_id', 'value' => $data['address_id']]);
            if (!$data['address_id']) return [false, 'Unable to create address record'];
        }

        // unset fields not required for cages_data table
        unset($data['name'], $data['address'], $data['branch_id']);

        // if squirrel and raccon cages are zero now, set the reterived status as 1
        if ($data['racoon_cages'] == 0 && $data['squirrel_cages'] == 0) {
            $update_data = ['retrieved' =>  1];
            $response = $this->updateAddressRecord($data['address_id'], $update_data);
            if (!$response) [false, $this->debug ? $wpdb->last_error : 'Unable to update reterived status for cages'];
        } else {
            // extend the pickup date and set reterived as 0
            $update_data = [
                'pickup_date'   =>  date('Y-m-d', strtotime('+31 days')),
                'retrieved'     =>  0
            ];
            $response = $this->updateAddressRecord($data['address_id'], $update_data);
            if (!$response) return [false, 'Unable to update pickup date and reterieved status'];
        }

        // insert the cage record in cage data table
        $response = $this->createSingleCageRecord($data);
        return $response ? [$wpdb->insert_id, null] : [false, $this->debug ? $wpdb->last_error : 'Unable to insert data in database'];
    }

    public function createSingleCageRecord(array $data)
    {
        global $wpdb;
        return $wpdb->insert($wpdb->prefix . "cage_data", $data);
    }

    // Display data in Pop-up
    public function view_contact_detail()
    {
        global $wpdb;
        $this->verify_nonce_field('view_contact_detail');
        $s_id = $_POST['contact_id'];

        $deposit_proof =  $wpdb->get_results("SELECT ca.*,inv.client_name,inv.email,inv.phone_no FROM wp_cage_address as ca 
            LEFT JOIN wp_invoices as inv
            ON ca.invoice_id = inv.id
            WHERE inv.id = '$s_id' ORDER BY ca.id desc
            ");
        if (!empty($deposit_proof)) {
            $output = '<table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email Id</th>
                    <th>Phone No</th>
                </tr>
            </thead>
            <tbody>';
            foreach ($deposit_proof as $row) :
                $output .= '<tr>';
                $output .= '<td>' . (!empty($row->client_name) ? $row->client_name : '') . '</td>';
                $output .= '<td>' . (!empty($row->email) ? $row->email : '') . '</td>';
                $output .= '<td>' . (!empty($row->phone_no) ? $row->phone_no : '') . '</td>';
                $output .= '</tr>';
            endforeach;
            $output .= '</tbody></table>';
            echo $output;
        } else {

            echo '<h3 class="text-center text-danger">No Contact Details Found</h3>';
        }
        wp_die();
    }
}

new AnimalCageTracker();
