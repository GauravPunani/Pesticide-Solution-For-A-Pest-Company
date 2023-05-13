<?php
$conditions = [];

if (isset($_GET['tab']) && !empty($_GET['tab'])) {
    switch ($_GET['tab']) {
        case 'resolved':
            $conditions[] = " UC.satisfaction_status = 'resolved'";
            $tbl_hd = 'Resolved Clients';
            break;
        case 'client_fired_us':
            $conditions[] = " UC.satisfaction_status = 'client_fired_us'";
            $tbl_hd = 'Client Fired Us';
            break;
        case 'client_still_upset':
            $conditions[] = " UC.satisfaction_status = 'client_still_upset'";
            $tbl_hd = 'Client Still Upset';
            break;
    }
} else {
    $conditions[] = " UC.satisfaction_status IS NULL OR UC.satisfaction_status = ''";
    $tbl_hd = 'Dissatisfied Clients';
}

$conditions = (new GamFunctions)->generate_query($conditions);
$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 50;
$offset = ($pageno - 1) * $no_of_records_per_page;

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}unsatisfied_clients UC
    left join {$wpdb->prefix}invoices I
    on UC.invoice_id = I.id
    $conditions
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$clients = $wpdb->get_results("
    select UC.reason,UC.id,UC.satisfaction_status, I.client_name, I.email, I.phone_no, I.date, I.total_amount
    from {$wpdb->prefix}unsatisfied_clients UC
    left join {$wpdb->prefix}invoices I
    on UC.invoice_id = I.id
    $conditions
    LIMIT $offset, $no_of_records_per_page
");
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header"><?= $tbl_hd; ?></h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone No.</th>
                                <th>Invoice Amount</th>
                                <th>Reason</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($clients) && count($clients) > 0) : ?>
                                <?php foreach ($clients as $client) : ?>
                                    <tr>
                                        <td><?= $client->client_name; ?></td>
                                        <td><?= $client->email; ?></td>
                                        <td><?= $client->phone_no; ?></td>
                                        <td>$<?= $client->total_amount; ?></td>
                                        <td><?= nl2br($client->reason); ?></td>
                                        <td><?= date('d M Y', strtotime($client->date)); ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <?php 
                                                $status = '';
                                                if(!empty($client->satisfaction_status)) : 
                                                    $status = $client->satisfaction_status;
                                                endif;?>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                    <li><a onclick="showPopupToUpdateClientStatus(<?= $client->id;?>,'<?= $status;?>')" href="javascript:void(0)"><span><i class="fa fa-edit"></i></span> Update satisfaction status</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6">No Record Found</td>
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

<div id="updateClientStatusModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Update Situations Status</h4>
            </div>
            <div class="modal-body">
                <form id="updateClientStatusForm" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">

                    <?php wp_nonce_field('tm_update_task_status_by_office'); ?>

                    <input type="hidden" name="action" value="tm_update_task_status_by_office">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <input type="hidden" name="client_id" value="">

                    <div class="form-group">
                        <label for="">Select Status*</label>
                        <select name="status" class="form-control select2-field">
                            <option value="">Select Status</option>';
                            <option value="resovled">Resovled</option>
                            <option value="client_still_upset">Client still upset</option>
                            <option value="client_fired_us">Client fired us</option>
                        </select>
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Update Status</button>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<style>
    select.swal2-select {
        max-width: none;
    }

    h2#swal2-title {
        font-size: 25px;
    }
</style>

<script>
    function showPopupToUpdateClientStatus(client_id,status) {
        Swal.fire({
            title: 'Update Satisfaction Status',
            input: 'select',
            customClass: {
                input: 'status-update-field'
            },
            inputOptions: {
                'resolved': 'Resovled',
                'client_still_upset': 'Client still upset',
                'client_fired_us': 'Client fired us'
            },
            inputPlaceholder: 'Select status',
            showCancelButton: true,
            confirmButtonText: 'Update Status',
            showLoaderOnConfirm: true,
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to choose correct option!'
                }
            },
            preConfirm: (status) => {
                return jQuery.ajax({
                    type: "post",
                    url: "<?= admin_url('admin-ajax.php'); ?>",
                    data: {
                        client_id: client_id,
                        client_status: status,
                        action: "update_client_invoice_situation_status",
                        "_wpnonce": "<?= wp_create_nonce('update_client_invoice_situation_status'); ?>",
                    },
                    dataType: "json",
                    success: function(data) {
                        if (data.status == "success") {
                            return true;
                        } else {
                            Swal.showValidationMessage(data.message)
                        }
                    },
                    error: function() {
                        Swal.showValidationMessage(`Something went wrong, please try again later`)
                    }
                })
            }
        })
        .then((result) => {
            if (result.isConfirmed) {
                new swal('Success!', result.value.message, 'success').then(() => {
                    window.location.reload();
                })
            }
        })
        jQuery('.status-update-field').val(status).attr("selected","selected");
    }
</script>