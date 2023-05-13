<?php

global $wpdb;

$conditions = $meta_query = [];

if (!current_user_can('other_than_upstate')) {
    $accessible_branches = (new Branches)->partner_accessible_branches(true);
    $accessible_branches = "'" . implode("', '", $accessible_branches) . "'";

    $conditions[] = " Q.branch_id IN ($accessible_branches)";
}

if (!empty($_GET['branch_id'])) {
    $branch = $_GET['branch_id'];
    array_push($meta_query,(new GamPageMetaBox)->gam_ads_search_query_string($branch,'ads_branch_id'));
}else{
    array_push($meta_query,(new GamPageMetaBox)->gam_ads_search_query_string('all','ads_branch_id'));
}

if (!empty($_GET['ads_provider'])) {
    $ad_provider = $_GET['ads_provider'];
    array_push($meta_query,(new GamPageMetaBox)->gam_ads_search_query_string($ad_provider,'ads_provider'));
}else{
    array_push($meta_query,(new GamPageMetaBox)->gam_ads_search_query_string('all','ads_provider'));
}

$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 50;
$landing_pages = (new GamFunctions)->fetchActiveAdslandingPages(['per_page' => $no_of_records_per_page, 'page_no' => $pageno, 'query' => $meta_query]);
$total_rows = $landing_pages->found_posts;
$total_pages = ceil($total_rows / $no_of_records_per_page);
$branches = (new Branches)->getAllBranches();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Filters</h3>

                    <form action="" id="filtersForm">
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                        <div class="form-group">
                            <label for="">Branches</label>
                            <select name="branch_id" class="form-control select2-field">
                                <option value="all">All</option>
                                <?php if (is_array($branches) && count($branches) > 0) : ?>
                                    <?php foreach ($branches as $branch) : ?>
                                        <option value="<?= $branch->id; ?>" <?= (!empty($_GET['branch_id']) && $_GET['branch_id'] == $branch->id) ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Ads Type</label>
                            <?php
                            $provider = (new GamFunctions)->gamlandingPageAdsProvider(); ?>
                            <select name="ads_provider" class="form-control select2-field">
                                <option value="all">All</option>
                                <?php if (is_array($provider) && count($provider) > 0) : ?>
                                    <?php foreach ($provider as $k => $item) : ?>
                                        <option value="<?= $k; ?>" <?= (!empty($ad_provider) && $ad_provider == $k) ? 'selected' : ''; ?>><?= $item; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <p><a onclick="resetFilters('filtersForm')" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Reset Filters</a></p>
                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Records</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">

                    <?php (new GamFunctions)->getFlashMessage(); ?>

                    <h3 class="page-header">Ads Landing Pages</h3>

                    <?php if (!empty($_GET['search'])) : ?>
                        <p><?= $total_rows ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a href="<?= admin_url('admin.php?page=' . $_GET['page']); ?>">Show All Records</a> </p>
                    <?php else : ?>
                        <p><?= $total_rows; ?> Records Found</p>
                    <?php endif; ?>

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Page Name</th>
                                <th>Branch</th>
                                <th>Ad Provider</th>
                                <th>Page URL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($landing_pages->posts) && !empty($landing_pages->posts)) : ?>
                                <?php foreach ($landing_pages->posts as $page) :
                                    $ads_data = (new GamPageMetaBox)->gam_fetch_landing_page_data($page->ID);
                                ?>
                                    <tr>
                                        <td><?= $page->post_title; ?></td>
                                        <td><?= (new Branches)->getBranchName($ads_data['branch']); ?></td>
                                        <td><?= (new GamFunctions)->gamlandingPageAdsProvider()[$ads_data['provider']]; ?></td>
                                        <td><?= sprintf('<a target="_blank" href="%s">View Page</a>', get_permalink($page->ID));  ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5">No Record found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno, $total_pages); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="downloadResidentialQuoteForm" method="post" action="<?= admin_url('admin-post.php'); ?>">
    <?php wp_nonce_field('download_residential_quote_pdf'); ?>
    <input type="hidden" name="action" value="download_residential_quote_pdf">
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

                    <?php wp_nonce_field('sms_quote_link'); ?>

                    <input type="hidden" name="action" value="sms_quote_link">
                    <input type="hidden" name="quote_id" value="">

                    <div class="form-group">
                        <label for="">Client Phone No.</label>
                        <input type="text" class="form-control" name="phone_no" value="" placeholder="e.g. +1123-456-7890" required>
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
                    <input type="hidden" name="quote_type" value="residential">

                    <input type="hidden" name="quote_id">

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
                    <input type="hidden" name="quote_type" value="residential">

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

    function addUpdateOfficeNotes(quote_id, ref) {
        const office_notes = jQuery(ref).attr('data-office-notes');

        jQuery('#addUpdateOfficeNoteForm input[name="quote_id"]').val(quote_id);
        jQuery('#addUpdateOfficeNoteForm textarea[name="office_notes"]').val(office_notes);
        jQuery('#addUpdateOfficeNotesModal').modal('show');
    }

    function smsQuoteLink(quote_id, phone_no = '') {
        jQuery('#smsQuoteLinkForm input[name="quote_id"]').val(quote_id);
        jQuery('#smsQuoteLinkForm input[name="phone_no"]').val(phone_no);
        jQuery('#smsQuoteLinkModal').modal('show');
    }

    function deleteQuote(quote_id, ref) {
        if (!confirm('Are you sure you want to delete this Residential quote ?')) return false;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: "json",
            data: {
                action: "delete_quotesheet",
                quote_id,
                '_wpnonce': "<?= wp_create_nonce('delete_quotesheet'); ?>"
            },
            beforeSend: function() {
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
            },
            success: function(data) {

                if (data.status === "success") {
                    jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                } else {
                    alert(data.message);
                    jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
                }

            }
        })

    }

    function downloadResidentialQuote(quote_id) {
        jQuery('#downloadResidentialQuoteForm input[name="quote_id"]').val(quote_id);
        jQuery('#downloadResidentialQuoteForm').submit();
    }

    (function($) {
        $(document).ready(function() {

            $('.mymodal').click(function() {
                let quote_id = $(this).attr('data-quote-id');
                $('#quote_id').val(quote_id);
                $('#commentModal').modal({
                    show: 'true'
                });
            });

            $('.lead-status').on('click', function() {

                let quote_id = $(this).attr('data-lead-id');
                let quote_status = $(this).attr('data-lead-status');
                let lead = $(this);
                $.ajax({
                    type: 'post',
                    url: "<?= esc_url(admin_url('admin-ajax.php')); ?>",
                    data: {
                        action: 'udpate_quote_status',
                        quote_id,
                        quote_status,
                        quote_type: 'residential',
                        "_wpnonce": "<?= wp_create_nonce('udpate_quote_status'); ?>"
                    },
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        if (data.status == "success") {
                            $(`.lead-${quote_id}`).removeClass('btn-primary').addClass('btn-default');
                            lead.removeClass('btn-default').addClass('btn-primary');
                        } else {
                            console.log('someghint went wrong...please try again later');

                        }
                    }
                })

            });

            $('#emailQuoteForm').validate({
                rules: {
                    email: 'required'
                },
                submitHandler: function(form) {
                    $.ajax({
                        type: 'post',
                        url: '<?= admin_url('admin-ajax.php'); ?>',
                        data: $(form).serialize(),
                        dataType: 'json',
                        beforeSend: function() {
                            $('#emailQuoteBtn').attr('disabled', true);
                        },
                        success: function(data) {
                            if (data.status === 'success') {
                                swal.fire(
                                    'Email Sent!',
                                    'Email sent to client successfully',
                                    'success'
                                )
                            } else {
                                swal.fire(
                                    'Oops!',
                                    data.message,
                                    'error'
                                )
                            }

                            $('#emailQuoteBtn').attr('disabled', false);
                            $('#emailQuoteModal').modal('hide');
                        },
                        error: function() {
                            swal.fire(
                                'Oops!',
                                'Something went wrong, please try again later',
                                'error'
                            )
                        }
                    })
                }
            });

            $('#addUpdateOfficeNoteForm').validate({
                rules: {
                    office_notes: {
                        required: true,
                    }
                }
            });

            $('#smsQuoteLinkForm').validate({
                rules: {
                    phone_no: "required"
                },
                submitHandler: function(form) {
                    $.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        data: $('#smsQuoteLinkForm').serialize(),
                        dataType: "json",
                        beforeSend: function() {
                            $('#sms_quote_btn').attr('disabled', true);
                        },
                        success: function(data) {
                            alert(data.message);
                            $('#sms_quote_btn').attr('disabled', false);
                            $('#smsQuoteLinkModal').modal('hide');
                        }
                    });
                }
            });
        });
    })(jQuery);
</script>