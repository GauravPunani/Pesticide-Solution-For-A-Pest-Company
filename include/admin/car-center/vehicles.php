<?php

global $wpdb;

if (!empty($_GET['vehicle_id'])) return get_template_part('/include/admin/car-center/vehicle-info', null, ['vehicle_id' => $_GET['vehicle_id']]);

if (!empty($_GET['edit_vehicle_id'])) return get_template_part('/include/admin/car-center/edit-vehicle-info', null, ['data' => $_GET['edit_vehicle_id']]);

$conditions = [];

if (!empty($_GET['vehicle_status_id'])) $conditions[] = " V.status_id = '{$_GET['vehicle_status_id']}' ";

if (!empty($_GET['vehicle_owner'])) {
    switch ($_GET['vehicle_owner']) {
        case 'company-owned':
            $conditions[] = " V.owner = 'company'";
            break;
        case 'technician-owned':
            $conditions[] = " V.owner = 'technician'";
            break;
    }
}

if (!current_user_can('other_than_upstate')) {
    $accessible_branches = (new Branches)->partner_accessible_branches(true);
    $accessible_branches = "'" . implode("', '", $accessible_branches) . "'";

    $conditions[] = " T.branch_id IN ($accessible_branches)";
}

if (!empty($_GET['branch_id']) && $_GET['branch_id'] != "all") {
    $branch = esc_html($_GET['branch_id']);
    $conditions[] = " T.branch_id='$branch'";
}

if (!empty($_GET['search'])) {
    $whereSearch = (new GamFunctions)->get_table_coloumn($wpdb->prefix . 'vehicles');
    $conditions[] = (new GamFunctions)->create_search_query_string($whereSearch, $_GET['search'], 'no_type', 'V');
}

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 50;
$offset = ($pageno - 1) * $no_of_records_per_page;

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}vehicles V
    left join {$wpdb->prefix}technician_details T
    on V.id=T.vehicle_id
    left join {$wpdb->prefix}vehicle_status VS
    on V.status_id = VS.id
    $conditions
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$vehicles = $wpdb->get_results("
    select V.*,T.id as tech_id,T.first_name,T.last_name,T.vehicle_id , VS.name as status_name
    from {$wpdb->prefix}vehicles V
    left join {$wpdb->prefix}technician_details T
    on V.id=T.vehicle_id
    left join {$wpdb->prefix}vehicle_status VS
    on V.status_id = VS.id
    $conditions
    LIMIT $offset, $no_of_records_per_page
");

$branches = (new Branches)->getAllBranches();
$vehicle_statuses = (new CarCenter)->getVehicleStatuses();
$technicians = (new Technician_details)->getWithoutVehicleTechnicians(['id', 'first_name', 'last_name']);
$technicians_all = (new Technician_details)->getTechniciansWithVehicles();
?>

<div class="container-fluid">
    <div class="row">
        <?php (new GamFunctions)->getFlashMessage(); ?>
        <?php (new Navigation)->location_tabs(@$_GET['branch_id']); ?>
        <div class="form-group">
            <?php
            $page_url = $_SERVER['PHP_SELF'];
            $url_parameters = $_GET;
            $page_url = $page_url . "?" . http_build_query($url_parameters);
            echo '<h2 class="nav-tab-wrapper">'; ?>
            <?php foreach ($vehicle_statuses as $vehicle_status) : ?>
                <a href="<?= $page_url; ?>&vehicle_status_id=<?= $vehicle_status->id; ?>" class="nav-tab  <?= (!empty($_GET['vehicle_status_id']) && $_GET['vehicle_status_id'] == $vehicle_status->id) ? 'nav-tab-active' : ''; ?> " value="<?= $vehicle_status->id; ?>" <?= (!empty($_GET['vehicle_status_id']) && $_GET['vehicle_status_id'] == $vehicle_status->id) ? 'selected' : ''; ?>><span><i class="fa fa-location-arrow"></i></span> <?= $vehicle_status->name; ?></a>

            <?php endforeach;
            echo "</h2>";
            ?>
        </div>
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header"><span><i class="fa fa-filter"></i></span> Filter</h3>
                    <form id="filtersForm">

                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <div class="form-group">
                            <label for="">Search</label>
                            <input type="text" class="form-control" name="search" value="<?= !empty($_GET['search']) ? $_GET['search'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">Owner</label>
                            <select name="vehicle_owner" class="form-group select2-field">
                                <option value="">Select</option>
                                <option value="company-owned" <?= !empty($_GET['vehicle_owner']) && $_GET['vehicle_owner'] == "company-owned" ? 'selected' : ''; ?>>Company Owned</option>
                                <option value="technician-owned" <?= !empty($_GET['vehicle_owner']) && $_GET['vehicle_owner'] == "technician-owned" ? 'selected' : ''; ?>>Technician Owned</option>
                            </select>
                        </div>

                        <p><a onclick="resetFilters('filtersForm')" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Reset Filters</a></p>

                        <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                    </form>
                </div>
            </div>
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="card-title">Vehicles Information <small>(<?= $total_rows; ?> results found)</small></h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Plate No.</th>
                                <th>Owner</th>
                                <th>Assigned to</th>
                                <th>Status</th>
                                <th>Parking Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($vehicles) && count($vehicles) > 0) : ?>
                                <?php foreach ($vehicles as $vehicle) : ?>
                                    <tr>
                                        <td><?= $vehicle->year . " " . $vehicle->make . " " . $vehicle->model; ?></td>
                                        <td><?= $vehicle->plate_number; ?></td>
                                        <td><?= $vehicle->owner; ?></td>
                                        <td><?= $vehicle->first_name . " " . $vehicle->last_name; ?></td>
                                        <td><?= $vehicle->status_name; ?></td>
                                        <td><?= $vehicle->parking_address; ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                    <li>
                                                        <a href="<?= $_SERVER['REQUEST_URI']; ?>&vehicle_id=<?= $vehicle->id; ?>"><span><i class="fa fa-eye"></i></span> View</a>
                                                    </li>
                                                    <li>
                                                        <a href="<?= $_SERVER['REQUEST_URI']; ?>&edit_vehicle_id=<?= $vehicle->id; ?>"><span><i class="fa fa-edit"></i></span> Edit</a>
                                                    </li>
                                                    <?php if (empty($vehicle->first_name)) : ?>
                                                        <li>
                                                            <a onclick="deleteVehicles(<?= $vehicle->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete</a>
                                                        </li>
                                                    <?php endif ?>
                                                    <?php if (!empty($vehicle->first_name) && !empty($vehicle->last_name)) : ?>
                                                        <li>
                                                            <a onclick="swapTechCar(<?= $vehicle->id; ?>,<?= $vehicle->tech_id; ?>)" href="javascript:void(0)"><span><i class="fa fa-car"></i></span> Swap Car</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <a onclick="updateVehicleStatus(<?= $vehicle->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Update Vehicle Status</a>
                                                    </li>
                                                    <?php if (!empty($vehicle->vehicle_id)) : ?>
                                                        <li><a onclick="unlinkFromTechnician(<?= $vehicle->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-unlink"></i></span> Unlink From Technician</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="9">No Record Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno, $total_pages); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="updateVehicleStatusModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Update Vehicle Status</h4>
            </div>
            <div class="modal-body">
                <form id="updateVehicleStatusForm" action="<?= admin_url('admin-post.php') ?>" method="post">

                    <?php wp_nonce_field('update_vehicle_status'); ?>

                    <input type="hidden" name="action" value="update_vehicle_status">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <input type="hidden" name="vehicle_id">

                    <div class="form-group">
                        <label for="">Select Status</label>
                        <select name="status_slug" class="form-control select2-field">
                            <option value="">Select</option>
                            <?php if (is_array($vehicle_statuses) && count($vehicle_statuses) > 0) : ?>
                                <?php foreach ($vehicle_statuses as $vehicle_status) : ?>
                                    <option value="<?= $vehicle_status->slug; ?>"><?= $vehicle_status->name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group status_description_input hidden">
                        <div class="vehicle_lables hidden" id="placed_garbage_label">
                            <label for="">Please describe in which scrap yard it went and any other info.</label>
                        </div>
                        <div class="vehicle_lables hidden" id="sold_vehicle_label">
                            <label for="">Please desribe to whom vehicle was sold with all the info</label>
                        </div>
                        <textarea name="status_description" cols="30" rows="5" class="form-control"></textarea>
                    </div>

                    <div class="form-group hidden" id="assignableTechnicians">
                        <label for="">Please select technician to whom vehicle is assigned</label>
                        <select name="technician_id" class="form-control select2-field">
                            <option value="">Select</option>
                            <?php if (is_array($technicians) && count($technicians) > 0) : ?>
                                <?php foreach ($technicians as $technician) : ?>
                                    <option value="<?= $technician->id; ?>"><?= $technician->first_name . " " . $technician->last_name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group parking_address_box hidden">
                        <div class="parking_address_labeles" id="technician_parking_address_label">
                            <label for="">Please provide the parking address where technician is parking the vehicle</label>
                        </div>
                        <div class="parking_address_labeles" id="parked_somwhere_secure_label">
                            <label for="">Please provide the parking address where vehicle is parked securly</label>
                        </div>
                        <input type="text" class="form-control" name="parking_address" id="parking_address">
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Vehicle Status</button>
                </form>
            </div>
        </div>

    </div>
</div>

<div id="swapTechCarModal" class="modal fade" role="dialog" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Swap Technician Cars</h4>
            </div>
            <div class="modal-body">
                <form id="swapCarForm">

                    <?php wp_nonce_field('swap_car_within_technician'); ?>

                    <input type="hidden" name="action" value="swap_car_within_technician">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <input type="hidden" name="vehicle_id">

                    <div class="swapCarTechList">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="technician">Selected Technician</label>
                                <select class="form-control tech_from_swap" id="swapFirstElm" name="tech_from[0]">
                                    <option value="">Select</option>
                                    <?php foreach ($technicians_all as $technician) : ?>
                                        <option value="<?= urlencode($technician->id); ?>"><?= ucwords(str_replace('_', ' ', $technician->first_name . " " . $technician->last_name)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="technician">Swap Car With Technician</label>
                                <select class="form-control tech_with_swap" name="tech_with[0]">
                                    <option value="">Select</option>
                                    <?php foreach ($technicians_all as $technician) : ?>
                                        <option value="<?= urlencode($technician->id); ?>"><?= ucwords(str_replace('_', ' ', $technician->first_name . " " . $technician->last_name)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Swap Cars</button>
                </form>
            </div>
        </div>

    </div>
</div>

<div id="unlinkVehicleModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Unlink Vehicle From Technician</h4>
            </div>
            <div class="modal-body">
                <form id="unlinkVehicleForm" action="<?= admin_url('admin-post.php') ?>" method="post">

                    <?php wp_nonce_field('unlink_vehicle_from_technician'); ?>

                    <input type="hidden" name="action" value="unlink_vehicle_from_technician">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <input type="hidden" name="vehicle_id">

                    <div class="form-group">
                        <label for="">Please provide the parking address where vehicle is parked right now.</label>
                        <input type="text" name="parking_address" id="parking_address_new" class="form-control">
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Vehicle Status</button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    const parking_address = document.getElementById('parking_address');
    let autocomplete_parking_address;

    const parking_address_new = document.getElementById('parking_address_new');
    let autocomplete_parking_address_new;


    const updateVehicleStatus = (vehicle_id) => {
        jQuery('#updateVehicleStatusForm input[name="vehicle_id"]').val(vehicle_id);
        jQuery('#updateVehicleStatusModal').modal('show');
    }

    const swapTechCar = (vehicle_id, tech_id) => {
        jQuery('#swapFirstElm').val(tech_id).trigger("change");
        jQuery('#swapCarForm input[name="vehicle_id"]').val(vehicle_id);
        jQuery('#swapTechCarModal').modal('show');
    }

    const unlinkFromTechnician = (vehicle_id) => {
        jQuery('#unlinkVehicleForm input[name="vehicle_id"]').val(vehicle_id)
        jQuery('#unlinkVehicleModal').modal('show');
    }

    (function($) {
        $(document).ready(function() {

            initMap('parking_address', (err, autoComplete) => {
                autoComplete.addListener('place_changed', function() {
                    let place = autoComplete.getPlace();
                    parking_address.value = place.formatted_address;
                    autocomplete_parking_address = parking_address.value;
                });
            });

            initMap('parking_address_new', (err, autoComplete) => {
                autoComplete.addListener('place_changed', function() {
                    let place = autoComplete.getPlace();
                    parking_address_new.value = place.formatted_address;
                    autocomplete_parking_address_new = parking_address_new.value;
                });
            });

            $('#updateVehicleStatusForm select[name="status_slug"]').on('change', function() {
                const status_slug = $(this).val()

                console.log('status slug is' + status_slug);

                jQuery('.vehicle_lables').addClass('hidden');
                jQuery('.parking_address_labeles').addClass('hidden');
                jQuery('#assignableTechnicians').addClass('hidden');
                jQuery('.status_description_input').addClass('hidden');

                if (status_slug == "assigned_to_employee" || status_slug == "parked_somewhere_secure")
                    jQuery('.parking_address_box').removeClass('hidden')
                else
                    jQuery('.parking_address_box').addClass('hidden')

                if (status_slug == "assigned_to_employee") {
                    jQuery('#normal_vehicle_label').removeClass('hidden');
                    jQuery('#technician_parking_address_label').removeClass('hidden');
                    jQuery('#assignableTechnicians').removeClass('hidden');
                } else if (status_slug == "parked_somewhere_secure") {
                    jQuery('#normal_vehicle_label').removeClass('hidden');
                    jQuery('#parked_somwhere_secure_label').removeClass('hidden');
                } else if (status_slug == "placed_in_garbage") {
                    jQuery('.status_description_input').removeClass('hidden');
                    jQuery('#placed_garbage_label').removeClass('hidden');
                } else if (status_slug == "sold") {
                    jQuery('.status_description_input').removeClass('hidden');
                    jQuery('#sold_vehicle_label').removeClass('hidden')
                }


            })

            $('#updateVehicleStatusForm').validate({
                rules: {
                    status_slug: "required",
                    status_description: "required",
                    parking_address: "required",
                    technician_id: "required",
                },
                submitHandler: function() {
                    const status_slug = $('#updateVehicleStatusForm select[name="status_slug"]').val();

                    if (status_slug == "assigned_to_employee" || status_slug == "parked_somewhere_secure") {
                        const input_parking_address = $('#updateVehicleStatusForm input[name="parking_address"]').val();
                        if (input_parking_address != autocomplete_parking_address)
                            return alert('Please make sure parking address is selected from suggessted address');
                    }

                    return true;
                }
            })

            $('#unlinkVehicleForm').validate({
                rules: {
                    parking_address: "required"
                },
                submitHandler: function() {
                    const input_parking_address = $('#unlinkVehicleForm input[name="parking_address"]').val();
                    if (input_parking_address != autocomplete_parking_address_new)
                        return alert('Please make sure parking address is selected from suggessted address');
                    return true;
                }
            })

            $('#swapCarForm').validate({
                rules: {
                    "tech_from[0]": "required",
                    "tech_with[0]": "required"
                },
                submitHandler: function(form) {
                    //send code to server and swap cars
                    $.ajax({
                        type: "post",
                        url: my_ajax_object.ajax_url,
                        dataType: "json",
                        data: $('#swapCarForm').serialize(),
                        beforeSend: function() {
                            $('#swapCarForm button.btn').text('Processing please wait...').prop('disabled', true);
                        },
                        success: function(data) {
                            if (data.status === "success") {
                                jQuery('#swapTechCarModal').modal('hide');
                                new swal('Success!', data.message, 'success').then(() => {
                                    location.reload();
                                })
                            } else {
                                swal.fire('Oops!', data.message, 'error');
                                $("<div class=\"alert alert-warning\">" + data.message + "</span>").insertBefore("#swapCarForm");
                                $('#swapCarForm button.btn').text('Swap Cars').prop('disabled', false);
                            }
                        }
                    });
                }
            });
        })
    })(jQuery)

    function deleteVehicles(vehicle_id, ref) {
        swal.fire({
                title: "Are you sure",
                text: "You want to delete this vehicles ?",
                showCancelButton: true,
                confirmButtonText: 'Yes, I am sure!',
                icon: "warning",
            })
            .then((willDelete) => {
                if (willDelete.isConfirmed) {
                    jQuery.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        dataType: "json",
                        data: {
                            action: "delete_car_center",
                            vehicle_id,
                            '_wpnonce': "<?= wp_create_nonce('delete_car_center'); ?>"
                        },
                    beforeSend: function() {
                            jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
                            showLoader('Deleting vehicles in system, please wait...');
                        },
                    success: function(data) {
                        if (data.status === "success") {
                            swal.close();
                            location.reload();
                        } else {
                            swal.fire(
                                'Oops!',
                                data.message,
                                'error'
                            );
                            jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
                        }
                    }
                })
            }
        });
    }
</script>