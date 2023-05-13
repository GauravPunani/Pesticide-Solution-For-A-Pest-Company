<?php

class Pl_report extends GamFunctions{

    function __construct(){
        add_action('wp_ajax_generate_pl_report',array($this,'generate_pl_report'));
        add_action('wp_ajax_nopriv_generate_pl_report',array($this,'generate_pl_report'));

        add_action('admin_post_download_pl_report',array($this,'generate_pl_report'));
        add_action('admin_post_nopriv_download_pl_report',array($this,'generate_pl_report'));
    }

    public function generate_pl_report(){

        // check for nonce field
        $this->verify_nonce_field('generate_pl_report');

        if($_POST['date_type']=="date"){
            $from_date=$_POST['from_date'];
            $to_date=$_POST['to_date'];
        }
        else{
            $from_date=date('Y-m-d',strtotime('this monday',strtotime($_POST['week'])));
            $to_date=date(('Y-m-d'),strtotime('this sunday',strtotime($_POST['week'])));
        }


        $template='';

        if(isset($_POST['action']) && !empty($_POST['action']) && $_POST['action'] == 'download_pl_report'){
            $account = explode(',', $_POST['account']);
        }else{
            $account = $_POST['account'];
        }

        if($_POST['branch']=="all_branches"){
            $branches=(new Branches)->getAllBranches(false);
            if(is_array($branches) && count($branches)>0){
                foreach ($branches as $key => $branch) {
                    $template.=$this->pl_report_by_branch($branch->slug, $from_date, $to_date, $account);
                }
            }
        }
        else{
            $template=$this->pl_report_by_branch($_POST['branch'],$from_date,$to_date,$account);
        }

        if($_POST['action']=="generate_pl_report"){
            $download_btn=$this->pl_report_download_button($_POST['branch'],$from_date,$to_date,$_POST['page_url'],implode(',', $_POST['account']));
            echo $download_btn.$template;
            wp_die();    
        }
        else{
            // download the report
            $report_header=$this->pl_report_header();
            $report_footer=$this->pl_report_footer();
            $report_data=$report_header.$template.$report_footer;
            
            // load mpdf php sdk from vendor
            self::loadVendor();

            $upload_dir=wp_upload_dir();
            $mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
            $mpdf->WriteHTML($report_data);
            $branch_name=$this->beautify_string($_POST['branch']);
            $mpdf->Output("$branch_name P_L Report.pdf","D");

        }
    }

    public function pl_report_by_branch($branch,$from_date,$to_date,$account){
        global $wpdb;

        $branch_id = (new Branches)->getBranchIdBySlug($branch);

        // first get all trackers for the branch
        $invoices=$wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}invoices
            where DATE(date) >='$from_date'
            and DATE(date)<='$to_date'
            and (callrail_id IS NULL or callrail_id='' or callrail_id='unknown')
            and is_deleted != 1
            and branch_id = '$branch_id'
        ");
       
        if($invoices>0){
            // return the error to link all callrial number with invoices
            $invoice_url = admin_url("admin.php?page=invoice&branch_id=".$branch_id."&tab=unknown_leads&date_from=".$from_date."&date_to=".$to_date."");
            $message="<div class='text-danger'>There are invoices pending to be assigned callrail no between the date range for the branch $branch : <a href='$invoice_url' target=\"_blank\"><u>Click here to assign callrail no for pending invoices</u></a></div>";
            return $message;
        }

        // check if any pending ads spent is there to be linked first before generating the report
        $unknown_ads_spents=$wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}unknown_spends
            where DATE(date)>='$from_date'
            and DATE(date)<='$to_date'
        ");

        if($unknown_ads_spents){
            $unknown_ads_spents_page_link=admin_url('admin.php?page=ads-spent&ads_tab=unknown-spents');
            $message="There are unknown ads spent in the system for the date range, please link them first. <a href='$unknown_ads_spents_page_link'>Check Unknown Spents</a>";
            return $message;
        }
        
        $template = '';
        $pl_data = [];
        if (count($account) > 0) {
            foreach ($account as $ads_account) {
                // Google campaign ads
                if ($ads_account == "pest_control") {
                    $trackers = $wpdb->get_results("
                        select id,tracking_name 
                        from {$wpdb->prefix}callrail 
                        where actual_location='$branch' 
                        and tracking_name not like '%map%' 
                        and tracking_name not like '%bing%'                
                        and branch_parent='0'
                    ", ARRAY_A);

                    if (is_array($trackers) && count($trackers) > 0) {

                        $pl_data['total_spent'] = $wpdb->get_var("
                        select sum(G.total_cost) as total_cost
                        from {$wpdb->prefix}googleads_daily_data G
                        left join {$wpdb->prefix}callrail C
                        ON G.tracking_id=C.id
                        where C.actual_location='$branch'
                        and C.tracking_name not like '%map%'
                        and C.tracking_name not like '%bing%'
                        and C.branch_parent='0'
                        and DATE(G.date) >= '$from_date'
                        and DATE(G.date) <= '$to_date'
                    ");

                        $pl_data['total_revenue'] = $wpdb->get_var("
                        select sum(I.total_amount)
                        from {$wpdb->prefix}invoices I
                        left join {$wpdb->prefix}callrail C
                        ON I.callrail_id=C.id
                        where C.actual_location='$branch'
                        and C.tracking_name not like '%map%'
                        and C.tracking_name not like '%bing%'
                        and C.branch_parent='0'
                        and DATE(I.date) >= '$from_date'
                        and DATE(I.date) <= '$to_date'
                    ");

                        if ((float)$pl_data['total_revenue'] > (float)$pl_data['total_spent']) {
                            $pl_data['total_profit'] = (float)$pl_data['total_revenue'] - (float)$pl_data['total_spent'];
                        } else {
                            $pl_data['total_loss'] = (float)$pl_data['total_spent'] - (float)$pl_data['total_revenue'];
                        }

                        foreach ($trackers as $key => $data) {

                            // get the ads spent cost
                            $trackers[$key]['ads_spent'] = $wpdb->get_var("select sum(total_cost) from {$wpdb->prefix}googleads_daily_data where DATE(date) >='$from_date' and DATE(date)<='$to_date' and tracking_id='{$data['id']}'");

                            // get the invoice revenue
                            $trackers[$key]['invoice_amount'] = $wpdb->get_var("select SUM(total_amount) from {$wpdb->prefix}invoices where DATE(date) >='$from_date' and DATE(date)<='$to_date' and callrail_id='{$data['id']}' ");

                            // get the quotes amount

                            // get the maintenance amount
                        }

                        // SEPERATE PROFIT MAKING, LOSSS MAKING
                        foreach ($trackers as $key => $value) {

                            if (empty($value['ads_spent']) && empty($value['invoice_amount'])) {
                                $pl_data['not_doing_anything'][] = $value;
                            } else if ((float)$value['ads_spent'] >= (float)$value['invoice_amount']) {
                                $value['total_loss'] = (float)$value['ads_spent'] - (float)$value['invoice_amount'];
                                $pl_data['loss_making'][] = $value;
                            } else {
                                $value['total_profit'] = (float)$value['invoice_amount'] - (float)$value['ads_spent'];
                                $pl_data['profit_making'][] = $value;
                            }
                        }

                        $template .= $this->pl_report_body_pest_control($pl_data, $from_date, $to_date, $branch, $ads_account);
                        $pl_data = [];
                    } else {
                        // return error that no ads record found
                        $template .= "<div class='alert alert-danger'>No Campaign found for the location $branch</div>";
                    }
                }

                // Bing Ads
                if ($ads_account == "bing") {
                    $pl_data = $this->bing_pl_data($from_date, $to_date, $branch);
                    $template .= $this->pl_report_body_pest_control($pl_data, $from_date, $to_date, $branch, $ads_account);
                    $pl_data = [];
                }

                // Google maps ads
                if ($ads_account == "map_ads") {
                    $trackers = $wpdb->get_results("
                        select id,tracking_name 
                        from {$wpdb->prefix}callrail 
                        where actual_location='$branch' 
                        and tracking_name like '%map%' 
                        and branch_parent='0'
                    ", ARRAY_A);

                    $branch_parent_id = $wpdb->get_var("
                        select id
                        from {$wpdb->prefix}callrail
                        where actual_location='$branch'
                        and branch_parent='1'
                    ");

                    if (is_array($trackers) && count($trackers) > 0) {

                        $pl_data['total_spent'] = $wpdb->get_var("
                            select COALESCE(sum(G.total_cost),0) as total_cost
                            from {$wpdb->prefix}googleads_daily_data G
                            left join {$wpdb->prefix}callrail C
                            ON G.tracking_id=C.id
                            where C.actual_location='$branch'
                            and C.tracking_name like '%map%'
                            and C.branch_parent='0'
                            and DATE(G.date) >= '$from_date'
                            and DATE(G.date) <= '$to_date'
                        ");

                        $pl_data['seo_revenue'] = $wpdb->get_var("
                            select COALESCE(sum(I.total_amount),0)
                            from {$wpdb->prefix}invoices I
                            where I.callrail_id='$branch_parent_id'
                            and DATE(I.date) >= '$from_date'
                            and DATE(I.date) <= '$to_date'
                        ");

                        // echo "<pre>";print_r($pl_data);wp_die();

                        if ((float)$pl_data['seo_revenue'] > (float)$pl_data['total_spent']) {
                            $pl_data['total_profit'] = (float)$pl_data['seo_revenue'] - (float)$pl_data['total_spent'];
                        } else {
                            $pl_data['total_loss'] = (float)$pl_data['total_spent'] - (float)$pl_data['seo_revenue'];
                        }


                        foreach ($trackers as $key => $data) {

                            // get the ads spent cost
                            $trackers[$key]['ads_spent'] = $wpdb->get_var("select sum(total_cost) from {$wpdb->prefix}googleads_daily_data where DATE(date) >='$from_date' and DATE(date)<='$to_date' and tracking_id='{$data['id']}'");

                            // get the quotes amount

                            // get the maintenance amount
                        } 

                        // SEPERATE PROFIT MAKING, LOSSS MAKING
                        foreach ($trackers as $key => $value) {

                            if (empty($value['ads_spent'])) {
                                $pl_data['not_doing_anything'][] = $value;
                            } else {
                                $pl_data['campaigns'][] = $value;
                            }
                        }

                        //pdie($pl_data);

                        $template .= $this->pl_report_body_map_ads($pl_data, $from_date, $to_date, $branch, $ads_account);
                    } else {
                        // return error that no ads record found
                        $template .= "<div class='alert alert-danger'>No Campaign found for the location $branch</div>";
                    }
                }
            }
        }else{
            $template .= "<div class='alert alert-danger'>No Campaign found for the location $branch</div>";
        }
        return $template;
    }

    public function bing_pl_data(string $from_date, string $to_date, string $branch){
        global $wpdb;

        $trackers = $wpdb->get_results("
            select id,tracking_name 
            from {$wpdb->prefix}callrail 
            where actual_location = '$branch' 
            and tracking_name like '%bing%' 
            and branch_parent='0'
        ",ARRAY_A);

        if(count($trackers) <=0 ){
            return "<div class='text-danger'>No Campaign found for the location $branch</div>";
        }

        $pl_data['total_spent']=$wpdb->get_var("
            select sum(G.total_cost) as total_cost
            from {$wpdb->prefix}googleads_daily_data G
            left join {$wpdb->prefix}callrail C
            ON G.tracking_id=C.id
            where C.actual_location='$branch'
            and C.tracking_name like '%bing%'
            and C.branch_parent='0'
            and DATE(G.date) >= '$from_date'
            and DATE(G.date) <= '$to_date'
        ");

        $pl_data['total_revenue']=$wpdb->get_var("
            select sum(I.total_amount)
            from {$wpdb->prefix}invoices I
            left join {$wpdb->prefix}callrail C
            ON I.callrail_id=C.id
            where C.actual_location='$branch'
            and C.tracking_name like '%bing%'
            and C.branch_parent='0'
            and DATE(I.date) >= '$from_date'
            and DATE(I.date) <= '$to_date'
        ");

        if((float)$pl_data['total_revenue'] > (float)$pl_data['total_spent']){
            $pl_data['total_profit']=(float)$pl_data['total_revenue']-(float)$pl_data['total_spent'];
        }
        else{
            $pl_data['total_loss']=(float)$pl_data['total_spent']-(float)$pl_data['total_revenue'];
        }

        foreach ($trackers as $key => $data) {

            // get the ads spent cost
            $trackers[$key]['ads_spent'] = $wpdb->get_var("
                select sum(total_cost) 
                from {$wpdb->prefix}googleads_daily_data 
                where DATE(date) >='$from_date' 
                and DATE(date)<='$to_date' 
                and tracking_id='{$data['id']}'
            ");

            // get the invoice revenue
            $trackers[$key]['invoice_amount'] = $wpdb->get_var("
                select SUM(total_amount) 
                from {$wpdb->prefix}invoices 
                where DATE(date) >='$from_date' 
                and DATE(date)<='$to_date' 
                and callrail_id='{$data['id']}'
            ");

            // get the quotes amount

            // get the maintenance amount
        }

        // SEPERATE PROFIT MAKING, LOSSS MAKING
        foreach ($trackers as $key => $value) {

            if(empty($value['ads_spent']) && empty($value['invoice_amount'])){
                $pl_data['not_doing_anything'][]=$value;
            }
            else if((float)$value['ads_spent'] >= (float)$value['invoice_amount']){
                $value['total_loss']=(float)$value['ads_spent']-(float)$value['invoice_amount'];
                $pl_data['loss_making'][]=$value;
            }
            else{
                $value['total_profit']=(float)$value['invoice_amount']-(float)$value['ads_spent'];
                $pl_data['profit_making'][]=$value;
            }
        }

        return $pl_data;
    }

    public function pl_report_body_map_ads($data,$from_date,$to_date,$branch,$ads_account=''){
 
        $template = "<hr><h3 class='text-center'>" . $this->beautify_string($branch) . " Map Ads Profit & Loss Report for <span class=\"label label-success\">".(!empty($ads_account) && $ads_account == 'map_ads' ? 'Google map ads' : '')."</span></h3><hr>";
        $template.="<h4 class='text-center'>For the period ".date('d M Y',strtotime($from_date))." to ".date('d M Y',strtotime($to_date))."</h4>";

            if(isset($data['campaigns']) && is_array($data['campaigns']) && count($data['campaigns'])>0){
                $template.="
                    <table class='table table-striped table-hover'>
                    <thead>
                            <tr>
                                <th>Campaign Name</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>";

                foreach ($data['campaigns'] as $key => $value) {

                    $template.="
                            <tr>
                                <td>{$value['tracking_name']}</td>
                                <td>".(new GamFunctions)->beautify_amount_field($value['ads_spent'])."</td>
                            </tr>
                            ";
                }

                $template.="</tbody>";

                $template.="<tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th>".(new GamFunctions)->beautify_amount_field($data['total_spent'])."</th>
                                </tr>
                            </tfoot>
                ";
                

                $template.="
                </table>";
            }
            else{
                $template.="<div class='alert alert-danger'>No Campaign with any spent found</div>";
            }


        $template.="<br><br>";

        if(isset($data['not_doing_anything']) && count($data['not_doing_anything'])>0){

            if(isset($data['not_doing_anything']) && count($data['not_doing_anything']) >0){
                $template.="<h4 class='text-info'>No spend or revenue found for these campaigns</h4>";
                $template.="<ul>";
                foreach($data['not_doing_anything'] as $key=>$val){
                    $template.="<li>{$val['tracking_name']}</li>";
                }
                $template.="</ul>";
                
            }

        }

        $template.="<br><br>";

        $template.="<table class='table table-striped table-hover'>
                        <caption class='text-center text-info'><h4>".$this->beautify_string($branch)." Map Ads Overall total Profit/Loss</h4></caption>
                        <tbody>
                            <tr>
                                <th>Total Ads Spent</th>
                                <td>".(new GamFunctions)->beautify_amount_field($data['total_spent'])."</td>
                            </tr>
                            <tr>
                                <th>Total Seo Revenue</th>
                                <td>".(new GamFunctions)->beautify_amount_field($data['seo_revenue'])."</td>
                            </tr>
                            ";
        
        if(isset($data['total_profit'])){
            $template.="<tr>
                            <th>Total Proft</th>
                            <td>".(new GamFunctions)->beautify_amount_field($data['total_profit'])."</td>
                        </tr>";
        }
        else{
            $template.="<tr>
                            <th>Total Loss</th>
                            <td>".(new GamFunctions)->beautify_amount_field($data['total_loss'])."</td>
                        </tr>";
        }
        
        $template.="</tbody>";

        $template.="</table>";
        
        return $template;
    }

    public function pl_report_body_pest_control($data,$from_date,$to_date,$branch,$ads_account = ''){
 
        $template = "<hr>";
        $template .= "<h3 class='text-center'>" . $this->beautify_string($branch) . " Profit & Loss Report for 
        <span class=\"label label-success\">".(!empty($ads_account) && $ads_account == 'pest_control' ? 'Google' : ucfirst($ads_account)) ."</span></h3>";
        $template .= "<hr>";
        $template.="<h4 class='text-center'>For the period ".date('d M Y',strtotime($from_date))." to ".date('d M Y',strtotime($to_date))."</h4>";

        if(isset($data['loss_making']) && count($data['loss_making'])>0){
        
            $template.="
                <table class='table table-striped table-hover'>
                <caption class='text-center text-danger'><h4>Loss Making Campaings</h4></caption>
                <thead>
                        <tr>
                            <th>Campaign Name</th>
                            <th>Total Spent</th>
                            <th>Total Revenue</th>
                            <th>Total Loss</th>
                        </tr>
                    </thead>
                    <tbody>";

            $total['ads_spent']=0;
            $total['invoice_amount']=0;
            $total['total_loss']=0;
            foreach ($data['loss_making'] as $key => $value) {

                $total['ads_spent']+=$value['ads_spent'];
                $total['invoice_amount']+=$value['invoice_amount'];
                $total['total_loss']+=$value['total_loss'];

                $template.="
                        <tr>
                            <td>{$value['tracking_name']}</td>
                            <td>".(new GamFunctions)->beautify_amount_field($value['ads_spent'])."</td>
                            <td>".(new GamFunctions)->beautify_amount_field($value['invoice_amount'])."</td>
                            <td>".(new GamFunctions)->beautify_amount_field($value['total_loss'])."</td>
                        </tr>
                        ";
            }

            $template.="</tbody>";

            $template.="<tfoot>
                            <tr>
                                <th>Total</th>
                                <th>".(new GamFunctions)->beautify_amount_field($total['ads_spent'])."</th>
                                <th>".(new GamFunctions)->beautify_amount_field($total['invoice_amount'])."</th>
                                <th>".(new GamFunctions)->beautify_amount_field($total['total_loss'])."</th>
                            </tr>
                        </tfoot>
            ";
            

            $template.="
            </table>";

        }

        $template.="<br><br>";

        if(isset($data['profit_making']) && count($data['profit_making'])>0){

            $template.="
                <table class='table table-striped table-hover'>
                    <caption class='text-center text-success'><h4>Profit Making Campaings</h4></caption>
                    <thead>
                        <tr>
                            <th>Campaign Name</th>
                            <th>Total Spent</th>
                            <th>Total Revenue</th>
                            <th>Total Profit</th>
                        </tr>
                    </thead>
                    <tbody>";

            $total['ads_spent']=0;
            $total['invoice_amount']=0;
            $total['total_profit']=0;

            foreach ($data['profit_making'] as $key => $value) {
                $total['ads_spent']+=$value['ads_spent'];
                $total['invoice_amount']+=$value['invoice_amount'];
                $total['total_profit']+=$value['total_profit'];
                $template.="
                        <tr>
                            <td>{$value['tracking_name']}</td>
                            <td>".(new GamFunctions)->beautify_amount_field($value['ads_spent'])."</td>
                            <td>".(new GamFunctions)->beautify_amount_field($value['invoice_amount'])."</td>
                            <td>".(new GamFunctions)->beautify_amount_field($value['total_profit'])."</td>
                        </tr>
                        ";
            }

            $template.="</tbody>";

            $template.="<tfoot>
                            <tr>
                                <th>Total</th>
                                <th>".(new GamFunctions)->beautify_amount_field($total['ads_spent'])."</th>
                                <th>".(new GamFunctions)->beautify_amount_field($total['invoice_amount'])."</th>
                                <th>".(new GamFunctions)->beautify_amount_field($total['total_profit'])."</th>
                            </tr>
                        </tfoot>
            ";

            $template.="
            </table>";

        }

        $template.="<br><br>";

        if(isset($data['not_doing_anything']) && count($data['not_doing_anything'])>0){

            if(isset($data['not_doing_anything']) && count($data['not_doing_anything']) >0){
                $template.="<h4 class='text-info'>No spend or revenue found for these campaigns</h4>";
                $template.="<ul>";
                foreach($data['not_doing_anything'] as $key=>$val){
                    $template.="<li>{$val['tracking_name']}</li>";
                }
                $template.="</ul>";
                
            }

        }

        $template.="<br><br>";

        $template.="<table class='table table-striped table-hover'>
                        <caption class='text-center text-info'><h4>".$this->beautify_string($branch)." Overall total Profit/Loss</h4></caption>
                        <tbody>
                            <tr>
                                <th>Total Ads Spent</th>
                                <td>".(new GamFunctions)->beautify_amount_field($data['total_spent'])."</td>
                            </tr>
                            <tr>
                                <th>Total Revenue</th>
                                <td>".(new GamFunctions)->beautify_amount_field($data['total_revenue'])."</td>
                            </tr>
                            ";
        
        if(isset($data['total_profit'])){
            $template.="<tr>
                            <th>Total Proft</th>
                            <td>".(new GamFunctions)->beautify_amount_field($data['total_profit'])."</td>
                        </tr>";
        }
        else{
            $template.="<tr>
                            <th>Total Loss</th>
                            <td>".(new GamFunctions)->beautify_amount_field($data['total_loss'])."</td>
                        </tr>";
        }
        
        $template.="</tbody>";

        $template.="</table>";
        
        return $template;
    }

    public function pl_report_header(){
        $template='<!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Document</title>
                        </head>
                        <style>
                            .text-center{
                                text-align:center;
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

                        <body>';
        
        return $template;
    }

    public function pl_report_footer(){
        $template="</body>
                    </html>";
        return $template;
    }

    public function pl_report_download_button($branch,$from_date,$to_date,$page_url,$account){
        $template = "<form method='post' action='".admin_url('admin-post.php')."' >";
            $template.=wp_nonce_field('generate_pl_report');
            $template.="<input type='hidden' name='action' value='download_pl_report' />
                        <input type='hidden' name='branch' value='$branch' />
                        <input type='hidden' name='from_date' value='$from_date' />
                        <input type='hidden' name='to_date' value='$to_date' />
                        <input type='hidden' name='page_url' value='$page_url' />
                        <input type='hidden' name='account' value='$account' />
                        <input type='hidden' name='date_type' value='date' />
                        <button class='btn btn-success'><span><i class='fa fa-download'></i></span> Download Report</button>
                    </form>
        ";
        return $template;     
    }
}

new Pl_report();