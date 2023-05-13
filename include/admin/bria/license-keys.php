<?php
global $wpdb;

if(!empty($_GET['edi_license_key_id'])) return get_template_part('include/admin/bria/edit-license-key', null, ['edi_license_key_id' => $_GET['edi_license_key_id']]);

$conditions = [];

if(!empty($_GET['assign_status'])){

    if($_GET['assign_status'] == "assigned") $conditions[] = " (BL.employee_id is not null or BL.employee_id <> '')";
    if($_GET['assign_status'] == "not_assigned") $conditions[] = " (BL.employee_id is null or BL.employee_id = '')";
}

if(!empty($_GET['search'])){
    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'bria_licenses');
    $conditions[] =(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'no_type', 'BL');
}

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}bria_licenses BL
    left join {$wpdb->prefix}employees E
    on BL.employee_id = E.id
    $conditions
");

$total_pages = ceil($total_rows / $no_of_records_per_page);


$license_keys = $wpdb->get_results("
    select BL.*, E.name
    from {$wpdb->prefix}bria_licenses BL
    left join {$wpdb->prefix}employees E
    on BL.employee_id = E.id
    $conditions
    LIMIT $offset, $no_of_records_per_page 
");

$cold_callers = (new BriaAdmin)->getUnassignedKeyEmployees(['id','name']);

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Filters</h3>
                    <form id="filterForm" action="">
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" value="<?= !empty($_GET['search']) ? $_GET['search'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="assign_status">Assign Status</label>
                            <select name="assign_status" id="assign_status" class="form-control select2-field">
                                <option value="">All</option>
                                <option value="assigned" <?= (!empty($_GET['assign_status']) && $_GET['assign_status'] == 'assigned') ? 'selected' : ''; ?>>Assigned License Keys</option>
                                <option value="not_assigned" <?= (!empty($_GET['assign_status']) && $_GET['assign_status'] == 'not_assigned') ? 'selected' : ''; ?>>Not Assigned License Keys</option>
                            </select>
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Data</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-9">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <h3 class="page-header">Bria License Keys</h3>
                    <div class="float-right text-right">
                        <button onclick="openCreateLicenseModal()" class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create License Key</button>    
                    </div>
                    
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Key</th>
                                <th>Assigned to</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($license_keys) && count($license_keys) > 0): ?>
                                <?php foreach($license_keys as $license_key): ?>
                                    <tr>
                                        <td><?= $license_key->title; ?></td>
                                        <td><?= $license_key->key; ?></td>
                                        <td><?= $license_key->name; ?></td>
                                        <td><?= date('d M y h:i A', strtotime($license_key->created_at)); ?></td>
                                        <td><?= date('d M y h:i A', strtotime($license_key->updated_at)); ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                    <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&edi_license_key_id=<?= $license_key->id; ?>"><span><i class="fa fa-edit"></i></span> Edit License Key</a></li>
                                                    <li><a onclick="linkColdCaller(<?= $license_key->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-link"></i></span> Link Cold Caller</a></li>
                                                    <li><a onclick="deleteLicensekey(<?= $license_key->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete License Key</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No Bria License Key Found</td>
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

<!-- CREATE LICENSE KEY MODAL -->
<div id="createLicenseModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Licsense Key</h4>
            </div>
            <div class="modal-body">
                <form id="createLicenseKeyForm" action="<?= admin_url('admin-post.php'); ?>" method="post">
                
                    <?php wp_nonce_field('create_bria_key'); ?>
                    <input type="hidden" name="action" value="create_bria_key">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <div class="form-group">
                        <label for="title">Title <small class="text-danger">*</small></label>
                        <input type="text" class="form-control" name="title">
                    </div>

                    <div class="form-group">
                        <label for="key">Key <small class="text-danger">*</small></label>
                        <input type="text" class="form-control" id="key" name="key">
                    </div>
                    
                    <div class="form-group">
                        <label for="employee_id">Link Cold Caller (optional)</label>
                        <select name="employee_id" class="form-group select2-field">
                            <?php if(is_array($cold_callers) && count($cold_callers) > 0): ?>
                                <option value="">Select</option>
                                <?php foreach($cold_callers as $cold_caller): ?>
                                    <option value="<?= $cold_caller->id; ?>"><?= $cold_caller->name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Bria Licsense Key</button>
                    
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- LINK COLD CALLER -->
<div id="linkLicenseModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Link Cold Caller</h4>
            </div>
            <div class="modal-body">
                <form id="linkLicenseKeyForm" action="<?= admin_url('admin-post.php'); ?>" method="post">

                    <?php wp_nonce_field('link_bria_key'); ?>
                    <input type="hidden" name="action" value="link_bria_key">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <input type="hidden" name="license_key_id">
                    
                    <div class="form-group">
                        <label for="employee_id">Link Cold Caller <small class="text-danger">*</small></label>
                        <select name="employee_id" class="form-group select2-field">
                            <?php if(is_array($cold_callers) && count($cold_callers) > 0): ?>
                                <option value="">Select</option>
                                <?php foreach($cold_callers as $cold_caller): ?>
                                    <option value="<?= $cold_caller->id; ?>"><?= $cold_caller->name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-link"></i></span> Link Bria Licsense Key</button>
                    
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<script>
    function openCreateLicenseModal(){
        jQuery('#createLicenseModal').modal('show');
    }

    function deleteLicensekey(key_id, ref){
        if(!confirm('Are you sure you want to delete this bria license key ?')) return false;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            data:{
                action: "delete_bria_key",
                key_id,
                "_wpnonce": "<?= wp_create_nonce('delete_bria_key'); ?>"
            },
            dataType: "json",
            beforeSend: function(){
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
            },
            success:function(data){

                if(data.status === "success"){
                    jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                }
                else{
                    alert(data.message);
                }

                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
            }
        })


    }

    function linkColdCaller(license_key_id, ref){
        jQuery('#linkLicenseKeyForm input[name="license_key_id"]').val(license_key_id);
        jQuery('#linkLicenseModal').modal('show');
    }

    (function($){
        $(document).ready(function($){
            $('#createLicenseKeyForm').validate({
                rules:{
                    title: "required",
                    key: "required",
                }
            });

            $('#linkLicenseKeyForm').validate({
                rules:{
                    employee_id: "required",
                }
            });
        })
    })(jQuery);
</script>