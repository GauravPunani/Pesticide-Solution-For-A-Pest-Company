<?php

if (empty($args)) return;

$technician_id = $args['id'];

global $wpdb;

$calendars = Calendar::getSystemCalendars();

$technician = (new Technician_details)->getTechnicianById($technician_id);

if ($technician->vehicle_id) {
    $vehicle = (new CarCenter)->getVehicleById($technician->vehicle_id);
}

$technician = $wpdb->get_row("
    select TD.* , V.year, V.make, V.model, V.plate_number, V.vin_number, V.parking_address, B.location_name
    from {$wpdb->prefix}technician_details TD 
    left join {$wpdb->prefix}vehicles V
    on TD.vehicle_id=V.id
    left join {$wpdb->prefix}branches B
    on B.id = TD.branch_id 
    where TD.id='$technician_id'
");

$freely_parked_vehicles = (new CarCenter)->getFreelyParkedVehicles();

$upload_dir = wp_upload_dir();

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <?php (new GamFunctions)->getFlashMessage(); ?>
                        </div>
                        <!-- Technician Information  -->
                        <div class="col-md-6">
                            <table class="table table-striped table-hover">
                                <caption>Technician Information</caption>
                                <tbody>
                                    <tr>
                                        <th>Name</th>
                                        <td><?= $technician->first_name . " " . $technician->last_name; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Username</th>
                                        <td><?= $technician->slug; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?= $technician->email; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date of birth</th>
                                        <td><?= date('d M Y', strtotime($technician->dob)); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Home Address</th>
                                        <td><?= $technician->address; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Social Security</th>
                                        <td><?= $technician->social_security; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Technician State</th>
                                        <td><?= $technician->state; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Branch</th>
                                        <td><?= $technician->location_name; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Calendar ID</th>
                                        <td><?= $technician->calendar_id; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Driver License</th>
                                        <?php if (!empty($technician->driver_license)) : ?>
                                            <td><a class="btn btn-primary" href="<?= $technician->driver_license; ?>" target="_blank"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <?php else : ?>
                                            <td class="text-danger">Not Found</td>
                                        <?php endif; ?>
                                    </tr>
                                    <tr>
                                        <th>Pesticide License</th>
                                        <?php if (!empty($technician->pesticide_license)) : ?>
                                            <td><a class="btn btn-primary" href="<?= $technician->pesticide_license; ?>" target="_blank"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <?php else : ?>
                                            <td>N/A</td>
                                        <?php endif; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Vehicle Information  -->
                        <?php if (!empty($vehicle)) : ?>
                            <div class="col-md-6">
                                <table class="table table-striped table-hover">
                                    <caption>Vehicle Information</caption>
                                    <tbody>
                                        <tr>
                                            <th>Year</th>
                                            <td><?= $technician->year; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Make</th>
                                            <td><?= $technician->make; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Model</th>
                                            <td><?= $technician->model; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Plate Number</th>
                                            <td><?= $technician->plate_number; ?></td>
                                        </tr>
                                        <tr>
                                            <th>VIN Number</th>
                                            <td><?= $technician->vin_number; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Parking Address of vehicle</th>
                                            <td><?= $technician->parking_address; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <h4>Vehicle Information</h4>
                            <p class="text-danger">No vehicled linked to account yet.</p>
                        <?php endif; ?>

                        <!-- Agreement Documents  -->
                        <div class="col-md-6">
                            <table class="table table-striped table-hover">
                                <caption>Agreement Documents</caption>
                                <tbody>
                                    <tr>
                                        <th>Independent Contract</th>
                                        <?php if (!empty($technician->independent_contractor)) : ?>
                                            <td><a class="btn btn-primary" target="_blank" href="<?= $upload_dir['baseurl'] . $technician->independent_contractor; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <?php else : ?>
                                            <td class="text-danger">Not Found</td>
                                        <?php endif; ?>

                                    </tr>
                                    <tr>
                                        <th>Non Compete</th>
                                        <?php if (!empty($technician->non_competes)) : ?>
                                            <td><a class="btn btn-primary" target="_blank" href="<?= $upload_dir['baseurl'] . $technician->non_competes; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <?php else : ?>
                                            <td class="text-danger">Not Found</td>
                                        <?php endif; ?>

                                    </tr>
                                    <tr>
                                        <th>Form W9</th>
                                        <?php if (!empty($technician->fw9_taxpayer)) : ?>
                                            <td><a class="btn btn-primary" target="_blank" href="<?= $upload_dir['baseurl'] . $technician->fw9_taxpayer; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <?php else : ?>
                                            <td class="text-danger">Not Found</td>
                                        <?php endif; ?>
                                    </tr>
                                    <tr>
                                        <th>Salary Contract</th>
                                        <?php if (!empty($technician->salary_1099_contract)) : ?>
                                            <td><a class="btn btn-primary" target="_blank" href="<?= $upload_dir['baseurl'] . $technician->salary_1099_contract; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <?php else : ?>
                                            <td class="text-danger">Not Found</td>
                                        <?php endif; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Approve Reject form  -->
                        <div class="col-md-6">
                            <?php if ($technician->application_status == "pending" || $technician->application_status == "rejected") : ?>
                                <h4 class="text-center">Verify & Fill Details</h4>
                                <?php $branches = (new Branches)->getAllBranches(); ?>
                                <form id="verifyTechnicianForm" autocomplete="off" action="<?= admin_url('admin-post.php') ?>" method="post">

                                    <?php wp_nonce_field('verify_technician_account'); ?>

                                    <input type="hidden" name="action" value="verify_technician_account">
                                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                                    <input type="hidden" name="technician_id" value="<?= $technician->id; ?>">

                                    <!-- branch  -->
                                    <div class="form-group">
                                        <label for="branch_id">Select Branch</label>
                                        <select class="branch_id form-control select2-field" name="branch_id" required>
                                            <option value="">Select</option>
                                            <?php if (is_array($branches) && count($branches) > 0) : ?>
                                                <?php foreach ($branches as $branch) : ?>
                                                    <option value="<?= $branch->id; ?>" <?= $technician->branch_id == $branch->id ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="">Select Technician Calendar <small><i>(from google calendar)</i></small></label>
                                        <select name="calendar_id" class="form-control calendar_accounts select2-field" required>
                                            <option value="">Select</option>
                                        </select>
                                    </div>

                                    <button class="btn btn-success"><span><i class="fa fa-check"></i></span> Verify Application</button>

                                </form>
                                <?php if ($technician->application_status != "rejected") : ?>
                                    <div class="separator">Or Reject Application</div>
                                    <form method="post" action="<?= admin_url('admin-post.php') ?>">
                                        <input type="hidden" name="action" value="reject_application">
                                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                                        <input type="hidden" name="technician_id" value="<?= $technician->id; ?>">
                                        <button class="btn btn-danger"><span><i class="fa fa-ban"></i></span> Reject Application</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($technician->application_status == "verified") : ?>
                                <button data-toggle="modal" data-target="#fire_technician" class="btn btn-danger"><span><i class="fa fa-ban"></i></span> Fire Technician</button>
                            <?php elseif ($technician->application_status == "fired") : ?>
                                <p><b>Reason Technician Got Fired -</b> <?= nl2br(stripslashes($technician->application_status_reason)); ?></p>
                                <button data-toggle="modal" data-target="#rehire_technician" class="btn btn-success"><span><i class="fa fa-plus"></i></span> Re-hire Technician</button>
                            <?php elseif ($technician->application_status == "resigned") : ?>
                                <p><b>Reason Technician Resigned -</b> <?= nl2br($technician->resign_reason); ?></p>
                                <button data-toggle="modal" data-target="#rehire_technician" class="btn btn-success"><span><i class="fa fa-plus"></i></span> Re-hire Technician</button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


<!-- Re-hire Technician -->
<div id="rehire_technician" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Re-hire Technician </h4>
            </div>
            <div class="modal-body">
                <form onsubmit="return confirm('Are you sure you want to re-hire this technician ?')" action="<?= admin_url('admin-post.php'); ?>" method="post">
                    <input type="hidden" name="action" value="rehire_technician">
                    <input type="hidden" name="technician_id" value="<?= $technician->id; ?>">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <div class="form-group">
                        <label for="">Select a vehicle to assign <a target="_blank" href="<?= admin_url('admin.php?page=gam-vehicles&tab=create-vehicles') ?>"><span><i class="fa fa-plus"></i></span> Create Vehicle</a></label>
                        <select name="vehicle_id" class="form-control select2-field" required>
                            <option value="">Select</option>
                            <?php if (is_array($freely_parked_vehicles) && count($freely_parked_vehicles) > 0) : ?>
                                <?php foreach ($freely_parked_vehicles as $fpv) : ?>
                                    <option value="<?= $fpv->id; ?>"><?= $fpv->year . " " . $fpv->make . " " . $fpv->model . " (" . $fpv->plate_number . ")"; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <button class="btn btn-success"><span><i class="fa fa-plus"></i></span> Re-hire Technician</button>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- Fire Technician -->
<div id="fire_technician" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Fire Technician </h4>
            </div>
            <div class="modal-body">
                <form onsubmit="return confirm('Are you sure you want to fire this technician ?')" method="post" action="<?= admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="fire_technician">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <input type="hidden" name="technician_id" value="<?= $technician->id; ?>">

                    <div class="form-group">
                        <label for="">Reason Technician Fired ?</label>
                        <textarea name="fire_reason" cols="30" rows="5" class="form-control"></textarea>
                    </div>

                    <?php if (!empty($vehicle) && $vehicle->owner == 'company') : ?>
                        <div class="form-group">
                            <label for="">Please provide the parking address where technician parked company vehicle.</label>
                            <input type="text" class="form-control" name="parking_address" id="parking_address">
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-danger"><span><i class="fa fa-ban"></i></span> Fire Technician</button>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<style>
    .separator {
        display: flex;
        align-items: center;
        text-align: center;
    }

    .separator::before,
    .separator::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #000;
    }

    .separator::before {
        margin-right: .25em;
    }

    .separator::after {
        margin-left: .25em;
    }
</style>

<script>
    function getCalendarAccounts(branch_id) {
        console.log('calendar accounts called');
        jQuery.ajax({
            type: 'post',
            url: "<?= admin_url('admin-ajax.php') ?>",
            data: {
                branch_id,
                action: "get_calendar_accounts",
                "_wpnonce": "<?= wp_create_nonce('get_calendar_accounts'); ?>"
            },
            dataType: "json",
            success: function(data) {
                console.log(data);
                if (data.status == "success") {
                    let accounts = data.data;
                    let accounts_option_html = '<option value="">Select</option>';

                    jQuery.each(accounts, function(key, value) {
                        accounts_option_html += `
                            <option value='${value.id}'>${value.name}</option>
                        `;
                    });

                    jQuery('.calendar_accounts').html(accounts_option_html);
                }
            }
        });
    }

    (function($) {
        $(document).ready(function() {

            const branch_id = $('#verifyTechnicianForm select[name="branch_id"]').val();
            if (branch_id != undefined) getCalendarAccounts(branch_id);

            $('.branch_id').select2().on('change', function() {
                const branch_id = $(this).val();
                getCalendarAccounts(branch_id);
            });

            if ($('#parking_address').length) {
                initMap('parking_address', (err, autoComplete) => {
                    autoComplete.addListener('place_changed', function() {
                        let place = autoComplete.getPlace();
                        parking_address.value = place.formatted_address;
                        autocomplete_parking_address = parking_address.value;
                    });
                });
            }
        });
    })(jQuery);
</script>