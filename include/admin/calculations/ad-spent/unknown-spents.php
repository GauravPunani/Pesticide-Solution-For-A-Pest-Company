<?php

global $wpdb;

$unknown_spents=$wpdb->get_results("select * from {$wpdb->prefix}unknown_spends");
$campaigns=(new Callrail_new)->get_all_tracking_no('',false);

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Unkown Spents</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Tracking Name</th>
                                <th>Cost</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($unknown_spents) && count($unknown_spents)>0): ?>
                                <?php foreach($unknown_spents as $spent): ?>
                                    <tr>
                                        <td><?= $spent->campaign_name; ?></td>
                                        <td><?= (new GamFunctions)->beautify_amount_field($spent->cost); ?></td>
                                        <td><?= date('d M Y',strtotime($spent->date)); ?></td>
                                        <td><button data-spent-id="<?= $spent->id; ?>" class="btn btn-primary link_spent row_id_<?=$spent->id; ?>"><span><i class="fa fa-paperclip"></i></span> Link Spent</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No Unknown Spent Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Link Spent -->
<div id="link_spent" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Link Unknown Spent to Campaign</h4>
      </div>
      <div class="modal-body">
            <form id="link_spent_form" action="">
                <?php wp_nonce_field('link_uknown_spent'); ?>
                <input type="hidden" name="action" value="link_uknown_spent">
                <input type="hidden" name="spent_id" value="">
                <div class="form-group">
                    <label for="">Select Campaign</label>
                    <select name="campaign" class="form-control select2-field" required>
                        <option value="">Select</option>
                        <?php if(is_array($campaigns) && count($campaigns)>0): ?>
                            <?php foreach($campaigns as $campaign): ?>
                                <option value="<?= $campaign->id; ?>"><?= $campaign->tracking_name; ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <button class="btn btn-primary"><span><i class="fa fa-paperclip"></i></span> <span class="linking_btn">Link Spent</span></button>
            </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<script>

var row_id;
    (function($){
        $(document).ready(function(){

            $('#link_spent_form').on('submit',function(e){
                e.preventDefault();

                $.ajax({
                    type:"post",
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    dataType:"json",
                    data:$(this).serialize(),
                    beforeSend:function(){
                        $('.linking_btn').text('linking....').attr('disabled',true);
                    },
                    success:function(data){

                        if(data.status=="success"){
                            $(`.row_id_${row_id}`).parent().parent().fadeOut();
                            $('.linking_btn').text('Link Spent').attr('disabled',true);
                        }
                        else{
                            alert('Someting went wrong,please try again later');
                        }
                        $('select[name="campaign"]').prop('selectedIndex',0);

                        $('#link_spent').modal('hide');

                    }
                })

            })

            $('.link_spent').on('click',function(){

                row_id=$(this).attr('data-spent-id');

                let spent_id=$(this).attr('data-spent-id');
                console.log("spent id is"+spent_id);
                $('input[name="spent_id"]').val(spent_id);
                $('#link_spent').modal('show');
            })
        })
    })(jQuery);
</script>