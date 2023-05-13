<?php

global $wpdb;

$conditions=[];

if(isset($_GET['branch_id']) && !empty($_GET['branch_id'])){
    if($_GET['branch_id']!="all"){
        $conditions[]=" I.branch_id='{$_GET['branch_id']}'";
    }
}

$conditions[]=" (I.status IS NULL or I.status='' or I.status='not_paid')";
$conditions[]=" (I.email IS NULL or  I.email='')";

if(count($conditions)>0){
    $conditions=(new GamFunctions)->generate_query($conditions);
}
else{
    $conditions="";
}

$unpaid_no_email_invoices=$wpdb->get_results("
select I.* from 
{$wpdb->prefix}invoices I
$conditions
order by client_name
");
return $invoices;


?>


<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <table id="no_email_table" class="table table-striped">
            <?php if(is_array($unpaid_no_email_invoices) && count((array)$unpaid_no_email_invoices)>0): ?>
                <caption><?= count($unpaid_no_email_invoices); ?> record found</caption>
            <?php else: ?>
                <caption>No Record Found</caption>
            <?php endif; ?>
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Client Address</th>
                        <th>Client Phone No.</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(is_array($unpaid_no_email_invoices) && count((array)$unpaid_no_email_invoices)>0): ?>
                    <?php foreach($unpaid_no_email_invoices as $key=>$val): ?>
                    <tr id="row_<?= $val->id; ?>">
                        <td><?= $val->client_name; ?></td>
                        <td><?= $val->address; ?></td>
                        <td><?= $val->phone_no; ?></td>
                        <td>
                            <button data-invoice-id="<?= $val->id; ?>" data-toggle="modal" data-target="#myModal" class="btn btn-primary invoice_add_email"><span><i class="fa fa-plus"></i></span> Add Email</button>
                            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                                <input type="hidden" name="action" value="download_invoice">
                                <input type="hidden" name="invoice_id" value="<?= $val->id; ?>">
                                <button class="btn btn-success"><span><i class="fa fa-download"></i></span></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            </table>
        </div>
    </div>
</div>


<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add Email to Invoice</h4>
        </div>
        <div class="modal-body">
            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" id="invoice-email-form" method="post">
                <input type="hidden" name="action" value="invoice_update_email">
                <input type="hidden" name="invoice-id" value="">
                <div class="form-group">
                    <label for="">Enter Email</label>
                    <input type="email" name="invoice-email" class="form-control">
                </div>
                <button class="btn btn-primary"><span><i class="fa fa-plus"></i> Add</span></button>
            </form>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>

  </div>
</div>

<script>
(function($){
    $(document).ready(function() {
    $('#no_email_table').DataTable();
} );
})(jQuery);
</script>