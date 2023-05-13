<?php

class OfficeTasks extends Task_manager
{

    private $linkUnknownGoogleLeads = "link_unknown_leads";
    private $linkBriaKey = "link_bria_key";
    private $roleForResignFired = "collect/update_address_on_technician_resign/fire";
    private $addContractOnCalendar = "add_contract_on_calendar";
    private $bingIntegration = "fix_bing_spents_integration";
    private $clientDisatisfaction = "call_client_for_service_disatisfaction";
    private $put_reservice_client_on_calendar = "put_reservice_client_on_calendar";
    private $prospect_reminder_task = "set_reminder_or_create_calendare_event_for_prospect";
    private $realtor_appointement = "set_appointement_for_realtor";
    private $remindClientToSignContract = "remind_client_to_sign_contract";
    private $setAppointementForCagePickup = 'set_appointement_for_cage_pickup';
    private $updateVehicleStatus = "update_vehicles_status";
    private $check_for_google_calendars_not_in_sytem = "check_for_google_calendar_accounts_not_in_system_as_technician";
    private $closedLeadExplanation = "closed_lead_explanation";
    private $monthyVerifyCarInformation = "monthly_verify_car_information";
    private $need_spring_treatment = "need_spring_treatment";
    private $office_staff_create_invoice = "office_staff_create_invoice";

    public function linkUnknownGoogleLeads()
    {
        $task = "There are unknown google spents in system, please link them with correct callrail campaigns";
        return $this->assignTaskByRole($this->linkUnknownGoogleLeads, $task, 0, true);
    }

    public function clearLinkUnknownGoogleLeads()
    {
        return $this->clearTaskByRole($this->linkUnknownGoogleLeads);
    }

    public function linkBriaKey(int $employee_id)
    {

        $employee = (new Employee\Employee)->getEmployee($employee_id, ['name']);
        if (!$employee) return false;

        $task = "Please Link bria key to cold caller $employee->name";
        return $this->assignTaskByRole($this->linkBriaKey, $task, $employee_id);
    }

    public function clearBriaKeyTask(int $employee_id)
    {
        return $this->clearTaskByRole($this->linkBriaKey, $employee_id);
    }

    public function collectVehicleEquipments(int $technician_id, int $vehicle_id)
    {

        $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($technician_id, 1);

        $employee = (new Employee\Employee)->getEmployee($employee_id, ['name']);
        if (!$employee) return false;

        $vehicle = (new CarCenter)->getVehicleById($vehicle_id, ['owner']);
        $vehicle_name = (new CarCenter)->getName($vehicle_id);

        if ($vehicle->owner == "technician") {
            $task = "Technician $employee->name has resigned or got fired. Please collect the everyhting from technician vehicle $vehicle_name ";
        } else {
            $task = "Technician $employee->name has resigned or got fired. Please reterieve the vehicle $vehicle_name from technician and update the parking address in system where vehicle is parked.";
        }

        return $this->assignTaskByRole($this->roleForResignFired, $task, $employee_id);
    }

    public function taskForContractNotFound(object $contract, string $contract_type)
    {

        if ($contract_type == "commercial") {
            $clientName = $contract->establishement_name;
        } else {
            $clientName = $contract->client_name;
        }

        $task = "A $contract_type contract  with client name $clientName not found on google calendar. Please confirm on calendar if not there.";

        return $this->assignTaskByRole($this->addContractOnCalendar, $task);
    }

    public function fixBingIntegration()
    {
        $task = "Bing spnets integration might be broken. Please check for refresh token and bing spents if working fine or not";
        return $this->assignTaskByRole($this->bingIntegration, $task, 0, true);
    }

    public function callDisatisfactionTask(int $invoice_id)
    {
        $invoice = (new Invoice)->getInvoiceById($invoice_id, ['client_name', 'address', 'phone_no']);

        $task = "Client $invoice->client_name with address $invoice->address was dissatisfied with service. Please call the client and sort it out. Contact :- $invoice->phone_no";

        return $this->assignTaskByRole($this->clientDisatisfaction, $task);
    }

    public function developerTask(string $task)
    {

        // create task
        $task_data = ['task_description' => $task];
        $task_id = $this->createTask($task_data);
        if (!$task_id) return false;

        // link task
        $response = $this->linkEmployeeToTask($task_id, 96);
        if (!$response) return false;

        return true;
    }

    public function putReserviceClientOnCalendar(int $invoice_id)
    {
        global $wpdb;

        $invoice = $wpdb->get_row("
            select I.id as invoice_id, I.client_name, I.address, RC.*
            from {$wpdb->prefix}invoices I
            join {$wpdb->prefix}reservice_clients RC
            on I.reservice_id = RC.id
            where I.id = '$invoice_id'
        ");

        if (!$invoice) return false;

        $invoiceViewPageUrl = (new Invoice)->adminInvoiceViewPageUrl($invoice->invoice_id);

        $task_description = "
            <p>Client $invoice->client_name with address $invoice->address requires reservice and needs to be put on calendar. Check <a href='$invoiceViewPageUrl'>last invoice.</a></p>
            <p><b>Total Revisits : </b> $invoice->total_reservices</p>
            <p><b>Reservice Frequency : </b> Every $invoice->revisit_frequency_unit $invoice->revisit_frequency_timeperiod</p>
            <p><b>Reservice Price</b> \$$invoice->reservice_fee</p> 
        ";

        return $this->assignTaskByRole($this->put_reservice_client_on_calendar, $task_description);
    }

    public function prospectReminderTask(int $prospect_id)
    {
        global $wpdb;

        $prospect = (new Prospectus)->getProspect($prospect_id);
        if (!$prospect) return false;

        $task_description = "
            <p>Please set reminder for the following prospect or put on calendar event</p>
            <p><b>Client Name</b> $prospect->name </p>
            <p><b>Email</b> $prospect->email </p>
            <p><b>Address</b> $prospect->address </p>
            <p><b>Phone</b> $prospect->phone </p>
            <p><b>Business Name</b> $prospect->business_name </p>
        ";
        return $this->assignTaskByRole($this->prospect_reminder_task, $task_description);
    }

    public function setRealtorAppointement(int $lead_id)
    {

        $lead = (new Leads)->getLead($lead_id);

        $task_description = " 
            <p>Please set appointement for realtor lead</p>
            <p>Client Name: $lead->name </p>
            <p>Email : $lead->email </p>
            <p>Phone Number : $lead->phone </p>
            <p>Notes : " . nl2br($lead->notes) . " </p>
            <p>Appointement Date : " . date('d M Y', strtotime($lead->appointement_date)) . "</p>
        ";

        return $this->assignTaskByRole($this->realtor_appointement, $task_description);
    }

    public function remindClientToSignContract(string $contract_type, int $contract_id)
    {

        $contract = (new Maintenance)->getContract($contract_type, $contract_id);
        if (!$contract) return false;

        $basic_info = (new Maintenance)->getContractBasicInfo($contract_type, $contract);

        $task_description = " 
            <p>Please remind client <b>$basic_info->name</b> to sign the $contract_type Maintenance Contract</p>
            <p>Address : $basic_info->address</p>
            <p>Phone : $basic_info->phone</p>
            <p>Email : $basic_info->email</p>
        ";

        return $this->assignTaskByRole($this->remindClientToSignContract, $task_description);
    }

    public function setAppointementForCagePickup(array $data)
    {
        $task_description = '';
        foreach ($data as $record) {
            $task_description .= "
                <p>Client <b>$record->name</b> with address <b>$record->address</b> have cages on site for more than 30 days now.</p>";
        }
        $task_description .= '<p>Please setup appointment for cage pickup or extend the pickup date.</p>';
        return $this->assignTaskByRole($this->setAppointementForCagePickup, $task_description);
    }

    public function reassignVehicleAfterChange(int $vehicle_id, string $old_vehicle_parking_address)
    {
        global $wpdb;

        $vehicle = (new CarCenter)->getVehicleById($vehicle_id);
        if ($vehicle->owner != "company") return false;

        $technician = (new Technician_details)->getTechnicianByVehicleId($vehicle_id);

        $task_description = "
            <p>technician <b>$technician->first_name $technician->last_name</b> has change his vehicle and his old vehicle <b>$vehicle->year $vehicle->make $vehicle->model</b> with plate number <b>$vehicle->plate_number</b> is now parked freely at address <b>$old_vehicle_parking_address</b></p>
            <p>Pleas assign this vehicle to some other technician.</p>
        ";

        return $this->assignTaskByRole($this->updateVehicleStatus, $task_description);
    }

    public function notInSystemCalendars(array $calendars)
    {

        $task_description = "
            <p>Please check the google calendar accounts list not in system as technician.</p>
        ";

        foreach ($calendars as $calendar) {
            $task_description .= "
                <p><b>Calendar Account : </b> $calendar->email</p>
                <p><b>Calendar List :</b> " . implode(', ', $calendar->not_in_system) . "</p>
            ";
        }

        return $this->assignTaskByRole($this->check_for_google_calendars_not_in_sytem, $task_description);
    }

    public function remindClientToSignContractGeneralTask()
    {
        $task_description = "
            <p>Please remind clients to sign the contract if not signed yet. Check the list of client who have not signed under all types of maintenance contract by filtering for status \"Offiice sent contract.\"</p>
        ";
        return $this->assignTaskByRole($this->remindClientToSignContract, $task_description);
    }

    public function remindStaffMonthlyVerifyCarInformation()
    {
        $task_description = "
            <p>Please verify all the car information in the system.</p>
        ";
        return $this->assignTaskByRole($this->monthyVerifyCarInformation, $task_description);
    }

    public function remindStaffOnSpringTreatmentIntrest(array $data)
    {
        $query = add_query_arg(['tab' => 'non-reocurring','search' => urlencode($data['email'])], admin_url('admin.php?page=email-database'));
        
        $task_description = "
            <p>Client ".sprintf('<b>%s</b> with email <a target="_blank" href="%s"><b>%s</b></a>', $data['name'],$query, $data['email'])." is interested in spring treatment for home please contact asap.</p>
        ";
        return $this->assignTaskByRole($this->need_spring_treatment, $task_description);
    }

    public function remindStaffOnCreateInvoice(array $data)
    {
        $encrypt = (new GamFunctions);
        $invoice_url = add_query_arg(['tech' => $encrypt->encrypt_data((new Technician_details)->get_technician_id()),'date' => $data['date'], 'event_id' => $data['calendar_id'], 'action' => 'staff_invoice'], esc_url_raw(home_url('/invoice')));
        $task_description = "
            <p>".sprintf('Please <a target="_blank" href=\'%s\'>create invoice</a> in system technician <b>%s</b> bypassed from making invoice for below client', $invoice_url,$data['tech_name'])."</p>
            <p><b>Client Details :-</b></p>
            <p><b>Name :</b> {$data['client-name']}</p> 
            <p><b>Email :</b> {$data['client-email']}</p> 
            <p><b>Phone :</b> {$data['phone-no']}</p> 
            <p><b>Address :</b> {$data['client-location']}</p>
        ";
        
        return $this->assignTaskByRole($this->office_staff_create_invoice, $task_description,0,'',$data['calendar_id']);
    }

    public function closedLeadExplanation(int $lead_id, string $source)
    {
        global $wpdb;

        $lead = (new Leads)->getLead($lead_id);
        if (!$lead) return [false, 'Unable to fetch lead data by id'];

        $task_description = "
            <p>A lead has been closed by system as matching details $source is found in system</p>
            <p>Please confirm if it's correct and update any note for lead clouse for lead</p>
            <p><b>Lead Details</b></p>
            <p><b>Name :</b> $lead->name </p> 
            <p><b>Email :</b> $lead->email </p> 
            <p><b>Address :</b> $lead->address</p> 
        ";

        $response = $this->assignTaskByRole($this->closedLeadExplanation, $task_description);
        if (!$response) return [false, $wpdb->last_error];

        return [true, null];
    }

    public function updateVehicleInformation(string $message)
    {
        return $this->assignTaskByRole($this->updateVehicleStatus, $message);
    }
}

new OfficeTasks();
