<?php

global $wpdb;

$conditions=[];

$conditions[]=" CCP.payment_status='paid'";

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

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_pages_sql = "
select count(*) as total_rows 
from {$wpdb->prefix}cold_caller_payments CCP
left join {$wpdb->prefix}cold_callers CC
on CCP.cold_caller_id=CC.id
$conditions
";

$total_rows= $wpdb->get_var($total_pages_sql);
$total_pages = ceil($total_rows / $no_of_records_per_page);

$cold_caller_payments=$wpdb->get_results("
select CCP.*,CC.name 
from {$wpdb->prefix}cold_caller_payments CCP
left join {$wpdb->prefix}cold_callers CC
on CCP.cold_caller_id=CC.id
$conditions
order by CCP.date_created Desc
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
                                <th>Cold Caller</th>
                                <th>Calulcated Commission</th>
                                <th>Amount Paid</th>
                                <th>Week</th>
                                <th>Payment Proof</th>
                                <th>Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($cold_caller_payments) && count($cold_caller_payments)>0): ?>
                                <?php foreach($cold_caller_payments as $cc_payment): ?>
                                    <tr>
                                        <td><?= $cc_payment->name; ?></td>
                                        <td><?= (new GamFunctions)->beautify_amount_field($cc_payment->calculated_commission); ?></td>
                                        <td><?= (new GamFunctions)->beautify_amount_field($cc_payment->amount_paid); ?></td>
                                        <?php 
                                            $week_start_date=date('d M Y',strtotime('this monday',strtotime($cc_payment->week)));
                                            $week_end_date=date(('d M Y'),strtotime('this sunday',strtotime($cc_payment->week)));
                                        ?>
                                        <td><?= $week_start_date." to ".$week_end_date; ?></td>
                                        <td><button data-description="<?= $cc_payment->payment_description; ?>" data-docs='<?= $cc_payment->proof_docs; ?>' class="btn btn-primary show_docs"><span><i class="fa fa-eye"></i></span> View</button></td>
                                        <td><?= date('d M Y',strtotime($cc_payment->date_created)); ?></td>
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