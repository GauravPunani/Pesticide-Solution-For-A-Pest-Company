<?php

global $wpdb;
$branches = (new Branches)->getAllBranches();
$partner_accessible = (new Branches)->partner_accessible_branches(true);
?>

<div class="container-fluid">

    <!-- Tools Rows  -->
    <div class="row">
        <div class="col-sm-12">
            <?= (new Notices)->get_notice_with_html('contract_not_found_on_calendar'); ?>            
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Admin Tools</h3>

                    <!-- Chemical Reports  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-flask"></i></span> Chemical Reports
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=chemical-reports-newyork'); ?>"><span><i class="fa fa-map-marker"></i></span> New York</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=chemical-reports-california'); ?>"><span><i class="fa fa-map-marker"></i></span> California</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=chemical-reports-florida'); ?>"><span><i class="fa fa-map-marker"></i></span> Florida</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=chemical-reports-newjersey'); ?>"><span><i class="fa fa-map-marker"></i></span> New Jersey</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=newyork-animal-trapping-report'); ?>"><span><i class="fa fa-map-marker"></i></span> New York Animal Trapping</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=chemical-report-texas'); ?>"><span><i class="fa fa-map-marker"></i></span> Texas Chemical Report</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>

                    <!-- Maintenance -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-wrench"></i></span> Maintenance
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=maintenance'); ?>"><span><i class="fa fa-wrench"></i></span> Monthly Maintenance</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=quarterly-maintenance'); ?>"><span><i class="fa fa-wrench"></i></span> Quarterly Maintenance</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=commercial-maintenance'); ?>"><span><i class="fa fa-wrench"></i></span> Commercial Maintenance</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=special-maintenance'); ?>"><span><i class="fa fa-wrench"></i></span> Special Maintenance</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=yearly-termite-contract'); ?>"><span><i class="fa fa-wrench"></i></span> Yearly Termite Contract</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>

                    <!-- Invoices  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-files-o"></i></span> Invoices
                                <span class="caret"></span></a>

                                <?php if(is_array($branches) && count($branches)>0): ?>
                                    <ul class="dropdown-menu">
                                        <?php foreach($branches as $branch): ?>
                                            <?php if(current_user_can('other_than_upstate')): ?>
                                                <li><a target="_blank" href="<?= admin_url('admin.php?page=invoice&branch_id='.$branch->id); ?>"><span><i class="fa fa-map-marker"></i></span> <?= $branch->location_name; ?></a></li>
                                            <?php elseif(in_array($branch->id, $partner_accessible)): ?>
                                                <li><a target="_blank" href="<?= admin_url('admin.php?page=invoice&branch_id='.$branch->id); ?>"><span><i class="fa fa-map-marker"></i></span> <?= $branch->location_name; ?></a></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>                
                        </div>
                    </div>

                    <!-- Calculation/Deposit  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-calculator"></i></span> Calculation<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=invoice-calculations'); ?>"><span><i class="fa fa-calculator"></i></span> Invoice Calculation</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=sales-tax'); ?>"><span><i class="fa fa-money"></i></span> Sales Tax</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=ads-spent'); ?>"><span><i class="fa fa-money"></i></span> Ad Spend</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>

                    <!-- Quotesheets -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-file-text-o"></i></span> Quotesheets
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=resdiential-quotesheet'); ?>"><span><i class="fa fa-home"></i></span> Residential Quotesheets</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=commercial-quotesheet'); ?>"><span><i class="fa fa-building-o"></i></span> Commercial Quotesheets</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=non-interested-maintenance-quotes'); ?>"><span><i class="fa fa-file"></i></span> Non-interested maintenance quotes</a></li>
                                    <li class="hidden"><a target="_blank" href="<?= admin_url('admin.php?page=quote-offered-services'); ?>"><span><i class="fa fa-file"></i></span> Services Offered</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>

                    <!-- Billing  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-money"></i></span> Billing
                                <span class="caret"></span></a>
                                <?php if(is_array($branches) && count($branches)>0): ?>
                                    <ul class="dropdown-menu">
                                        <?php foreach($branches as $branch): ?>
                                            <?php if(current_user_can('other_than_upstate')): ?>
                                                <li><a target="_blank" href="<?= admin_url('admin.php?page=billing&branch_id='.$branch->id); ?>"><span><i class="fa fa-map-marker"></i></span> <?= $branch->location_name; ?></a></li>
                                            <?php elseif(in_array($branch->id,$partner_accessible)): ?>
                                                <li><a target="_blank" href="<?= admin_url('admin.php?page=billing&branch_id='.$branch->id); ?>"><span><i class="fa fa-map-marker"></i></span> <?= $branch->location_name; ?></a></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>                
                        </div>
                    </div>

                    <!-- weekly deposit total  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=daily-deposit'); ?>"><span><i class="fa fa-money"></i></span> Daily Proof Of Deposit</a>
                        </div>
                    </div>

                    <!-- Service Reports  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=office-notes&tab=office-feedback'); ?>"><span><i class="fa fa-file"></i></span> Service Reports</a>
                        </div>
                    </div>
                    
                    <!-- Alert History  -->
                    <div class="col-md-3 hidden">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=alerts-history'); ?>"><span><i class="fa fa-history"></i></span> Alert History</a>
                        </div>
                    </div>

                    <!-- Reimbursement  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-exchange"></i></span> Reimbursement
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=reimbursement'); ?>"><span><i class="fa fa-user"></i></span> Technician Reimbursement</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=reimbursement&type=door-to-door-reimbursement'); ?>"><span><i class="fa fa-exchange"></i></span> Door To Door Reimbursement</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=reimbursement&type=office-staff-reimbursement'); ?>"><span><i class="fa fa-exchange"></i></span> Office Staff Reimbursement</a></li>
                                </ul>
                            </div>    
                        </div>
                    </div>

                    <!-- Unattributed Invoices/ weekly report generate  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=weekly-ads-alert'); ?>"><span><i class="fa fa-chain-broken"></i></span> Current Unatrributed Invoices</a>
                        </div>
                    </div>

                    <!-- Callrail Trackers  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-bullhorn"></i></span> Callrail
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=callrail-trackers'); ?>"><span><i class="fa fa-list"></i></span> All Callrail Trackers</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=callrail-trackers&tab=unattributed-trackers'); ?>"><span><i class="fa fa-unlink"></i></span> Unassigned Tracking Number</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=callrail-trackers&tab=create-tracker'); ?>"><span><i class="fa fa-plus"></i></span> Create Tracker</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>

                    <!-- Gam Employees  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-user"></i></span> Gam Employees
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">

                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=gam-technicians'); ?>"><span><i class="fa fa-users"></i></span> Technicians</a></li>

                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=cold-caller'); ?>"><span><i class="fa fa-users"></i></span> Cold Callers</a></li>

                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=office-staff'); ?>"><span><i class="fa fa-users"></i></span> Office Staff</a></li>

                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=door-to-door-sales'); ?>"><span><i class="fa fa-users"></i></span> Door To Door Sales</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>

                    <!-- Employees Payment -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-money"></i></span> Employees Payment
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">

                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=pending-payments'); ?>"><span><i class="fa fa-clock-o"></i></span> Pending Payments</a></li>

                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=payment-structure'); ?>"><span><i class="fa fa-money"></i></span> Payment Structure</a></li>


                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=payment-proofs'); ?>"><span><i class="fa fa-list"></i></span> Payment Proofs</a></li>

                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=parking-tickets'); ?>"><span><i class="fa fa-ticket"></i></span> Parking Tickets</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>


                    <!-- EMPLOYEE NOTICES  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-bell"></i></span> Employee Notices
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=employee-notices'); ?>"><span><i class="fa fa-plus"></i></span> Add Notice</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=employee-notices&tab=view-notices'); ?>"><span><i class="fa fa-bell"></i></span> View Notices</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=employee-notices&tab=critical-notices'); ?>"><span><i class="fa fa-exclamation-triangle"></i></span> Critical Notices</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>

                    <!-- CAR CENTER  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-car"></i></span> Car Center
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=gam-vehicles'); ?>"><span><i class="fa fa-car"></i></span> Vehicles</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=gam-vehicles&tab=mileage-proof'); ?>"><span><i class="fa fa-tachometer"></i></span> Mileage Proof</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=gam-vehicles&tab=oil-change-proof'); ?>"><span><i class="fa fa-filter"></i></span> Oil Change Proof</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=gam-vehicles&tab=break-pads-change-proof'); ?>"><span><i class="fa fa-car"></i></span> Break Pads Change Proof</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=gam-vehicles&tab=vehicle-condition-proof'); ?>"><span><i class="fa fa-car"></i></span> Vehicle Condition Proof</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=gam-vehicles&tab=car-wash-proof'); ?>"><span><i class="fa fa-car"></i></span> Car Wash Proof</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=gam-vehicles&tab=create-vehicles'); ?>"><span><i class="fa fa-car"></i></span> Create Vehicle</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>

                    <!-- Termite Paperwork  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-file"></i></span> Termite Paperwork
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=termite-paperwork'); ?>"><span><i class="fa fa-certificate"></i></span> Termite Certificate</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=termite-paperwork&tab=termite-graph'); ?>"><span><i class="fa fa-bar-chart"></i></span> Termite Graph</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=termite-paperwork&tab=florida-wood-inspection'); ?>"><span><i class="fa fa-file"></i></span> Florida Wood Inspection</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=termite-paperwork&tab=florida-consumer-consent'); ?>"><span><i class="fa fa-file"></i></span> Florida Consumer Consent</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=termite-paperwork&tab=npma-33'); ?>"><span><i class="fa fa-file"></i></span> NPMA 33</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>
                    
                    <!-- Task Manager  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=task-manager'); ?>"><span><i class="fa fa-tasks"></i></span> Task Manager</a> 
                        </div>
                    </div>

                    <!-- All Branches  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="https://trello.com/b/jGaa4SWW/yajurdeep-task-mangement-board"><span><i class="fa fa-trello"></i></span> Developer Tasks</a>
                        </div>
                    </div>

                    <!-- System Codes  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=system-codes'); ?>"><span><i class="fa fa-code"></i></span> System Codes</a> 
                        </div>
                    </div>

                    <!-- Termite Paperwork  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-calendar"></i></span> Calendar
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=calendar-events&tab=create-event'); ?>"><span><i class="fa fa-plus"></i></span> Create Calendar Events</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=calendar-events&tab=events'); ?>"><span><i class="fa fa-calendar"></i></span> Calendar Events</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=calendar-events&tab=add-new-calendar'); ?>"><span><i class="fa fa-plus"></i></span> Add New Calendar</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=calendar-events&tab=system-calendars'); ?>"><span><i class="fa fa-calendar"></i></span> System Calendars</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=animal-cage-tracker-new'); ?>"><span><i class="fa fa-list"></i></span> Animal Cage Tracker</a>
                        </div>
                    </div>                    

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=track-technician'); ?>"><span><i class="fa fa-map-marker"></i></span> Track Techniciain</a> 
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=email-database&tab=all-clients'); ?>"><span><i class="fa fa-users"></i></span> Clients Database</a> 
                        </div>
                    </div>

                    
                    <!-- Branches  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-map-marker"></i></span> Branches
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=test'); ?>"><span><i class="fa fa-map-marker"></i></span> test</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=branches'); ?>"><span><i class="fa fa-map-marker"></i></span> All Branches</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=branches&tab=create-branch'); ?>"><span><i class="fa fa-plus"></i></span> Create New Branch</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>                    

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=gam-videos'); ?>"><span><i class="fa fa-video-camera"></i></span> Gam Videos</a> 
                        </div>
                    </div>
                    
                    
                    <!-- E Roles  -->
                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-list"></i></span> Employee Roles
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=employee-roles'); ?>"><span><i class="fa fa-building-o"></i></span> Office Roles</a></li>
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=cold-caller-roles'); ?>"><span><i class="fa fa-phone"></i></span> Cold Caller Roles</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=bria-license-keys'); ?>"><span><i class="fa fa-key"></i></span> Bria License Keys</a> 
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=employee-dashboards'); ?>"><span><i class="fa fa-dashboard"></i></span> Employees Dashboards</a> 
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=employee-attendence'); ?>"><span><i class="fa fa-dashboard"></i></span> Employee Attendence</a> 
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=tekcard-payments'); ?>"><span><i class="fa fa-money"></i></span> Card Payments</a> 
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=flamingo'); ?>"><span><i class="fa fa-bullhorn"></i></span> Contact Forms Leads</a> 
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=dissatisfied-clients'); ?>"><span><i class="fa fa-frown-o"></i></span> Dissatisfied Clients</a> 
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=prospectus'); ?>"><span><i class="fa fa-file"></i></span> Prospect</a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=gam-settings'); ?>"><span><i class="fa fa-cog"></i></span> GAM Settings</a> 
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=landing-pages'); ?>"><span><i class="fa fa-list"></i></span> Ads Landing Pages</a> 
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <a target="_blank" href="<?= admin_url('admin.php?page=calendar-tech-address'); ?>"><span><i class="fa fa-map-marker"></i></span> Calendars Techniciain Address</a> 
                        </div>
                    </div>

                
                    <!-- Cold Calls  -->
                    <!-- <div class="col-md-3">
                        <div class="well well-sm text-center">
                            <div class="dropdown">
                                <a class="dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-phone"></i></span> Cold Calls
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a target="_blank" href="<?= admin_url('admin.php?page=cold-calls&tab=create-cold-calls'); ?>"><span><i class="fa fa-plus"></i></span> Create Cold Calls</a></li>
                                </ul>
                            </div>                
                        </div>
                    </div>       -->
                     <!-- Cold Calls  -->                           
                </div>
            </div>
        </div>
    </div>
    <!-- Tools Rows end  -->


    <!-- Technician Verification Pannel  -->
    <?php get_template_part('/include/admin/verificatin-codes/technician-codes'); ?>
</div>
