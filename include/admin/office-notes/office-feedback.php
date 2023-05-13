<?php

global $wpdb;

$conditions = [];

$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 20;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}office_notes
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

if(!empty($_GET['branch_id'])) $conditions[] = " B.id = '{$_GET['branch_id']}'";
if(!empty($_GET['technician_id'])) $conditions[] = " ONT.technician_id = '{$_GET['technician_id']}'";
if(!empty($_GET['date'])) $conditions[] = " date(ONT.date) = '{$_GET['date']}'";

if(!empty($_GET['search'])){
    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'office_notes');
    $conditions[] =(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'no_type', 'ONT');
}

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';

$notes = $wpdb->get_results("
    select ONT.*, TD.first_name, TD.last_name, I.type_of_service_provided,I.chemical_report_id, I.service_description, I.area_of_service, I.findings, I.invoice_no, I.address, I.client_notes, UC.reason
    from {$wpdb->prefix}office_notes ONT

    left join {$wpdb->prefix}technician_details TD
    on TD.id = ONT.technician_id

    left join {$wpdb->prefix}branches B
    on TD.branch_id = B.id

    left join {$wpdb->prefix}invoices I
    on ONT.invoice_id = I.id

    left join {$wpdb->prefix}unsatisfied_clients UC
    on ONT.unsatisfied_client_id = UC.id

    $conditions
    order by ONT.created_at desc
    LIMIT $offset, $no_of_records_per_page 
");
$technicians = (new Technician_details)->get_all_technicians();
$branches = (new Branches)->getAllBranches();

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Filters</h3>                    
                    <form id="filtersForm">
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                        <div class="form-group">
                            <label for="">Search</label>
                            <input type="text" class="form-control" name="search" placeholder="e.g. client name, note etc.." value="<?= !empty($_GET['search']) ? $_GET['search'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">Branch</label>
                            <select name="branch_id" class="form-control select2-field">
                                <option value="">All Branches</option>
                                <?php if(is_array($branches) && count($branches) > 0): ?>
                                    <?php foreach($branches as $branch): ?>
                                        <option value="<?= $branch->id; ?>" <?= (!empty($_GET['branch_id']) && $_GET['branch_id'] == $branch->id) ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Technician</label>
                            <select name="technician_id" class="form-control select2-field">
                                <option value="">All Technicians</option>
                            <?php if(is_array($technicians) && count($technicians) > 0): ?>
                                <?php foreach($technicians as $technician): ?>
                                    <option value="<?= $technician->id; ?>" <?= (!empty($_GET['technician_id']) && $_GET['technician_id'] == $technician->id) ? 'selected' : ''; ?>><?= $technician->first_name." ".$technician->last_name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Date <small><a onclick="clearDate()" href="javascript:void(0)"><i>Clear Date</i></a></small></label>
                            <input type="date" name="date" class="form-control" value="<?= !empty($_GET['date']) ? $_GET['date'] : ''; ?>">
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Records</button>
                    </form>
                </div>
            </div>
        </div>

        <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>

        <div class="col-sm-12">
            <h3><span><i class="fa fa-file"></i></span> Service Reports</h3>
        </div>        

        <div class="col-sm-12">
            <?php foreach($notes as $note): 
                    $client_email = (new Invoice)::getInvoiceById($note->invoice_id,['email']);
                ?>
                <div class="card full_width tale-responsive">
                    <div class="card-body"> 
                        <h3 class="page-header"><?= $note->client_name; ?>
                        <span class="notify-label pull-right">
                            <?php if(!empty($note->service_report)) : ?>
                                <span class="label label-info"><i class="fa fa-check"></i> Service Report Sent</span>
                            <?php else : ?>
                                <button data-service-info='<?php echo json_encode($note);?>' data-client-email='<?php echo (!empty($client_email->email) ? $client_email->email : '');?>' class="btn btn-success client_service_report_notify"><span><i class="fa fa-envelope"></i></span> Email Service Report</button>
                            <?php endif;?>
                            <button data-service-info='<?php echo json_encode($note);?>' class="btn btn-primary client_service_report_download"><span><i class="fa fa-download"></i></span> Download Service Report</button>
                        </span>
                        </h3>
                        <table class="table table-striped table-hover">
                            <tbody>
                                <tr>
                                    <th>Technician Name</th>
                                    <td><?= $note->first_name." ".$note->last_name; ?></td>
                                    <th>Invoice Number</th>
                                    <td><a target="_blank" href="<?= admin_url('admin.php?page=invoice&search='.$note->invoice_no); ?>"><?= $note->invoice_no; ?></a></td>
                                </tr>
                                <?php if($note->unsatisfied_client_id): ?>
                                <tr>
                                    <td> <p class="text-danger">Client was dissatisfied</p></td>
                                    <td colspan="3"><p><b> Reason</b></p><?= empty($note->unsatisfied_client_id) ? 'N/A' : nl2br($note->reason); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if($note->invoice_id): ?>
                                <tr>
                                    <th>Type of service provided</th>
                                    <td><?= $note->type_of_service_provided; ?></td>
                                    <th>Service Description</th>
                                    <td><?= $note->service_description; ?></td>
                                </tr>
                                <tr>
                                    <th>Area of service</th>
                                    <td><?= $note->area_of_service; ?></td>
                                    <th>Findings</th>
                                    <td><?= $note->findings; ?></td>
                                </tr>
                                <tr>
                                    <th>Past Invoices</th>
                                    <td><a target="_blank" href="<?= admin_url('admin.php?page=invoice&search='.$note->address); ?>"><span><i class="fa fa-file"></i></span> View Past Invoices</a></td>
                                    <th>Pesticides Used</th>
                                    <td>
                                        <?php
                                            $pest = (new ChemicalReport)->getPestsUsedInService($note->invoice_id); 
                                            if(!empty($pest)){
                                                echo $pest;
                                            }else{
                                                echo "N/A";
                                            }
                                        ?>
                                    </td>                                    
                                </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4"><p class="text-danger">Invoice was not linked</p></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Notes / Comment</th>
                                    <td><?= nl2br($note->client_notes); ?></td>
                                    <th>Attachments</th>
                                    <td>
                                        <?php if(!empty($note->optional_images)): ?>
                                            <a data-note-id="<?= $note->id; ?>" data-attach='<?= $note->optional_images; ?>' data-model-id="listattachments" class="openmodal attachments" href="javascript:void(0)"><span><i class="fa fa-paperclip"></i></span> View</a>
                                        <?php else: ?>
                                            <p class="text-danger">Not found</p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>

                                    <th>Created At</th>
                                    <td colspan="3"><?= date('d M Y', strtotime($note->date)); ?></td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
        
    </div>
</div>

<form id="downloadServiceReportForm" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
    <?php wp_nonce_field('download_service_report'); ?>
    <input type="hidden" name="action" value="download_service_report">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
    <input type="hidden" name="service_report_data">
</form>

<div id="attachements_modal" class="modal fade" role="dialog"> 
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Attachments </h4> 
        </div>
        <div class="modal-body">
                <div class="all-attachments"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>

  </div>
</div>

<!-- EMAIL SERVICE REPORT MODAL  -->
<div id="service_report_modal" class="modal fade" rold="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3><span><i class="fa fa-file"></i></span> Service Report</h3>
            </div>
            <div class="modal-body">
                <form id="service_report_form" action="<?= admin_url('admin-post.php'); ?>" method="post">
                    <input type="hidden" name="action" value="notify_client_service_report">
                    <input type="hidden" name="service_data" value="">
                    <input type="hidden" name="_wpnonce" value="<?= wp_create_nonce('client_service_notify') ?>">
                    <div class="form-group">
                        <label for="">Enter Client Email</label>
                        <input type="text" class="form-control" name="client_email" value="">
                    </div>
                    <button id="service_report_submit_btn" class="btn btn-primary"><span><i class="fa fa-envelope"></i></span> <span id="service_report_submit_span">Send Service Report</span></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function clearDate(){
        jQuery('#filtersForm input[name="date"]').val("");
    }

    (function($){
        $(document).on('click','.attachments',function(){

        let attachments=$(this).attr('data-attach');
        attach_html='';

        if(attachments!=""){
            attach_html+= generateDocsHtml(attachments);
        }
        else{
            attach_html+="<p class='text-danger'>No Attachments Found</p>";
        }

        $('.all-attachments').html(attach_html);
        $('#attachements_modal').modal('show');
        });

        jQuery(".client_service_report_notify").on("click", function() {
            let email = jQuery(this).attr('data-client-email'),
                service_info = jQuery(this).attr('data-service-info'),
                service_data = JSON.parse(service_info);

            jQuery('#service_report_form input[name="client_email"]').val(email);
            jQuery('#service_report_form input[name="service_data"]').val(service_info);
            jQuery('#service_report_modal').modal('show');
        });

        jQuery(".client_service_report_download").on("click", function() {
            let service_info = jQuery(this).attr('data-service-info'),
                service_data = JSON.parse(service_info);
            jQuery('#downloadServiceReportForm input[name="service_report_data"]').val(service_info);
            jQuery('#downloadServiceReportForm').submit();
        });

        $('#service_report_form').on('submit',function(e){
            e.preventDefault();            
            $.ajax({
                type:"post",
                url:my_ajax_object.ajax_url,
                data:$(this).serialize(),
                dataType:"json",
                beforeSend:function(){
                    $('#service_report_submit_btn').attr('disabled',true);
                    $('#service_report_submit_span').text('Sending...').attr('disabled',true);
                },
                success:function(data){
                    $('#service_report_submit_span').text('Send Service Report');
                    $('#service_report_submit_btn').attr('disabled',false);
                    if (data.status === "success") {
                        $('#service_report_modal').modal('hide');
                        new swal('Success!', data.message, 'success').then(() => {
                            window.location.reload();
                        })
                    } else {
                        new Swal('Oops!', data.message, 'error');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    new Swal('Oops!', 'Something went wrong, please try again later' , 'error');
                    $('#service_report_submit_span').text('Send Service Report');
                    $('#service_report_submit_btn').attr('disabled',false);
                }
            })
        });
    })(jQuery);
</script>