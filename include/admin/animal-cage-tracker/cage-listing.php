<?php

$conditions = [];

if (!empty($_GET['search'])) {
    $whereSearch = (new GamFunctions)->get_table_coloumn($wpdb->prefix . 'cage_address');
    $conditions[] = (new GamFunctions)->create_search_query_string($whereSearch, $_GET['search'], "no_type");
}
if (!empty($_GET['branch_id'])) $conditions[] = " branch_id = '{$_GET['branch_id']}' ";

$show_action_btn = false;
if (!empty($_GET['retrieved_status'])) {
    if ($_GET['retrieved_status'] == "retrieved") $conditions[] = " retrieved = 1 ";
    if ($_GET['retrieved_status'] == "not_retrieved") $conditions[] = " retrieved = 0 ";
    if ($_GET['retrieved_status'] == "due_to_be_picked_up") {
        $conditions[] = " date(pickup_date) <= CURDATE() and retrieved = 0";
        $show_action_btn = true;
    }
}

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 50;
$offset = ($pageno - 1) * $no_of_records_per_page;
$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}cage_address
    $conditions
    order by updated_at desc
");
$total_pages = ceil($total_rows / $no_of_records_per_page);

$cages_addresses = $wpdb->get_results("
    select *
    from {$wpdb->prefix}cage_address
    $conditions
    order by updated_at desc
    LIMIT $offset, $no_of_records_per_page 
");

$branches = (new Branches)->getAllBranches();

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php (new GamFunctions)->getFlashMessage(); ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Filters</h3>
                    <form id="filtersForm" action="">

                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <div class="form-group">
                            <label for="">Search</label>
                            <input type="text" class="form-control" name="search" value="<?= !empty($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="e.g. client name, address etc..">
                        </div>

                        <div class="form-group">
                            <label for="">Branch</label>
                            <select name="branch_id" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php foreach ($branches as $branch) : ?>
                                    <option value="<?= $branch->id; ?>" <?= !empty($_GET['branch_id']) && $_GET['branch_id'] == $branch->id ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Retrieved Status</label>
                            <select name="retrieved_status" class="form-group select2-field">
                                <option value="">Select</option>
                                <option value="retrieved" <?= !empty($_GET['retrieved_status']) && $_GET['retrieved_status'] == "retrieved" ? 'selected' : ''; ?>>Retrieved</option>
                                <option value="not_retrieved" <?= !empty($_GET['retrieved_status']) && $_GET['retrieved_status'] == "not_retrieved" ? 'selected' : ''; ?>>Not Retrieved</option>
                                <option value="due_to_be_picked_up" <?= !empty($_GET['retrieved_status']) && $_GET['retrieved_status'] == "due_to_be_picked_up" ? 'selected' : ''; ?>>Cages due to be picked up</option>
                            </select>
                        </div>

                        <p><a onclick="resetFilters('filtersForm')" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Reset Filters</a></p>

                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Records</button>
                    </form>
                </div>
            </div>
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Animal Cage Tracker <small>(<?= $total_rows ?> address found)</small>
                        <?php if ($show_action_btn) : ?>
                            <button class="btn btn-info select_all_clients"><span><i class="fa fa-check"></i></span> Select All</button>
                            <button class="btn btn-success client_return_cage_notify" disabled="disabled"><span><i class="fa fa-plane"></i></span> Send Notification</button>
                        <?php endif; ?>
                    </h3>
                    <?php if (is_array($cages_addresses) && count($cages_addresses) > 0) : ?>
                        <?php foreach ($cages_addresses as $key => $address) : ?>
                            <?php
                            $cage_records = $wpdb->get_results("
                                    select CD.*, CONCAT_WS(' ', TD.first_name, TD.last_name) as technician_name
                                    from {$wpdb->prefix}cage_data CD
                                    left join {$wpdb->prefix}technician_details TD
                                    on CD.technician_id = TD.id
                                    where address_id = '$address->id'
                                    order by created_at desc
                                ");
                            ?>
                            <div class="panel-group">
                                <div class="panel panel-default">
                                    <?php if ($show_action_btn) : ?>
                                        <input type="checkbox" name="clients_cage_due[]" value="<?= $address->id; ?>" class="clients_cage_install">
                                    <?php endif; ?>
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" href="#collapse<?= $key; ?>"><span><i class="fa fa-play text-<?= $address->retrieved ? 'success' : 'danger'; ?>"></i></span> <?= $address->name; ?> - <?= $address->address; ?> <small>
                                                    <?php if (!$address->retrieved) : ?>
                                                        (
                                                        <?php if (strtotime(date('Y-m-d')) >= strtotime(($address->pickup_date))) : ?>
                                                            <b class="text-danger">Pickup Due</b>
                                                        <?php else : ?>
                                                            <b> Next Pickup Date :</b>
                                                        <?php endif; ?>
                                                        <?= date('d M Y', strtotime($address->pickup_date)); ?>
                                                        )
                                                    <?php endif; ?>
                                                </small>
                                            </a>
                                            <?php if (!$address->retrieved) : ?>
                                                <span class="dropdown">
                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                    <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                        <li>
                                                            <a onclick="extendPickupDate('<?= $address->id; ?>', '<?= $address->pickup_date; ?>')" href="javascript:void(0)">
                                                                <span><i class="fa fa-edit"></i></span> Extend Pickup Date
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a onclick="markEverythingRetrieved(<?= $address->id; ?>, this)" href="javascript:void(0)">
                                                                <span><i class="fa fa-check"></i></span> Mark Everything Retrieved
                                                            </a>
                                                        </li>
                                                        <li><a data-contact-id="<?= $address->invoice_id; ?>" class="view_reocurring_modal" data-toggle="modal" data-target="#reocurring_modal"><span><i class="fa fa-eye"></i></span> View contact details</a></li>
                                                    </ul>
                                                </span>
                                            <?php endif; ?>
                                        </h4>
                                    </div>
                                    <div id="collapse<?= $key; ?>" class="panel-collapse collapse">
                                        <div class="panel-body">

                                            <?php if (is_array($cage_records) && count($cage_records) > 0) : ?>
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Raccon Cages</th>
                                                            <th>Squirrel Cages</th>
                                                            <th>Total Cages On Site</th>
                                                            <th>Technician</th>
                                                            <th>Note</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($cage_records as $cage_record) : ?>
                                                            <tr>
                                                                <td><?= date('d M Y', strtotime($cage_record->created_at)); ?></td>
                                                                <td><?= $cage_record->racoon_cages; ?></td>
                                                                <td><?= $cage_record->squirrel_cages; ?></td>
                                                                <td><?= $cage_record->squirrel_cages + $cage_record->racoon_cages; ?></td>
                                                                <td><?= $cage_record->technician_name; ?></td>
                                                                <td><?= nl2br($cage_record->notes); ?></td>
                                                                <td>
                                                                    <span class="dropdown">
                                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                                        <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                                            <li><a data-cage-record="<?= htmlspecialchars(json_encode($cage_record), ENT_QUOTES); ?>" onclick="editAddressRecord(this)" href="javascript:void(0)"><span><i class="fa fa-edit"></i></span> Edit Record</a></li>
                                                                        </ul>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php else : ?>
                                                <p class="text-danger">No record found for the address</p>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                </div>
                            </div>


                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php (new GamFunctions)->render_pagination($pageno, $total_pages); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="extendPickupDateModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Extend Cage Pickup Date</h4>
            </div>
            <div class="modal-body">
                <form id="extendPickupDateForm" action="<?= admin_url('admin-post.php') ?>" method="post">

                    <?php wp_nonce_field('extend_pickup_date'); ?>
                    <input type="hidden" name="action" value="extend_pickup_date">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <input type="hidden" name="address_id">

                    <div class="form-group">
                        <label for="">Select Date</label>
                        <input type="date" name="pickup_date" class="form-control">
                    </div>
                    <button class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Update Pickup Date</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="editAddressRecordModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Address Record</h4>
            </div>
            <div class="modal-body">
                <form id="editAddressRecordForm" action="<?= admin_url('admin-post.php') ?>" method="post">

                    <?php wp_nonce_field('act_edit_address_record'); ?>
                    <input type="hidden" name="action" value="act_edit_address_record">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <input type="hidden" name="address_record_id">

                    <div class="form-group">
                        <label for="">Raccon Cages</label>
                        <input type="text" class="form-control" name="racoon_cages">
                    </div>

                    <div class="form-group">
                        <label for="">Squirrel Cages</label>
                        <input type="text" class="form-control" name="squirrel_cages">
                    </div>

                    <div class="form-group">
                        <label for="">Notes</label>
                        <textarea name="notes" cols="30" rows="5" class="form-control"></textarea>
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Update Address Record</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- View contact details -->
<div id="reocurring_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><strong>Contact Details</strong></h4>
            </div>
            <div class="modal-body stu_data">
            </div>
            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
            </div>
        </div>
    </div>
</div>

<script>
    function editAddressRecord(ref) {
        console.log('before parse');
        console.log(jQuery(ref).attr('data-cage-record'));
        address_record = jQuery.parseJSON(jQuery(ref).attr('data-cage-record'));
        console.log(address_record);
        jQuery('#editAddressRecordForm input[name="address_record_id"]').val(address_record.id);
        jQuery('#editAddressRecordForm input[name="racoon_cages"]').val(address_record.racoon_cages);
        jQuery('#editAddressRecordForm input[name="squirrel_cages"]').val(address_record.squirrel_cages);
        jQuery('#editAddressRecordForm textarea[name="notes"]').val(address_record.notes);

        jQuery('#editAddressRecordModal').modal('show');
    }

    function markEverythingRetrieved(address_id, ref) {
        if (!confirm('Are you sure you want to mark all cages on this address as retrieved ?')) return false;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            data: {
                "_wpnonce": "<?= wp_create_nonce('act_mark_everything_retrieved') ?>",
                action: 'act_mark_everything_retrieved',
                address_id
            },
            dataType: "json",
            beforeSend: function() {
                jQuery(ref).attr('disabled', true);
            },
            success: function(data) {
                alert(data.message);
                jQuery(ref).attr('disabled', false);
            },
            error: function() {
                console.error('Something went wrong');
                jQuery(ref).attr('disabled', false);
            }
        })
    }

    function extendPickupDate(address_id, current_pickup_date) {
        console.log('in pickup method');
        jQuery('#extendPickupDateForm input[name="address_id"]').val(address_id);
        jQuery('#extendPickupDateForm input[name="pickup_date"]').attr('min', current_pickup_date);
        jQuery('#extendPickupDateForm input[name="pickup_date"]').val(current_pickup_date);
        jQuery('#extendPickupDateModal').modal('show');
    }

    (function($) {
        $(document).ready(function() {
            $('#extendPickupDateForm').validate({
                rules: {
                    pickup_date: "required"
                }
            })

            jQuery('.clients_cage_install').on("click", function() {
                enable_btn_on_client_select('clients_cage_due[]', '.client_return_cage_notify');
            });

            let clicked = false;
            jQuery(".select_all_clients").on("click", function() {
                jQuery(".clients_cage_install").prop("checked", !clicked);
                clicked = !clicked;
                this.innerHTML = clicked ? '<i class="fa fa-close"></i></span> Deselect All' : '<i class="fa fa-check"></i></span> Select All';
                enable_btn_on_client_select('clients_cage_due[]', '.client_return_cage_notify');
            });

            $('.view_reocurring_modal').on('click', function() {
                let contact_id = $(this).attr('data-contact-id');
                $.ajax({
                    type: "post",
                    url: "<?= admin_url('admin-ajax.php'); ?>",
                    data: {
                        action: "view_contact_detail",
                        "_wpnonce": "<?= wp_create_nonce('view_contact_detail'); ?>",
                        contact_id: contact_id
                    },
                    beforeSend: function() {
                        $('#reocurring_modal .modal-body').html('<div class="loader"></div>');
                    },
                    success: function(data) {
                        $('#reocurring_modal .modal-body').html(data);
                    }
                })
            });

            jQuery(".client_return_cage_notify").on("click", function() {
                let atLeastOneIsChecked = jQuery('input[name="clients_cage_due[]"]:checked');
                if (atLeastOneIsChecked.length > 0) {
                    let selected_clients = new Array();
                    $('input[name="clients_cage_due[]"]:checked').each(function() {
                        selected_clients.push($(this).val());
                    });
                    jQuery.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        dataType: "json",
                        data: {
                            "_wpnonce": "<?= wp_create_nonce('client_bulk_notify') ?>",
                            action: 'notify_client_return_cage',
                            selected_clients
                        },
                        beforeSend: function() {
                            showLoader('Sending notification to clients, please wait...');
                        },
                        success: function(data) {
                            if (data.status === "success") {
                                new swal('Success!', data.message, 'success').then(() => {
                                    window.location.reload();
                                })
                            } else {
                                new Swal('Oops!', data.message, 'error');
                            }
                        },
                        error: function() {
                            new Swal('Oops!', 'Something went wrong, please try again later', 'error');
                        }
                    });
                }
            });
        });
    })(jQuery);
</script>