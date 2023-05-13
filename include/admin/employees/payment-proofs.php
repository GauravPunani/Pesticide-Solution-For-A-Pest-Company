<?php

global $wpdb;

$conditions=[];

$conditions[]=" P.payment_status = 'paid'";





if(!empty($_GET['employee_type'])) $conditions[] = " ER.slug = '{$_GET['employee_type']}'";
if(!empty($_GET['employee_id'])) $conditions[] = " E.id = '{$_GET['employee_id']}'";

if(!empty($_GET['employee_type'])){
    $type_slug = $_GET['employee_type'];
    $all_employees = (new Employee\Employee)->getAllEmployees([$type_slug]);
}else{
    $all_employees = (new Employee\Employee)->getAllEmployees();
}



$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows= $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}payments P

    left join {$wpdb->prefix}employees E
    on P.employee_id = E.id

    left join {$wpdb->prefix}employees_types ER
    on ER.id = E.role_id

    $conditions
");
$total_pages = ceil($total_rows / $no_of_records_per_page);

$employees_payments = $wpdb->get_results("
    select P.*, E.name
    from {$wpdb->prefix}payments P

    left join {$wpdb->prefix}employees E
    on P.employee_id = E.id

    left join {$wpdb->prefix}employees_types ER
    on ER.id = E.role_id

    $conditions
    order by P.created_at Desc
    LIMIT $offset, $no_of_records_per_page
");

// echo "<pre>"; print_r($conditions); die;

$employee_types = (new Employee\Employee)->getEmployeeTypes();

?>

<?php (new Navigation)->employeePaymentNavigation();?>

<div class="container-fluid">
    <div class="row">
            <div class="col-md-12 col-sm-12">
                <?php if(isset($_GET['search']) || isset($_GET['requester_id']) || isset($_GET['status']) || isset($_GET['date_created'])): ?>
                    <p class="alert alert-success alert-dismissible">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                            <?= $total_rows; ?> Records Found for the search : <b><?= $_GET['search']; ?></b><br>    
                        <?php endif; ?>
                        <?php if(isset($_GET['status']) && !empty($_GET['status'])): ?>
                            <?= $total_rows; ?> Records Found for the search : <b><?= $_GET['status']; ?></b><br>
                        <?php endif; ?>
                        <a class="btn btn-info" href="<?= strtok($_SERVER["REQUEST_URI"], '?'); ?>?view=view-task"><span><i class="fa fa-database"></i></span> Show All Records</a>
                    </p>
                <?php endif; ?>
                
            </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Filters</h3>
                    <form action="">
                        
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                        <div class="form-group">
                            <label for="">Employee Type</label>
                            <select name="employee_type"  class="form-control select2-field">
                                <option value="">All</option>
                                <?php if(is_array($employee_types) && count($employee_types) > 0): ?>
                                    <?php foreach($employee_types as $employee_type): ?>
                                        <option value="<?= $employee_type->slug; ?>" <?= (!empty($_GET['employee_type']) && $_GET['employee_type'] == $employee_type->slug) ? 'selected' : ''; ?>><?= $employee_type->name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">By Employee</label>
                            <select name="employee_id" class="form-control select2-field">
                                <option value="">All</option>
                                <?php if(is_array($all_employees) && count($all_employees) > 0): ?>
                                    <?php foreach($all_employees as $employee): ?>
                                        <option value="<?= $employee->id ?>" <?= (!empty($_GET['employee_id']) && $_GET['employee_id'] == $employee->id) ? 'selected' : ''; ?>><?= $employee->name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        

                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter</button>

                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <h3 class="page-header">Proof Of Payments</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Calulcated Commission</th>
                                <th>Amount Paid</th>
                                <th>Week</th>
                                <th>Payment Proof</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($employees_payments) && count($employees_payments)>0): ?>
                                <?php foreach($employees_payments as $employee_payment): ?>
                                    <tr>
                                        <td><?= $employee_payment->name; ?></td>
                                        <td><?= (new GamFunctions)->beautify_amount_field($employee_payment->calculated_commission); ?></td>
                                        <td><?= (new GamFunctions)->beautify_amount_field($employee_payment->amount_paid); ?></td>
                                        <?php 
                                            $week_start_date=date('d M Y',strtotime('this monday',strtotime($employee_payment->week)));
                                            $week_end_date=date(('d M Y'),strtotime('this sunday',strtotime($employee_payment->week)));
                                        ?>
                                        <td><?= $week_start_date." to ".$week_end_date; ?></td>
                                        <td><button data-description="<?= $employee_payment->payment_description; ?>" data-docs='<?= $employee_payment->proof_docs; ?>' class="btn btn-primary show_docs"><span><i class="fa fa-eye"></i></span> View</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No Payment Proof Found</td>
                                </tr>
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