<?php

global $wpdb;

if(isset($_GET['view-calculation'])){
    get_template_part('/include/admin/technician-pay/view-calculation');
    return;
}

$conditions=[];

$conditions[]=" P.payment_status = 'paid' and P.role = 'technician'";

if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches(true);
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    $conditions[]=" T.branch_id IN ($accessible_branches)";
}

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_pages_sql = "
select count(*) as total_rows 
from {$wpdb->prefix}payments P
left join {$wpdb->prefix}technician_details T
on P.user_id=T.id
$conditions
";

$total_rows= $wpdb->get_var($total_pages_sql);
$total_pages = ceil($total_rows / $no_of_records_per_page);

$tech_payments=$wpdb->get_results("
select P.*,T.first_name,T.last_name 
from {$wpdb->prefix}payments P
left join {$wpdb->prefix}technician_details T
on P.user_id=T.id
$conditions
order by P.created_at Desc
LIMIT $offset, $no_of_records_per_page
");

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <h3 class="page-header">Proof Of Payments</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Technician</th>
                                <th>Calulcated Commission</th>
                                <th>Amount Paid</th>
                                <th>Week</th>
                                <th>Payment Proof</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($tech_payments) && count($tech_payments)>0): ?>
                                <?php foreach($tech_payments as $tech_payment): ?>
                                    <tr>
                                        <td><?= $tech_payment->first_name." ".$tech_payment->last_name; ?></td>
                                        <td><?= (new GamFunctions)->beautify_amount_field($tech_payment->calculated_commission); ?></td>
                                        <td><?= (new GamFunctions)->beautify_amount_field($tech_payment->amount_paid); ?></td>
                                        <?php 
                                            $week_start_date=date('d M Y',strtotime('this monday',strtotime($tech_payment->week)));
                                            $week_end_date=date(('d M Y'),strtotime('this sunday',strtotime($tech_payment->week)));
                                        ?>
                                        <td><?= $week_start_date." to ".$week_end_date; ?></td>
                                        <td><button data-description="<?= $tech_payment->payment_description; ?>" data-docs='<?= $tech_payment->proof_docs; ?>' class="btn btn-primary show_docs"><span><i class="fa fa-eye"></i></span> View</button></td>
                                        <td><?= date('d M Y',strtotime($tech_payment->created_at)); ?></td>
                                        <td>
                                            <a target="_blank" href="<?= $_SERVER['REQUEST_URI']."&week=$tech_payment->week&user_id=$tech_payment->user_id&view-calculation=true" ?>" class="btn btn-success"><span><i class="fa fa-eye"></i></span> View Calculation</a>
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

<!-- Docs Modal -->
<div id="proof-docs" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Proof of Payment</h4>
      </div>
      <div class="modal-body">
            <div class="payment-description"></div>
            <div class="proof-docs"></div>
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

            $(document).on('click','.upoad-payment-proof',function(){
                let payment_id=$(this).attr('data-payment-id');
                let tech_payble_amount=$(this).attr('data-tech-payment');
                let tech_id=$(this).attr('data-technician-id');
                let week=$(this).attr('data-week');

                $("#payble_amount").html(`$${tech_payble_amount}`);
                $("input[name='payment_id']").val(payment_id);
                $("input[name='payble_amount']").val(tech_payble_amount);
                $('input[name="user_id"]').val(tech_id);
                $('input[name="week"]').val(week);

                $('#proof-of-payment').modal('show');
            });

            $('.show_docs').on('click',function(){
                let docs=$(this).attr('data-docs');
                let payment_description=$(this).attr('data-description');
                docs=$.parseJSON(docs);

                payment_description_html="<p><b>Payment Description</b></p>";
                payment_description_html+=`<p>${payment_description}</p>`;

                docs_html=`
                    <table class='table table-striped table-hover'>
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>File Url</th>
                            </tr>
                        </thead>
                        <tbody>
                    `;

                $.each(docs,function(index,value){
                    docs_html+=`<tr>`;
                    docs_html+=`<td>${value.file_name}</td>`;
                    docs_html+=`<td><a target='_blank' href='${value.file_url}'><span><i class='fa fa-eye'></i></span> Show</a></td>`;
                    docs_html+=`</tr>`;
                });

                docs_html+="</tbody>";
                docs_html+="</table>";

                payment_description_html=payment_description_html.replace(/\n/g, '<br>');

                $('.payment-description').html(payment_description_html);
                $('.proof-docs').html(docs_html);

                $('#proof-docs').modal('show');

            });


        });
    })(jQuery);
</script>