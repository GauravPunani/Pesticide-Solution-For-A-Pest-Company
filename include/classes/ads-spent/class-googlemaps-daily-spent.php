<?php

require get_template_directory()."/libraries/vendor/autoload.php";

use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\Reporting\v201809\DownloadFormat;
use Google\AdsApi\AdWords\Reporting\v201809\ReportDefinition;
use Google\AdsApi\AdWords\v201809\cm\DateRange;
use Google\AdsApi\AdWords\Reporting\v201809\ReportDefinitionDateRangeType;
use Google\AdsApi\AdWords\Reporting\v201809\ReportDownloader;
use Google\AdsApi\AdWords\ReportSettingsBuilder;
use Google\AdsApi\AdWords\v201809\cm\Predicate;
use Google\AdsApi\AdWords\v201809\cm\PredicateOperator;
use Google\AdsApi\AdWords\v201809\cm\ReportDefinitionReportType;
use Google\AdsApi\AdWords\v201809\cm\Selector;
use Google\AdsApi\Common\OAuth2TokenBuilder;

/**
 * Downloads CRITERIA_PERFORMANCE_REPORT for the specified client customer ID.
 */
class DownloadCriteriaReportWithSelector
{

    private $config_file_path;

    function __construct(){
        $this->config_file_path=get_template_directory()."/config_files/google map key/adsapi_php.ini";
    }

    public function runExample(AdWordsSession $session, $filePath,$date){
        global $wpdb;

        // Create selector.
        $selector = new Selector();
        $selector->setFields(
            [
                'CampaignId',
                'CampaignName',
                'Cost'
            ]
        );

        $FromDate=date('Ymd',strtotime($date));
        $ToDate=date('Ymd',strtotime($date));
        $selector->setDateRange(new DateRange($FromDate, $ToDate));

        // Create report definition.
        $reportDefinition = new ReportDefinition();
        $reportDefinition->setSelector($selector);
        $reportDefinition->setReportName(
            'Criteria performance report #' . uniqid()
        );
        $reportDefinition->setDateRangeType(
            ReportDefinitionDateRangeType::CUSTOM_DATE
            // ReportDefinitionDateRangeType::YESTERDAY
        );
        $reportDefinition->setReportType(
            ReportDefinitionReportType::CAMPAIGN_PERFORMANCE_REPORT
            // ReportDefinitionReportType::CRITERIA_PERFORMANCE_REPORT
        );


        $reportDefinition->setDownloadFormat(DownloadFormat::CSV);


        // Download report.
        $reportDownloader = new ReportDownloader($session);

        // Optional: If you need to adjust report settings just for this one
        // request, you can create and supply the settings override here. Otherwise,
        // default values from the configuration file (adsapi_php.ini) are used.
        $reportSettingsOverride = (new ReportSettingsBuilder())->includeZeroImpressions(false)->build();


        $reportDownloadResult = $reportDownloader->downloadReport(
            $reportDefinition,
            $reportSettingsOverride
        );
        $reportDownloadResult->saveToFile($filePath);
        // printf(
        //     "Report with name '%s' was downloaded to '%s'.\n",
        //     $reportDefinition->getReportName(),
        //     $filePath
        // );
        $file = fopen($filePath, 'r');
        $data=[];
        while (($line = fgetcsv($file)) !== FALSE) {
        //$line is an array of the csv elements
            $data[]=$line;

        }
        fclose($file);
        // echo "upto here";
        // echo "<pre>";print_r($data);

        unset($data[0],$data[1]);
        array_pop($data);
        $data=array_values($data);

        $data=$this->group_campaign_amounts($data);
        
        // search for campaign name name in db  and insert in database with campaign id
        $unknown_spends = [];

        foreach($data as $key=>$val){

            // $key -   CAMPAIGN NAME 
            // $val -   CAMPAIGN COST 

            // search in callrail records 
            $campaign_name=str_replace("_"," ",strtolower($key));

            try{
                $cost=$val/1000000;
            }
            catch(DivisionByZeroError $e){
                $cost=0;
            }

            $campaign_name = trim(strtolower($campaign_name));            

            $result=$wpdb->get_row("
                select id,tracking_phone_no,location 
                from {$wpdb->prefix}callrail 
                where lower(tracking_name) = '$campaign_name'
            ");

            if($result){

                $total_calls=(new Callrail_new)->get_calls_by_tracking_no($result->tracking_phone_no,$result->location,$date,$date);


                // save the cost and total call for the week in database 

                $google_ads_data=[
                    'tracking_id'   =>  $result->id,
                    'date'          =>  $date,
                    'total_cost'    =>  $cost,
                    'total_unique_calls'    =>  $total_calls,
                    'account_name'  =>  'map_ads',
                    'date_created'  =>  date('Y-m-d h:i:s')
                ];

                $wpdb->insert($wpdb->prefix."googleads_daily_data",$google_ads_data);

            }
            else{

                $unknown_spends[] = [
                    'campaign_name' =>  $campaign_name,
                    'cost'          =>  $cost,
                    'date'          =>  $date,
                    'account'       =>  'map_ads'
                ];
            }

        }

        if(count($unknown_spends) > 0){
            (new OfficeTasks)->linkUnknownGoogleLeads();

            foreach($unknown_spends as $unknown_spend){
                $wpdb->insert($wpdb->prefix.'unknown_spends', $unknown_spend);
            }
        }
    }

    public function group_campaign_amounts($data=[]){

        $temp=[];

        // first group the all sub ads in to a single campaign array
        foreach ($data as $element) {
            $temp[$element[1]][] = $element;
        }

        // sum all sub ads of a campaign and return the campaign cost 

        foreach($temp as $key=>$val){
            $total=0;

            foreach($val as $amount){
                $total=$total+$amount[2];
            }
            $temp[$key]=$total;
        }
        
        return $temp;
    }

    public function main($date){
        global $wpdb;

        // first check if data is already fetched for the given date
        $res=$wpdb->get_var("
            select count(*) 
            from {$wpdb->prefix}googleads_daily_data 
            where date='$date' 
            and account_name='map_ads'
        ");

        if($res){
            $comment="google mapd ads ac daily data is already fetched for the date $date";
            return $wpdb->insert($wpdb->prefix."dev_notices", ['comment'=>$comment]);
        }

        // Generate a refreshable OAuth2 credential for authentication.
        $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile($this->config_file_path)->build();

        // See: AdWordsSessionBuilder for setting a client customer ID that is
        // different from that specified in your adsapi_php.ini file.
        // Construct an API session configured from a properties file and the
        // OAuth2 credentials above.
        $session = (new AdWordsSessionBuilder())->fromFile($this->config_file_path)->withOAuth2Credential($oAuth2Credential)->build();

        $filePath = sprintf(
            '%s.csv',
            tempnam(sys_get_temp_dir(), 'criteria-report-')
        );
        $this->runExample($session, $filePath,$date);
    }
}