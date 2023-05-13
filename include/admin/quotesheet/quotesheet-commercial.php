<?php

global $wpdb;
if(!empty($_GET['edit-id'])){
    return get_template_part('template-parts/quotes/commercial/edit-quote', null, ['quote_id' => $_GET['edit-id']]); 
}

if(!empty($_GET['quote_id'])){
    return get_template_part('/include/admin/quotesheet/view-commercial-quote',null,['data'=>$_GET['quote_id']]);
}

$conditions=[];


if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches(true);
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    $conditions[]=" C.branch_id IN ($accessible_branches)";
}

if(!empty($_GET['branch_id']) && $_GET['branch_id']!="all"){
    $conditions[]=" C.branch_id = '{$_GET['branch_id']}'";
}


if(!empty($_GET['lead_type'])){
    $active_tab=$_GET['lead_type'];
    
    switch ($_GET['lead_type']) {
        case 'pending':
            case 'dead':
        case 'closed':
            $conditions[]=" C.lead_status='{$_GET['lead_type']}'";
            break;
    }
}

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';

if(!empty($_GET['search'])){
    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'commercial_quotesheet');
    if(!empty($conditions)){
        $conditions .= " ".(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'and','C');
    }
    else{
        $conditions = (new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'], '', 'C');
    }
}

$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;

$no_of_records_per_page = 10;
$offset = ($pageno-1) * $no_of_records_per_page; 
$total_rows= $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}commercial_quotesheet C
    left join {$wpdb->prefix}technician_details TD
    on C.technician_id = TD.id
    $conditions
");
$total_pages = ceil($total_rows / $no_of_records_per_page);

$quotes = $wpdb->get_results("
select C.*, TD.first_name, TD.last_name
    from {$wpdb->prefix}commercial_quotesheet C
    left join {$wpdb->prefix}technician_details TD
    on C.technician_id = TD.id
    $conditions
    order by created_at DESC 
    LIMIT $offset, $no_of_records_per_page
");

$branches = (new Branches)->getAllBranches();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <?php (new GamFunctions)->getFlashMessage(); ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Filters</h3>

                    <form action="">
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <div class="form-group">
                            <label for="">Search</label>
                            <input type="text" class="form-control" name="search" value="<?= !empty($_GET['search']) ? $_GET['search'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">Branches</label>
                            <select name="branch_id" class="form-control select2-field">
                                <option value="">All</option>
                                <?php if(is_array($branches) && count($branches) > 0): ?>
                                    <?php foreach($branches as $branch): ?>
                                        <option value="<?= $branch->id; ?>" <?= (!empty($_GET['branch_id']) && $_GET['branch_id'] == $branch->id) ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Lead Type</label>
                            <select name="lead_type" class="form-control select2-field">
                                <option value="">All</option>
                                <option <?= (!empty($_GET['lead_type']) && $_GET['lead_type'] == "pending" )  ? 'selected' : ''; ?> value="pending">Pending Lead</option>
                                <option <?= (!empty($_GET['lead_type']) && $_GET['lead_type'] == "dead" )  ? 'selected' : ''; ?> value="dead">Dead Lead</option>
                                <option <?= (!empty($_GET['lead_type']) && $_GET['lead_type'] == "closed" )  ? 'selected' : ''; ?> value="closed">Closed Lead</option>
                            </select>
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Records</button>
                    </form>
                </div>
            </div>
        </div>        
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Commercial Quotes</h3>                    

                    <?php if(!empty($_GET['search'])): ?>
                        <p class="alert alert-success"><?= $total_rows ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= admin_url('admin.php?page='.$_GET['page']); ?>"><span><i class="fa fa-database"></i></span> Show All Records</a> </p>
                    <?php elseif(!empty($total_rows)): ?>
                        <p class="alert alert-info">
                            <?= $total_rows; ?> Records Found for the branch
                        </p>
                    <?php endif; ?>

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Quote No.</th>
                                <th>NAME</th>
                                <th>Address</th>
                                <th>Email</th>
                                <th>Office Notes</th>
                                <th>Email Status</th>
                                <th>Date</th>
                                <th>Lead Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($quotes) && !empty($quotes)): ?>
                                <?php foreach($quotes as $quote): ?>
                                    <tr>
                                        <td><?= $quote->quote_no; ?></td>
                                        <td><?= $quote->client_name; ?></td>
                                        <td><?= $quote->client_address; ?></td>
                                        <td><?= $quote->clientEmail;  ?></td>
                                        <td><?= nl2br(stripslashes(htmlspecialchars($quote->office_notes, ENT_QUOTES)));  ?></td>
                                        <td><?= (new GamFunctions)->emailStatusHtml($quote); ?></td>
                                        <td><?= date('d M Y',strtotime($quote->date)); ?></td>
                                        <td>
                                            <div class="btn-group" data-toggle="buttons">

                                                <button  data-quote-status="pending" class="btn <?= $quote->lead_status=="pending"  ? 'btn-primary' : 'btn-default'; ?> lead-<?= $quote->id; ?> lead-status" data-quote-id="<?= $quote->id; ?>" >Pending</button>

                                                <button  data-quote-status="dead" class="btn <?= $quote->lead_status=="dead"  ? 'btn-primary' : 'btn-default'; ?> lead-<?= $quote->id; ?> lead-status" data-quote-id="<?= $quote->id; ?>" >Dead</button>

                                                <button  data-quote-status="closed" class="btn <?= $quote->lead_status=="closed"  ? 'btn-primary' : 'btn-default'; ?> lead-<?= $quote->id; ?> lead-status" data-quote-id="<?= $quote->id; ?>" >Closed</button>

                                            </div>
                                        </td>

                                        <td>

                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">

                                                    <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&quote_id=<?= $quote->id; ?>"><span><i class="fa fa-eye"></i></span> View</a></li>

                                                    <li><a data-office-notes = "<?= stripslashes(htmlspecialchars($quote->office_notes, ENT_QUOTES)); ?>" onclick="addUpdateOfficeNotes(<?= $quote->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Office Notes</a></li>

                                                    <li><a onclick="deleteQuote(<?= $quote->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete</a></li>

                                                    <li><a onclick="downloadCommercialQuote(<?= $quote->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-download"></i></span> Download</a></li>

                                                    <li><a target="_blank" href="<?= $_SERVER['REQUEST_URI']; ?>&edit-id=<?= $quote->id; ?>"><span><i class="fa fa-edit"></i></span> Edit</a></li>

                                                    <?php if(!empty($quote->clientEmail)): ?>
                                                        <li><a onclick="smsQuoteLink(<?= $quote->id; ?>, '<?= $quote->client_phone; ?>')" href="javascript:void(0)"><span><i class="fa fa-envelope"></i></span> SMS Link</a></li>
                                                    <?php else: ?>
                                                        <li class="disabled"><a href="javascript:void(0)"><span><i class="fa fa-envelope"></i></span> SMS Quote Link (<i>No Email</i>)</a></li>
                                                    <?php endif; ?>

                                                    <li><a onclick="emailQuote(<?= $quote->id; ?>, '<?= $quote->clientEmail; ?>')" href="javascript:void(0)"><span><i class="fa fa-envelope"></i></span> Email</a></li>
                                                </ul>
                                            </div>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No Record found</td>
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

<form id="downloadCommercialQuoteForm" method="post" action="<?= admin_url('admin-post.php'); ?>">
    <?php wp_nonce_field('download_commercial_quote_pdf'); ?>
    <input type="hidden" name="action" value="download_commercial_quote_pdf">
    <input type="hidden" name="quote_id">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
</form>

<div id="smsQuoteLinkModal" class="modal fade" rold="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <p>SMS Quote Link To Client</p>
            </div>
            <div class="modal-body">
                <form id="smsQuoteLinkForm" action="<?= admin_url('admin-post.php'); ?>" method="post">
                    
                    <?php wp_nonce_field('sms_commercial_quote_link'); ?>

                    <input type="hidden" name="action" value="sms_commercial_quote_link">
                    <input type="hidden" name="quote_id" value="">

                    <div class="form-group">
                        <label for="">Client Phone No.</label>
                        <input type="text" class="form-control" name="phone_no" value="" placeholder="e.g. +1123-456-7890" required >
                    </div>

                    <button id="sms_quote_btn" class="btn btn-primary"><span><i class="fa fa-envelope"></i></span> SMS Quote Link</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="addUpdateOfficeNotesModal" class="modal fade" rold="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <p>Add/Update Office Notes</p>
            </div>
            <div class="modal-body">
                <form id="addUpdateOfficeNoteForm" action="<?= admin_url('admin-post.php'); ?>" method="post">
                    
                    <?php wp_nonce_field('add_update_office_notes'); ?>

                    <input type="hidden" name="action" value="add_update_office_notes">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <input type="hidden" name="quote_type" value="commercial">

                    <input type="hidden" name="quote_id" >

                    <div class="form-group">
                        <label for="">Office Notes</label>
                        <textarea name="office_notes" cols="30" rows="5" class="form-control"></textarea>
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Add/Update Office Notes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="emailQuoteModal" class="modal fade" rold="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Email Quote</h4>
            </div>
            <div class="modal-body">
                <form id="emailQuoteForm" action="<?= admin_url('admin-post.php'); ?>" method="post">
                    
                    <?php wp_nonce_field('email_quote'); ?>

                    <input type="hidden" name="action" value="email_quote">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <input type="hidden" name="quote_type" value="commercial">

                    <input type="hidden" name="quote_id">

                    <div class="form-group">
                        <label for="">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>

                    <button id="emailQuoteBtn" class="btn btn-primary"><span><i class="fa fa-envelope"></i></span> Email Quote</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

    const emailQuote = async (quote_id, email) => {
        jQuery('#emailQuoteForm input[name="quote_id"]').val(quote_id);
        jQuery('#emailQuoteForm input[name="email"]').val(email);
        jQuery('#emailQuoteModal').modal('show');
    }    

    function addUpdateOfficeNotes(quote_id, ref){
        const office_notes = jQuery(ref).attr('data-office-notes');

        jQuery('#addUpdateOfficeNoteForm input[name="quote_id"]').val(quote_id);
        jQuery('#addUpdateOfficeNoteForm textarea[name="office_notes"]').val(office_notes);
        jQuery('#addUpdateOfficeNotesModal').modal('show');
    }

    function smsQuoteLink(quote_id, phone_no = ''){
        jQuery('#smsQuoteLinkForm input[name="quote_id"]').val(quote_id);
        jQuery('#smsQuoteLinkForm input[name="phone_no"]').val(phone_no);
        jQuery('#smsQuoteLinkModal').modal('show');
    }    

    function downloadCommercialQuote(quote_id){
        jQuery('#downloadCommercialQuoteForm input[name="quote_id"]').val(quote_id);
        jQuery('#downloadCommercialQuoteForm').submit();
    }

    function deleteQuote(quote_id, ref){
        if(!confirm('Are you sure you want to delete this commercial quote ?')) return false;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: "json",
            data: {
                action: "delete_commercial_quotesheet",
                quote_id,
                '_wpnonce': "<?= wp_create_nonce('delete_commercial_quotesheet'); ?>"
            },
            beforeSend: function(){
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
            },
            success: function(data){

                if(data.status === "success"){
                    jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                }
                else{
                    alert(data.message);
                    jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
                }

            }
        })

    }

    (function($){
        $(document).ready(function(){

            $('#emailQuoteForm').validate({
                rules: {
                    email: 'required'
                },
                submitHandler: function(form){
                    $.ajax({
                        type: 'post',
                        url: '<?= admin_url('admin-ajax.php'); ?>',
                        data: $(form).serialize(),
                        dataType: 'json',
                        beforeSend: function(){
                            $('#emailQuoteBtn').attr('disabled', true);
                        },
                        success: function(data){
                            if(data.status === 'success'){
                                swal.fire(
                                    'Email Sent!',
                                    'Email sent to client successfully',
                                    'success'
                                )
                            }
                            else{
                                swal.fire(
                                    'Oops!',
                                    data.message,
                                    'error'
                                )
                            }

                            $('#emailQuoteBtn').attr('disabled', false);
                            $('#emailQuoteModal').modal('hide');
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
            });            

            $('.lead-status').on('click',function(){

                const quote_id = $(this).attr('data-quote-id');
                const quote_status = $(this).attr('data-quote-status');
                let lead=$(this);
                $.ajax({
                    type:'post',
                    url:"<?= esc_url( admin_url('admin-ajax.php') ); ?>",
                    data:{
                        action:'udpate_quote_status',
                        quote_id,
                        quote_status,
                        quote_type:"commercial",
						"_wpnonce": "<?= wp_create_nonce('udpate_quote_status'); ?>"
                    },
                    dataType:'json',
                    success:function(data){
                        if(data.status=="success"){
                            $(`.lead-${quote_id}`).removeClass('btn-primary').addClass('btn-default');
                            lead.removeClass('btn-default').addClass('btn-primary');
                            swal.fire('Quote Updated!', 'Quote status updated successfully', 'success');
                        }
                        else{
                            swal.fire('Oops', 'something went wrong', 'error');
                            
                        }
                    }
                })

            });

            $('#smsQuoteLinkForm').validate({
                rules: {
                    phone_no: "required"                   
                },
                submitHandler: function(form){
                    jQuery.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        data: $('#smsQuoteLinkForm').serialize(),
                        dataType: "json",
                        beforeSend: function(){
                            $('#sms_quote_btn').attr('disabled', true);
                        },
                        success: function(data){
                            alert(data.message);
                            $('#sms_quote_btn').attr('disabled', false);
                            $('#smsQuoteLinkModal').modal('hide');
                        }
                    })
                }
            });
        });
    })(jQuery);

</script>