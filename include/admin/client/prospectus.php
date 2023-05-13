<?php

$conditions = [];

if(!empty($_GET['search'])){
    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'prospectus');
    $conditions[] =(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'no_type', 'P');
}

if(!empty($_GET['employee_id'])) $conditions[] = " P.employee_id = '{$_GET['employee_id']}'";
if(!empty($_GET['from_date'])) $conditions[] = " DATE(P.created_at) >= '{$_GET['from_date']}'";
if(!empty($_GET['to_date'])) $conditions[] = " DATE(P.created_at) <= '{$_GET['to_date']}'";

if(!empty($_GET['status'])) $conditions[] = " P.status = '{$_GET['status']}' ";
if(!empty($_GET['reminder_week'])) $conditions[] = " P.reminder_week = '{$_GET['reminder_week']}' ";

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}prospectus P
    left join {$wpdb->prefix}employees E
    on P.employee_id = E.id
    $conditions
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$prospectus =  $wpdb->get_results("
    select P.*, E.name as employee_name
    from {$wpdb->prefix}prospectus P
    left join {$wpdb->prefix}employees E
    on P.employee_id = E.id
    $conditions
    LIMIT $offset, $no_of_records_per_page     
");

$employees = (new Employee\Employee)->getAllEmployees();

function prospectusStatusValue($status){
    if($status == "interested"){
        echo  '<span><i class="fa fa-square text-success"></i> Interested</span>';
    }
    elseif($status == 'semi_interested'){
        echo '<span><i class="fa fa-square text-warning"></i> Semi Interested</span>';
    }
    elseif($status == 'not_interested'){
        echo '<span><i class="fa fa-square text-danger"></i> Not Interested</span>';
    }
}

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Filters</h3>
                    <form id="filtersForm">

                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <div class="form-group">
                            <label for="">Search</label>
                            <input type="text" class="form-control" name="search" placeholder="e.g. name, email etc.." value="<?= !empty($_GET['search']) ? $_GET['search'] : ''; ?>" >
                        </div>

                        <div class="form-group">
                            <label for="">Employee</label>
                            <select name="employee_id" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($employees) && count($employees) > 0): ?>
                                    <?php foreach($employees as $employee): ?>
                                        <option value="<?= $employee->id; ?>" <?= (!empty($_GET['employee_id']) && $_GET['employee_id'] == $employee->id) ? 'selected' : ''; ?>><?= $employee->name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Status</label>
                            <select name="status" class="form-group select2-field">
                                <option value="">Select</option>
                                <option value="interested" <?= (!empty($_GET['status']) && $_GET['status'] == "interested") ? 'selected' : ''; ?>>Interested</option>
                                <option value="semi_interested" <?= (!empty($_GET['status']) && $_GET['status'] == "semi_interested") ? 'selected' : ''; ?>>Semi Interested</option>
                                <option value="not_interested" <?= (!empty($_GET['status']) && $_GET['status'] == "not_interested") ? 'selected' : ''; ?>>Not Interested</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Reminder Week</label>
                            <input type="week" name="reminder_week" class="form-control" value="<?= !empty($_GET['reminder_week']) ? $_GET['reminder_week'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="<?= !empty($_GET['from_date']) ? $_GET['from_date'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="<?= !empty($_GET['to_date']) ? $_GET['to_date'] : ''; ?>">
                        </div>

                        <p><a onclick="resetFilters('filtersForm')" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Reset Filters</a></p>

                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Prospect</button>

                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Prospect <small>(<?= $total_rows ?> records found)</small></h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Client Name</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Phone No</th>
                                <th>Status</th>
                                <th>Reminder Week</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($prospectus) && count($prospectus) > 0): ?>
                                <?php foreach($prospectus as $client): ?>
                                    <tr>
                                        <td><?= $client->employee_name; ?></td>
                                        <td><?= $client->name; ?></td>
                                        <td><?= $client->email; ?></td>
                                        <td><?= $client->address; ?></td>
                                        <td><?= $client->phone; ?></td>
                                        <td><?= prospectusStatusValue($client->status); ?></td>
                                        <td><?= $client->reminder_week; ?></td>
                                        <td><?= date('d M Y', strtotime($client->created_at)); ?></td>
                                        <td>
                                        <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                    <li><a onclick="showLabelProspecutsForm('<?= $client->id; ?>', '<?= $client->status; ?>')" href="javascript:void(0)"><span><i class="fa fa-tag"></i></span> Label Prospectus</a></li>
                                                    
                                                    <li><a onclick="setReminder('<?= $client->id ?>')" href="javascript:void(0)"><span><i class="fa fa-bell"></i></span> Set Reminder</a></li>

                                                    <li><a onclick="viewNotes('<?= $client->id; ?>')" href="javascript:void(0)"><span><i class="fa fa-comment-o"></i></span> View Notes</a></li>

                                                </ul>
                                        </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No Prospect Found</td>
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

<div id="labelProspectusModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Label Prospect</h4>
            </div>
            <div class="modal-body">
                <form id="setProspectusLableForm" >

                    <?php wp_nonce_field('update_prospectus_status'); ?>
                    <input type="hidden" name="prospectus_id">
                    <input type="hidden" name="action" value="update_prospectus_status">
                    
                    <div class="form-group">
                        <label for="">Select Status</label>
                        <div class="radio">
                            <label><input name="status" type="radio" value="interested"><span><i class="fa fa-square text-success"></i> Interested</span></label>
                        </div>
                        <div class="radio">
                            <label><input name="status" type="radio" value="semi_interested"><span><i class="fa fa-square text-warning"></i> Semi Interested</span></label>
                        </div>
                        <div class="radio">
                            <label><input name="status" type="radio" value="not_interested"><span><i class="fa fa-square text-danger"></i> Not Interested</span></label>
                        </div>                    
                    </div>

                    <button id="lableFormSubmitBtn" class="btn btn-primary"><span><i class="fa fa-tag"></i></span> Label Prospect</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="setReminderModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Set Reminder</h4>
            </div>
            <div class="modal-body">
                <form id="setReminderForm" >

                    <?php wp_nonce_field('set_reminder_week'); ?>
                    <input type="hidden" name="prospectus_id">
                    <input type="hidden" name="action" value="set_reminder_week">

                    <div class="form-group">
                        <label for="">Select Week</label>
                        <input type="week" name="week" class="form-control">
                    </div>

                    <button id="reminderFormSubmitBtn" class="btn btn-primary"><span><i class="fa fa-bell"></i></span> Set Reminder</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="notesModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Notes</h4>
            </div>
            <div class="modal-body prospectNotes">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>

    function viewNotes(prospect_id){
        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: "json",
            data: {
                action: "get_prospect_notes",
                "_wpnonce": "<?= wp_create_nonce('get_prospect_notes'); ?>",
                prospect_id
            },
            beforeSend: function(){
                jQuery('#notesModal').modal('show');
                jQuery('.prospectNotes').html('<div class="loader"></div>');
            },
            success: function(data){
                if(data.status === "success"){
                    jQuery('.prospectNotes').html(data.data);
                }
                else{
                    jQuery('.prospectNotes').html(data.message);
                }
            },
            error: function(request, status, error){
                jQuery('.prospectNotes').html('<p class="text-danger">Something went wrong, please try again later</p>')
            }
        })
    }

    function showLabelProspecutsForm(prospectus_id, status){
        jQuery('#labelProspectusModal').modal('show');
        jQuery('#setProspectusLableForm input[name="prospectus_id"]').val(prospectus_id);

        if(status === "" || status === undefined){
            jQuery('input[name=status]').prop('checked', false);
        }
        else{
            jQuery(`input[name=status][value="${status}"]`).attr('checked', 'checked');
        }
    }

    function setReminder(prospectus_id){
        jQuery('#setReminderModal').modal('show');
        jQuery('#setReminderForm input[name="prospectus_id"]').val(prospectus_id);
    }

    (function($){
        $(document).ready(function(){

            $('#setProspectusLableForm').validate({
                rules: {
                    status: "required"
                },
                submitHandler: function(form){
                    $.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php') ?>",
                        dataType: "json",
                        data: $(form).serialize(),
                        beforeSend: function(){
                            $('#lableFormSubmitBtn').attr('disabled', true);
                        },
                        success: function(data){
                            alert(data.message);
                            $('#lableFormSubmitBtn').attr('disabled', false);
                            jQuery('#labelProspectusModal').modal('hide');
                        }
                    });
                }
            });

            $('#setReminderForm').validate({
                rules: {
                    week: 'required'
                },
                submitHandler: function(form){
                    $.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php') ?>",
                        dataType: "json",
                        data: $(form).serialize(),
                        beforeSend: function(){
                            $('#reminderFormSubmitBtn').attr('disabled', true);
                        },
                        success: function(data){
                            alert(data.message);
                            $('#reminderFormSubmitBtn').attr('disabled', false);
                            jQuery('#setReminderModal').modal('hide');
                        }
                    })
                }
            })

        })
    })(jQuery);
</script>