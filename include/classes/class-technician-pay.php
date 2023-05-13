<?php

class TechnicianPay extends GamFunctions{

    function __construct(){
        add_action('wp_ajax_calculate_tech_pay',array($this,'calculate_tech_pay'));
        add_action('wp_ajax_nopriv_calculate_tech_pay',array($this,'calculate_tech_pay'));

        add_action('wp_ajax_refresh_tech_pay_calculation',array($this,'refresh_tech_pay_calculation'));
        add_action('wp_ajax_nopriv_refresh_tech_pay_calculation',array($this,'refresh_tech_pay_calculation'));

        add_action('admin_post_update_payment_method',array($this,'update_payment_method'));
        add_action('admin_post_nopriv_update_payment_method',array($this,'update_payment_method'));
    }

    public function refresh_tech_pay_calculation(){
        global $wpdb;

		$this->verify_nonce_field('refresh_calculation');
     

        if(isset($_POST['user_id']) && isset($_POST['week']) && isset($_POST['table_id']) ){


            if(!empty($_POST['user_id']) && !empty($_POST['week']) && !empty($_POST['table_id'])){
                $week_start_date=date('Y-m-d',strtotime('this monday',strtotime($_POST['week'])));
                $week_end_date=date(('Y-m-d'),strtotime('this sunday',strtotime($_POST['week'])));    
    
                // fetch tech lastet pay by calculation
                list($salary_calculation,$week_final_commission) = $this->payment_calculation($_POST['user_id'],$week_start_date,$week_end_date);
    
                // update the commission to the table as well 
                $wpdb->update($wpdb->prefix."payments", ['calculated_commission' => $week_final_commission], ['id' => $_POST['table_id']]);
    
                $week_final_commission = $this->beautify_amount_field($week_final_commission);
    
                $this->response('success','test message',['week_final_commission'=>$week_final_commission]);    
            }
            else{
                $this->response('error','something went wrong, please try again later');
            }
        }
        else{
            $this->response('error','something went wrong, please try again later');
        }

    }

    public function update_payment_method(){
        global $wpdb;
		$this->verify_nonce_field('update_payment_method');
        if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
            $data=[
                'payment_type'  =>  $_POST['payment_type'],
                'payment_amount'    =>  $_POST['amount']
            ];

            $res=$wpdb->update($wpdb->prefix."technician_details",$data,['id'=>$_POST['user_id']]);

            if($res){
                $message="Payment method updated successfully";
                $this->setFlashMessage($message,'success');
            }
            else{
                $message="something went wrong, please try again later";
                $this->setFlashMessage($message,'danger');
            }

        }
        else{
            $message="something went wrong, please try again later";
            $this->setFlashMessage($message,'danger');
        }

        wp_redirect($_POST['page_url']);

    }

    public function get_all_technician_pay($week=''){
        global $wpdb;

        $technicians=(new Technician_details)->get_all_technicians(false, '', true, false);
        // echo "<pre>";print_r($technicians);wp_die();

        $week_start_date = date('Y-m-d',strtotime('this monday',strtotime($week)));
        $week_end_date = date(('Y-m-d'),strtotime('this sunday',strtotime($week)));

        // foreach technician calculate his total pay for the week
        if(is_array($technicians) && count($technicians) >0){
            foreach ($technicians as $technician) {

                list($salary_calculation,$week_final_commission)=$this->payment_calculation($technician->id,$week_start_date,$week_end_date);

                list($eligibility_status, $message) = (new EmployePayment)->isEligibleForPay($technician->id, 'technician', $week);
                
                $tech_pay_data=[
                    'week'                  =>  $week,
                    'user_id'               =>  $technician->id,
                    'role'                  =>  "technician",
                    'calculated_commission' =>  $week_final_commission,
                    'payment_status'        =>  'pending',
                    'is_eligible'           =>  $eligibility_status,
                ];

                $wpdb->insert($wpdb->prefix."payments",$tech_pay_data);

            }
        }
    }

    public function calculate_tech_pay(){
	$this->verify_nonce_field('calculate_tech_pay');
        $week_start_date=date('Y-m-d',strtotime('this monday',strtotime($_POST['week'])));
        $week_end_date=date(('Y-m-d'),strtotime('this sunday',strtotime($_POST['week'])));

        list($salary_calculation,$week_final_commission)=$this->payment_calculation($_POST['technician'],$week_start_date,$week_end_date);

        $invoices_breakdown_html=$this->invoice_maintenance_quotes_breakdown($_POST['technician'],$week_start_date,$week_end_date);

        $payment_summary_html=$this->payment_summary($_POST['technician'],$week_start_date,$week_end_date,$_POST['week'],$week_final_commission);

        $this->response('success','',['invoice_breakdown'=>$invoices_breakdown_html,'payment_summary'=>$payment_summary_html,'payment_calculation'=>$salary_calculation]);
    
        wp_die();
    }

    public function invoice_maintenance_quotes_breakdown($technician,$week_start_date,$week_end_date){

        global $wpdb;

        $invoices_breakdown=$wpdb->get_results("
        select id,client_name,address,total_amount,id,callrail_id,date,credit 
        from {$wpdb->prefix}invoices
        where DATE(date) >='$week_start_date'
        and DATE(date) <='$week_end_date'
        and technician_id='$technician'
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
                $invoices_breakdown_html.="<td>\$$invoice->total_amount</td>";
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
        and technician_id='$technician'
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
        <tbody>";

        if(is_array($residential_quotes) && count($residential_quotes)>0){
            foreach ($residential_quotes as $quote) {
                $invoices_breakdown_html.="<tr>";
                $invoices_breakdown_html.="<td><input data-type='residential-quote' data-id='$quote->id' type='checkbox' class='office_sold' ".($quote->credit=='office_sold' ? 'checked' : '')." ></td>";
                $invoices_breakdown_html.="<td>$quote->clientName</td>";
                $invoices_breakdown_html.="<td>$quote->clientAddress</td>";
                $invoices_breakdown_html.="<td>\$$quote->total_cost</td>";
                $invoices_breakdown_html.="<td>".date('d M Y',strtotime($quote->date))."</td>";
                $invoices_breakdown_html.="</tr>";
            }
        }
        else{
            $invoices_breakdown_html.='
            <tr>
                <td colspan="6">No Residential Quote Found</td>
            </tr>';
        }
        $invoices_breakdown_html.="</tbody>
        </table>";

        $commercial_quotes=$wpdb->get_results("
        select id,client_name,client_address,date,credit,cost_per_visit 
        from {$wpdb->prefix}commercial_quotesheet 
        where DATE(date)>='$week_start_date' 
        and DATE(date)<='$week_end_date' 
        and technician_id='$technician'
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
        and technician_id='$technician'
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
        and technician_id='$technician'
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
        and technician_id='$technician'
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

    public function payment_calculation($technician,$week_start_date,$week_end_date){
        global $wpdb;

        $technician_details = $wpdb->get_row("
            select payment_type,payment_amount 
            from {$wpdb->prefix}technician_details 
            where id='$technician'
        ");

        $invoices_breakdown = $wpdb->get_results("
            select id,client_name,address,total_amount,id,callrail_id,date,credit 
            from {$wpdb->prefix}invoices
            where DATE(date) >='$week_start_date'
            and DATE(date) <='$week_end_date'
            and technician_id='$technician'
        ");

        $begin = new DateTime($week_start_date);
        $end = new DateTime($week_end_date);
        $end->setTime(0,0,1);
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        $salary_calculation='';
        $all_day_commission=[];
        $week_final_commission=0;

        // calculate technician pay day by day 
        foreach ($period as $dt) {
            $date=$dt->format("Y-m-d");
            
            $salary_calculation.="<div class='page-header'>".$dt->format("l d M Y")."</div>";
            $salary_calculation.="<div class='row'>";

            $date_invoices=[];

            foreach ($invoices_breakdown as $key => $invoice) {
                if($invoice->date==$date && $invoice->credit == 'technician'){
                    $date_invoices[]=$invoice;
                }
            }

            $commission_17=0;
            $commission_10=0;

            $invoice_status=false;
            $maintenance_status=false;
            $quotes_status=false;

            // for invoice calculation
            if(count($date_invoices)>0){
                $invoice_status=true;
                $new_job_sold_amount=0;
                $total_invoices_amount=0;

    
                $salary_calculation.="<div class='col-md-6'>";
                $salary_calculation.="<p>Total Invoices ".count($date_invoices)."</p>";
                $salary_calculation.="</div>";

                foreach ($date_invoices as $key => $invoice) {
                    $total_invoices_amount+=$invoice->total_amount;
                    if($invoice->callrail_id!="reoccuring_customer"){
                        $new_job_sold_amount+=$invoice->total_amount;
                    }
                }

                $commission_17=(count($date_invoices)*17);
                $commission_10=(float)(($new_job_sold_amount*10)/100);

                $salary_calculation.="<div class='col-md-6'>";
                $salary_calculation.="<p>Total Amount : $total_invoices_amount</p>";
                $salary_calculation.="</div>";

                $salary_calculation.="<div class='col-md-6'>";
                $salary_calculation.="<p>Commission ($17 per invoice) : $commission_17</p>";
                $salary_calculation.="</div>";


                $salary_calculation.="<div class='col-md-6'>";
                $salary_calculation.="<p>Commission @10% : $commission_10</p>";
                $salary_calculation.="</div>";
            }
            else{
                $salary_calculation.="<div class='col-md-12'>";
                $salary_calculation.="<p>No Invoice done on the date</p>";
                $salary_calculation.="</div>";    
            }

            // calculate total maintenance contracts for the day by querying in each type of contract one by one
            $monthly_quarterly_total=$wpdb->get_var("
                select COALESCE(count(*),0) as total_contracts 
                from {$wpdb->prefix}maintenance_contract 
                where technician_id='$technician' 
                and date like '%$date%'
            ");

            $special_maintenance_total=$wpdb->get_var("
                select COALESCE(count(*),0) as total_contracts 
                from {$wpdb->prefix}special_contract 
                where technician_id='$technician' 
                and date_created like '%$date%'
            ");

            $commercial_maintenance_total=$wpdb->get_var("
                select COALESCE(count(*),0) as total_contracts 
                from {$wpdb->prefix}commercial_maintenance 
                where technician_id='$technician' 
                and date_created like '%$date%'
            ");
    
            $total_contracts=$monthly_quarterly_total+$special_maintenance_total+$commercial_maintenance_total;            

            $maintenance_commission=0;
            if($total_contracts > 0 ){
                $maintenance_status=true;
                $salary_calculation.="<div class='col-md-6'>";
                $salary_calculation.="<p>Total Maintenance Contracts : $total_contracts</p>";
                $salary_calculation.="</div>";
    
                $maintenance_commission=((float)$total_contracts*25);
    
                $salary_calculation.="<div class='col-md-6'>";
                $salary_calculation.="<p>Total Maintenance Commission : $maintenance_commission</p>";
                $salary_calculation.="</div>";
                    
            }
            else{
                $salary_calculation.="<div class='col-md-12'>";
                $salary_calculation.="<p>No Maintenance Contract done on the date</p>";
                $salary_calculation.="</div>";
            }

            // if($invoice_status || $maintenance_status || $quotes_status){
            if($invoice_status || $maintenance_status || $quotes_status){

                $total_commission=(float)($commission_17+$commission_10+$maintenance_commission);
                $salary_calculation.="<div class='col-md-6'>";
                $salary_calculation.="<p>Total Commission for the day    : $commission_17 + $commission_10 + $maintenance_commission = $total_commission</p>";
                $salary_calculation.="</div>";

                // set tech daily minimum amount
                if($technician_details->payment_type=="daily"){
                    $daily_minimum_amount=$technician_details->payment_amount;

                    if((float)$total_commission <= $daily_minimum_amount){
                        $all_day_commission[]=$daily_minimum_amount;
                        $salary_calculation.="<div class='col-md-6'>";
                        $salary_calculation.="<p>Day Final Commission : \$$daily_minimum_amount (as miminum commission)</p>";
                        $salary_calculation.="</div>";    
                    }
                    else{
                        $all_day_commission[]=$total_commission;
                        $salary_calculation.="<div class='col-md-6'>";
                        $salary_calculation.="<p>Day Final Commission : $total_commission</p>";
                        $salary_calculation.="</div>";
                    }
                    
                }
                else{
                    $all_day_commission[]=$total_commission;
                }
                    
            }
            else{
                $salary_calculation.="<div class='col-md-12'>";
                $salary_calculation.="<p>No Commision for the date</p>";
                $salary_calculation.="</div>";
            }

            $salary_calculation.="</div>";
        }

        if(count($all_day_commission)>0){
            $salary_calculation.="<h4 class='page-header'> Week Total Commission </h4>";
            $salary_calculation.="<p>";

            for($i=0;$i<count($all_day_commission);$i++){
                if($i==count($all_day_commission)-1){
                    $salary_calculation.=$all_day_commission[$i]."=";
                }
                else{
                    $salary_calculation.=$all_day_commission[$i]."+";
                }
                $week_final_commission+=$all_day_commission[$i];
            }

            $salary_calculation.="$week_final_commission";
            
            $salary_calculation.="</p>";
        }

        if($technician_details->payment_type="weekly"){
            $weekly_minimum_amount=$technician_details->payment_amount;

            if((float)$week_final_commission <=$weekly_minimum_amount){
                $week_final_commission=$weekly_minimum_amount;
                $salary_calculation.="<p>Week Final Commission : \$$week_final_commission (as week miminum commission)</p>";
            }
            else{
                $salary_calculation.="<p>Week Final Commission : $week_final_commission</p>";
            }

        }

        return [$salary_calculation,$week_final_commission];
    }

    public function payment_summary($technician,$week_start_date,$week_end_date,$week,$week_final_commission){

        global $wpdb;

        $weekly_invoices=$wpdb->get_var("
            select count(*) as total_invoices 
            from {$wpdb->prefix}invoices
            where DATE(date) >='$week_start_date'
            and DATE(date) <='$week_end_date'
            and technician_id='$technician'
            and credit='technician'
        ");
        
        $weekly_monthly_quarterly_maintenance=$wpdb->get_var("
            select COALESCE(count(*),0) as total_contracts 
            from {$wpdb->prefix}maintenance_contract 
            where technician_id='$technician' 
            and DATE(date) >='$week_start_date' 
            and DATE(date) <='$week_end_date'
            and credit='technician'        
        ");

        $weekly_special_maintenance=$wpdb->get_var("
            select COALESCE(count(*),0) as total_contracts 
            from {$wpdb->prefix}special_contract 
            where technician_id='$technician' 
            and DATE(date_created) >='$week_start_date' 
            and DATE(date_created) <='$week_end_date'        
            and credit='technician'
        ");

        $weekly_commercial_maintenance=$wpdb->get_var("
            select COALESCE(count(*),0) as total_contracts 
            from {$wpdb->prefix}commercial_maintenance 
            where technician_id='$technician' 
            and DATE(date_created) >='$week_start_date' 
            and DATE(date_created) <='$week_end_date'        
            and credit='technician'
        ");

        $weekly_total_contracts=$weekly_monthly_quarterly_maintenance+$weekly_special_maintenance+$weekly_commercial_maintenance;

        // calculate total quotes for the week
        $weekly_residential_quotes=$wpdb->get_var("
            select count(*) as total_quotes 
            from {$wpdb->prefix}quotesheet 
            where DATE(date)>='$week_start_date' 
            and DATE(date)<='$week_end_date' 
            and technician_id='$technician'
            and credit='technician'        
        ");

        $weekly_commercial_quotes=$wpdb->get_var("
            select count(*) as total_quotes 
            from {$wpdb->prefix}commercial_quotesheet 
            where DATE(date)>='$week_start_date' 
            and DATE(date)<='$week_end_date' 
            and technician_id='$technician'
            and credit='technician'
        ");

        $weekly_total_quotes=$weekly_residential_quotes+$weekly_commercial_quotes;

        $payment_summary_html="
        <table class='table table-striped table-hover'>
            <tbody>
                <tr>
                    <th>Total Invoices</th>
                    <td>$weekly_invoices</td>
                </tr>
                <tr>
                    <th>Total Maintenance Contracts</th>
                    <td>$weekly_total_contracts</td>
                </tr>
                <tr>
                    <th>Total Quotes</th>
                    <td>$weekly_total_quotes</td>
                </tr>
                <tr>
                    <th>Technician Commission</th>
                    <td>".$this->beautify_amount_field($week_final_commission)."</td>
                </tr>
            </tbody>
        </table>
        ";

        return $payment_summary_html;
    }

}

new TechnicianPay();