<?php

global $wpdb;

$data=$wpdb->get_results("select * from {$wpdb->prefix}daily_deposit where technician_id='{$_SESSION['technician_id']}'");

?>

<table class="table table-hover table-striped">
    <thead>
        <tr>
            <th>Id</th>
            <th>Total Amount</th>
            <th>Desposit Proofs</th>
            <th>Discrepancy Amount</th>
            <th>Description Discrepancy</th>
            <th>Discrepancy Proofs</th>
            <th>Approval Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if(is_array($data) && count($data)>0): ?>
            <?php foreach($data as $key=>$val): ?>
                <tr>
                    <td>#<?= $val->id; ?></td>
                    <td><?= $val->total_amount; ?></td>
                    <?php if(!empty($val->deposit_proof)): ?>
                    <td><button data-docs='<?= $val->deposit_proof; ?>' class="btn btn-primary open_docs_modal"><span><i class="fa fa-eye"></i></span> View</button></td>
                    <?php else: ?>
                    <td>No Doc Found</td>
                    <?php endif; ?>
                    <td><?= $val->dscrepancy_amount; ?></td>
                    <td><?= $val->describe_discrepancy; ?></td>
                    <?php if(!empty($val->dscrepancy_proof)): ?>
                    <td><button data-docs='<?= $val->dscrepancy_proof; ?>' class="btn btn-info open_docs_modal"><span><i class="fa fa-eye"></i></span> View</button></td>
                    <?php else: ?>
                    <td>No Doc Found</td>
                    <?php endif; ?>
                    <td><?= $val->status; ?></td>
                    <td><?= date('d M Y',strtotime($val->date)); ?></td>
                    <td><button data-total-amount="<?= $val->total_amount; ?>" data-dscrepancy-amount="<?= $val->dscrepancy_amount; ?>" data-dscrepancy-description="<?= $val->describe_discrepancy; ?>" data-deposit-id="<?= $val->id; ?>" class="btn btn-primary edit_daily_deposit"><span><i class="fa fa-edit"></i></span> Edit</button></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No Record Found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div id="docs_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Daily Deposit Docs</h4>
      </div>
      <div class="modal-body deposit_docs">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<div id="daily_proof_edit_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit Daily Deposit</h4>
      </div>
      <div class="modal-body">
            <p><i>Pleas note that if you edit the daily deposit, you'll have to get it approved from the office before you can continue with daily events</i></p>
            <form id="edit_deposit_docs_form" method="post" enctype="multipart/form-data" action="<?= admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="edit_daily_proof_of_deposit">
                <input type="hidden" name="proof_id" value="">
                <div class="form-group">
                    <label for="">Total Amount Deposited</label>
                    <input type="text" name="total_amount" class="form-control numberonly" required>
                </div>
                <div class="form-group">
                    <label for="">Add More Docuements (optional)</label>
                    <input type="file" class="form-control" name="deposit_docs[]" multiple>
                </div>
                <div class="form-group">
                    <label for="">Discrepancy Amount</label>
                    <input type="text" name="dscrepancy_amount"  class="form-control numberonly">
                </div>
                <div class="form-group">
                    <label for="">Describe Discrepancy</label>
                    <textarea class="form-control" name="describe_discrepancy"></textarea>
                    
                </div>
                <div class="form-group">
                    <label for="">Please Upload Proof Of Discrepancy (optional)</label>
                    <input type="file" class="form-control" name="dscrepancy_proof[]"  multiple>
                </div>
                <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update</button>
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
    $(document).ready(function(){

        $('.edit_daily_deposit').on('click',function(){
            let deposit_id=$(this).attr('data-deposit-id');
            let total_amount=$(this).attr('data-total-amount');
            let dscrepancy_amount=$(this).attr('data-dscrepancy-amount');
            let describe_discrepancy=$(this).attr('data-dscrepancy-description');

            $('#edit_deposit_docs_form input[name="proof_id"]').val(deposit_id);
            $('#edit_deposit_docs_form input[name="total_amount"]').val(total_amount);
            $('#edit_deposit_docs_form input[name="dscrepancy_amount"]').val(dscrepancy_amount);
            $('#edit_deposit_docs_form textarea[name="describe_discrepancy"]').val(describe_discrepancy);


            $('#daily_proof_edit_modal').modal('show');
        });

    });
})(jQuery);
</script>