<?php

global $wpdb;
$date_filter=false;
$date_condition='';
$commercial_maintenance_date_condition='';
$special_maintenance_date_condition='';

if(isset($_GET['from_date']) && isset($_GET['to_date'])){
    if(!empty($_GET['from_date']) && !empty($_GET['to_date'])){
        $date_filter=true;
        $date_condition="and date >= '{$_GET['from_date']}' and date <= '{$_GET['to_date']}'";
        $commercial_maintenance_date_condition="and date_created >= '{$_GET['from_date']}' and date_created <= '{$_GET['to_date']}'";
        $special_maintenance_date_condition="and date_created >= '{$_GET['from_date']}' and date_created <= '{$_GET['to_date']}'";    
    }
}


// get callrail phone no and name 
$callrail_data=$wpdb->get_row("select * from {$wpdb->prefix}callrail where id='{$_GET['tracking_id']}'");

$total_calls=(new Callrail_new)->get_calls_by_tracking_no($callrail_data->tracking_phone_no,$callrail_data->location);
// echo "select COUNT(callrail_id) as total_invoices, SUM(total_amount) as total_amount from {$wpdb->prefix}invoices where callrail_id='$callrail_data->id' $date_condition";wp_die();
$invoice=$wpdb->get_row("select COUNT(callrail_id) as total_invoices, SUM(total_amount) as total_amount from {$wpdb->prefix}invoices where callrail_id='$callrail_data->id' $date_condition");

$residential_quotesheet=$wpdb->get_row("select COUNT(callrail_id) as total_quotes, SUM(total_cost) as total_cost from {$wpdb->prefix}quotesheet where callrail_id='$callrail_data->id' $date_condition");

$commercial_quotesheet=$wpdb->get_row("select COUNT(callrail_id) as total_quotes, SUM(cost_per_visit) as total_cost from {$wpdb->prefix}commercial_quotesheet where callrail_id='$callrail_data->id' $date_condition");

$monthly_maintenance=$wpdb->get_row("select COUNT(callrail_id) as total_contracts, SUM(total_cost) as total_cost from {$wpdb->prefix}maintenance_contract where callrail_id='$callrail_data->id' and type='monthly' $date_condition");

$quarterly_maintenance=$wpdb->get_row("select COUNT(callrail_id) as total_contracts, SUM(total_cost) as total_cost from {$wpdb->prefix}maintenance_contract where callrail_id='$callrail_data->id' and type='quarterly' $date_condition");

$commercial_maintenance=$wpdb->get_row("select COUNT(callrail_id) as total_contracts, SUM(cost_per_visit) as total_cost from {$wpdb->prefix}commercial_maintenance where callrail_id='$callrail_data->id' $commercial_maintenance_date_condition");

$special_maintenance=$wpdb->get_row("select COUNT(callrail_id) as total_contracts, SUM(cost) as total_cost from {$wpdb->prefix}special_contract where callrail_id='$callrail_data->id' $special_maintenance_date_condition");

$google_spends=$wpdb->get_row("select SUM(total_cost) as total_cost from {$wpdb->prefix}googleads_daily_data where tracking_id='$callrail_data->id' $date_condition");


// echo "<pre>";print_r($google_spends);wp_die();


?>
<div class="row">
    <?php if($date_filter): ?>
        <div class="col-md-12">
            <p><b>From Date: </b><?= date("d M Y",strtotime($_GET['from_date'])); ?><b> To Date: </b><?= date("d M Y",strtotime($_GET['to_date'])); ?></p>
        </div>
    <?php endif; ?>
    <div class="col-md-6">
        <table class="table table-striped table-hover">
            <caption>Callrail Details</caption>
            <tbody>
                <tr>
                    <th>Tracking Number</th>
                    <td><?= $callrail_data->tracking_phone_no; ?></td>
                </tr>
                <tr>
                    <th>Tracking Name</th>
                    <td><?= $callrail_data->tracking_name; ?></td>
                </tr>
                <tr>
                    <th>Total Calls</th>
                    <td><?= $total_calls; ?></td>
                </tr>
                <tr>
                    <th>Total Ad Spend (Google)</th>
                    <td>$<?= !empty($google_spends->total_cost) ? $google_spends->total_cost : "0" ; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col-md-6">
        <table class="table table-striped table-hover">
            <caption>Invoice Details</caption>
            <tbody>
                <tr>
                    <th>Total Invoices</th>
                    <td><?= $invoice->total_invoices; ?></td>
                </tr>
                <tr>
                    <th>Total Invoice Amount</th>
                    <td><?= $invoice->total_amount!="" ? "$".number_format((float)$invoice->total_amount, 2, '.', '') : "$0" ?></td>
                </tr>
            </tbody>
        </table>
    
    </div>
</div>
<div class="row">
    <div class="col-md-6">
            <table class="table table-striped table-hover">
                <caption>Residential Quotesheet Details</caption>
                <tbody>
                    <tr>
                        <th>Total Quotes</th>
                        <td><?= $residential_quotesheet->total_quotes; ?></td>
                    </tr>
                    <tr>
                        <th>Total Quotes Amount</th>
                        <td><?= $residential_quotesheet->total_cost!="" ? "$".number_format((float)$residential_quotesheet->total_cost, 2, '.', '') : "$0" ?></td>
                    </tr>
                </tbody>
            </table>    
    </div>
    <div class="col-md-6">
        <table class="table table-striped table-hover">
            <caption>Commercial Quotesheet Details</caption>
            <tbody>
                <tr>
                    <th>Total Quotes</th>
                    <td><?= $commercial_quotesheet->total_quotes; ?></td>
                </tr>
                <tr>
                    <th>Total Quotes Amount</th>
                    <td><?= $commercial_quotesheet->total_cost!="" ? "$".number_format((float)$commercial_quotesheet->total_cost, 2, '.', '') : "$0" ?></td>
                </tr>
            </tbody>
        </table>
    
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <table class="table table-striped table-hover">
            <caption>Monthly Maintenance Details</caption>
            <tbody>
                <tr>
                    <th>Total Contracts</th>
                    <td><?= $monthly_maintenance->total_contracts; ?></td>
                </tr>
                <tr>
                    <th>Total Contracts Amount</th>
                    <td><?= $monthly_maintenance->total_cost!="" ? "$".number_format((float)$monthly_maintenance->total_cost, 2, '.', '') : "$0" ?></td>
                </tr>
            </tbody>
        </table>
    
    </div>
    <div class="col-md-6">
        <table class="table table-striped table-hover">
            <caption>Quarterly Maintenance Details</caption>
            <tbody>
                <tr>
                    <th>Total Contracts</th>
                    <td><?= $quarterly_maintenance->total_contracts; ?></td>
                </tr>
                <tr>
                    <th>Total Contracts Amount</th>
                    <td><?= $quarterly_maintenance->total_cost!="" ? "$".number_format((float)$quarterly_maintenance->total_cost, 2, '.', '') : "$0" ?></td>
                </tr>
            </tbody>
        </table>
    
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <table class="table table-striped table-hover">
            <caption>Commercial Maintenance Details</caption>
            <tbody>
                <tr>
                    <th>Total Contracts</th>
                    <td><?= $commercial_maintenance->total_contracts; ?></td>
                </tr>
                <tr>
                    <th>Total Contracts Amount</th>
                    <td><?= $commercial_maintenance->total_cost!="" ? "$".number_format((float)$commercial_maintenance->total_cost, 2, '.', '') : "$0" ?></td>
                </tr>
            </tbody>
        </table>
    
    </div>
    <div class="col-md-6">
        <table class="table table-striped table-hover">
            <caption>Special Maintenance Details</caption>
            <tbody>
                <tr>
                    <th>Total Contracts</th>
                    <td><?= $special_maintenance->total_contracts; ?></td>
                </tr>
                <tr>
                    <th>Total Contracts Amount</th>
                    <td><?= $special_maintenance->total_cost!="" ? "$".number_format((float)$special_maintenance->total_cost, 2, '.', '') : "$0" ?></td>
                </tr>
            </tbody>
        </table>
    
    </div>
</div>