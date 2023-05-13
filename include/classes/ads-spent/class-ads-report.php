<?php

class AdsReport extends GamFunctions{

    function __construct(){

        add_action('admin_post_generate_weekly_alert_report',array($this,'generate_weekly_alert_report'));
        add_action('admin_post_nopriv_generate_weekly_alert_report',array($this,'generate_weekly_alert_report'));

        add_action('wp_ajax_delete_ad_report',array($this,'delete_ad_report'));
        add_action('wp_ajax_nopriv_delete_ad_report',array($this,'delete_ad_report'));
        
        add_action('wp_ajax_get_ads_report',array($this,'get_ads_report'));
        add_action('wp_ajax_nopriv_get_ads_report',array($this,'get_ads_report'));

        add_action('wp_ajax_search_missing_data',array($this,'search_missing_data'));
        add_action('wp_ajax_nopriv_search_missing_data',array($this,'search_missing_data'));

        add_action('wp_ajax_fetch_ads_spent_by_date',array($this,'fetch_ads_spent_by_date'));
        add_action('wp_ajax_nopriv_fetch_ads_spent_by_date',array($this,'fetch_ads_spent_by_date'));

    }

    public function isPenndingGoogleUnknownLeads(){
        global $wpdb;

        return $wpdb->get_var("
            select count(*)
            from {$wpdb->prefix}unknown_spends
        ");
    }

    public function generate_weekly_alert_report(){

        global $wpdb;

        if(empty($_POST['week']) || empty($_POST['location'])){
            $message="Please Select both week and date range option in order to generate report";
            $this->setFlashMessage($message,'danger');
            wp_redirect($_POST['page_url']);
            return;
        }

        // check if location is all branches then call the all branch report function 
        if($_POST['location']=='all_branches'){
            $this->generate_report_for_all_locations($_POST['week'],true);
            return;
        }

        $report_html=$this->report_header();
        require_once get_template_directory()."/include/classes/ads-spent/class-google-weekly-report.php";
        $obj=new WeeklyReport();
        $date_data=[
            'type'  =>  'week',
            'week'  =>  $_POST['week']
        ];
        $report_html.=$obj->generate_report($date_data,$_POST['location']);
        $report_html.=$this->report_footer();

        // load mpdf php sdk from vendor 
        self::loadVendor();

        $upload_dir=wp_upload_dir();
        $mpdf = new \Mpdf\Mpdf([
            'allow_output_buffering' => true,
            'mode' => 'utf-8', 
            'format' => 'A4-L'
        ]);
        $mpdf->WriteHTML($report_html);
        
        $actual_path=date('Y/m/d')."/report_".date('Ymdhis').".pdf";
        $path_for_db="/pdf/weekly-reports/".$actual_path;
        $folder_path=$upload_dir['basedir']."/pdf/weekly-reports/";
        $this->genreate_saving_directory($folder_path);
        $file_path=$upload_dir['basedir'].$path_for_db;
        $mpdf->Output($file_path,"F");

        // save the file path in database as well
        $weekly_reports_data=[
            'location'  =>  $_POST['location'],
            'file_path' =>  $path_for_db,
            'date'      =>  date('Y-m-d'),
            'week'      =>  $_POST['week']
        ];

        $wpdb->insert($wpdb->prefix."weekly_reports",$weekly_reports_data);

        unset($mpdf);

        $mpdf = new \Mpdf\Mpdf([
            'allow_output_buffering' => true,
            'mode' => 'utf-8', 
            'format' => 'A4-L'
        ]);

        $mpdf->WriteHTML($report_html);
        $mpdf->Output('Weekly Alert Report.pdf',"D");
        return;
        
    }

    public function generate_report_for_all_locations($week='',$download=true){

        global $wpdb;
        $upload_dir=wp_upload_dir();

        $branches=(new Branches)->getAllBranches();
        // GENERATING REPORT
        require_once get_template_directory()."/include/classes/ads-spent/class-google-weekly-report.php";
        $report_html=$this->report_header();
        if(is_array($branches) && count($branches)>0){
            foreach ($branches as $branch) {
                $obj=new WeeklyReport();
                $date_data=[
                    'type'  =>  'week',
                    'week'  =>  $week
                ];
                $report_html.=$obj->generate_report($date_data,$branch->slug);
            }
        }
        $report_html.=$this->report_footer();

        // load mpdf php sdk from vendor 
        self::loadVendor();
        
        // GENERATING REPORT PDF
        $mpdf = new \Mpdf\Mpdf([
            'allow_output_buffering' => true,
            'mode' => 'utf-8', 
            'format' => 'A4-L'
        ]);
        $mpdf->WriteHTML($report_html);

        $actual_path=date('Y/m/d')."/report_".date('Ymdhis').".pdf";
        $path_for_db="/pdf/weekly-reports/".$actual_path;
        $folder_path=$upload_dir['basedir']."/pdf/weekly-reports/";
        $this->genreate_saving_directory($folder_path);
        $file_path=$upload_dir['basedir'].$path_for_db;
        $mpdf->Output($file_path,"F");

        // save the file path in database as well
        $weekly_reports_data=[
            'location'  =>  "all_branches",
            'file_path' =>  $path_for_db,
            'date'      =>  date('Y-m-d'),
            'week'      =>  $week
        ];

        $wpdb->insert($wpdb->prefix."weekly_reports",$weekly_reports_data);

        unset($mpdf);

        if($download){
            $mpdf = new \Mpdf\Mpdf([
                // 'debug' => true,
                'allow_output_buffering' => true,
                'mode' => 'utf-8', 
                'format' => 'A4-L'
            ]);
    
            $mpdf->WriteHTML($report_html);
            $mpdf->Output('All Branches Report.pdf',"D");    
        }
        return;
    }

    public function report_header(){
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
                    .text-left{
                        text-align:left !important;
                    }
                    .text-danger{
                        color:red !important;
                    }
                </style>
                <body>';

        return $template;

    }

    public function report_footer(){
        $template="</body>
                    </html>";
        return $template;
    }

    public function delete_ad_report(){
        if(isset($_POST['report_id']) && !empty($_POST['report_id'])){
            global $wpdb;

            $upload_dir=wp_upload_dir();

            $path=$wpdb->get_var("select file_path from {$wpdb->prefix}weekly_reports where id='{$_POST['report_id']}'");

            unlink($upload_dir['basedir']."/".$path);

            $wpdb->delete($wpdb->prefix."weekly_reports",['id'=>$_POST['report_id']]);

            $this->response("success",'File and record deleted successfully');
            
        }
        else{
            $this->response("error","Something went wrong, please try again later");
        }
    }

    public function get_ads_report(){
	    $this->verify_nonce_field('get_ads_report');
        
        require_once get_template_directory()."/include/classes/ads-spent/class-google-weekly-report.php";
        $obj=new WeeklyReport();
        
        $date=[];

        if($_POST['date_type']=="week"){
            $date['type']="week";
            $date['week']=$_POST['week'];
        }
        else{
            $date['type']="date_range";
            $date['from_date']=$_POST['from_date'];
            $date['to_date']=$_POST['to_date'];
        }

        $report_html=$obj->generate_report($date,$_POST['branch'],$_POST['tracking_id']);

        echo $report_html;
        wp_die();

    }

    public function search_missing_data(){
        global $wpdb;
		$this->verify_nonce_field('search_missing_data');
        $no_spent_found=[];

        $begin = new DateTime($_POST['from_date']);
        $end = new DateTime($_POST['to_date']);
        $end->setTime(0,0,1);
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        foreach ($period as $dt) {
            $date=$dt->format("Y-m-d");
            $spent=$wpdb->get_var("select count(*) from {$wpdb->prefix}googleads_daily_data where date='$date' and account_name='{$_POST['account']}'");
            if(!$spent){
                $no_spent_found[]=$date;
            }
        }

        if(count($no_spent_found)>0){
            $no_spent_found_html="
                <table class='table table-striped table-hover'>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Account</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
            ";
            foreach ($no_spent_found as $val) {
                $no_spent_found_html.="
                        <tr>
                            <td>$val</td>
                            <td>".$this->beautify_string($_POST['account'])."</td>
                            <td><button data-date='$val' data-account='{$_POST['account']}' class='btn btn-primary fetch_spents'><span><i class='fa fa-database'></i></span> Fetch Spents</button></td>
                        </tr>
                ";
            }
            $no_spent_found_html.="
                    </tbody>
                </table>
            ";
            echo $no_spent_found_html;
        }
        else{
            echo '<div class="notice notice-success">
                    <p>All spent are in system for the date range</p>
                    </div>';
        }

        wp_die();
    }

    public function fetch_ads_spent_by_date(){
        if(isset($_POST['date']) && isset($_POST['account']) && !empty($_POST['date']) && !empty($_POST['account'])){
            if($_POST['account']=="pest_control"){
                // include google daily data file 
                require_once get_template_directory()."/include/classes/ads-spent/class-google-daily-spent.php";

                // create the google daily class object and fetch data for yesterday date
                $obj=new DownloadCriteriaReportWithSelector();
                $obj->main($_POST['date']);
            }
            elseif($_POST['account']=="map_ads"){
                // include google daily data file 
                require_once get_template_directory()."/include/classes/ads-spent/class-googlemaps-daily-spent.php";

                //create the google daily class object and fetch data for yesterday date
                $obj=new DownloadCriteriaReportWithSelector();
                $obj->main($_POST['date']);
            }
            else{
                $this->response('error','account name not found');
            }
            $this->response('success','ads spent fetch and stored successfully');
        }
        else{
            $this->response('error','fields not found or empty');
        }
    }

}

new AdsReport();