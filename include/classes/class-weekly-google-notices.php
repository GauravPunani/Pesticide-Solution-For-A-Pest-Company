<?php

class google_weekly_notices extends GamFunctions{

    private $week_start_date;
    private $week_end_date;
    private $google_query;
    private $week;

    function __construct(){

    }

    public function set_week_dates($week){
        $this->week=$week;
        $this->week_start_date=date('Y-m-d',strtotime('tuesday last week',strtotime($week)));
        $this->week_end_date=date(('Y-m-d'),strtotime('monday this week',strtotime($week)));
        return $this;
    }

    public function check_for_all_invoices_assigned_callrail(){
        global $wpdb;
        $records=$wpdb->get_var("select COUNT(*) from wp_invoices where (callrail_id IS NULL or callrail_id='unknown' or callrail_id='') and DATE(date) >= '$this->week_start_date' and DATE(date) <= '$this->week_end_date' ");
        if($records>0){
            $notice="Sorry, System was not able to generate weekly google ads related alerts for date range ".date('d M Y',strtotime($this->week_start_date))." - ".date('d M Y',strtotime($this->week_end_date))." as  one or more locations have invoices pending to be assigned callrail number (Total ".$records."). <a href='".admin_url('admin.php?page=weekly-ads-alert')."'>Check Details</a>";
            // echo $notice;
            $this->push_notice($notice,'weekly_notice_error','error','',$this->week);
            exit();
        }
        else{
            return $this;
        }


    }

    public function generate_query_location_wise($accout='google',$location){
        $query="select SUM(wp_googleads_weekly_data.total_cost) as total_cost , SUM(wp_invoices.total_amount) as total_revenue 
        from wp_googleads_weekly_data

        left join wp_callrail 
        on wp_googleads_weekly_data.tracking_id=wp_callrail.id

        left join wp_invoices
        on wp_googleads_weekly_data.tracking_id=wp_invoices.callrail_id
        
        where wp_googleads_weekly_data.start_date >='$this->week_start_date'
        and wp_googleads_weekly_data.end_date<='$this->week_end_date'
        and wp_callrail.tracking_name like '%$accout%'
        and wp_callrail.actual_location='$location'";
        return $query;
    }

    public function generate_overall_spent_query($accout='google'){

        $query="select SUM(wp_googleads_weekly_data.total_cost) as total_cost , SUM(wp_invoices.total_amount) as total_revenue from wp_googleads_weekly_data

        left join wp_callrail 
        on wp_googleads_weekly_data.tracking_id=wp_callrail.id

        left join wp_invoices 
        on wp_googleads_weekly_data.tracking_id=wp_invoices.callrail_id

        where wp_googleads_weekly_data.start_date >='$this->week_start_date' 
        and wp_googleads_weekly_data.end_date<='$this->week_end_date' 
        and wp_callrail.tracking_name like '%$accout%'";
        return $query;

    }

    public function push_google_location_wise_notices(){
        global $wpdb;

        $locations=(new GamFunctions)->get_all_locations();

        if(is_array($locations) && count($locations)>0){
            foreach ($locations as $key => $location) {

                // fetch data for google ac location wise reveune 
                $google_query=$this->generate_query_location_wise('google',$location->slug);
                $google_revenue=$wpdb->get_row($google_query);

                if($google_revenue){
                    if($google_revenue->total_cost!=0){
                        if($google_revenue->total_cost >= $google_revenue->total_revenue){
                            $total_cost=number_format((float)$google_revenue->total_cost, 2, '.', '');
                            $total_revenue=number_format((float)$google_revenue->total_revenue, 2, '.', '');
                            $notice="<b>Google $location->location_name</b> ads spent was <b>\$$total_cost</b> but we generated revenue of <b>\$$total_revenue</b> only between dates <b>".date('d M',strtotime($this->week_start_date)) ." - ". date('d M',strtotime($this->week_end_date))."</b>";
                            
                            // echo $notice."<br>";
                            $this->push_notice($notice,'google_weekly_spent_notice','error',$location->slug,$this->week);
                        }
    
                    }
    
                }

                // fetch data for bing ac location wise reveune 
                $bing_query=$this->generate_query_location_wise('bing',$location->slug);
                $bing_revenue=$wpdb->get_row($bing_query);

                if($bing_revenue){
                    if($bing_revenue->total_cost!=0){
                        if($bing_revenue->total_cost >= $bing_revenue->total_revenue){
                            $total_cost=number_format((float)$bing_revenue->total_cost, 2, '.', '');
                            $total_revenue=number_format((float)$bing_revenue->total_revenue, 2, '.', '');
                            $notice="<b>Bing $location->location_name</b> ads spent was <b>\$$total_cost</b> but we generated revenue of <b>\$$total_revenue</b> only between dates <b>".date('d M',strtotime($this->week_start_date)) ." - ". date('d M',strtotime($this->week_end_date))."</b>";
                            
                            // echo $notice;
                            $this->push_notice($notice,'bing_weekly_spent_notice','error',$location->slug,$this->week);
                        }
    
                    }
    
                }


        
            }
        }

        return $this;
    }

    // THIS METHOD CALCULATE OVERALL SPENT FOR BOTH GOOGLE AND BING 
    public function google_overall_notice(){
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
                
                    // echo $notice."<br>";
                    $this->push_notice($notice,'google_weekly_spent_notice','error','global',$this->week);
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
                    
                    // echo $notice;
                    $this->push_notice($notice,'bing_weekly_spent_notice','error','global',$this->week);
                }
            }
        }

        return $this;

    }

    public function cost_per_call_notice(){

        global $wpdb;

        $cost_per_call_query="select wp_googleads_weekly_data.* ,wp_callrail.tracking_name from wp_googleads_weekly_data left join wp_callrail on wp_googleads_weekly_data.tracking_id=wp_callrail.id where wp_googleads_weekly_data.start_date >= '$this->week_start_date' and wp_googleads_weekly_data.end_date <='$this->week_end_date'";

        $records=$wpdb->get_results($cost_per_call_query);

        // if cost is more notice 
        if(is_array($records) && count($records)>0){
            foreach ($records as $key => $value) {

                $cost_per_call_limit=75;

                if(stripos($value->tracking_name,'bed bug')!==false || stripos($value->tracking_name,'bed bugs')!==false){
                    $cost_per_call_limit=100;
                }

                if($value->cost_per_call > $cost_per_call_limit){

                    $cost_per_call=number_format((float)$value->cost_per_call, 2, '.', '');

                    if($value->total_calls==0 || $value->total_calls=="0"){
                        $notice="Cost for campaign <b>$value->tracking_name</b> exceeded the weekly budget of \$$cost_per_call_limit and reached to <b>\$$cost_per_call</b> but you did not recived any call between dates <b>".date('d M',strtotime($this->week_start_date)) ." - ". date('d M',strtotime($this->week_end_date))."</b>";
                    }else{
                        $notice="Cost Per Call for the campaign <b>$value->tracking_name</b> had exceeded the weekly budget of \$$cost_per_call_limit and reached to <b>\$$cost_per_call</b> per call between dates <b>".date('d M',strtotime($this->week_start_date)) ." - ". date('d M',strtotime($this->week_end_date))."</b>";

                    }

                    // echo $notice;
                    $this->push_notice($notice,'cost_per_call','error',$value->tracking_id,$this->week);

                }

                $total_revenue=$wpdb->get_row("select SUM(total_amount) as total_revenue from wp_invoices where callrail_id='$value->tracking_id' and DATE(date) >= '$this->week_start_date' and DATE(date) <= '$this->week_end_date'");

                // if revnue is less notice 
                if((float)$value->total_cost >0 && $total_revenue->total_revenue <= $value->total_cost){

                    $total_cost=number_format((float)$value->total_cost, 2, '.', '');
                    $total_revenue=number_format((float)$total_revenue->total_revenue, 2, '.', '');

                    $notice="Weekly cost for campaign <b>$value->tracking_name</b> is <b>\$$total_cost</b> but the revenue generated from that campaign is only <b>\$$total_revenue</b> between dates <b>".date('d M',strtotime($this->week_start_date)) ." - ". date('d M',strtotime($this->week_end_date))."</b>";

                    // echo $notice."<br>";
                    $this->push_notice($notice,'cost-vs-revenue','error',$value->tracking_id,$this->week);

                }


            }
        }


        return $this;

    }

    public function check_for_no_sale_in_week(){

        global $wpdb;

        $tracking_nos=(new Callrail_new)->get_all_tracking_no();

        if(is_array($tracking_nos) && count($tracking_nos)>0){
            foreach ($tracking_nos as $tracking_no) {
                $total_invoices=$wpdb->get_var("select COUNT(*) from {$wpdb->prefix}invoices where callrail_id='$tracking_no->id' and DATE(date) >= '$this->week_start_date' and DATE(date) <= '$this->week_end_date'");

                if($total_invoices <=0){
                    $notice="Ads Campaign <b>$tracking_no->tracking_name</b> did't generate any sales between dates <b> ".date('d M Y',strtotime($this->week_start_date))." - ".date('d M Y',strtotime($this->week_end_date))."</b>";
                    
                    // echo $notice;
                    $this->push_notice($notice,'no_sales_in_week','error',$tracking_no->id,$this->week);
                }
            }
        }

        return $this;
    }

    public function update_weekly_alert_status(){
        global $wpdb;

        if(empty($this->week)){
            $this->week=date('Y-\WW');
        }
        
        $wpdb->update($wpdb->prefix."weekly_alert_status",['status'=>'true'],['week'=>$this->week]);

        return $this;
    }

    public function generate_notices(){
        $this->set_week_dates(date('Y-\WW'))
                ->check_for_all_invoices_assigned_callrail()
                ->push_google_location_wise_notices()
                ->google_overall_notice()
                ->cost_per_call_notice()
                ->check_for_no_sale_in_week()
                ->update_weekly_alert_status();        
    }


}
