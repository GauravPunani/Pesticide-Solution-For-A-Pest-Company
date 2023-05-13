<?php

global $wpdb;

$technician = $args['technician'];
$conditions = [];

$conditions[] = " TD.id = '$technician->id' ";

$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 20;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}office_notes
    where technician_id = '$technician->id'
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

if(!empty($_GET['date'])) $conditions[] = " date(ONT.date) = '{$_GET['date']}'";

if(!empty($_GET['search'])){
    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'office_notes');
    $conditions[] =(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'no_type', 'ONT');
}

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';

$notes = $wpdb->get_results("
    select ONT.*, I.type_of_service_provided, I.service_description, I.area_of_service, I.findings, I.invoice_no, I.address, UC.reason
    from {$wpdb->prefix}office_notes ONT

    left join {$wpdb->prefix}technician_details TD
    on TD.id = ONT.technician_id

    left join {$wpdb->prefix}invoices I
    on ONT.invoice_id = I.id

    left join {$wpdb->prefix}unsatisfied_clients UC
    on ONT.unsatisfied_client_id = UC.id

    $conditions
    order by ONT.created_at desc
    LIMIT $offset, $no_of_records_per_page 
");

?>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-6">
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
                            <label for="">Date <small><a onclick="clearDate()" href="javascript:void(0)"><i>Clear Date</i></a></small></label>
                            <input type="date" name="date" class="form-control" value="<?= !empty($_GET['date']) ? $_GET['date'] : ''; ?>">
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Records</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <h3><span><i class="fa fa-file"></i></span> Service Reports</h3>
        </div>        

        <div class="col-md-12">
            <?php foreach($notes as $note): ?>
                <div class="card full_width tale-responsive">
                    <div class="card-body">
                        <h3 class="page-header"><?= $note->client_name; ?></h3>
                        <table class="table table-striped table-hover">
                            <tbody>
                                <tr>
                                    <th>Invoice Number</th>
                                    <td><a target="_blank" href="<?= (new Technician_details)->dashboardUrl('?view=invoice&invoice_id='.$note->invoice_id) ?>"><?= $note->invoice_no; ?></a></td>
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
                                    <td><a target="_blank" href="<?= (new Technician_details)->dashboardUrl('?view=invoice&search='.$note->invoice_no) ?>"><span><i class="fa fa-file"></i></span> View Past Invoices</a></td>
                                    <th>Pesticides Used</th>
                                    <td><?= (new ChemicalReport)->getPestsUsedInService($note->invoice_id); ?></td>                                    
                                </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4"><p class="text-danger">Invoice was not linked</p></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Notes / Comment</th>
                                    <td><?= nl2br($note->note); ?></td>
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
            <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>            
        </div>        
    </div>
</div>

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
    })(jQuery);
</script>