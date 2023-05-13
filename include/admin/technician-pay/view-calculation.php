<?php

$week_start_date=date('Y-m-d',strtotime('this monday',strtotime($_GET['week'])));
$week_end_date=date(('Y-m-d'),strtotime('this sunday',strtotime($_GET['week'])));

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">

            <!-- PAYMENT CALCULATION  -->
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Payment Calculation</h3>
                    <?php
                        list($calculation_html,$final_commission)=(new TechnicianPay)->payment_calculation($_GET['user_id'],$week_start_date,$week_end_date); 
                        echo $calculation_html;
                    ?>
                </div>
            </div>

            <!-- INVOICE/MAINTENANCE QUOTE BREAKDOWN -->
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Invoice Maintenance Breakdown</h3>
                    <?= (new TechnicianPay)->invoice_maintenance_quotes_breakdown($_GET['user_id'],$week_start_date,$week_end_date); ?>
                </div>
            </div>

            <!-- PAYMENT CALCULATION  -->
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Payment Summary</h3>
                    <?= (new TechnicianPay)->payment_summary($_GET['user_id'],$week_start_date,$week_end_date,$_GET['week'],$final_commission); ?>
                </div>
            </div>



        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
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
        })
    })(jQuery);
</script>