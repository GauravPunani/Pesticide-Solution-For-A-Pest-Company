<?php

class Navigation extends GamFunctions{

    public function billing_location_page($active_location=''){
        $branches=(new Branches)->getAllBranches();
        $page_url=admin_url('admin.php?page=billing');
        if(is_array($branches) && count($branches)>0){
            ?>
            <h2 class="nav-tab-wrapper">
            <?php
                foreach($branches as $branch){
                    ?>
                    <a href="<?= $page_url; ?>&location=<?= $branch->slug; ?>" class="nav-tab <?= ($active_location==$branch->slug) || $active_location=="" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-map-marker"></i></span> <?= $branch->location_name; ?></a>
                    <?php
                }
            ?>
            </h2>
            <?php
        }        
    }
    
    public function location_wise_pages($location){
        $db_locations=(new GamFunctions)->get_all_locations();
        ?>
        <?php if(is_array($db_locations) && count($db_locations)>0): ?>
            <h2 class="nav-tab-wrapper">
                <?php foreach($db_locations as $db_loc): ?>
                    <a href="<?= admin_url("admin.php?page=invoice&location=".$db_loc->slug); ?>" class="nav-tab <?= ($location==$db_loc->slug || ($location=="" && $db_loc=="upstate")) ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-map-marker"></i></span> <?= $db_loc->location_name; ?></a>
                <?php endforeach; ?>
            </h2>
        <?php endif; ?>

		<?php

    }

    public function stripe_details_page_tabs($location){
        $locations=(new GamFunctions)->get_all_locations();
        ?>
        <?php if(is_array($locations) && count($locations)>0): ?>            
            <h2 class="nav-tab-wrapper">
            <?php foreach($locations as $key=>$val): ?>
                <a href="<?= admin_url('admin.php?page='.$_GET['page']); ?>&location=<?= $val->slug;  ?>" class="nav-tab <?= $location==$val->slug || ($location=='' && $val->slug=="upstate") ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-map-marker"></i></span> <?= $val->location_name; ?></a>
            <?php endforeach; ?>
            </h2>
        <?php endif; ?>

        <?php
    }

    public function ads_spent_navigation($ads_tab){
        global $wpdb;
        $total_unknown_spents=$wpdb->get_var("select count(*) from {$wpdb->prefix}unknown_spends");

        $page_url=admin_url('admin.php?page='.$_GET['page']); 
        ?>
            <h2 class="nav-tab-wrapper">
                <a href="<?= $page_url; ?>&ads_tab=daily-data" class="nav-tab <?= $ads_tab=="daily-data" || $ads_tab=="" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-money"></i></span> Ad Spend</a>
                <a href="<?= $page_url; ?>&ads_tab=ad-spent-calculation" class="nav-tab <?= $ads_tab=="ad-spent-calculation" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-bar-chart"></i></span> Campaign Performance</a>
                <a href="<?= $page_url; ?>&ads_tab=weekly-alert-report" class="nav-tab <?= $ads_tab=="weekly-alert-report" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-file-pdf-o"></i></span> Weekly Alert Report</a>
                <a href="<?= $page_url; ?>&ads_tab=pl-calculator" class="nav-tab <?= $ads_tab=="pl-calculator" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-calculator"></i></span> P/L Report</a>
                <a href="<?= $page_url; ?>&ads_tab=unknown-spents" class="nav-tab <?= $ads_tab=="unknown-spents" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-question-circle"></i></span> Link Unknown Spents <span class="badge"><?= $total_unknown_spents; ?></span></a>
                <a href="<?= $page_url; ?>&ads_tab=missing-ad-spent-data" class="nav-tab <?= $ads_tab=="missing-ad-spent-data" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-question-circle"></i></span> Missing ad-spend data</a>
            </h2>
        <?php
    }

    public function maintenancePages(){
        $page = $_GET['page'];
        ?>
        <h2 class="nav-tab-wrapper">
            <a href="?page=maintenance&contract_type=monthly" class="nav-tab <?= $page=="maintenance" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-list"></i></span> Monthly Maintenance</a>
            <a href="?page=quarterly-maintenance&contract_type=quarterly" class="nav-tab <?= $page=="quarterly-maintenance" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-list"></i></span> Quarterly Maintenance</a>
            <a href="?page=commercial-maintenance&contract_type=commercial" class="nav-tab <?= $page=="commercial-maintenance" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-list"></i></span> Commercial Maintenance</a>
            <a href="?page=special-maintenance&contract_type=special" class="nav-tab <?= $page=="special-maintenance" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-list"></i></span> Special Maintenance</a>
        </h2>        
        <?php
    }

    public function calculation_navigation($page){
        ?>
        <h2 class="nav-tab-wrapper">
            <a href="?page=invoice-calculations" class="nav-tab <?= $page=="invoice-calculations" || $page=="" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-file"></i></span> Invoice/Quote Calculation</a>
            <a href="?page=sales-tax" class="nav-tab <?= $page=="sales-tax"  ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-calculator"></i></span> Sales Tax</a>
            <a href="?page=ads-spent" class="nav-tab <?= $page=="ads-spent"  ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-money"></i></span> Ad Spend</a>
        </h2>
        <?php
    }

    public function location_tabs($branch_id=''){

        $branches = (new Branches)->getAllBranches();
        $parnter_branches = (new Branches)->partner_accessible_branches(true);

        if(count((array)$branches)>0){

            $page_url=$_SERVER['PHP_SELF'];
            $url_parameters=$_GET;
            
            if(isset($url_parameters['branch_id'])) unset($url_parameters['branch_id']);
            if(isset($url_parameters['technician_id'])) unset($url_parameters['technician_id']);
            if(isset($url_parameters['date'])) unset($url_parameters['date']);
    
            $page_url=$page_url."?".http_build_query($url_parameters);
        
            echo '<h2 class="nav-tab-wrapper">';
            ?>
            <a href="<?= $page_url; ?>&branch_id=all" class="nav-tab <?= $branch_id=='' || $branch_id=='all'  ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-location-arrow"></i></span> All</a>            

            <?php
            foreach ($branches as $key => $value) {
                if (!current_user_can('other_than_upstate')){
                    if(!in_array($value->id, $parnter_branches)){
                        continue;
                    }
                }
            ?>
                <a href="<?= $page_url; ?>&branch_id=<?= $value->id; ?>" class="nav-tab <?= $branch_id == $value->id  ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-location-arrow"></i></span> <?= $value->location_name; ?></a>
            <?php
            }
            echo "</h2>";
        }
    
    }

    public function quotesheet_tabs($page,$active_tab){
        $page_url=$_SERVER['REQUEST_URI']; 

        ?>
        <h2 class="nav-tab-wrapper">
            <a href="<?= $page_url; ?>&tab=all" class="nav-tab <?= $active_tab=="all" || $active_tab=="" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-database"></i></span> All</a>
            <a href="<?= $page_url; ?>&tab=pending" class="nav-tab <?= $active_tab=="pending"  ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-clock-o"></i></span> Pending Lead</a>
            <a href="<?= $page_url; ?>&tab=dead" class="nav-tab <?= $active_tab=="dead" ? 'nav-tab-active' : ''; ?>"><span><i class="fa fa-ban"></i></span> Dead Lead</a>
            <a href="<?= $page_url; ?>&tab=closed" class="nav-tab <?= $active_tab=="closed" ? 'nav-tab-active' : ''; ?>"><span><i class="fa fa-times"></i></span> Closed Lead</a>
        </h2>
    <?php
    }

    public function billing_tabs($page,$active_tab=''){
        $page_url=$_SERVER['REQUEST_URI'];
        ?>
            <h2 class="nav-tab-wrapper">
                <a href="<?= $page_url; ?>&tab=billing" class="nav-tab <?= $active_tab=="billing" || $active_tab=="" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-file"></i></span> Billing - Unpaid Invoices</a>
                <a href="<?= $page_url; ?>&tab=30_days_due" class="nav-tab <?= $active_tab=="30_days_due" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-file"></i></span> 30 Days Past Due</a>
                <a href="<?= $page_url; ?>&tab=in_collection" class="nav-tab <?= $active_tab=="in_collection" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-file"></i></span> In Collections</a>
                <a href="<?= $page_url; ?>&tab=mini_statement_log" class="nav-tab <?= $active_tab=="mini_statement_log"  ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-tasks"></i></span> Mini Statement Log</a>
                <a href="<?= $page_url; ?>&tab=billing_no_email" class="nav-tab <?= $active_tab=="billing_no_email"  ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-file"></i></span> Billing - No Email</a>
            </h2>

        <?php
    }
    
    public function common_location_tabs($page='', $url_location=''){

        if(current_user_can('other_than_upstate')){
            $locations=(new GamFunctions)->get_all_locations();

            $page_url=$_SERVER['REQUEST_URI'];
        
            if(is_array($locations) && count($locations)>0){
                echo '<h2 class="nav-tab-wrapper">';
                foreach ($locations as $key => $value) {
                    ?>
                    <a href="<?= $page_url; ?>&location=<?= $value->slug; ?>" class="nav-tab <?= ($url_location==$value->slug || $value->slug=="ny_metro" && $url_location
                    =="") ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-map-marker"></i></span> <?= $value->location_name; ?></a>

                    <?php
                }
                echo "</h2>";           
            }
    
        }
    }

    public function reimbursement_tabs($tab=''){
        $page_url=admin_url('admin.php?page=reimbursement');
        $page_url .= (isset($_GET['type']) && !empty($_GET['type']) ? '&type='.$_GET['type'] : '');
        ?>
        <h2 class="nav-tab-wrapper">
            <a href="<?= $page_url; ?>" class="nav-tab <?= ($tab=="" || $tab=="reimbursement") ? 'nav-tab-active' : ''; ?>"><span><i class="fa fa-clock-o"></i></span> Pending</a>
            <a href="<?= $page_url; ?>&tab=reimbursement-log" class="nav-tab <?= $tab=="reimbursement-log" ? 'nav-tab-active' : ''; ?>"><span><i class="fa fa-history"></i></span> Reimbursement Log</a>
        </h2>
        <?php
    }

    public function callrail_listing_tabs($active_tab=''){
        $page_url=admin_url('admin.php?page=callrail-trackers');
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="all-callrail-trackers" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=all-callrail-trackers" ><span><i class="fa fa-list"></i></span> All Callrail Trackers</a>
            <a class="nav-tab <?= $active_tab=="unattributed-trackers" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=unattributed-trackers" ><span><i class="fa fa-unlink"></i></span> Unassigned Tracking Number</a>
            <a class="nav-tab <?= $active_tab=="create-tracker" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=create-tracker" ><span><i class="fa fa-plus"></i></span> Create Tracker</a>
        </h2>
        <?php
    }

    public function technician_listing_tabs($active_view=''){

        $pending_applications = (new Technician_details)->getPendingApplications(['id']);
        $page_url = admin_url('admin.php?page='.$_GET['page']);

        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_view == "" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>" ><span><i class="fa fa-users"></i></span> Technicians</a>

            <a class="nav-tab <?= $active_view == "pending-applications" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&view=pending-applications" ><span><i class="fa fa-clock-o"></i></span> Pending Applications <span class="badge"><?= count($pending_applications); ?></span></a>

            <a class="nav-tab <?= $active_view=="rejected-applications" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&view=rejected-applications" ><span><i class="fa fa-ban"></i></span> Rejected Applications</a>
            <a class="nav-tab <?= $active_view == "fired-technician" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&view=fired-technician" ><span><i class="fa fa-ban"></i></span> Fired Technicians</a>

            <a class="nav-tab <?= $active_view == "resigned-technician" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&view=resigned-technician" ><span><i class="fa fa-ban"></i></span> Resigned technicians</a>
			
        </h2>
        <?php
    }

    public function technician_dashboard_navigation($active_tab=''){
        $page_url=admin_url('admin.php?page=technician-notices');
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="all-notices" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>" ><span><i class="fa fa-bell"></i></span> View Notices</a>
            <a class="nav-tab <?= $active_tab=="add-new" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=add-new" ><span><i class="fa fa-plus"></i></span> Add Technician Notice</a>
            <a class="nav-tab <?= $active_tab=="critical-notices" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=critical-notices" ><span><i class="fa fa-exclamation-triangle"></i></span> Critical Notices</a>
        </h2>
        <?php
    }

    public function car_center($active_tab=''){
        $page_url=admin_url('admin.php?page=gam-vehicles');
        ?>
        <h2 class="nav-tab-wrapper">

            <a class="nav-tab <?= $active_tab=="gam-vehicles" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>" ><span><i class="fa fa-car"></i></span> GAM Vehicles</a>

            <a class="nav-tab <?= $active_tab=="mileage-proof" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=mileage-proof" ><span><i class="fa fa-tachometer"></i></span> Mileage Proof</a>

            <a class="nav-tab <?= $active_tab=="oil-change-proof" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=oil-change-proof" ><span><i class="fa fa-filter"></i></span> Oil Change Proof</a>

            <a class="nav-tab <?= $active_tab=="break-pads-change-proof" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=break-pads-change-proof" ><span><i class="fa fa-car"></i></span> Break Pads Change Proof</a>

            <a class="nav-tab <?= $active_tab=="vehicle-condition-proof" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=vehicle-condition-proof" ><span><i class="fa fa-car"></i></span> Vehicle Condition Proof</a>

            <a class="nav-tab <?= $active_tab=="car-wash-proof" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=car-wash-proof" ><span><i class="fa fa-car"></i></span> Car Wash Proof</a>

            <a class="nav-tab <?= $active_tab=="link-vehicle" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=link-vehicle" ><span><i class="fa fa-link"></i></span> Link Vehicle</a>

            <a class="nav-tab <?= $active_tab=="create-vehicle" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=create-vehicle" ><span><i class="fa fa-plus"></i></span> Create Vehicle</a>
         
        </h2>
        <?php
    }

    public function termite_navigation($active_tab=''){
        $page_url=admin_url('admin.php?page=termite-paperwork');
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="termite-certificate" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>" ><span><i class="fa fa-certificate"></i></span> Termite Certificates</a>
            <a class="nav-tab <?= $active_tab=="termite-graph" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=termite-graph" ><span><i class="fa fa-bar-chart"></i></span> Termite Graph</a>
            <a class="nav-tab <?= $active_tab=="florida-wood-inspection" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=florida-wood-inspection" ><span><i class="fa fa-file"></i></span> Florida Wood Inspection</a>
            <a class="nav-tab <?= $active_tab=="florida-consumer-consent" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=florida-consumer-consent" ><span><i class="fa fa-file"></i></span> Florida Consumer Consent</a>
            <a class="nav-tab <?= $active_tab=="npma-33" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=npma-33" ><span><i class="fa fa-file"></i></span> NPMA 33</a>
        </h2>
        <?php
    }

    public function task_manager($active_tab=''){
        $page_url=admin_url('admin.php?page=task-manager');
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="tasks" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>" ><span><i class="fa fa-list"></i></span> Tasks</a>
            <a class="nav-tab <?= $active_tab=="create-task" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=create-task" ><span><i class="fa fa-plus"></i></span> Create Task</a>
        </h2>
        <?php
    }

    public function dissatisfied_clients($active_tab=''){
        $page_url=admin_url('admin.php?page=dissatisfied-clients');
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="dissatisfied-clients" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>" ><span><i class="fa fa-frown-o"></i></span> Dissatisfied clients</a>
            <a class="nav-tab <?= $active_tab=="resolved" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=resolved" ><span><i class="fa fa-thumbs-up"></i></span> Resolved</a>
            <a class="nav-tab <?= $active_tab=="client_still_upset" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=client_still_upset" ><span><i class="fa fa-thumbs-down"></i></span> Client still upset</a>
            <a class="nav-tab <?= $active_tab=="client_fired_us" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=client_fired_us" ><span><i class="fa fa-fire"></i></span> Client fired us</a>
        </h2>
        <?php
    }

    public function non_reocurring_navigation($active_tab=''){
        $page_url=admin_url('admin.php?page=nonrecurring-clients');
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="calendar-non-reocurring" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=calendar-non-reocurring" ><span><i class="fa fa-calendar"></i></span> Calendar - Non-reocurring</a>
            <a class="nav-tab <?= $active_tab=="system-non-reocurring" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=system-non-reocurring" ><span><i class="fa fa-windows"></i></span> System - Non-reocurring</a>
        </h2>
        <?php
    }

    public function email_database_navigation($active_tab=''){
        $page_url=admin_url('admin.php?page=email-database');
        ?>
        <h2 class="nav-tab-wrapper">    
        <a class="nav-tab <?= $active_tab=="all-clients" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=all-clients" ><span><i class="fa fa-envelope"></i></span> All Clients</a>    
            <!-- <a class="nav-tab <?= $active_tab=="non-reocurring" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=non-reocurring" ><span><i class="fa fa-envelope"></i></span> Non-reocurring Clients</a>
            <a class="nav-tab <?= $active_tab=="reocurring" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=reocurring" ><span><i class="fa fa-envelope"></i></span> Reocurring Clients</a>
            <a class="nav-tab <?= $active_tab=="create-cold-calls" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=cold-calls" ><span><i class="fa fa-envelope"></i></span> Cold Calls</a> -->
            <a class="nav-tab <?= $active_tab=="view-call-logs" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=view-call-logs" ><span><i class="fa fa-envelope"></i></span> View Call Logs</a>
        </h2>
        <?php
    }

    public function cold_caller_navigation($active_tab=''){
        $page_url=admin_url('admin.php?page=cold-caller');
        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;
        $pending_applications = (new ColdCaller)->getPendingColdCallerApplications(['id']);
        ?>
        <h2 class="nav-tab-wrapper"> 

            <a class="nav-tab <?= $active_tab=="all-cold-callers" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=all-cold-callers" ><span><i class="fa fa-users"></i></span> Cold Callers</a>

            <a class="nav-tab <?= $active_tab == "pending-applications" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&status=pending&tab=pending-applications" ><span><i class="fa fa-clock-o"></i></span> Pending Applications <span class="badge"><?= count($pending_applications); ?></span></a>
            
            <a class="nav-tab <?= $active_tab == "fired-cold-callers" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&status=fired&tab=fired-cold-callers" ><span><i class="fa fa-ban"></i></span> Fired Cold Callers</a>

            <a class="nav-tab <?= $active_tab=="leads" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=leads" ><span><i class="fa fa-bullhorn"></i></span> Leads</a>
            
            <a class="nav-tab <?= $active_tab=="performance" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=performance" ><span><i class="fa fa-bar-chart"></i></span> Performance</a>

			<a class="nav-tab <?= $active_tab=="score_board" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=score_board" ><span><i class="fa fa-star"></i></span> Score Board</a>
        </h2>
        <?php
    }

    public function calendar_navigation($active_tab=''){
        $page_url=admin_url('admin.php?page=calendar-events');
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="create-event" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=create-event" ><span><i class="fa fa-plus"></i></span> Create Calendar Events</a>
            <a class="nav-tab <?= $active_tab=="events" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=events" ><span><i class="fa fa-calendar"></i></span> Calendar Events</a>
            <a class="nav-tab <?= $active_tab=="add-new-calendar" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=add-new-calendar" ><span><i class="fa fa-plus"></i></span> Add New Calendar</a>
            <a class="nav-tab <?= $active_tab=="system-calendars" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=system-calendars" ><span><i class="fa fa-calendar"></i></span> System Calendars</a>
        </h2>
        <?php
    }

    public function cage_tracker_navigation($active_tab=''){
        $page_url=admin_url('admin.php?page=animal-cage-tracker');
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="cage-records" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=cage-records" ><span><i class="fa fa-list"></i></span> Cages</a>

            <a class="nav-tab <?= $active_tab=="create-record" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=create-record" ><span><i class="fa fa-plus"></i></span> Create Record</a>
        </h2>
        <?php
    }

    public function cage_tracker_records_navigation($active_tab=''){
        $page_url=admin_url('admin.php?page=animal-cage-tracker&tab=cage-records');
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="cage-records" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&cage_status=cage-records" ><span><i class="fa fa-list"></i></span> All Cages</a>

            <a class="nav-tab <?= $active_tab=="retrieved-cages" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&cage_status=retrieved-cages" ><span><i class="fa fa-list"></i></span> Retrieved Cages</a>

            <a class="nav-tab <?= $active_tab=="not-retrieved-cages" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&cage_status=not-retrieved-cages" ><span><i class="fa fa-list"></i></span> Not Retrieved Cages</a>

            <a class="nav-tab <?= $active_tab=="30-days-past" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&cage_status=30-days-past" ><span><i class="fa fa-list"></i></span> 30 Days Past Cages</a>

            
        </h2>
        <?php
    }

    public function branches_navigation($active_tab=''){
        $page_url=admin_url('admin.php?page='.$_GET['page']);
        ?>
        <h2 class="nav-tab-wrapper">        

        <a class="nav-tab <?= $active_tab=="test" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=test" ><span><i class="fa fa-map-marker"></i></span> test</a>

            <a class="nav-tab <?= $active_tab=="all-branches" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=all-branches" ><span><i class="fa fa-map-marker"></i></span> All Branches</a>

            <a class="nav-tab <?= $active_tab=="active-branches" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=active-branches" ><span><i class="fa fa-map-marker"></i></span> Active Branches</a>

            <a class="nav-tab <?= $active_tab=="inactive-branches" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=inactive-branches" ><span><i class="fa fa-map-marker"></i></span> Inactive Branches</a>

            <a class="nav-tab <?= $active_tab=="create-branch" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=create-branch" ><span><i class="fa fa-plus"></i></span> New Branch</a>
        </h2>
        <?php
    }

    public function tekcard_navigation( $active_tab = ''){
        $page_url=admin_url('admin.php?page='.$_GET['page']);
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="all-payments" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>" ><span><i class="fa fa-money"></i></span> All Payments</a>
            <a class="nav-tab <?= $active_tab=="confirmed-payments" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=confirmed-payments" ><span><i class="fa fa-check"></i></span> Confirmed Payments</a>
            <a class="nav-tab <?= $active_tab=="pending-confirmation" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=pending-confirmation" ><span><i class="fa fa-clock-o"></i></span> Pending Confirmation</a>
        </h2>
        <?php
    }

    public function office_staff( $active_tab = ''){
        $page_url=admin_url('admin.php?page='.$_GET['page']);
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="staff-members" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>" ><span><i class="fa fa-users"></i></span> Staff Members</a>

            <a class="nav-tab <?= $active_tab=="pending-verification" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=pending-verification" ><span><i class="fa fa-user-plus"></i></span> Pending Verification</a>

            <a class="nav-tab <?= $active_tab=="fired" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=fired" ><span><i class="fa fa-user-plus"></i></span> Fired Staff</a>

            <a class="nav-tab <?= $active_tab=="inactive" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=inactive" ><span><i class="fa fa-user-plus"></i></span> Inactive Staff</a>

            <a class="nav-tab <?= $active_tab=="signup" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=signup" ><span><i class="fa fa-user-plus"></i></span> Sign up</a>
           
        </h2>
        <?php
    }

    public function doorToDoorSales( $active_tab = ''){
        $page_url=admin_url('admin.php?page='.$_GET['page']);
        ?>
        <h2 class="nav-tab-wrapper">        

            <a class="nav-tab <?= $active_tab=="staff-members" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>" ><span><i class="fa fa-users"></i></span> Sales Persons</a>

            <a class="nav-tab <?= $active_tab=="pending-application" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=pending-application" ><span><i class="fa fa-clock-o"></i></span> Pending Applications</a>

            <a class="nav-tab <?= $active_tab=="fired-sales-persons" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=fired-sales-persons" ><span><i class="fa fa-ban"></i></span> Fired Sales Persons</a>

            <a class="nav-tab <?= $active_tab=="inactive-sales-persons" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=inactive-sales-persons" ><span><i class="fa fa-users"></i></span> Inactive Sales Persons</a>

        </h2>
        <?php
    }
    
    public function parkingTickets( $active_tab = ''){
        $page_url=admin_url('admin.php?page='.$_GET['page']);
        ?>
        <h2 class="nav-tab-wrapper">        

            <a class="nav-tab <?= $active_tab=="ticket-list" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=ticket-list" ><span><i class="fa fa-ticket"></i></span> Tickets List</a>

            <a class="nav-tab <?= $active_tab=="create-ticket" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=create-ticket" ><span><i class="fa fa-plus"></i></span> Create Ticket</a>

            <a class="nav-tab <?= $active_tab=="completed-ticket" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=completed-ticket" ><span><i class="fa fa-check"></i></span> Completed Tickets</a>

        </h2>
        <?php
    }

    public function gam_employees(){
        $technician = admin_url('admin.php?page=gam-technicians');
        $cold_caller = admin_url('admin.php?page=cold-caller');
        $office_staff = admin_url('admin.php?page=office-staff');
        $door_to_door_sales = admin_url('admin.php?page=door-to-door-sales');
        $payment_structure = admin_url('admin.php?page=payment-structure');

        $active_page = $_GET['page'];

        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_page == "gam-technicians" || $active_page=="" ? 'nav-tab-active' : ''; ?>" href="<?= $technician; ?>" ><span><i class="fa fa-users"></i></span> Technicians</a>
            <a class="nav-tab <?= $active_page == "cold-caller" ? 'nav-tab-active' : ''; ?>" href="<?= $cold_caller; ?>&user-type=cold-caller" ><span><i class="fa fa-users"></i></span> Cold Callers</a>
            <a class="nav-tab <?= $active_page == "office-staff" ? 'nav-tab-active' : ''; ?>" href="<?= $office_staff; ?>" ><span><i class="fa fa-users"></i></span> Office Staff</a>
            <a class="nav-tab <?= $active_page == "door-to-door-sales" ? 'nav-tab-active' : ''; ?>" href="<?= $door_to_door_sales; ?>" ><span><i class="fa fa-users"></i></span> Door To Door Sales</a>
        </h2>
        <?php
    }

    public function employeeNavigation($active_type = ''){
        $types = (new Employee\Employee)->getEmployeeTypes();
        if(count($types) <= 0) return;

        $page_url = admin_url('admin.php?page='.$_GET['page']);
        if(empty($active_type)) $active_type = 'technician';

        ?>
        <h2 class="nav-tab-wrapper">
            <?php foreach($types as $type): ?>
                <a class="nav-tab <?= $active_type == $type->slug ? 'nav-tab-active' : ''; ?>" href="<?= $page_url."&active_role=".$type->slug; ?>" ><span><i class="fa fa-user"></i></span> <?= $type->name; ?></a>
            <?php endforeach; ?>
        </h2>
        <?php
    }

    public function employeePaymentNavigation(){
        $pending_payments = admin_url('admin.php?page=pending-payments');
        $payment_structure = admin_url('admin.php?page=payment-structure');
        $payment_proofs = admin_url('admin.php?page=payment-proofs');
        $parking_tickets = admin_url('admin.php?page=parking-tickets');

        $active_page = $_GET['page'];

        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_page == "pending-payments" || $active_page=="" ? 'nav-tab-active' : ''; ?>" href="<?= $pending_payments; ?>" ><span><i class="fa fa-clock-o"></i></span> Pending Payments</a>
            <a class="nav-tab <?= $active_page == "payment-structure" ? 'nav-tab-active' : ''; ?>" href="<?= $payment_structure; ?>" ><span><i class="fa fa-money"></i></span> Payment Structure</a>
            <a class="nav-tab <?= $active_page == "payment-proofs" ? 'nav-tab-active' : ''; ?>" href="<?= $payment_proofs; ?>" ><span><i class="fa fa-list"></i></span> Payment Proofs</a>
            <a class="nav-tab <?= $active_page == "parking-tickets" ? 'nav-tab-active' : ''; ?>" href="<?= $parking_tickets; ?>" ><span><i class="fa fa-ticket"></i></span> Parking Tickets</a>
        </h2>
        <?php
    }

    public function coldCallerRolesNavigation( $active_tab = ''){
        $page_url=admin_url('admin.php?page='.$_GET['page']);
        ?>
        <h2 class="nav-tab-wrapper">        
    
            <a class="nav-tab <?= $active_tab=="available-roles" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=available-roles" ><span><i class="fa fa-list"></i></span> Available Roles</a>
    
            <a class="nav-tab <?= $active_tab=="assigned-roles" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=assigned-roles" ><span><i class="fa fa-link"></i></span> Assigned Roles</a>

            <a class="nav-tab <?= $active_tab=="cold-caller-types" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=cold-caller-types" ><span><i class="fa fa-list"></i></span> Cold Caller Types</a>
    
        </h2>
        <?php
    }    

    public function employee_notices($active_tab=''){
        $page_url=admin_url('admin.php?page='.$_GET['page']);
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="add-notice" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>" ><span><i class="fa fa-plus"></i></span> Add Notices </a>
            
            <a class="nav-tab <?= $active_tab=="view-notices" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=view-notices" ><span><i class="fa fa-map-marker"></i></span> View Notices</a>

            <a class="nav-tab <?= $active_tab=="critical-notices" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=critical-notices" ><span><i class="fa fa-exclamation-triangle"></i></span> Critical Notices</a>
        </h2>
        <?php
    }

    public function officeNotes($active_tab=''){
        $page_url=admin_url('admin.php?page='.$_GET['page']);
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="office-feedback" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>" ><span><i class="fa fa-list"></i></span> Office Feedback </a>
            
            <a class="nav-tab <?= $active_tab=="special-notes" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=special-notes" ><span><i class="fa fa-list"></i></span> Special Notes</a>

        </h2>
        <?php
    }

    /* Frontend Navigations */

    public function employeeTaskNavigation($active_tab=''){
        global $post;
        $post_slug = $post->post_name;
        $page_url = site_url()."/".$post_slug."?view=view-task";

        ?>
        <ul class="nav nav-tabs">        
            <li class="nav-item">
                <a class="nav-link <?= $active_tab=="" || $active_tab=="pending" ? 'active' : ''; ?>" href="<?= $page_url; ?>&status=pending" ><span><i class="fa fa-list"></i></span> Pending Tasks</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $active_tab=="completed" ? 'active' : ''; ?>" href="<?= $page_url; ?>&status=completed" ><span><i class="fa fa-list"></i></span> Completed Tasks</a>
            </li>
    </ul>
        <?php
    }

    public function officeRolesNavigation($active_tab=''){
        $page_url=admin_url('admin.php?page='.$_GET['page']);
        ?>
        <h2 class="nav-tab-wrapper">        
            <a class="nav-tab <?= $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>" ><span><i class="fa fa-list"></i></span> Roles</a>
            
            <a class="nav-tab <?= $active_tab=="linked_employees" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=linked_employees" ><span><i class="fa fa-link"></i></span> Linked Roles/Employees</a>

        </h2>
        <?php
    }

    // Cold calls
    public function coldcall_navigation($active_tab=''){
        $page_url=admin_url('admin.php?page=cold-calls');
        ?>
        <h2 class="nav-tab-wrapper">        
        <a class="nav-tab <?= $active_tab=="create-cold-calls" || $active_tab=="" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=create-cold-calls" ><span><i class="fa fa-phone"></i></span> Create Cold calls</a>
       
        <a class="nav-tab <?= $active_tab=="view-call-logs" ? 'nav-tab-active' : ''; ?>" href="<?= $page_url; ?>&tab=view-call-logs" ><span><i class="fa fa-link"></i></span> View Call Logs</a>
    </h2>
        <?php
    }


}