<?php

global $wpdb;

$employees = (new Employee\Employee)->getAllEmployees(['technician']);
$vehicles = (new CarCenter)->getAllVehicles([], ['plate_number']);

$conditions = [];

if(!empty($_GET['search'])){
    $whereSearch = (new GamFunctions)->get_table_coloumn($wpdb->prefix.'parking_tickets');
    $conditions[] = (new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'no_type', 'PT');
}

// change ticket status on ticket list & completed tickets tab based on args
$ticket_status = $args['ticket_status'] ?? '';
if(is_numeric($ticket_status)){
    $conditions[] = " PT.ticket_status <> $ticket_status";
}else{
    $conditions[] = " PT.ticket_status <> 1";
}

if(!empty($_GET['employee_id'])) $conditions[] = " PT.employee_id = '{$_GET['employee_id']}' ";
if(!empty($_GET['week'])) $conditions[] = " PT.week = '{$_GET['week']}' ";
if(!empty($_GET['date'])) $conditions[] = " date(PT.created_at) = '{$_GET['date']}' ";
if(!empty($_GET['plate_number'])) $conditions[] = " V.plate_number = '{$_GET['plate_number']}' ";

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}parking_tickets PT

    left join {$wpdb->prefix}employees E
    on E.id = PT.employee_id

    left join {$wpdb->prefix}technician_details TD
    on E.employee_ref_id = TD.id

    left join {$wpdb->prefix}vehicles V
    on TD.vehicle_id = V.id

    $conditions
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$tickets = $wpdb->get_results("
    select PT.*, E.name, V.plate_number
    from {$wpdb->prefix}parking_tickets PT

    left join {$wpdb->prefix}employees E
    on E.id = PT.employee_id

    left join {$wpdb->prefix}technician_details TD
    on E.employee_ref_id = TD.id

    left join {$wpdb->prefix}vehicles V
    on TD.vehicle_id = V.id

    $conditions

    LIMIT $offset, $no_of_records_per_page 
");

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="filtersForm">
                        <h3 class="page-header">Filters</h3>

                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <div class="form-group">
                            <label for="">Search</label>
                            <input type="text" class="form-control" name="search" value="<?= !empty($_GET['search']) ? $_GET['search'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">Emmployee</label>
                            <select name="employee_id" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php foreach($employees as $employe): ?>
                                    <option value="<?= $employe->id; ?>" <?= (!empty($_GET['employee_id']) && $_GET['employee_id'] == $employe->id) ? 'selected' : ''; ?>><?= $employe->name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>                        

                        <div class="form-group">
                            <label for="">By Week</label>
                            <input type="week" name="week" class="form-control" value="<?= !empty($_GET['week']) ? $_GET['week'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">Date Created</label>
                            <input type="date" name="date" class="form-control" value="<?= !empty($_GET['date']) ? $_GET['date'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">By Plate No.</label>
                            <select name="plate_number" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php foreach($vehicles as $vehicle): ?>
                                    <option value="<?= $vehicle->plate_number; ?>" <?= (!empty($_GET['plate_number']) && $_GET['plate_number'] == $vehicle->plate_number) ? 'selected' : ''; ?>><?= $vehicle->plate_number; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <p><a onclick="resetFilters('filtersForm')" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Reset Filters</a></p>

                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Tickets</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Parking Tickets</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Plate No.</th>
                                <th>Amount</th>
                                <th>Proof Documents</th>
                                <th>Week</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($tickets) && count($tickets) > 0): ?>
                                <?php foreach($tickets as $ticket): ?>
                                    <tr>
                                        <td><?= $ticket->name; ?></td>
                                        <td><?= $ticket->plate_number; ?></td>
                                        <td>$<?= $ticket->amount; ?></td>
                                        <td><button onclick='showDocsListing(`<?= $ticket->proof_doc; ?>`)' class="btn btn-primary"><span><i class="fa fa-eye"></i></span> View</button></td>
                                        <td><?= $ticket->week; ?></td>
                                        <td><?= date('d M Y', strtotime($ticket->created_at)); ?></td>
                                        <td>

                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                    <li><a onclick="deleteTicket(<?= $ticket->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete Ticket</a></li>
                                                    <li><a onclick="markTicketCompleted(<?= $ticket->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-check"></i></span> Mark Completed</a></li>
                                                    <li><a onclick="viewTicketNotes(<?= $ticket->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-comment-o"></i></span> View Notes</a></li>
                                                    <li><a onclick="addTicketNotes(<?= $ticket->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-plus"></i></span> Add Notes</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td>No Parking Ticket Found</td>
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

<!-- TICKET DOCUMENTS MODAL -->
<div id="ticketDocumentsModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Ticket Documents</h4>
      </div>
      <div class="modal-body">
            <div class="ticket-documents"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<div id="ticketNotesModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Ticket Notes</h4>
      </div>
      <div class="modal-body ticketNotes">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<div id="addTicketNoteModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add Ticket Note</h4>
      </div>
      <div class="modal-body">
          <form id="addTicketNoteForm" action="<?= admin_url('admin-post.php'); ?>" method="post">
            
            <?php wp_nonce_field('add_ticket_note'); ?>
            <input type="hidden" name="action" value="add_ticket_note">
            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
            <input type="hidden" name="ticket_id" >

            <div class="form-group">
                <label for="">Add Note</label>
                <textarea name="note" cols="30" rows="5" class="form-control"></textarea>
            </div>

            <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Add Note</button>
          </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<script>
    function showDocsListing(docs){
        docs_html = generateDocsHtml(docs);

        console.log('docs html recieved' , docs_html);

        jQuery('.ticket-documents').html(docs_html);
        jQuery('#ticketDocumentsModal').modal('show');
    }

    function deleteTicket(ticket_id, ref){

        if(!confirm('Are you sure you want to delete this ticket ?')) return false;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            data: {
                action: "delete_parking_ticket",
                ticket_id
            },
            dataType: "json",
            beforeSend: function(){
                jQuery(ref).attr('disabled', true);
            },
            success: function(data){
                if(data.status === "success"){
                    jQuery(ref).parent().parent().fadeOut();
                }
                else{
                    alert(data.message);
                    jQuery(ref).attr('disabled', false);
                }
            }
        })
    }

    function viewTicketNotes(ticket_id){
        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: "json",
            data: {
                action: "view_parking_ticket_notes",
                "_wpnonce": "<?= wp_create_nonce('view_parking_ticket_notes'); ?>",
                ticket_id
            },
            beforeSend: function(){
                console.log('in before send');
                jQuery('#ticketNotesModal').modal('show');
                jQuery('.ticketNotes').html('<div class="loader"></div>');
            },
            success: function(data){
                if(data.status === "success"){
                    jQuery('.ticketNotes').html(data.message);
                }
                else{
                    alert(data.message);
                    jQuery('#ticketNotesModal').modal('hide');
                    jQuery('.ticketNotes').html('');
                }
            },
            error: function(){
                alert('Something went wrong, please try again later');
            }
        })
    }

    function addTicketNotes(ticket_id){
        jQuery('#addTicketNoteForm input[name="ticket_id"]').val(ticket_id);
        jQuery('#addTicketNoteModal').modal('show');
    }

    function markTicketCompleted(ticket_id, ref) {
        if (!confirm('Are you sure you want to mark ticket as completed ?')) return false;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            data: {
                "_wpnonce": "<?= wp_create_nonce('act_mark_ticket_completed') ?>",
                action: 'act_mark_ticket_completed',
                ticket_id
            },
            dataType: "json",
            beforeSend: function() {
                showLoader('Processing request, please wait...');
            },
            success: function(data) {
                if (data.status === "success") {
                    new swal('Great!', data.message, 'success').then(() => {
                        window.location.reload();
                    })
                } else {
                    new Swal('Oops!', data.message, 'error');
                }
            },
            error: function() {
                new Swal('Oops!', 'Something went wrong, please try again later', 'error');
            }
        })
    }

    (function($){
        $(document).ready(function(){
            $('#addTicketNoteForm').validate({
                rules: {
                    note: "required"
                }
            })
        })
    })(jQuery);
</script>