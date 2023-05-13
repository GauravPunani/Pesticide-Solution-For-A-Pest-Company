<?php
global $wpdb;

$lead_statuses = (new Leads)->getLeadStauses();
$cc_roles = (new ColdCallerRoles)->getAssignedRoles();

$conditions=[];
$conditions[] = " E.role_id = '2' ";

if(!empty($_GET['cold_caller'])){
    $conditions[] = " L.cold_caller_id='{$_GET['cold_caller']}'"; 
}

if(!empty($_GET['from_date'])){
    $conditions[] = " DATE(L.date) >= '{$_GET['from_date']}' ";
}

if(!empty($_GET['to_date'])){
    $conditions[] = " DATE(L.date) <= '{$_GET['to_date']}' ";
}

if(!empty($_GET['lead_type'])) $conditions[] = " L.lead_type = '{$_GET['lead_type']}' ";

if(!empty($_GET['status_id'])) $conditions[] = " L.status_id = '{$_GET['status_id']}' ";

if(!empty($_GET['role_id'])){
    $role_id = sanitize_text_field($_GET['role_id']);
    $linked_callers = (new ColdCallerRoles)->getLinkedColdCallers($role_id);
    $linked_callers = implode("','", $linked_callers);

    $conditions[] = " E.id in ('$linked_callers') ";
}

$cold_callers = (new ColdCaller)->getAllColdCallers();

if(count($conditions)>0){
    $conditions=(new GamFunctions)->generate_query($conditions);
}
else{
    $conditions="";
}

if(!empty($_GET['search'])){

    $tc_table=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'leads');

    if(!empty($conditions)){
        $conditions .= (new GamFunctions)->create_search_query_string($tc_table,$_GET['search'],'and','L');    
    }
    else{
        $conditions = (new GamFunctions)->create_search_query_string($tc_table,$_GET['search'],'where','L');
    }
}

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

$no_of_records_per_page = 10;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_pages_sql = "
    select count(*)
    from {$wpdb->prefix}leads L

    left join {$wpdb->prefix}cold_callers C
    on L.cold_caller_id=C.id

    left join {$wpdb->prefix}employees E
    on E.employee_ref_id = C.id

    $conditions
";

$total_rows= $wpdb->get_var($total_pages_sql);

$total_pages = ceil($total_rows / $no_of_records_per_page);

$leads = $wpdb->get_results("
    select L.*,C.name as cold_caller, LSL.name as status_name,LSL.slug status_slug
    from {$wpdb->prefix}leads L

    left join {$wpdb->prefix}cold_callers C
    on L.cold_caller_id=C.id

    left join {$wpdb->prefix}employees E
    on E.employee_ref_id = C.id

    left join {$wpdb->prefix}lead_status_list LSL
    on L.status_id = LSL.id

    $conditions
    order by date DESC LIMIT $offset, $no_of_records_per_page
");

?>
<div class="table-responsive">
    <div class="row">

        <div class="col-sm-12">
            <?php (new GamFunctions)->getFlashMessage(); ?>
            <div class="card">
                <div class="card-body">
                    <form id="filtersForm" action="<?= $_SERVER['REQUEST_URI']; ?>">

                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <input type="hidden" name="action" value="gam_export_leads">

                        <h4 class="text-center"><b><span><i class="fa fa-filter"></i></span> Filters</b></h4>                            
                        
                        <div class="form-group">
                            <label for="">Search Records</label>
                            <input type="text" name="search" value="<?= @$_GET['search']; ?>" class="form-control" placeholder="Enter Name,email etc..">
                        </div>

                        <div class="form-group">
                            <label for="">Cold Caller</label>
                            <select name="cold_caller" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($cold_callers) && count($cold_callers)>0): ?>
                                    <?php foreach($cold_callers as $key=>$val): ?>
                                        <option value="<?= $val->id; ?>" <?= $val->id==@$_GET['cold_caller'] ? 'selected': ''; ?>><?= $val->name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">By Cold Caller Role</label>
                            <select name="role_id" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($cc_roles) && count($cc_roles) > 0): ?>
                                    <?php foreach($cc_roles as $cc_role): ?>
                                        <option value="<?= $cc_role->role_id; ?>" <?= (!empty($_GET['role_id']) && $_GET['role_id'] == $cc_role->role_id) ? 'selected' : ''; ?>><?= $cc_role->role_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Lead Status</label>
                            <select name="status_id" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php foreach($lead_statuses as $lead_status): ?>
                                    <option value="<?= $lead_status->id; ?>" <?= (!empty($_GET['status_id']) && $_GET['status_id'] == $lead_status->id) ? 'selected' : ''; ?>><?= $lead_status->name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="<?= !empty($_GET['from_date']) ? $_GET['from_date'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="<?= !empty($_GET['to_date']) ? $_GET['to_date'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">Lead Type</label>
                            <select name="lead_type" class="form-control select2-field">
                                <option value="">All</option>
                                <option value="normal" <?= (!empty($_GET['lead_type']) && $_GET['lead_type'] == 'normal') ? 'selected' : ''; ?>>Normal</option>
                                <option value="property_management" <?= (!empty($_GET['lead_type']) && $_GET['lead_type'] == 'property_management') ? 'selected' : ''; ?>>Property Management</option>
                                <option value="realtor" <?= (!empty($_GET['lead_type']) && $_GET['lead_type'] == 'realtor') ? 'selected' : ''; ?>>Realtor</option>
                            </select>
                        </div>
                        
                        <p><a onclick="resetFilters('filtersForm')" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Reset Filters</a></p>

                        <div class="btn-group">
                            <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                            <button onclick="return exportLeads(this)" type="button" class="btn btn-default"><span><i class="fa fa-download"></i></span> Export Leads</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Leads <small>(<?= $total_rows; ?> Records Found)</small></h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
							
                                <th>Cold Caller</th>
                                <th>Type</th>
                                <th>Establishment Name</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone No.</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($leads) && count($leads)>0): ?>
                                <?php foreach($leads as $lead): ?>
                                    <tr>
									    <td><?= $lead->cold_caller; ?></td>

                                        <?php
                                            switch ($lead->lead_type) {
                                                case 'normal':
                                                    $badge_class = 'success';
                                                break;
                                                case 'realtor':
                                                    $badge_class = 'primary';
                                                break;
                                                case 'property_management':
                                                    $badge_class = 'default';
                                                break;
                                            }
                                        ?>

									    <td><span class="label label-<?= $badge_class; ?>"><?= (new GamFunctions)->beautify_string($lead->lead_type); ?></span></td>
                                        <td><?= $lead->establishment_name; ?></td>
                                        <td><?= $lead->name; ?></td>
                                        <td><?= $lead->email; ?></td>
                                        <td><?= $lead->phone; ?></td>
                                        <td><?= $lead->address; ?></td>
                                        <?php if($lead->lead_type == "realtor"): ?>
                                            <td class='text-danger'>N/A</td>
                                        <?php else: ?>
                                            <td><?= !empty($lead->status_name) ? $lead->status_name : '-'; ?></td>
                                        <?php endif; ?>
                                        <td><?= date('d M Y',strtotime($lead->date)); ?></td>
                                        <td>
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                            <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                <li><a data-lead-id="<?= $lead->id; ?>" class="delete_lead" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete lead</a></li>
                                                <?php if($lead->lead_type != 'realtor'): ?>
                                                    <li><a onclick="editLeadStatus('<?= $lead->status_name; ?>', <?= $lead->id ?>)" href="javascript:void(0)"><span><i class="fa fa-edit"></i></span> Change Lead Status</a></li>
                                                <?php endif; ?>

                                                <li><a data-status-desc="<?= htmlspecialchars($lead->status_desc, ENT_QUOTES); ?>" onclick="statusDescription(this)" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> Status Desc</a></li>

                                                <li><a data-status-desc="<?= htmlspecialchars($lead->status_desc, ENT_QUOTES); ?>" onclick="editStatusDesc(<?= $lead->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-edit"></i></span> Edit Status Desc</a></li>
                                            </ul>
                                        </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No Lead Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
	<?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
</div>

<div id="editLeadStatusModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Change Lead Status</h4>
                </div>
                <div class="modal-body">
                    <form method="post" id="editLeadStatusForm" action="<?= admin_url('admin-post.php'); ?>">
                        
                        <?php wp_nonce_field('update_lead_status'); ?>
                        <input type="hidden" name="action" value="update_lead_status">

                        <input type="hidden" name="lead_id" >

                        <div class="form-group">
                            <label for="">Change Lead Status</label>
                            <select name="lead_status" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php foreach($lead_statuses as $lead_status): ?>
                                    <option value="<?= $lead_status->id; ?>"><?= $lead_status->name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Update Lead Status</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
        </div>
    </div>
</div>

<script>

    const editStatusDesc = async(lead_id, ref) => {

        let old_status_desc = jQuery(ref).attr('data-status-desc');
        old_status_desc = old_status_desc.replace(/\\/g,'');

        console.log(old_status_desc);

        const { value: status_desc } = await Swal.fire({
            input: 'textarea',
            inputLabel: 'Edit Status Description',
            inputPlaceholder: 'Type your status description here...',
            inputAttributes: {
                'aria-label': 'Type your message here',
            },
            showCancelButton: false,
            inputValue: old_status_desc,
            confirmButtonText: 'Update status description',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-primary' //insert class here
            },
            preConfirm: () => {
                const statusDesc = document.getElementById('swal2-input').value;
                if(!statusDesc){
                    Swal.showValidationMessage(`Please enter status description first`)
                }

            }
        })

        if (status_desc) {

            jQuery.ajax({
                type: "post",
                url: "<?= admin_url('admin-ajax.php'); ?>",
                dataType: 'json',
                data: {
                    action: "update_lead",
                    lead_id,
                    data: {
                        status_desc
                    },
                    "_wpnonce": "<?= wp_create_nonce('update_lead'); ?>"
                },
                success: function(data){
                    if(data.status == "success"){
                        Swal.fire(
                            'Status description updated!',
                            'Lead status description updated successfully',
                            'success'
                        )                        
                    }
                    else{
                        swal.fire(
                            'Oops!',
                            'Something went wrong, please try again later',
                            'error'
                        )
                    }
                },
                error: function(){
                    swal.fire(
                        'Oops!',
                        'Something went wrong, please try again later',
                        'error'
                    )
                }
            })
        }        
    }

    const statusDescription = (ref) => {

        let description = jQuery(ref).attr('data-status-desc');
        description = description.replace(/\\/g,'');

        Swal.fire({
            title: "Lead Status Description", 
            html: description,
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-primary'
            }
        });
    }

    function editLeadStatus(currentLeadStatus, lead_id){
        jQuery('#editLeadStatusForm input[name="lead_id"]').val(lead_id);
        jQuery('#editLeadStatusModal').modal('show');
    }

    function exportLeads(ref){
        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: "json",
            data: jQuery('#filtersForm').serialize(),
            beforeSend: function(){
                jQuery(ref).attr('disabled', true);
            },
            success: function(data){
                if(data.status === "success"){
                    console.log(data);

                    // download data as csv
                    arrayToCsvDownload(data.data, 'Leads');

                    // enable the export button
                    jQuery(ref).attr('disabled', false);
                }
                else{
                    alert(data.message);
                }
            }
        })
    }

    (function($){
        $(document).ready(function(){
            $('#editLeadStatusForm').validate({
                rules: {
                    lead_status: "required"
                }
            });
        });
    })(jQuery);

</script>