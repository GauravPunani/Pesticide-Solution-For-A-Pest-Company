<?php

class EmployePayment extends GamFunctions{
    function __construct(){

        add_action('admin_post_upload_proof_of_payment',array($this,'upload_proof_of_payment'));
        add_action('admin_post_nopriv_upload_proof_of_payment',array($this,'upload_proof_of_payment'));

        add_action('wp_ajax_get_eniligibility_reasons',array($this,'get_eniligibility_reasons'));
        add_action('wp_ajax_nopriv_get_eniligibility_reasons',array($this,'get_eniligibility_reasons'));

        add_action('admin_post_create_payment_structure',array($this,'create_payment_structure'));
        add_action('admin_post_nopriv_create_payment_structure',array($this,'create_payment_structure'));

        add_action('wp_ajax_refresh_payment_eligibility',array($this,'refresh_payment_eligibility'));
        add_action('wp_ajax_nopriv_refresh_payment_eligibility',array($this,'refresh_payment_eligibility'));

        add_action('wp_ajax_refresh_employee_commission',array($this,'refresh_employee_commission'));
        add_action('wp_ajax_nopriv_refresh_employee_commission',array($this,'refresh_employee_commission'));

        add_action('wp_ajax_show_payment_calculation',array($this,'show_payment_calculation'));
        add_action('wp_ajax_nopriv_show_payment_calculation',array($this,'show_payment_calculation'));

        add_action('wp_ajax_update_credit',array($this,'update_credit'));
        add_action('wp_ajax_nopriv_update_credit',array($this,'update_credit'));        

    }

    public function show_payment_calculation(){

        if(!isset($_POST['week']) || empty($_POST['week'])) $this->response('error', 'Something went wrong, please try again later');

        if(!isset($_POST['employee_id']) || empty($_POST['employee_id'])) $this->response('error', 'Something went wrong, please try again later');  

        $week = esc_html($_POST['week']);
        $employee_id = esc_html($_POST['employee_id']);

        list($commission, $calculation_html) = $this->paymentCalculationNew($employee_id, $week); 

        if(!$calculation_html) $this->response('error', '1 Something went wrong, please try again later');

        $this->response('success', $calculation_html);
    }

    public function refresh_employee_commission(){

        if(!isset($_POST['week']) || empty($_POST['week'])) $this->response('error', 'Something went wrong, please try again later');

        if(!isset($_POST['employee_id']) || empty($_POST['employee_id'])) $this->response('error', 'Something went wrong, please try again later');

        $week = esc_html($_POST['week']);
        $employee_id = esc_html($_POST['employee_id']);

        list($commission, $calculation_html) = $this->paymentCalculationNew($employee_id, $week);

        $this->updateCommission($employee_id, $week, $commission);

        $this->response('success', 'Commission updated successfully', ['commission' => "$".$commission]);
    }

    public function refresh_payment_eligibility(){

        if(!isset($_POST['week']) || empty($_POST['week'])){
            $this->response('error', 'Something went wrong, please try again later');
        }

        $week = esc_html($_POST['week']);

        // get all employees
        $employees = (new Employee\Employee)->getAllEmployees();

        if(count($employees) <= 0) $this->response('error', 'Something went wrong, please try again later');

        // loop and check for eligibility
        foreach ($employees as $employee) {

            // first check if already eligible for pay then skip the iteration
            if($this->isAlreadyMarkedAsEligible($employee->id, $week)) continue;

            list($isEligible, $message) = $this->isEligibleForPay($employee->id, $week);

            if(!$isEligible) continue;

            // if eligible mark as eligible
            $response = $this->markAsEligibleForPay($employee->id, $week);

            if(!$response) $this->response('error', 'Employee was eligible for pay but something went wrong while marking as eligible, please contact with developer'); 
            
        }

        $this->response('success', 'Payment Eligibility status refreshed successfully for all employees');
    }

    public function create_payment_structure(){
        global $wpdb;
        $page_url = $_POST['page_url'];

        if(!isset($_POST['payment_structure']) || count($_POST['payment_structure']) <= 0){
            $this->sendErrorMessage($page_url);
        }

        if(!isset($_POST['employee_id']) || empty($_POST['employee_id'])){
            $this->sendErrorMessage($page_url);
        }

        $employee_id = esc_html($_POST['employee_id']);
        $payment_structure = json_encode($_POST['payment_structure']);

        // first check if employe payment structure already exists
        if($this->isPaymentStructureExist($employee_id)){
            $payment_data = ['payment_structure' => $payment_structure];
            $response = $this->updatePaymentStructure( $employee_id, $payment_data);
        }
        else{
            $payment_data = [
                'employee_id'   =>  $employee_id,
                'payment_structure' =>  $payment_structure
            ];
            $response = $this->createPaymentStructure($payment_data);
        }

        if(!$response) $this->sendErrorMessage($page_url);

        $message = "Payment record updated successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    public function get_eniligibility_reasons(){

        if(!isset($_POST['employee_id']) || empty($_POST['employee_id'])){
            $this->response('error', 'Something went wrong, please try again later');
        }

        if(!isset($_POST['week']) || empty($_POST['week'])){
            $this->response('error', 'Something went wrong, please try again later');
        }

        $employee_id = esc_html($_POST['employee_id']);
        $week = esc_html($_POST['week']);

        list($status, $message) = $this->isEligibleForPay($employee_id, $week);

        if($status){
            // first mark as eiligible for pay
            if(!$this->markAsEligibleForPay($employee_id, $week)){
                $this->response('error', 'Employee was eligible for pay but something went wrong while marking as eligible, please contact with developer');
            }

            // send message that user found eligible for pay now and marked as eligible
            $message = "<p class='text-success'>Employee was found eligible for pay and is marked as eligible as well</p>";
            $this->response('success','', ['message' => $message]);
        }
        else{
            $this->response('success','', ['message' => $message]);
        }

    }

    public function upload_proof_of_payment(){
        global $wpdb;

		$this->verify_nonce_field('upload_proof_of_payment');

        $page_url = $_POST['page_url'];

        if(!isset($_POST['payment_id']) || empty($_POST['payment_id'])) return $this->sendErrorMessage($page_url);
        if(!isset($_FILES['payment_proof']) || count($_FILES['payment_proof']['name']) <= 0) return $this->sendErrorMessage($page_url);

        $data=[
            'amount_paid'           =>  $_POST['amount_paid'],
            'payment_description'   =>  $_POST['payment_description'],
            'payment_status'        =>  'paid',
        ];

        $proof_file = count($_FILES['payment_proof']['name']);
        $docs=[];

        for($i=0;$i<$proof_file;$i++){
            if($_FILES['payment_proof']['tmp_name'][$i]!=""){
                $tmp_name=$_FILES['payment_proof']['tmp_name'][$i];
                $file_name=$_FILES['payment_proof']['name'][$i];
                $docs[$i]['file_name']=$file_name;
                $upload=wp_upload_bits($file_name, null, file_get_contents($tmp_name));
                if(array_key_exists('url',$upload)){
                    $docs[$i]['file_url']=$upload['url'];
                }	
            }
        }

        $data['proof_docs'] = json_encode($docs);
        $data['updated_at'] = date('Y-m-d h:i:s');

        $res=$wpdb->update($wpdb->prefix."payments", $data, ['id' => $_POST['payment_id']]);

        if(!$res) return $this->sendErrorMessage($page_url);

        $message="Payment Proof Uploaded Successfully and moved to proof of payments tab";
        $this->setFlashMessage($message,'success');
        
        wp_redirect($page_url);
    }    

    public function paymentCalculationNew( int $employee_id, string $week){

        // first get the technician payment structure
        $payment_structure = $this->getPaymentStructure($employee_id);
        if(empty($payment_structure)) return [false, 'Payment structure is not set for employee yet'];

        $payment_structure = json_decode($payment_structure);

        $final_commission = 0;

        if($payment_structure->payment_type == "percentage_of_route"){
            list($final_commission, $calculation_html) = $this->calculcatePercentageOfRoute($employee_id, $week, $payment_structure);
        }

        if($payment_structure->payment_type == "x_amount_per_appointement"){
            list($final_commission, $calculation_html) = $this->x_amount_per_appointement($employee_id, $week, $payment_structure);
        }

        if($payment_structure->payment_type == "fixed_weekly_salary"){
            $calculation_html = "<p><b>Calculation Type: </b> By fixed weekly salary</p>";
            $calculation_html .= "<p><b>Salary: </b>  $payment_structure->fixed_salary</p>";
            $final_commission = $payment_structure->fixed_salary;
        }

        return [$final_commission, $calculation_html];

    }

    public function __filterOfficeOwnedInvoices(array $invoices){

        if(count((array)$invoices) <= 0) return $invoices;

        foreach($invoices as $key => $invoice){
            if($invoice->credit == "office_sold") unset($invoices[$key]);
        }

        return $invoices;
    }

    public function calculcatePercentageOfRoute($employee_id, $week, $payment_structure){
        global $wpdb;

        $calculation_html = '';

        $fields = ['service_fee', 'credit'];
        $technician_id = (new Employee\Employee)->getReferenceIdByEmployeeId($employee_id);
        $invoices = (new Invoice)->getTechnicianInvoicesByWeek($employee_id, $week, $fields);
        $invoices = $this->__filterOfficeOwnedInvoices($invoices);

        $invoice_amount = array_reduce($invoices, function($total, $invoice){
            return $total + $invoice->service_fee;
        }, 0);

        $invoice_commission = ( (int) $invoice_amount * (float) $payment_structure->percentage) / 100;

        list($sunday, $saturday) = $this->weekRange($week);

        $maintenance_commission = $this->__maintenanceCommission($employee_id, $week, $payment_structure);
        
        $parking_tickets_amount = (new EmployeePayment\ParkingTickets)->getTicketsTotal($employee_id, $week);

        $final_commission = $invoice_commission + $maintenance_commission;

        // deduct the parking tickets amount from final commission
        $final_commission = $final_commission - $parking_tickets_amount;

        $calculation_html .= "<h3> <b> Payment Type: </b> By  $payment_structure->percentage% of route</h3>";
        $calculation_html .= "<p> <b> Total Invoices = </b> ".count($invoices)."  </p>";
        $calculation_html .= "<p><b>Total Invoices Amount = </b> \$$invoice_amount</p>";
        $calculation_html .= "<p> <b> Invoice commission by $payment_structure->percentage% of route: </b> \$$invoice_commission</p>";
        $calculation_html .= "<p><b>Total Maintenance Commission = </b> \$$maintenance_commission</p>";
        $calculation_html .= "<p><b>Parking Tickets Total = </b> \$$parking_tickets_amount</p>";
        $calculation_html .= "<p><b>Final Commission = </b> (\$$invoice_commission + \$$maintenance_commission - \$$parking_tickets_amount) = \$$final_commission </p>";

        $calculation_html .= $this->payment_breakdown($technician_id, $sunday, $saturday);

        return [$final_commission, $calculation_html];
    }

    public function x_amount_per_appointement($employee_id, $week, $payment_structure){
        global $wpdb;

        list($sunday, $saturday) = $this->weekRange($week);        

        $invoice_count = (new Invoice)->getInvoiceCount($sunday, $saturday, $employee_id);

        $invoice_commission = (int) $invoice_count * (float) $payment_structure->amount_per_appointement;

        $maintenance_commission = $this->__maintenanceCommission($employee_id, $week, $payment_structure);

        $final_commission = $invoice_commission + $maintenance_commission;

        $calculation_html = "<p><b>Calculation Type: </b> \$$payment_structure->amount_per_appointement per appointement</p>";
        $calculation_html .= "<p><b>Total Invoices: </b> $invoice_count</p>";
        $calculation_html .= "<p><b>Invoice Commission: </b> $invoice_commission</p>";
        $calculation_html .= "<p><b>Maintenance Commission: </b> $maintenance_commission</p>";
        $calculation_html .= "<p><b>Final Commission: </b> $final_commission</p>";

        return [$final_commission, $calculation_html];
    }

    public function __maintenanceCommission($employee_id, $week, $payment_structure){
        list($sunday, $saturday) = $this->weekRange($week);

        // calculate maintenance contracts commission
        $monthly_count = (new MonthlyQuarterlyMaintenance)->getMonthlyCount($sunday, $saturday, $employee_id, true);
        $quarterly_count = (new MonthlyQuarterlyMaintenance)->getQuarterlyCount($sunday, $saturday, $employee_id, true);
        $special_count = (new SpecialMaintenance)->getCount($sunday, $saturday, $employee_id, true);
        $commercial_count = (new CommercialMaintenance)->getCount($sunday, $saturday, $employee_id, true);

        $monthly_commission = (int) $monthly_count * (int) $payment_structure->monthly_maintenance;
        $quarterly_commission = (int) $quarterly_count * (int) $payment_structure->quarterly_maintenance;
        $special_commission = (int) $special_count * (int) $payment_structure->special_maintenance;
        $commercial_commission = (int) $commercial_count * (int) $payment_structure->commercial_maintenance;

        $maintenance_commission = $monthly_commission + $quarterly_commission + $special_commission + $commercial_commission;

        return $maintenance_commission;
    }

    public function createPaymentStructure( array $payment_data){
        global $wpdb;
        return $wpdb->insert($wpdb->prefix."payment_structure", $payment_data);
    }

    public function updatePaymentStructure( int $employee_id, array $payment_data){
        global $wpdb;
        $payment_data['updated_at'] = date('Y-m-d h:i:s');
        return $wpdb->update($wpdb->prefix."payment_structure", $payment_data, ['employee_id' => $employee_id]);
    }

    public function getPaymentStructure( int $employee_id){
        global $wpdb;

        return $wpdb->get_var("
            select payment_structure
            from {$wpdb->prefix}payment_structure
            where employee_id = '$employee_id'
        ");
    }

    public function isPaymentStructureExist( int $employee_id ): bool{
        global $wpdb;

        $count = $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}payment_structure
            where employee_id = '$employee_id'
        ");

        return $count ? true : false;

    }

    public function generatePaymentRecords(string $week){
        global $wpdb;

        $employees = (new Employee\Employee)->getAllEmployees();

        if(count($employees) <= 0 ) return;

        foreach ($employees as $employee) {

            list($eligibility_status, $message) = $this->isEligibleForPay($employee->id, $week);
            list($commission, $calculation_html) = $this->paymentCalculationNew($employee->id, $week);
            
            $eligibility_status = $eligibility_status ? 1 : 0;

            $payment_data = [
                'employee_id'           =>  $employee->id,
                'week'                  =>  $week,
                'calculated_commission' =>  $commission,
                'is_eligible'           =>  $eligibility_status,
            ];

            $this->__createPaymentRecord($payment_data);
    
        }

    }

    public function __createPaymentRecord( array $payment_data ){
        global $wpdb;

        $payment_data['payment_status'] = 'pending';
        $payment_data['created_at'] = date('Y-m-d h:i:s');
        $payment_data['updated_at'] = date('Y-m-d h:i:s');

        return $wpdb->insert($wpdb->prefix."payments", $payment_data); 
    }

    public function markAsEligibleForPay( int $employee_id, string $week){
        global $wpdb;

        $where_data = [
            'employee_id'   =>  $employee_id,
            'week'          =>  $week
        ];

        return $wpdb->update($wpdb->prefix."payments", ['is_eligible' => 1], $where_data);
    }

    public function isEligibleForPay( int $employee_id, string $week): array{

        $employee_type = (new Employee\Employee)->getEmployeeTypeSlug($employee_id);

        if($employee_type == "technician"){
            
            // STEP 1 : check if technician account is locked
            list($status, $message) = $this->__isTechnicianAccountLocked($employee_id);
            if($status) return [false, $message];
            
            // STEP 2 check if any event is pending, then return false            
            list($status, $message) = $this->__isTechnicianHavePendingEvents($employee_id, $week);
            if($status) return [false, $message];

            // STEP 3 : check if credit card payment that event is verified with tekcard            
            list($status, $message) =  $this->__isTechnicianHavePenindingCcVerfification($employee_id, $week);
            if($status) return [false, $message];            

            // if script reaches here return true
            return [true, null];

        }

        return [true, null];

    }

    public function isAlreadyMarkedAsEligible( int $employee_id, string $week){
        global $wpdb;

        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}payments
            where employee_id = '$employee_id'
            and week = '$week'
            and is_eligible = 1
        ");
    }

    public function updateCommission( int $employee_id, string $week, float $commission){
        global $wpdb;
        
        $conditions = [
            'week'          =>  $week,
            'employee_id'   =>  $employee_id
        ];

        return $wpdb->update($wpdb->prefix."payments", ['calculated_commission' => $commission], $conditions);
    }

    public function __isTechnicianHavePenindingCcVerfification( int $employee_id, string $week): array{
        global $wpdb;

        $technician_id = (new Employee\Employee)->getReferenceIdByEmployeeId($employee_id);
        if(!$technician_id) throw new Exception('Employee ref id not found');        

		$week_monday = date('Y-m-d',strtotime('this monday',strtotime($week)));
		$week_sunday = date('Y-m-d',strtotime('this sunday',strtotime($week)));
        
        $invoices = $wpdb->get_results("
            select client_name, address, date, invoice_no
            from {$wpdb->prefix}invoices
            where technician_id = '$technician_id'
            and payment_method = 'credit_card'
            and DATE(date) >= '$week_monday'
            and DATE(date) <= '$week_sunday'
            and (status = 'no_transaction_found' or status = '' or status is null)
        ");

        if(count($invoices) <= 0) return [false, null];

        $message = "";
        foreach($invoices as $invoice){
            $message .= "
                <p> Invoice $invoice->invoice_no ($invoice->client_name) with credit card payment was marked as \"not verified by system\" - ($invoice->date)</p>
            ";
        }

        return [true, $message];

    }

    public function __isTechnicianAccountLocked( int $employee_id): array{
        global $wpdb;

        $technician_id = (new Employee\Employee)->getReferenceIdByEmployeeId($employee_id);
        if(!$technician_id) throw new Exception('Employee ref id not found');

        $notices = $wpdb->get_results("
            select notice, date
            from {$wpdb->prefix}technician_account_status
            where technician_id = '$technician_id'
            and level = 'critical'
        ");

        if(count($notices) <= 0) return [false, null];

        $message = '';
        foreach($notices as $notice){
            $message .= "<p>$notice->notice - ($notice->date)</p>";
        }

        return [true, $message];
    }

    public function __isTechnicianHavePendingEvents( int $employee_id, $week): array{

        $fields = ['calendar_event_id'];

        $invoice_events = (new Invoice)->getTechnicianInvoicesByWeek($employee_id, $week, $fields);
        
        $residential_quote_events = (new Quote)->getTechnicianResidentialQuotesByWeek($employee_id, $week, $fields);
        
        $commercial_quote_events = (new Quote)->getTechnicianCommercialQuotesByWeek($employee_id, $week, $fields);

        
        list($last_sunday, $this_saturday) = $this->weekRange($week);

		$prospectConditions = [];
		$prospectConditions[] =" date(event_date) >= '$last_sunday' ";
		$prospectConditions[] =" date(event_date) <= '$this_saturday' ";
		$prospectNotes = (new Prospectus)->getProspectNotes($prospectConditions, ['calendar_event_id']);

        $calendar_events = (new Calendar)->getTechnicianEventsByWeek($employee_id, $week);

        $message = '';

        if(count((array)$calendar_events) <= 0){
            $message .= 'There is no event in calendar for the technician for the given week';
            return [true, $message];
        }

        // loop calendar events on system events to find if they exist in system or not
        $pending_event = false;

        foreach($calendar_events as $calendar_event){

            $event_found = false;

            // check in invoice events first
            foreach($invoice_events as $invoice_event){
                if($calendar_event->id == $invoice_event->calendar_event_id){
                    $event_found = true;
                    continue;
                }
            }

            if($event_found == true) continue;

            // check in residential quotes
            foreach($residential_quote_events as $residential_quote_event){
                if($calendar_event->id == $residential_quote_event->calendar_event_id){
                    $event_found = true;
                    continue;
                }
            }

            if($event_found == true) continue;

            // check in commerical quotes
            foreach($commercial_quote_events as $commercial_quote_event){
                if($calendar_event->id == $commercial_quote_event->calendar_event_id){
                    $event_found = true;
                    continue;
                }
            }

            if($event_found == true) continue;

            // check in prospect notes
            foreach($prospectNotes as $prospectNote){
                if($calendar_event->id == $prospectNote->calendar_event_id){
                    $event_found = true;
                    continue;
                }
            }

            if($event_found == false){
                $message .= "<p>Calendar Event with title \"$calendar_event->summary\" was not found in system by technician</p>";
                $pending_event = true;
            }

        }

        if($pending_event) return [true, $message];

        return [false, null];

    }
    
    public function payment_breakdown( int $technician_id, string $week_start_date, string $week_end_date){
        global $wpdb;

        $invoices_breakdown=$wpdb->get_results("
            select id,client_name,address,service_fee,id,callrail_id,date,credit 
            from {$wpdb->prefix}invoices
            where DATE(date) >='$week_start_date'
            and DATE(date) <='$week_end_date'
            and technician_id='$technician_id'
        ");


        $invoices_breakdown_html="
            <table class='table table-striped table-hover'>
                <caption>Invoices</caption>
                <thead>
                    <tr>
                        <th>Office Sold</th>
                        <th>Client Name</th>
                        <th>Client Address</th>
                        <th>Amount</th>
                        <th>Recurring ?</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>";

        if(is_array($invoices_breakdown) && count($invoices_breakdown)>0){
            foreach ($invoices_breakdown as $invoice) {
                $invoices_breakdown_html.='<tr>';

                $invoices_breakdown_html.="<td><input data-type='invoice' data-id='$invoice->id' type='checkbox' class='office_sold' ".($invoice->credit=='office_sold' ? 'checked' : '')." ></td>";
                $invoices_breakdown_html.="<td>$invoice->client_name</td>";
                $invoices_breakdown_html.="<td>$invoice->address</td>";
                $invoices_breakdown_html.="<td>\$$invoice->service_fee</td>";
                if($invoice->callrail_id!='reoccuring_customer'){
                    $invoices_breakdown_html.="<td>New Customer</td>";
                }
                else{
                    $invoices_breakdown_html.="<td>Reocurring Customer</td>";
                }
                $invoices_breakdown_html.="<td>".date('d M Y',strtotime($invoice->date))."</td>";
                $invoices_breakdown_html.="</tr>";

            }
        }
        else{
            $invoices_breakdown_html.='
            <tr>
                <td colspan="6">No Invoice Found</td>
            </tr>';
        }
        
        $invoices_breakdown_html.="</tbody>
        </table>";

        $residential_quotes=$wpdb->get_results("
            select id,clientName,clientAddress,date,credit,total_cost 
            from {$wpdb->prefix}quotesheet 
            where DATE(date)>='$week_start_date' 
            and DATE(date)<='$week_end_date' 
            and technician_id='$technician_id'
        ");

        $invoices_breakdown_html.="
            <table class='table table-striped table-hover'>
                <caption>Residential Quotes</caption>
                <thead>
                    <tr>
                        <th>Office Sold</th>
                        <th>Client Name</th>
                        <th>Client Address</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
        ";

        if(is_array($residential_quotes) && count($residential_quotes)>0){
            foreach ($residential_quotes as $quote) {
                $invoices_breakdown_html.= "
                    <tr>
                        <td><input data-type='residential-quote' data-id='$quote->id' type='checkbox' class='office_sold' ".($quote->credit=='office_sold' ? 'checked' : '')." ></td>
                        <td>$quote->clientName</td>
                        <td>$quote->clientAddress</td>
                        <td>\$$quote->total_cost</td>
                        <td>".date('d M Y',strtotime($quote->date))."</td>
                    </tr>
                ";
            }
        }
        else{
            $invoices_breakdown_html.='
                <tr>
                    <td colspan="6">No Residential Quote Found</td>
                </tr>
            ';
        }
        $invoices_breakdown_html.="
            </tbody>
            </table>
        ";

        $commercial_quotes=$wpdb->get_results("
        select id,client_name,client_address,date,credit,cost_per_visit 
        from {$wpdb->prefix}commercial_quotesheet 
        where DATE(date)>='$week_start_date' 
        and DATE(date)<='$week_end_date' 
        and technician_id='$technician_id'
        ");


        $invoices_breakdown_html.="
            <table class='table table-striped table-hover'>
                <caption>Commercial Quotes</caption>
                <thead>
                    <tr>
                        <th>Office Sold</th>
                        <th>Client Name</th>
                        <th>Client Address</th>
                        <th>Cost Per Visit</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>";

        if(is_array($commercial_quotes) && count($commercial_quotes)>0){
            foreach ($commercial_quotes as $quote) {
                $invoices_breakdown_html.="<tr>";
                $invoices_breakdown_html.="<td><input data-type='commercial-quote' data-id='$quote->id' type='checkbox' class='office_sold' ".($quote->credit=='office_sold' ? 'checked' : '')." ></td>";
                $invoices_breakdown_html.="<td>$quote->client_name</td>";
                $invoices_breakdown_html.="<td>$quote->client_address</td>";
                $invoices_breakdown_html.="<td>\$$quote->cost_per_visit</td>";
                $invoices_breakdown_html.="<td>".date('d M Y',strtotime($quote->date))."</td>";
                $invoices_breakdown_html.="</tr>";
            }
        }
        else{
            $invoices_breakdown_html.='
            <tr>
                <td colspan="6">No Commercial Quote Found</td>
            </tr>';
        }
        $invoices_breakdown_html.="</tbody>
        </table>";

        $monthly_quarterly_contracts=$wpdb->get_results("
            select id,client_name,client_address,date,credit,total_cost 
            from {$wpdb->prefix}maintenance_contract 
            where DATE(date)>='$week_start_date 00:00:00' 
            and DATE(date)<='$week_end_date 23:59:00' 
            and technician_id='$technician_id'
        ");


        $invoices_breakdown_html.="
            <table class='table table-striped table-hover'>
                <caption>Monthly/Quarterly Contracts</caption>
                <thead>
                    <tr>
                        <th>Office Sold</th>
                        <th>Client Name</th>
                        <th>Client Address</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>";

        if(is_array($monthly_quarterly_contracts) && count($monthly_quarterly_contracts)>0){
            foreach ($monthly_quarterly_contracts as $contract) {
                $invoices_breakdown_html.="<tr>";
                $invoices_breakdown_html.="<td><input data-type='monthly_quarterly' data-id='$contract->id' type='checkbox' class='office_sold' ".($contract->credit=='office_sold' ? 'checked' : '')." ></td>";
                $invoices_breakdown_html.="<td>$contract->client_name</td>";
                $invoices_breakdown_html.="<td>$contract->client_address</td>";
                $invoices_breakdown_html.="<td>\$$contract->total_cost</td>";
                $invoices_breakdown_html.="<td>".date('d M Y',strtotime($contract->date))."</td>";
                $invoices_breakdown_html.="</tr>";
            }
        }
        else{
            $invoices_breakdown_html.='
            <tr>
                <td colspan="6">No Monthly/Quarterly Contract Found</td>
            </tr>';
        }
        $invoices_breakdown_html.="</tbody>
        </table>";

        $special_contracts=$wpdb->get_results("
        select id,client_name,client_address,date_created,credit 
        from {$wpdb->prefix}special_contract 
        where DATE(date_created)>='$week_start_date 00:00:00' 
        and DATE(date_created)<='$week_end_date 23:59:00' 
        and technician_id='$technician_id'
        ");


        $invoices_breakdown_html.="
            <table class='table table-striped table-hover'>
                <caption>Special Contracts</caption>
                <thead>
                    <tr>
                        <th>Office Sold</th>
                        <th>Client Name</th>
                        <th>Client Address</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>";

        if(is_array($special_contracts) && count($special_contracts)>0){
            foreach ($special_contracts as $contract) {
                $invoices_breakdown_html.="<tr>";
                $invoices_breakdown_html.="<td><input data-type='special' data-id='$contract->id' type='checkbox' class='office_sold' ".($contract->credit=='office_sold' ? 'checked' : '')." ></td>";
                $invoices_breakdown_html.="<td>$contract->client_name</td>";
                $invoices_breakdown_html.="<td>$contract->client_address</td>";
                $invoices_breakdown_html.="<td>".date('d M Y',strtotime($contract->date_created))."</td>";
                $invoices_breakdown_html.="</tr>";
            }
        }
        else{
            $invoices_breakdown_html.='
            <tr>
                <td colspan="6">No Special Contract Found</td>
            </tr>';
        }
        $invoices_breakdown_html.="</tbody>
        </table>";

        $commercial_contracts=$wpdb->get_results("
        select id,establishement_name,person_in_charge,client_address,date_created,credit 
        from {$wpdb->prefix}commercial_maintenance 
        where DATE(date_created)>='$week_start_date 00:00:00' 
        and DATE(date_created)<='$week_end_date 23:59:00' 
        and technician_id='$technician_id'
        ");


        $invoices_breakdown_html.="
            <table class='table table-striped table-hover'>
                <caption>Commercial Contracts</caption>
                <thead>
                    <tr>
                        <th>Office Sold</th>
                        <th>Establishement Name</th>
                        <th>Person In Charge</th>
                        <th>Client Address</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>";

        if(is_array($commercial_contracts) && count($commercial_contracts)>0){
            foreach ($commercial_contracts as $contract) {
                $invoices_breakdown_html.="<tr>";
                $invoices_breakdown_html.="<td><input data-type='commercial' data-id='$contract->id' type='checkbox' class='office_sold' ".($contract->credit=='office_sold' ? 'checked' : '')." ></td>";
                $invoices_breakdown_html.="<td>$contract->establishement_name</td>";
                $invoices_breakdown_html.="<td>$contract->person_in_charge</td>";
                $invoices_breakdown_html.="<td>$contract->client_address</td>";
                $invoices_breakdown_html.="<td>".date('d M Y',strtotime($contract->date_created))."</td>";
                $invoices_breakdown_html.="</tr>";
            }
        }
        else{
            $invoices_breakdown_html.='
            <tr>
                <td colspan="6">No Commercial Contract Found</td>
            </tr>';
        }
        $invoices_breakdown_html.="</tbody>
        </table>";

        return $invoices_breakdown_html;
    }

    public function update_credit(){
        global $wpdb;

		$this->verify_nonce_field('update_credit');

        if(
            empty($_POST['office_sold']) ||
            empty($_POST['type']) ||
            empty($_POST['id'])
        ) $this->response('error');

        $office_sold = $this->sanitizeEscape($_POST['office_sold']);
        $id = $this->sanitizeEscape($_POST['id']);        
        $type = $this->sanitizeEscape($_POST['type']);        

        $office_sold = $office_sold == "true" ? "office_sold" : "technician";

        $table_name='';

        switch ($type) {
            case 'invoice':
                $table_name = "invoices";
            break;
            case 'residential-quote':
                $table_name = "quotesheet";
            break;
            case 'commercial-quote':
                $table_name = "commercial_quotesheet";
            break;
            case 'monthly_quarterly':
                $table_name = "maintenance_contract";
            break;
            case 'speical':
                $table_name = "special_contract";
            break;
            case 'commercial':
                $table_name = "commercial_maintenance";
            break;
            default:
                $this->response('error');
            break;
        }

        $res = $wpdb->update($wpdb->prefix.$table_name, ['credit' => $office_sold], ['id' => $_POST['id']]);

        if($res === false) $this->response('error');

        $this->response('success','credit field updated successfully');
    }
}

new EmployePayment();