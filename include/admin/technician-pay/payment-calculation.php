<?php
$branches= (new Branches)->getAllBranches(false);
$technicians=(new Technician_Details)->get_all_technicians(false);
?>

<div class="container">
    <div class="row">
        <?php (new GamFunctions)->getFlashMessage(); ?>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Technician Pay</h3>
                    <form id="tech-pay-calculation-form">
					<?php wp_nonce_field('calculate_tech_pay'); ?>
                        <input type="hidden" name="action" value="calculate_tech_pay">
                        <div class="form-group">
                            <label for="">Select Branch</label>
                            <select name="branch" class="form-control">
                                <option value="">Select</option>
                                <?php if(is_array($branches) && count($branches)>0): ?>
                                    <?php foreach($branches as $branch): ?>
                                        <option value="<?= $branch->slug; ?>"><?= $branch->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Select Week</label>
                            <input class="form-control" type="week" name="week">
                        </div>
                        <div class="form-group">
                            <label for="">Select Technician</label>
                            <select name="technician" class="form-control">
                                <option value="">Select</option>
                            <?php if(is_array($technicians) && count($technicians)>0): ?>
                                <?php foreach($technicians as $tech): ?>
                                    <option data-location="<?= $tech->state; ?>" value="<?= $tech->id; ?>"><?= $tech->first_name." ".$tech->last_name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-calculator"></i></span> Calculate Pay</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Payment Summary</h3>
                    <div class="payment-summary"></div>
                </div>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Payment Calculation</h3>
                    <div class="payment-calculation"></div>
                </div>
            </div>
        </div>

        <!-- Invoice/Maintenance/Quotes Breakdown -->
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Invoice/Maintenance/Quotes Breakdown</h3>
                    <div class="invoice-maintenance-breakdown"></div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php get_template_part('/include/admin/technician-pay/payment-proof-modal', null, ['role' => 'technician']); ?>

<script>
    (function($){
        $(document).ready(function(){

            $('select[name="branch"]').on('change',function(){
                let branch=$(this).val();

                $("select[name='technician'] > option").each(function() {

                        if($(this).val()!=""){
                            if($(this).attr('data-location')==branch){
                                $(this).removeClass('hidden');
                            }
                            else{
                                $(this).addClass('hidden');
                            }
                        }

                });                
            });


            $(document).on('click','.office_sold',function(){
                let ref=$(this);
                let office_sold=false;
                let type=$(this).attr('data-type');
                let id=$(this).attr('data-id');
                if($(this).prop('checked')==true){
                    office_sold=true;
                }

                // call ajax to update invoice credit field
                $.ajax({
                    type:'post',
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    data:{
                        office_sold:office_sold,
                        id:id,
                        type:type,
                        action:"update_credit",
						"_wpnonce": "<?= wp_create_nonce('update_credit'); ?>"
                    },
                    dataType:"json",
                    success:function(data){
                        if(data.status=="error"){
                            alert('Something went wrong while update office sold option');
                            ref.prop('checked',false);
                        }
                    }
                })
            });

            $(document).on('click','.upoad-payment-proof',function(){
                let tech_payble_amount=$(this).attr('data-tech-payment');
                let tech_id=$(this).attr('data-technician-id');
                let week=$(this).attr('data-week');

                $("#payble_amount").html(`$${tech_payble_amount}`);
                $("input[name='payble_amount']").val(tech_payble_amount);
                $('input[name="user_id"]').val(tech_id);
                $('input[name="week"]').val(week);

                $('#proof-of-payment').modal('show');
            });

            $('#tech-pay-calculation-form').validate({
                rules:{
                    week:"required",
                    technician:"required",
                    salary_type:"required",
                    tech_salary:"required",
                }
            })

            $('select[name="salary_type"]').on('change',function(){
                if($(this).val()=="normal_calculation"){
                    $('.manualy-salary').addClass('hidden');
                }
                else{
                    $('.manualy-salary').removeClass('hidden');
                }
            });

            $('#tech-pay-calculation-form').on('submit',function(e){
                e.preventDefault();
                $.ajax({
                    type:"post",
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    data:$(this).serialize(),
                    dataType:"json",
                    beforeSend:function(){
                        $('.invoice-maintenance-breakdown,.payment-summary,.payment-calculation').html(`<div class="loader"></div>`);
                    },
                    success:function(data){
                        $('.invoice-maintenance-breakdown').html(data.data.invoice_breakdown);
                        $('.payment-calculation').html(data.data.payment_calculation);
                        $('.payment-summary').html(data.data.payment_summary);
                        $('[data-toggle="tooltip"]').tooltip();
                    }
                })
            })
        })
    })(jQuery);
</script>