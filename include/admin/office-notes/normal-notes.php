<?php

global $wpdb;

$conditions = [];

$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page =50;
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
    select ONT.*, TD.first_name, TD.last_name
    from {$wpdb->prefix}office_notes ONT

    left join {$wpdb->prefix}technician_details TD
    on TD.id = ONT.technician_id

    left join {$wpdb->prefix}branches B
    on TD.branch_id = B.id
    $conditions
    LIMIT $offset, $no_of_records_per_page 
");

$technicians = (new Technician_details)->get_all_technicians();
$branches = (new Branches)->getAllBranches();

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
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
        <div class="col-md-9">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Notes</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Note</th>
                                <th>Technician</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($notes) && count($notes) > 0): ?>
                                <?php foreach($notes as $note): ?>
                                    <tr>
                                        <td><?= $note->client_name; ?></td>
                                        <td><?= $note->note; ?></td>
                                        <td><?= $note->first_name." ".$note->last_name; ?></td>
                                        <td><?= date('d M Y', strtotime($note->date)); ?></td>
                                        <td>
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                            <ul class="custom-dropdown dropdown-menu dropdown-menu-left">    
                                                <li><a data-note-id="<?= $note->id; ?>" data-attach='<?= $note->optional_images; ?>' data-model-id="listattachments" class="openmodal attachments" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> Attachments</a></li>
                                            </ul>
                                        </div>
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