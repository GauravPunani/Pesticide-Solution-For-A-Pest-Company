<?php

class WeeklyReport extends GamFunctions{

    private $week_start_date;
    private $week_end_date;
    private $google_query;
    private $week;
    private $report_data=[];
    private $branch;
    private $trackers;
    private $branch_weekly_notices=[];
    private $final_html;
    private $invoice_revenue;
    private $total_spent;
    private $tracking_ids=[];
    private $inactive_campaigns=[];

    public function set_date_by_week($week){
        $this->week_start_date=date('Y-m-d',strtotime('tuesday last week',strtotime($week)));
        $this->week_end_date=date(('Y-m-d'),strtotime('monday this week',strtotime($week)));
        return $this;
    }

    public function set_date_by_date_range($from_date,$to_date){
        $this->week_start_date=$from_date;
        $this->week_end_date=$to_date;
        return $this;
    }

    public function checks_before_generating_report(){
        global $wpdb;

        $branch_id = (new Branches)->getBranchIdBySlug($this->branch);

        // check if any invoice is pending to linked first 
        $records=$wpdb->get_var("
            select COUNT(*) 
            from wp_invoices 
            where (callrail_id IS NULL or callrail_id='unknown' or callrail_id='') 
            and DATE(date) >= '$this->week_start_date' 
            and DATE(date) <= '$this->week_end_date' 
            and branch_id = '$branch_id'
        ");

        if($records>0){
            $notice="Sorry, System was not able to generate weekly google ads related alerts for date range ".date('d M Y',strtotime($this->week_start_date))." - ".date('d M Y',strtotime($this->week_end_date))." as  one or more locations have invoices pending to be assigned callrail number (Total ".$records."). <a href='".admin_url('admin.php?page=weekly-ads-alert')."'>Check Details</a>";
            echo $notice;wp_die();
            exit();
        }

        // check if any pending ads spent is there to be linked first before generating the report
        $unknown_ads_spents=$wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}unknown_spends
            where DATE(date)>='$this->week_start_date'
            and DATE(date)<='$this->week_end_date'
        ");

        if($unknown_ads_spents){
            $unknown_ads_spents_page_link=admin_url('admin.php?page=ads-spent&ads_tab=unknown-spents');
            echo "There are unknown ads spent in the system for the date range, please link them first. <a href='$unknown_ads_spents_page_link'>Check Unknown Spents</a>";
            wp_die();
            exit();
        }

        return $this;

    }

    public function generate_overall_spent_query($account){

        $query="select SUM(wp_googleads_weekly_data.total_cost) as total_cost , SUM(wp_invoices.total_amount) as total_revenue from wp_googleads_weekly_data

        left join wp_callrail 
        on wp_googleads_weekly_data.tracking_id=wp_callrail.id

        left join wp_invoices 
        on wp_googleads_weekly_data.tracking_id=wp_invoices.callrail_id

        where wp_googleads_weekly_data.start_date >='$this->week_start_date' 
        and wp_googleads_weekly_data.end_date<='$this->week_end_date' 
        and wp_callrail.tracking_name like '%$account%'";
        return $query;

    }

    // get all trackers with revenu,total cost, total calls
    public function get_all_trackers(){
        global $wpdb;

        $query="
        SELECT C.id,C.tracking_name,C.tracking_phone_no,SUM(G.total_unique_calls) as total_calls,SUM(G.total_cost) as total_cost,(SUM(G.total_cost)/SUM(G.total_unique_calls)) as cost_per_call

        ,(select COALESCE(sum(total_amount),0) from {$wpdb->prefix}invoices where callrail_id=C.id and DATE(date) >='$this->week_start_date' and DATE(date) <='$this->week_end_date' ) as total_revenue

        ,(select COALESCE(count(*),0) from {$wpdb->prefix}invoices where callrail_id=C.id and DATE(date) >='$this->week_start_date' and DATE(date) <='$this->week_end_date' ) as total_invoices

        ,(select COALESCE(count(*),0) from {$wpdb->prefix}quotesheet where callrail_id=C.id and DATE(date) >='$this->week_start_date' and DATE(date) <='$this->week_end_date' ) as residential_quotes
        
        ,(select COALESCE(sum(total_cost),0) from {$wpdb->prefix}quotesheet where callrail_id=C.id and DATE(date) >='$this->week_start_date' and DATE(date) <='$this->week_end_date' ) as residential_cost
        
        ,(select COALESCE(count(*),0) from {$wpdb->prefix}commercial_quotesheet where callrail_id=C.id and DATE(date) >='$this->week_start_date' and DATE(date) <='$this->week_end_date' ) as commercial_quotes
        
        ,(select COALESCE(sum(cost_per_visit),0) from {$wpdb->prefix}commercial_quotesheet where callrail_id=C.id and DATE(date) >='$this->week_start_date' and DATE(date) <='$this->week_end_date' ) as commercial_cost

        ,(select COALESCE(count(*),0) from {$wpdb->prefix}maintenance_contract where callrail_id=C.id and DATE(date) >='$this->week_start_date' and type='monthly' and DATE(date) <='$this->week_end_date' ) as monthly_total
        
        ,(select COALESCE(sum(total_cost),0) from {$wpdb->prefix}maintenance_contract where callrail_id=C.id and type='monthly' and DATE(date) >='$this->week_start_date' and DATE(date) <='$this->week_end_date' ) as monthly_cost

        ,(select COALESCE(count(*),0) from {$wpdb->prefix}maintenance_contract where callrail_id=C.id and DATE(date) >='$this->week_start_date' and type='quarterly' and DATE(date) <='$this->week_end_date' ) as quarterly_total
        
        ,(select COALESCE(sum(total_cost),0) from {$wpdb->prefix}maintenance_contract where callrail_id=C.id and type='quarterly' and DATE(date) >='$this->week_start_date' and DATE(date) <='$this->week_end_date' ) as quartely_cost
        
        ,(select COALESCE(count(*),0) from {$wpdb->prefix}special_contract where callrail_id=C.id and DATE(date_created) >='$this->week_start_date' and DATE(date_created) <='$this->week_end_date' ) as special_total
        
        ,(select COALESCE(sum(cost),0) from {$wpdb->prefix}special_contract where callrail_id=C.id and DATE(date_created) >='$this->week_start_date' and DATE(date_created) <='$this->week_end_date' ) as special_cost
        
        ,(select COALESCE(count(*),0) from {$wpdb->prefix}commercial_maintenance where callrail_id=C.id and DATE(date_created) >='$this->week_start_date' and DATE(date_created) <='$this->week_end_date' ) as commercial_total
        
        ,(select COALESCE(sum(cost_per_visit),0) from {$wpdb->prefix}commercial_maintenance where callrail_id=C.id and DATE(date_created) >='$this->week_start_date' and DATE(date_created) <='$this->week_end_date' ) as commercial_cost
        
        from {$wpdb->prefix}callrail C
        left JOIN {$wpdb->prefix}googleads_daily_data G

        on G.tracking_id=C.id
        and DATE(G.date)>='$this->week_start_date'
        and DATE(G.date)<='$this->week_end_date'
        where C.actual_location='$this->branch'
        ";

        if(is_array($this->tracking_ids) && count($this->tracking_ids)>0){
            $concatenated_ids="'" . implode ( "', '", $this->tracking_ids ) . "'";
            $query.=" and C.id IN ($concatenated_ids)";
        }

        $query.=" GROUP BY C.tracking_name";

        $this->trackers=$wpdb->get_results($query);

        // echo "<pre>";print_r($this->trackers);wp_die();

        return $this;
    }

    public function assign_fix_spent_to_trackers(){

        if(is_array($this->trackers) && count($this->trackers)>0){
            foreach ($this->trackers as $key=>$tracker) {
                // if pestnet numberr, then fixe $375 per week 
                if(stripos($tracker->tracking_name,'pestnet')!==false || stripos($tracker->tracking_name,'pest net')!==false){
                    $this->trackers[$key]->total_cost=375;
                }
                elseif(stripos($tracker->tracking_name,'hassan')!==false){
                    // if hassan number then 13% of total revenue 
                    if((int)$tracker->total_revenue > 0){
                        $this->trackers[$key]->total_cost=((int)$tracker->total_revenue*13)/100;
                    }
                }
            }            
        }

        return $this;
    }

    public function filter_trackers_by_active_inactive(){
        
        if(is_array($this->trackers) && count($this->trackers)>0){
            foreach ($this->trackers as $index=>$tracker) {
                if(empty($tracker->total_cost) || $tracker->total_cost=="0"){
                    $this->inactive_campaigns[]=$tracker;
                    unset($this->trackers[$index]);
                }
            }
        }

        // echo "<pre>";print_r($this->trackers);wp_die();

        return $this;
    }


    // this method caluclate spends and revenue for location
    public function push_location_wise_notices(){
        global $wpdb;

        $this->invoice_revenue=0;
        foreach ($this->trackers as $key => $tracker) {
            $this->invoice_revenue+=$tracker->total_revenue;
        }

        $this->total_spent=0;
        foreach ($this->trackers as $key => $tracker) {
            $this->total_spent+=$tracker->total_cost;
        }

        if($this->total_spent!=0){
            if($this->total_spent >= $this->invoice_revenue){
                $total_cost=number_format((float)$this->total_spent, 2, '.', '');
                $total_revenue=number_format((float)$this->invoice_revenue, 2, '.', '');
                $branch_name=(new GamFunctions)->beautify_string($this->branch);
                $notice="<b>$branch_name </b> ads spent was <b>\$$total_cost</b> but we generated revenue of <b>\$$total_revenue</b> only between dates <b>".date('d M',strtotime($this->week_start_date)) ." - ". date('d M',strtotime($this->week_end_date))."</b>";

                $this->branch_weekly_notices[]=$notice;
            }

        }

        return $this;
    }

    // THIS METHOD CALCULATE OVERALL SPENT FOR BOTH GOOGLE AND BING 
    public function overall_notice(){
        global $wpdb;

        // FOR GOOGLE OVER ALL SPENT 
        $google_query=$this->generate_overall_spent_query('google');
        $google_result=$wpdb->get_row($google_query);
        
        if($google_result){
            if($google_result->total_cost >= $google_result->total_revenue){
                $total_cost=number_format((float)$google_result->total_cost, 2, '.', '');
                $total_revenue=number_format((float)$google_result->total_revenue, 2, '.', '');

                if($total_cost>0){
                    $notice="<b>Google overall </b> ads spent was <b>\$$total_cost</b> but overall google generated revenue was <b>\$$total_revenue</b> only between dates <b>".date('d M',strtotime($this->week_start_date)) ." - ". date('d M',strtotime($this->week_end_date))."</b>";
                
                    $this->report_data['google_weekly_spent_notice'][]=$notice;

                }
            }
        }

        // FOR BING OVER ALL SPENT 
        $bing_query=$this->generate_overall_spent_query('bing');
        $bing_result=$wpdb->get_row($bing_query);

        if($bing_result){
            if($bing_result->total_cost >= $bing_result->total_revenue){
                $total_cost=number_format((float)$bing_result->total_cost, 2, '.', '');
                $total_revenue=number_format((float)$bing_result->total_revenue, 2, '.', '');

                if($total_cost>0){
                    $notice="<b>Bing overall </b> ads spent was <b>\$$total_cost</b> but overall bing generated revenue was <b>\$$total_revenue</b> only between dates <b>".date('d M',strtotime($this->week_start_date)) ." - ". date('d M',strtotime($this->week_end_date))."</b>";
                    
                    $this->report_data['bing_weekly_spent_notice'][]=$notice;
                }
            }
        }

        return $this;

    }

    public function cost_per_call_notice(){
        global $wpdb;

        // if cost is more notice 
        if(is_array($this->trackers) && count($this->trackers)>0){
            foreach ($this->trackers as $key=>$tracker) {

                $cost_per_call_limit=75;

                if(stripos($tracker->tracking_name,'bed bug')!==false || stripos($tracker->tracking_name,'bed bugs')!==false){
                    $cost_per_call_limit=100;
                }

                if($tracker->cost_per_call > $cost_per_call_limit){

                    $cost_per_call=number_format((float)$tracker->cost_per_call, 2, '.', '');

                    if($tracker->total_calls==0 || $tracker->total_calls=="0"){
                        $notice="Cost for campaign <b>$tracker->tracking_name</b> exceeded the weekly budget of \$$cost_per_call_limit and reached to <b>\$$cost_per_call</b> but you did not recived any call between dates <b>".date('d M',strtotime($this->week_start_date)) ." - ". date('d M',strtotime($this->week_end_date))."</b>";
                    }else{
                        $notice="Cost Per Call for the campaign <b>$tracker->tracking_name</b> had exceeded the weekly budget of \$$cost_per_call_limit and reached to <b>\$$cost_per_call</b> per call between dates <b>".date('d M',strtotime($this->week_start_date)) ." - ". date('d M',strtotime($this->week_end_date))."</b>";

                    }

                    $this->trackers[$key]->notices[]=$notice;

                }

                // if revnue is less notice 
                if((float)$tracker->total_cost >0 && $tracker->total_revenue <= $tracker->total_cost){

                    $total_cost=number_format((float)$tracker->total_cost, 2, '.', '');
                    $total_revenue=number_format((float)$tracker->total_revenue, 2, '.', '');

                    $notice="Weekly cost for campaign <b>$tracker->tracking_name</b> is <b>\$$total_cost</b> but the revenue generated from that campaign is only <b>\$$total_revenue</b> between dates <b>".date('d M',strtotime($this->week_start_date)) ." - ". date('d M',strtotime($this->week_end_date))."</b>";

                    $this->trackers[$key]->notices[]=$notice;

                }


            }
        }

        return $this;

    }

    public function check_for_no_sale_in_week(){

        if(is_array($this->trackers) && count($this->trackers)>0){
            foreach ($this->trackers as $key=>$tracker) {
                if($tracker->total_invoices<=0){
                    $notice="Ads Campaign <b>$tracker->tracking_name</b> did't generate any sales between dates <b> ".date('d M Y',strtotime($this->week_start_date))." - ".date('d M Y',strtotime($this->week_end_date))."</b>";
                    $this->trackers[$key]->notices[]=$notice;   
                }
            }
        }

        return $this;
    }

    public function weekly_report_template(){
        global $wpdb;

        $final_html='';
        $branch_name=$this->beautify_string($this->branch);

        $previous_week_start_date=date('Y-m-d',strtotime('last week tuesday',strtotime($this->week_start_date)));
        $previous_week_end_date=date('Y-m-d',strtotime('last week monday',strtotime($this->week_end_date)));

        $previous_week_date_condition="and DATE(date_created) >= '$previous_week_start_date' and DATE(date_created) <= '$previous_week_end_date'";

        $date_condition="and DATE(date) >= '$this->week_start_date' and DATE(date) <= '$this->week_end_date'";

        $date_created_condition="and DATE(date_created) >= '$this->week_start_date' and DATE(date_created) <= '$this->week_end_date'";

        $campagin_data='';

        // if there is atleast one tracking number for the branc, then only calculate data
        if(is_array($this->trackers) && count($this->trackers)>0){

            $temp_ids=array_column($this->trackers,'id');
            // echo "<pre>";print_r($temp_ids);wp_die();

            $concatenated_ids="'" . implode ( "', '", $temp_ids ) . "'";
            $where_tracking_ids="where callrail_id IN ($concatenated_ids)"; 

            $total_invoices=$wpdb->get_var("select COUNT(*) as total_invoices from {$wpdb->prefix}invoices  $where_tracking_ids $date_condition");
        
            $residential_quotesheet=$wpdb->get_row("select COUNT(*) as total_quotes, SUM(total_cost) as total_cost from {$wpdb->prefix}quotesheet  $where_tracking_ids $date_condition");
            
            $commercial_quotesheet=$wpdb->get_row("select COUNT(*) as total_quotes, SUM(cost_per_visit) as total_cost from {$wpdb->prefix}commercial_quotesheet  $where_tracking_ids $date_condition");
    
            $total_quotes=$residential_quotesheet->total_quotes+$commercial_quotesheet->total_quotes;
            
            $monthly_maintenance=$wpdb->get_row("select COUNT(*) as total_contracts, SUM(total_cost) as total_cost from {$wpdb->prefix}maintenance_contract  $where_tracking_ids and type='monthly' $date_condition");
            
            $quarterly_maintenance=$wpdb->get_row("select COUNT(*) as total_contracts, SUM(total_cost) as total_cost from {$wpdb->prefix}maintenance_contract  $where_tracking_ids and type='quarterly' $date_condition");
            
            $commercial_maintenance=$wpdb->get_row("select COUNT(*) as total_contracts, SUM(cost_per_visit) as total_cost from {$wpdb->prefix}commercial_maintenance  $where_tracking_ids $date_created_condition");
            
            $special_maintenance=$wpdb->get_row("select COUNT(*) as total_contracts, SUM(cost) as total_cost from {$wpdb->prefix}special_contract  $where_tracking_ids $date_created_condition");
    
            $total_maintenance_contracts=$monthly_maintenance->total_contracts+$quarterly_maintenance->total_contracts+$commercial_maintenance->total_contracts+$special_maintenance->total_contracts;
    
            // quotes revenue 
            $total_quotes_revenue=$residential_quotesheet->total_cost+$commercial_quotesheet->total_cost;
    
            // mainteannce revenue 
            $total_mainteance_revenue=$monthly_maintenance->total_cost+$quarterly_maintenance->total_cost+$commercial_maintenance->total_cost+$special_maintenance->total_cost;

            $pdf_html=[];
                    
            $location_name="<h3 class='text-center'>".$branch_name."</h3>";

            foreach ($this->trackers as $key=>$tracker) {

                $ads_spent_amount=(new GamFunctions)->beautify_amount_field($tracker->total_cost);
                $total_revenue=(new GamFunctions)->beautify_amount_field($tracker->total_revenue);
                // echo $total_revenue;wp_die();

                $temp_data="<table class='table table-striped table-hover'>
                                <tbody>
                                    <tr>
                                        <th>Spends</th>
                                        <td>$ads_spent_amount</td>
                                        <th>Total Unique Calls</th>
                                        <td>$tracker->total_calls</td>
                                    </tr>
                                    <tr>
                                        <th>Total Invoices</th>
                                        <td>$tracker->total_invoices</td>
                                        <th>Total Invoices Amount</th>
                                        <td>$total_revenue</td>
                                    </tr>
                                    <tr>
                                        <th>Total Residential Quotes</th>
                                        <td>$tracker->residential_quotes</td>
                                        <th>Residential Quotes Amount </th>
                                        <td>".(new GamFunctions)->beautify_amount_field($tracker->residential_cost)."</td>
                                    </tr>
                                    <tr>
                                        <th>Total Commercial Quotes</th>
                                        <td>$tracker->commercial_quotes</td>
                                        <th>Commercial Quotes Amount </th>
                                        <td>".(new GamFunctions)->beautify_amount_field($tracker->commercial_cost)."</td>
                                    </tr>
                                    <tr>
                                        <th>Total Monthly Contracts</th>
                                        <td>$tracker->monthly_total</td>
                                        <th>Monthly Contracts Amount </th>
                                        <td>".(new GamFunctions)->beautify_amount_field($tracker->monthly_cost)."</td>
                                    </tr>
                                    <tr>
                                        <th>Total Quarterly Contracts</th>
                                        <td>$tracker->quarterly_total</td>
                                        <th>Quarterly Contracts Amount </th>
                                        <td>".(new GamFunctions)->beautify_amount_field($tracker->quartely_cost)."</td>
                                    </tr>
                                    <tr>
                                        <th>Total Special Contracts</th>
                                        <td>$tracker->special_total</td>
                                        <th>Special Contracts Amount </th>
                                        <td>".(new GamFunctions)->beautify_amount_field($tracker->special_cost)."</td>
                                    </tr>
                                    <tr>
                                        <th>Total Commercial Contracts</th>
                                        <td>$tracker->commercial_total</td>
                                        <th>Commercial Contracts Amount </th>
                                        <td>".(new GamFunctions)->beautify_amount_field($tracker->commercial_cost)."</td>
                                    </tr>
                                </tbody>
                            </table>
                ";


                $trackers_data[$key]="<h3>$tracker->tracking_name</h3>";
                $trackers_data[$key].=$temp_data;

                $done_notices=[];
                if(array_key_exists('notices',(array)$tracker)){
                    if(is_array($tracker->notices) && count($tracker->notices)>0){
                        $trackers_data[$key].="<ul>";
                        foreach ($tracker->notices as $notice_key => $notice) {
                            $trackers_data[$key].="<li class='text-danger'>$notice</li>";                            
                        }
                        $trackers_data[$key].="</ul>";
                        // echo "<pre>";print_r($done_notices);wp_die();
                    }
                    else{
                        $trackers_data[$key].="<p>No Alert </p>";
                    }    
                }
            }
    
            $campagin_data=implode(' ',$trackers_data);
        }
        else{
            $campagin_data='No tracker Found for the location';
        }

        $branch_summary="<h3 class='text-center'>".$branch_name." Summary</h3>";

        $invoice_revenue=$this->beautify_amount_field($this->invoice_revenue);
        $quotes_amount=$this->beautify_amount_field($total_quotes_revenue);
        $maintenance_amount=$this->beautify_amount_field($total_mainteance_revenue);
        $google_spends=$this->beautify_amount_field($this->total_spent);

        $branch_summary.="<table class='text-left table table-striped table-hover'>
                            <tbody>
                                <tr>
                                    <th>Total Invoices</th>
                                    <td>$total_invoices</td>                                                        
                                </tr>
                                <tr>
                                    <th>Total Quotesheets</th>
                                    <td>$total_quotes</td>                                                        
                                </tr>
                                <tr>
                                    <th>Total Maintenance Contracts</th>
                                    <td>$total_maintenance_contracts</td>                                                        
                                </tr>
                                <tr>
                                    <th>Invoice Revenue</th>
                                    <td>$invoice_revenue</td>                                                        
                                </tr>
                                <tr>
                                    <th>Quotes Amount</th>
                                    <td>$quotes_amount</td>                                                        

                                </tr>
                                <tr>
                                    <th>Maintenance Amount</th>
                                    <td>$maintenance_amount</td>                                                        
                                </tr>
                                <tr>
                                    <th>Total Ads Spent</th>
                                    <td>$google_spends</td>                                                        
                                </tr>
                            </tbody>
                        </table>";

        // list incative campaigns
        $inactive_campaigns="";
        if(is_array($this->inactive_campaigns) && count($this->inactive_campaigns)>0){
            $inactive_campaigns="<h3 class='page-header'>Inactive Campaigns</h3>";
            $inactive_campaigns.="<ul>";
            foreach ($this->inactive_campaigns as $campaign) {
                $inactive_campaigns.="<li>$campaign->tracking_name - $campaign->tracking_phone_no</li>";
            }    
            $inactive_campaigns.="</ul>";    
        }

        $branch_specific_notices='';
        if(is_array($this->branch_weekly_notices) && count($this->branch_weekly_notices)>0){
            $branch_specific_notices.="<h3 class='text-center'>$branch_name Specific Notices</h3>";
            $branch_specific_notices.="<ul>";
                foreach ($this->branch_weekly_notices as $key => $notice) {
                    $branch_specific_notices.="<li class='text-danger'>$notice</li>";
                }
            $branch_specific_notices.="</ul>";
        }
                
        // inactive technician alerts 
        $inactive_technicians_notices=$wpdb->get_results("select * from {$wpdb->prefix}notices where callrail_id='$this->branch' and type='inactive_technicians' $date_created_condition ");        

        $branch_inactive_techs='';
        if(is_array($inactive_technicians_notices) && count($inactive_technicians_notices)>0){
            $branch_inactive_techs.="<h3 class='text-center'>Inactive Technicians</h3>";
            $branch_inactive_techs.="<ul>";
            foreach ($inactive_technicians_notices as $key => $notice) {
                $branch_inactive_techs.="<li>$notice->notice</li>";
            }
            $branch_inactive_techs.="</ul>";
        }        

        $this->final_html=$location_name.$campagin_data.$inactive_campaigns.$branch_specific_notices.$branch_inactive_techs.$branch_summary;

        return $this;
    }

    public function generate_report($date=[],$branch,$tracking_ids=[]){

        $this->branch=$branch;
        $this->tracking_ids=$tracking_ids;

        if($date['type']=="week"){
            $this->set_date_by_week($date['week']);
        }
        else{
            $this->set_date_by_date_range($date['from_date'],$date['to_date']);
        }

        $this->checks_before_generating_report()
            ->get_all_trackers()
            ->assign_fix_spent_to_trackers()
            ->filter_trackers_by_active_inactive()
            ->push_location_wise_notices()
            ->cost_per_call_notice()
            ->check_for_no_sale_in_week()
            ->weekly_report_template();

        return $this->final_html;
    }


}