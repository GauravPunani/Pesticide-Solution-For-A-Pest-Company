<?php

$payment_structures=(new Technician_details)->get_all_technicians(false);
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Technician</th>
                                <th>Payment Type</th>
                                <th>Payment Amount(minimum)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($payment_structures) && count($payment_structures)>0): ?>
                                <?php foreach($payment_structures as $payment_structure): ?>
                                    <tr>
                                        <td><?= $payment_structure->first_name." ".$payment_structure->last_name ; ?></td>
                                        <td><?= (new GamFunctions)->beautify_string($payment_structure->payment_type); ?></td>
                                        <td><?= (new GamFunctions)->beautify_amount_field($payment_structure->payment_amount); ?></td>
                                        <td><button data-tech-id="<?= $payment_structure->id; ?>" data-payment-type="<?= $payment_structure->payment_type; ?>" data-payment-amount="<?= $payment_structure->payment_amount; ?>" class="btn btn-primary edit_payment_type"><span><i class="fa fa-edit"></i></span> Edit</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>


                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Structure Modal -->
<div id="payment-structure" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Update Payment Structure</h4>
      </div>
      <div class="modal-body">
            <form id="update_payment_method_form" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
				<?php wp_nonce_field('update_payment_method'); ?>
                <input type="hidden" name="action" value="update_payment_method">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="user_id" value="">
                
                <div class="form-group">
                    <label for="">Payment Type</label>
                    <select name="payment_type" class="form-control" required>
                        <option value="">Select</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="">Amount</label>
                    <input type="text" class="form-control" name="amount" required>
                </div>

                <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Upload Payment Method</button>
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
            $('.edit_payment_type').on('click',function(){
                let user_id=$(this).attr('data-tech-id');
                let payment_type=$(this).attr('data-payment-type');
                let payment_amount=$(this).attr('data-payment-amount');

                $('select[name="payment_type"] > option').each(function(){
                    if($(this).val()==payment_type){
                        $(this).attr('selected',true);
                    }
                    else{
                        $(this).attr('selected',false);
                    }
                });

                $('input[name="user_id"]').val(user_id);
                $('input[name="amount"]').val(payment_amount);

                $('#payment-structure').modal('show');
            })
        })
    })(jQuery);
</script>