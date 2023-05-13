<?php

global $wpdb;

$technician_details = (new technician_details)->get_all_technicians();
$branches = (new Branches)->getAllBranches();

$conditions=[];

if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches(true);
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    $conditions[]=" TD.branch_id IN ($accessible_branches)";
}


if(isset($_GET['branch_id']) && !empty($_GET['branch_id'])){
    $conditions[]=" TD.branch_id = '{$_GET['branch_id']}'";
}

if(isset($_GET['technician_id']) && !empty($_GET['technician_id'])){
    $conditions[]=" DD.technician_id = '{$_GET['technician_id']}'";
}

if(isset($_GET['date']) && !empty($_GET['date'])){
    $conditions[]=" DATE(DD.date)='{$_GET['date']}'";
}

if(count($conditions)>0){
    $conditions=(new GamFunctions)->generate_query($conditions);
}
else{
    $conditions="";
}

if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$total_pages_sql = "
    select count(*) 
    from {$wpdb->prefix}daily_deposit DD
    LEFT JOIN {$wpdb->prefix}technician_details TD 
    on DD.technician_id=TD.id 
    $conditions
";

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 
$total_rows= $wpdb->get_var($total_pages_sql);
$total_pages = ceil($total_rows / $no_of_records_per_page);

$daily_deposit = $wpdb->get_results("
    select DD.*, TD.first_name, TD.last_name, TD.id as technician_id, TD.slug 
    from {$wpdb->prefix}daily_deposit DD
    LEFT JOIN {$wpdb->prefix}technician_details TD 
    on DD.technician_id=TD.id 
    $conditions
    order by FIELD(DD.status,'approved'), 
    date DESC 
    LIMIT $offset, $no_of_records_per_page
");
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12 col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center">Filter</h4>
                </div>
                <div class="card-body">
                        <form action="">
                            <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                            <div class="form-group">
                                <label for="">Branch</label>
                                <select name="branch_id" class="form-control select2-field">
                                    <option value="">Select</option>
                                    <?php if(is_array($branches) && count($branches)>0): ?>
                                        <?php foreach($branches as $branch): ?>
                                            <option value="<?= $branch->id; ?>" <?= (isset($_GET['branch_id']) && $_GET['branch_id'] == $branch->id) ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- technicians  -->
                            <div class="form-group">
                                <label for="">Technician</label>
                                <select name="technician_id" class="form-control select2-field">
                                    <option value="">Select</option>
                                    <?php if(is_array($technician_details) && count($technician_details)>0): ?>
                                        <?php foreach($technician_details as $technician): ?>
                                            <option value="<?= $technician->id; ?>" <?= (isset($_GET['technician_id']) && $_GET['technician_id'] == $technician->id) ? 'selected' : ''; ?>><?= $technician->first_name." ".$technician->last_name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="">Date</label>
                                <input type="date" name="date" class="form-control" value="<?= (isset($_GET['date']) && !empty($_GET['date'])) ? $_GET['date'] : ''; ?>">
                            </div>

                            <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Records</button>
                        </form>
                </div>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <table class="table table-hover table-striped">
                        <caption>Daily Deposit Proofs - Showing <?= $offset+1; ?> to <?= $offset+$no_of_records_per_page; ?> of <?= $total_rows; ?> Records</caption>
                        <thead>
                            <tr>
                                <td>Approve</td>
                                <th>Technician Name</th>
                                <th>Amount Deposited</th>
                                <th>Deposit Proofs</th>
                                <th>Discrepancy Amount</th>
                                <th>Discrepancy Description</th>
                                <th>Discrepancy Proofs</th>
                                <th>Cash Owed</th>
                                <th>Check Owed</th>
                                <th>Total Owed</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($daily_deposit) && count($daily_deposit)>0): ?>
                                <?php foreach($daily_deposit as $key=>$val): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="approve_deposit" data-technician-id="<?= $val->technician_id; ?>" data-date="<?= $val->date; ?>" data-deposit-id="<?= $val->id; ?>" <?= $val->status=="approved" ? 'checked' : ''; ?>>
                                        </td>
                                        <td><?= $val->first_name." ".$val->last_name; ?></td>
                                        <td>$<?= $val->total_amount; ?></td>
                                        <?php if(!empty($val->deposit_proof) && $val->deposit_proof[0] != "["): ?>
                                            <td><a class="btn btn-primary" target="_blank" href="<?= $val->deposit_proof; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                          <?php else: ?>
                                            <td>
                                                <button data-deposit-id="<?= $val->id; ?>" class="btn btn-primary show_deposit_docs" data-toggle="modal" data-target="#docs_modal" ><span><i class="fa fa-eye"></i></span> View</button>
                                            </td>
                                        <?php endif; ?>
                                        <?php 
                                        
                                        $cash_owed=$wpdb->get_var("
                                            select SUM(total_amount) 
                                            from {$wpdb->prefix}invoices 
                                            where technician_id = '$val->technician_id' 
                                            and payment_method='cash' 
                                            and DATE(date)='$val->date'
                                        "); 

                                        $check_owed=$wpdb->get_var("
                                            select SUM(total_amount)
                                            from {$wpdb->prefix}invoices 
                                            where technician_id = '$val->technician_id'
                                            and payment_method='check' 
                                            and DATE(date)='$val->date'
                                        ");
                                        
                                        $total_owed=$cash_owed+$check_owed;
                                        
                                        ?>
                                        <td>$<?= $val->dscrepancy_amount; ?></td>
                                        <td><?= $val->describe_discrepancy; ?></td>
                                        <?php if(!empty($val->dscrepancy_proof) && $val->dscrepancy_proof[0] != "["): ?>
                                            <td><a class="btn btn-info" target="_blank" href="<?= $val->dscrepancy_proof; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <?php else: ?>
                                            <td><button data-deposit-id="<?= $val->id; ?>" class="btn btn-info show_dscrepancy_deposit_docs" data-toggle="modal" data-target="#discrepancy_docs_modal" ><span><i class="fa fa-eye"></i></span> View</button></td>
                                        <?php endif; ?>
                                        <td><?= (new GamFunctions)->beautify_amount_field($cash_owed);?></td>
                                        <td><?= (new GamFunctions)->beautify_amount_field($check_owed);?></td>
                                        <td><?= (new GamFunctions)->beautify_amount_field($total_owed);?></td>
                                        <td><?= date('d M Y',strtotime($val->date)); ?></td>
                                        <td><button data-deposit-id="<?= $val->id; ?>" class="btn btn-danger delete_deposit"><span><i class="fa fa-trash"></i></span></button>
                                            <button data-total-amount="<?= $val->total_amount; ?>" data-dscrepancy-amount="<?= $val->dscrepancy_amount; ?>" data-dscrepancy-description="<?= $val->describe_discrepancy; ?>"  data-deposit-id="<?= $val->id; ?>" class="btn btn-success edit_deposit"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                                        </td>
                                        
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal -->
<div id="docs_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Document Proofs</h4>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<!-- -->
<div id="discrepancy_docs_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Dscrepancy Document Proofs</h4>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<!-- edit modal -->
<div id="daily_proof_admin_edit_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit Daily Deposit</h4>
      </div>
      <div class="modal-body">
            <form id="edit_deposit_docs_form" method="post" enctype="multipart/form-data" action="<?= admin_url('admin-post.php'); ?>">
				<?php wp_nonce_field('edit_admin_daily_proof_of_deposit'); ?>
                <input type="hidden" name="action" value="edit_admin_daily_proof_of_deposit">
                <input type="hidden" name="admin_proof_id" value="">
                <div class="form-group">
                    <label for="">Total Amount Deposited</label>
                    <input type="text" name="total_amount" class="form-control numberonly" required>
                </div>
                <div class="form-group">
                    <label for="">Discrepancy Amount</label>
                    <input type="text" name="dscrepancy_amount"  class="form-control numberonly">
                </div>
                <div class="form-group">
                    <label for="">Describe Discrepancy</label>
                    <textarea class="form-control" name="describe_discrepancy"></textarea>
                    
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
<!-- end edit modal -->

<script>
var global_deposit_id;
(function($){
    $(document).ready(function(){

        $('.show_deposit_docs').on('click',function(){

            let deposit_id=$(this).attr('data-deposit-id');

            // if(global_deposit_id==deposit_id){
            //     return true;
            // }
            // global_deposit_id=deposit_id;

            // get data from ajax 
            $.ajax({
                type:"post",
                url:"<?= admin_url( 'admin-ajax.php'); ?>",
                data:{
                    action:"get_daily_deposit_doc_proofs",
                    deposit_id:deposit_id,
					"_wpnonce": "<?= wp_create_nonce('get_daily_deposit_doc_proofs'); ?>"
                },
                beforeSend:function(){
                    $('#docs_modal .modal-body').html('<div class="loader"></div>');
                },
                success:function(data){
                    $('#docs_modal .modal-body').html(data);
                }
            })

        });

        // show dscrepancy deposit docs data from ajax
         $('.show_dscrepancy_deposit_docs').on('click',function(){

            let deposit_id=$(this).attr('data-deposit-id');

            // if(global_deposit_id==deposit_id){
            //     return true;
            // }

            // global_deposit_id=deposit_id;
            
            // get data from ajax 
            $.ajax({
                type:"post",
                url:"<?= admin_url( 'admin-ajax.php'); ?>",
                data:{
                    action:"get_daily_deposit_dscrepancy_doc_proofs",
                    "_wpnonce": "<?= wp_create_nonce('get_daily_deposit_dscrepancy_doc_proofs'); ?>",
                    deposit_id:deposit_id
                },
                beforeSend:function(){
                    $('#discrepancy_docs_modal .modal-body').html('<div class="loader"></div>');
                },
                success:function(data){
                    $('#discrepancy_docs_modal .modal-body').html(data);
                }
            })

        });

        $('.approve_deposit').on('click',function(){

            let deposit_id=$(this).attr('data-deposit-id');
            let date=$(this).attr('data-date');
            let technician_id=$(this).attr('data-technician-id');

            let checked=false;

            if($(this).prop('checked')==true){
                checked=true;
            }


            if(deposit_id!=""){
                $.ajax({
                type:"post",
                url:"<?= admin_url('admin-ajax.php'); ?>",
                data:{
                    action:'approve_disaprove_deposit',
                    checked:checked,
                    deposit_id:deposit_id,
                    date:date,
                    technician_id:technician_id,
					"_wpnonce": "<?= wp_create_nonce('approve_disaprove_deposit'); ?>"
                },
                beforeSend:function(){
                    console.log('before send');
                },
                success:function(data){
                    console.log('status updated');
                }
            })
            }

        });

        $('.delete_deposit').on('click',function(){

            if(confirm('Are you sure, you want to delete this proof of deposit, it may freeze technician account as well')){

                let ref=$(this);
                let deposit_id=$(this).attr('data-deposit-id');
                $.ajax({
                    type:"post",
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    data:{
                        action:"delete_daily_deposit",
                        deposit_id:deposit_id,
						"_wpnonce": "<?= wp_create_nonce('delete_daily_deposit'); ?>"
                    },
                    dataType:"json",
                    beforeSend:function(){
                        ref.attr('disabled',true);
                    },
                    success:function(data){
                        if(data.status=="success"){
                            ref.parent().parent().fadeOut();
                        }
                        else{
                            alert(data.message);
                            ref.attr('disabled',false);
                        }
                    }
                })
            }

        })
        $('.edit_deposit').on('click',function(){
            let deposit_id=$(this).attr('data-deposit-id');
            let total_amount=$(this).attr('data-total-amount');
            let dscrepancy_amount=$(this).attr('data-dscrepancy-amount');
            let describe_discrepancy=$(this).attr('data-dscrepancy-description');

            $('#edit_deposit_docs_form input[name="admin_proof_id"]').val(deposit_id);
            $('#edit_deposit_docs_form input[name="total_amount"]').val(total_amount);
            $('#edit_deposit_docs_form input[name="dscrepancy_amount"]').val(dscrepancy_amount);
            $('#edit_deposit_docs_form textarea[name="describe_discrepancy"]').val(describe_discrepancy);

            $('#daily_proof_admin_edit_modal').modal('show');

            $('#edit_deposit_docs_form').on('submit',function(e){
            e.preventDefault();

            $.ajax({
                type:"post",
                url:"<?php echo admin_url('admin-ajax.php'); ?>",
                data:$('#edit_deposit_docs_form').serialize(),
                dataType:"json",
                beforeSend:function(){

                },
                success:function(data){

                     $('#daily_proof_admin_edit_modal').modal('hide');
                     location.reload();
                }
            })

        })
        });
    });

})(jQuery);
</script>