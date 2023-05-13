<?php
$technician_id = (new Technician_details)->get_technician_id();
$technician_branch = (new Technician_details)->getTechnicianBranchSlug($technician_id);
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">

            <form class="res-form reset-form" id="technician_location_form"  method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">

                <div class="row">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-danger btn-sm pull-right" id="reset_invoice_page"><span><i class="fa fa-refresh"></i></span> Restart the page</button> 
                    </div>
                </div>


                <input type="hidden" name="pesticide_used" value="true">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                <?php
                    switch ($technician_branch) {
                        case 'buffalo':
                        case 'upstate':
                        case 'rochester':
                            get_template_part('/include/frontend/chemical-reports/newyork-chemical-report');
                        break;

                        case 'ny_metro':
                            echo '
                                <div class="form-group">
                                    <label for="">Please select which state you are in</label>
                                    <select name="technician_state" id="technician_state" class="form-control select2-field">
                                        <option value="">Select</option>
                                        <option value="New York">New York</option>
                                        <option value="New Jersey">New Jersey</option>
                                    </select>
                                </div>';
                        break;

                        case 'san_francisco':
                        case 'los_angeles':
                            get_template_part('/include/frontend/chemical-reports/chemical-report-california');
                        break;

                        case 'houston':
                            get_template_part('/include/frontend/chemical-reports/chemical-report-texas');
                        break;

                        case 'fort_myers':
                        case 'miami':
                            get_template_part('/include/frontend/chemical-reports/florida-chemical-report');
                        break;

                        default:
                            get_template_part('/include/frontend/chemical-reports/newyork-chemical-report');
                        break;
                        
                    }
                ?>

                <div class="location-based-data"></div>
                <br>

                <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit Report</button>
            </form>

        </div>
    </div>
</div>

<div id="codemodal" class="modal fade" role="dialog">

    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Please enter your basic details</h4>
            </div>

            <div class="modal-body codecontent">
                <div class="error-info"></div>
                <div class="code-basic-details">
                    <form id="code_basic_details" action="" class="form-horizontal">
                        <input type="hidden" name="action" value="insert_technician_code">
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="name">Name</label>
                            <div class="col-sm-10">
                                <input type="text" name="code_name" id="code_name" class="form-control">
                            </div>
                        </div>
                        <button  id="sendcode" class="btn btn-primary">Request For Code Verification</button>
                    </form>
                </div>
                <div class="hidden code-verification">
                    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" id="codeverification" method="post">
                        <input type="hidden" name="action" id="code_action" value="technician_skip_chemical_report">
                        <input type="hidden" name="codeverify_name" id="codeverify_name"   value="">
                        <?php wp_nonce_field( 'skip_chemical_report' ); ?>
                        <div class="form-group">
                            <label for="code">Please Contact Back Office for code and enter it here</label>
                                <input type="text" maxlength="6" size="6" name="code" id="code" class="form-control" required>
                        </div>
                        <button id="verify_code" class="btn btn-primary">Verify Code</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>

<div id="technician_bypass" class="modal fade" role="dialog">

    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Please enter your basic details</h4>
            </div>

            <div class="modal-body codecontent">
                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" id="skip_chemcial_report_by_event" method="post">
                    <input type="hidden" name="action" value="technician_skip_chemical_report">
                    <input type="hidden" name="type" value="chemical_report">
                    <?php wp_nonce_field( 'skip_chemical_report' ); ?>

                    <div class="event_error text-danger text-left hidden"></div>
                    
                    <?php list($start_date, $end_date) = (new GamFunctions)->x_week_range(date('Y-m-d'),'sunday'); ?>
                    <div class="form-group">
                            <label for="date"><span><i class="fa fa-calendar"></i></span> Date<span class="text-danger"><small>*</small></span></label> 
                            <input class="form-control" id="event_date"  max="<?= $end_date ?>" name="startDate" type="date" > 
                    </div>
                    <!-- Technician Events -->
                    <div class="form-group">
                            <label for=" name">Select Appointment <span class="text-danger"><small>*</small></span></label>
                            <select name="technician_appointment" id="technician_bypass_events" class="form-control bypass_appointement calendar_events">
                                    <option value="">Select</option>
                            </select>
                    </div>
                    <div class="bypass-response">
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>