<?php

$fields = [
    'id','name','payment_type','pay_per_hour','total_hours'
];
$cold_callers=(new ColdCaller)->getAllColdCallers($fields);

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
                        <th>Cold Caller</th>
                        <th>Payment Type</th>
                        <th>Total Hours Worked</th>
                        <th>Pay Per Hour</th>
                        <th>Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if(is_array($cold_callers) && count($cold_callers)>0): ?>
                     <?php foreach($cold_callers as $cold_caller): ?>
                     <tr>
                        <td><?= $cold_caller->name; ?></td>
                        <td><?= (new GamFunctions)->beautify_string($cold_caller->payment_type); ?></td>
                        <td>
                           <?= !empty($cold_caller->total_hours) ?  $cold_caller->total_hours : 'N/A'; ?>
                        </td>
                        <td>
                           <?= !empty($cold_caller->pay_per_hour) ?  $cold_caller->pay_per_hour : 'N/A'; ?>
                        </td>
                        <td><button cold-caller-data='<?= json_encode($cold_caller) ?>' class="btn btn-primary edit_payment_type"><span><i class="fa fa-edit"></i></span> Edit</button></td>
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
            <form id="update_cold_caller_payment_method_form" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
			   <?php wp_nonce_field('update_cold_caller_payment_method'); ?>
               <input type="hidden" name="action" value="update_cold_caller_payment_method">
               <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
               <input type="hidden" name="cold_caller_id" value="">


                <div class="form-group">
                    <label for="">Payment Type</label>
                    <div class="radio">
                        <label><input name="payment_type" value="$40_per_lead" type="radio"  checked>$40 Per Lead</label>
                    </div>
                    <div class="radio">
                        <label><input name="payment_type" value="by_total_hours" type="radio" >Total Hours Worked In A Week</label>
                    </div>                    
                </div>
            
                <div class="by_total_hours_box">
                    <div class="form-group">
                        <label for="">Total Hours Worked</label>
                        <input type="number" class="form-control" name="total_hours_worked">
                    </div>
                    <div class="form-group">
                        <label for="">Pay Per Hour</label>
                        <input type="number" class="form-control" name="pay_per_hour">
                    </div>
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

            $('#update_cold_caller_payment_method_form').validate({
                rules:{
                    payment_type: "required",
                    total_hours_worked: "required",
                    pay_per_hour: "required",
                }
            });
            
            $('.edit_payment_type').on('click',function(){

                const cold_caller_data = JSON.parse(this.getAttribute('cold-caller-data'));

                if(cold_caller_data.payment_type=="$40_per_lead"){
                    $('input[name="payment_type"][value="$40_per_lead"]').attr('checked','checked');
                    $('.by_total_hours_box').addClass('hidden');
                }
                else{
                    $('input[name="payment_type"][value="by_total_hours"]').attr('checked','checked');
                    $('.by_total_hours_box').removeClass('hidden');
                }

                $('input[name="total_hours_worked"]').val(cold_caller_data.total_hours);
                $('input[name="pay_per_hour"]').val(cold_caller_data.pay_per_hour);
    
                $('input[name="cold_caller_id"]').val(cold_caller_data.id);

                $('#payment-structure').modal('show');
            });

            $('input[name="payment_type"]').on('change',function(){
                console.log($(this).val());
                if($(this).val()=="$40_per_lead"){
                    $('.by_total_hours_box').addClass('hidden');
                }
                else{
                    $('.by_total_hours_box').removeClass('hidden');
                }
            })
       })
   })(jQuery);
</script>