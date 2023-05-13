<?php
global $wpdb;

$upload_dir = wp_upload_dir();
$invoice = $wpdb->get_row("select * from {$wpdb->prefix}invoices where id={$_GET['invoice_id']} ");

// get all technicians including fired technicians
$technicians = (new Technician_details)->get_all_technicians(true, '', false);

$callrail_traking_numbers = (new Callrail_new)->get_all_tracking_no();
$lead_sources = (new ColdCaller)->get_lead_sources();
$payment_methods = (new TekCard)->paymentMethods();

$gam_service = new GamServices;
$type_of_services = $gam_service->getTypeOfServices();
$service_descriptions = $gam_service->getServiceDescriptions();
$area_of_services = $gam_service->getAreaOfService();
$findings = $gam_service->getFindinds();

$invoice_db_findings = !empty($invoice->findings) ? explode(' || ', $invoice->findings) : [];
$invoice_db_area_service = !empty($invoice->area_of_service) ? explode(' || ', $invoice->area_of_service) : [];
$invoice_db_service_desc = !empty($invoice->service_description) ? explode(' || ', $invoice->service_description) : [];

$other_finding_found = $invoice->other_findings;
$other_service_desc_found = $invoice->other_service_description;
$other_area_service_found = $invoice->other_area_of_service;
$warranty_explain = $invoice->warranty_explanation;
?>

<?php if ($invoice) : ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <?php (new GamFunctions)->getFlashMessage(); ?>
            </div>
            <div class="col-sm-12">

                <div class="card">
                    <div class="card-body">

                        <form id="updateInvoiceForm" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
                            <?php wp_nonce_field('edit_invoice'); ?>
                            <input type="hidden" name="action" value="edit_invoice">
                            <input type="hidden" name="invoice_id" value="<?= $_GET['invoice_id']; ?>">
                            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                            <?php
                            $reservice_option = false;
                            if (!empty($invoice->reservice_id)) :
                                $reserviceData = (new Invoice)->getReserviceData($invoice->reservice_id);
                                $reservice_option = true;
                                $total_reservices = $reserviceData->total_reservices;
                                $revisit_frequency_unit = $reserviceData->revisit_frequency_unit;
                                $revisit_frequency_timeperiod = $reserviceData->revisit_frequency_timeperiod;
                                $reservice_fee = $reserviceData->reservice_fee;
                                echo '<input type="hidden" name="reservice_id" value="' . $invoice->reservice_id . '">';
                            endif;
                            ?>

                            <h3 class="page-header">Edit Invoice</h3>
                            <div class="form-group">
                                <label for="">Name</label>
                                <input class="form-control" type="text" name="clientName" value="<?= $invoice->client_name; ?>">
                            </div>
                            <div class="form-group">
                                <label for="">Phone No.</label>
                                <input class="form-control" type="text" name="clientPhn" id="clientPhn" value="<?= $invoice->phone_no; ?>">
                            </div>
                            <div class="form-group">
                                <label for="">Address</label>
                                <textarea class="form-control" name="clientAddress" id="clientAddress" cols="30" rows="5"><?= $invoice->address; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="">Date</label>
                                <input class="form-control" type="date" name="startDate" value="<?= $invoice->date; ?>">
                            </div>
                            <div class="form-group">
                                <label for="">Email</label>
                                <input class="form-control" type="email" name="clientEmail" id="clientEmail" value="<?= $invoice->email; ?>">
                            </div>
                            <div class="form-group">
                                <label for="">Service fee</label>
                                <input class="form-control amount_fields" type="text" name="service_fee" id="service_fee" value="<?= $invoice->service_fee; ?>">
                            </div>

                            <!-- FINDINGS -->
                            <div class="form-group">
                                <label for="">Findings</label>
                                <select name="findings[]" class="form-control select2-field" multiple>
                                    <?php if (is_array($findings) && count($findings) > 0) : ?>
                                        <?php foreach ($findings as $findings) : ?>
                                            <option <?php
                                                    if (in_array($findings->name, $invoice_db_findings)) {
                                                        echo 'selected=selected';
                                                    }
                                                    ?> value="<?= $findings->name; ?>"><?= $findings->name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="checkbox">
                                <label><input <?= (!empty($other_finding_found) ? 'checked=checked' : '') ?> name="checkbox_other_findings" type="checkbox" value="yes">Other: (option to write something if not listed above)</label>
                            </div>

                            <div class="form-group <?= (!empty($other_finding_found) ? '' : 'hidden') ?>">
                                <label for="">Other (Findings)</label>
                                <input type="text" value="<?= (!empty($other_finding_found) ? $other_finding_found : '') ?>" class="form-control" name="findings_other">
                            </div>
                            <!-- end -->

                            <!-- SERVICE DESCRIPTION -->
                            <div class="form-group">
                                <label for="">Service Description</label>
                                <select name="service_description[]" class="form-control select2-field" multiple>
                                    <?php if (is_array($service_descriptions) && count($service_descriptions) > 0) : ?>
                                        <?php foreach ($service_descriptions as $service_description) : ?>
                                            <option <?php
                                                    if (in_array($service_description->name, $invoice_db_service_desc)) {
                                                        echo 'selected=selected';
                                                    }
                                                    ?> value="<?= $service_description->name; ?>"><?= $service_description->name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="checkbox">
                                <label><input <?= (!empty($other_service_desc_found) ? 'checked=checked' : '') ?> name="checkbox_other_service_description" type="checkbox" value="yes">Other: (option to write something if not listed above)</label>
                            </div>

                            <div class="form-group <?= (!empty($other_service_desc_found) ? '' : 'hidden') ?>">
                                <label for="">Other (Service Description)</label>
                                <input type="text" value="<?= (!empty($other_service_desc_found) ? $other_service_desc_found : '') ?>" class="form-control" name="other_service_description">
                            </div>
                            <!-- end -->


                            <!-- area of service/inspection -->
                            <div class="form-group">
                                <label for="">Area of service/inspection</label>
                                <select name="area_of_service[]" class="form-control select2-field" multiple>
                                    <?php if (is_array($area_of_services) && count($area_of_services) > 0) : ?>
                                        <?php foreach ($area_of_services as $area_of_service) : ?>
                                            <option <?php
                                                    if (in_array($area_of_service->name, $invoice_db_area_service)) {
                                                        echo 'selected=selected';
                                                    }
                                                    ?> value="<?= $area_of_service->name; ?>"><?= $area_of_service->name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="checkbox">
                                <label><input <?= (!empty($other_area_service_found) ? 'checked=checked' : '') ?> name="checkbox_other_area_of_service" type="checkbox" value="yes">Other: (option to write something if not listed above)</label>
                            </div>

                            <div class="form-group <?= (!empty($other_area_service_found) ? '' : 'hidden') ?>">
                                <label for="">Other (Area of service/inspection)</label>
                                <input type="text" value="<?= (!empty($other_area_service_found) ? $other_area_service_found : '') ?>" class="form-control" name="other_area_of_service">
                            </div>
                            <!-- end -->

                            <!-- FILE UPLOAD OPTIONAL  -->
                            <div class="form-group">
                                <label for="">Upload Images (optional) <small><i>These images will be sent to client as well</i></small></label>
                                <input accept="image/*" type="file" name="optional_images[]" class="form-control" multiple>
                                <?php if (!empty($invoice->optional_images)) : $images = json_decode($invoice->optional_images);
                                ?>
                                    <div class="invoice_optional_images row">
                                        <?php for ($i = 0; $i < count($images); $i++) : ?>
                                            <div class="col-md-3" data-img-id=<?= $images[$i]->id; ?>>
                                                <a target="_blank" href=<?= $images[$i]->url; ?>><img style="cursor:zoom-in;height:100px;width:100px" src="<?= $images[$i]->url; ?>">
                                                </a>
                                                <a style="margin: 15px 0px 15px 0px;" href="javascript:;" class="remove_extra_invoice_imgs btn btn-danger btn-xs" data-img-attr=<?= json_encode($images[$i]); ?>>Remove <i class="fa fa-times"></i></a>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="">Notes</label>
                                <textarea name="client_notes" cols="30" rows="5" class="form-control"><?= $invoice->client_notes; ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="">Do you recommend reservice ?</label>
                                <label class="radio-inline"><input type="radio" <?= ($reservice_option) ?  "checked" : "";  ?> value="yes" name="client_require_reservice">Yes</label>
                                <label class="radio-inline"><input type="radio" <?= (!$reservice_option) ?  "checked" : "";  ?> value="no" name="client_require_reservice">No</label>
                            </div>

                            <div class="revisit_questions <?= (!$reservice_option) ?  "hidden" : "";  ?>">
                                <div class="form-group">
                                    <label for="">Minimum number of reservices recommended</label>
                                    <input type="text" class="form-control numberonly" value="<?= (!empty($total_reservices) ? $total_reservices : ''); ?>" name="total_reservices">
                                </div>

                                <div class="form-group">
                                    <label for="">Revisit frequency</label>
                                    <p>
                                        <b>Every</b>
                                        <input style="width:50px" type="number" min="1" class="numberonly" value="<?= (!empty($revisit_frequency_unit) ? $revisit_frequency_unit : ''); ?>" name="revisit_frequency_unit">
                                        <select name="revisit_frequency_timeperiod" id="">
                                            <option <?= (!empty($revisit_frequency_timeperiod) &&  $revisit_frequency_timeperiod == 'days' ? ' selected="selected"' : ''); ?> value="days">Day(s)</option>
                                            <option <?= (!empty($revisit_frequency_timeperiod) &&  $revisit_frequency_timeperiod == 'weeks' ? ' selected="selected"' : ''); ?> value="weeks">Week(s)</option>
                                            <option <?= (!empty($revisit_frequency_timeperiod) &&  $revisit_frequency_timeperiod == 'months' ? ' selected="selected"' : ''); ?> value="months">Month(s)</option>
                                        </select>

                                        <span><small>for e.g. Every 1 month , Every 2 week etc..</small></span>
                                    </p>
                                </div>

                                <div class="form-group">
                                    <label for="">Cost per revisit</label>
                                    <input type="text" class="form-control numberonly" value="<?= (!empty($reservice_fee) ? $reservice_fee : ''); ?>" name="follow_up_fee" placeholder="e.g. 100">
                                </div>

                            </div>

                            <!-- Warranty Question -->
                            <div class="form-group">
                                <label for="">Is warranty included ?</label>
                                <label class="radio-inline"><input <?= (!empty($warranty_explain) ? 'checked=checked' : ''); ?> type="radio" value="yes" name="warranty_recommendation">Yes</label>
                                <label class="radio-inline"><input <?= (empty($warranty_explain) ? 'checked=checked' : ''); ?> type="radio" value="no" name="warranty_recommendation">No</label>
                            </div>

                            <div class="form-group warranty_explanation <?= (!empty($warranty_explain) ? '' : 'hidden'); ?>">
                                <label for="">Please explain warranty</label>
                                <textarea name="warranty_explanation" cols="30" rows="5" class="form-control"><?= (!empty($warranty_explain) ? $warranty_explain : ''); ?></textarea>
                            </div>

                            <table class="invoice_table invoice_mid_tbl">
                                <tr class="invoice_mid_tbl_tr">
                                    <th></th>
                                    <th>Units</th>
                                    <th>Price Per Unit</th>
                                    <th>Total</th>
                                </tr>
                                <?php $product = json_decode($invoice->product_used, true); ?>
                                <?php if (array_key_exists('0', (array)$product)) : ?>
                                    <?php foreach ($product as $key => $val) : ?>
                                        <tr>
                                            <th class="table-hd"><?= $val['name']; ?></th>
                                            <td><?= $val['Unit']; ?></td>
                                            <td>$<?= $val['Price']; ?></td>
                                            <td>$<?= $val['Total']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>

                                    <tr>
                                        <th class="table-hd">Bait station</th>
                                        <td><?= @$product['bail_stationUnit']; ?></td>
                                        <td><?= @$product['bail_stationPrice']; ?></td>
                                        <td><?= @$product['bail_stationTotal']; ?></td>
                                    </tr>

                                    <tr>
                                        <th class="table-hd">Glue boards</th>
                                        <td><?= @$product['glue_boardUnit']; ?></td>
                                        <td><?= @$product['glue_boardPrice']; ?></td>
                                        <td><?= @$product['glue_boardTotal']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="table-hd">Mouse snap traps</th>
                                        <td><?= @$product['mouse_snapUnit']; ?></td>
                                        <td><?= @$product['mouse_snapPrice']; ?></td>
                                        <td><?= @$product['mouse_snapTotal']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="table-hd">Rat snap traps</th>
                                        <td><?= @$product['rat_snapUnit']; ?></td>
                                        <td><?= @$product['rat_snapPrice']; ?></td>
                                        <td><?= @$product['rat_snapTotal']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="table-hd">Hole sealing</th>
                                        <td><?= @$product['hole_sealUnit']; ?></td>
                                        <td><?= @$product['hole_sealPrice']; ?></td>
                                        <td><?= @$product['hole_sealTotal']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="table-hd">Tin cats</th>
                                        <td><?= @$product['tin_catsUnit']; ?></td>
                                        <td><?= @$product['tin_catsPrice']; ?></td>
                                        <td><?= @$product['tin_catsTotal']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="table-hd">Poison</th>
                                        <td><?= @$product['poisonUnit']; ?></td>
                                        <td><?= @$product['poisonPrice']; ?></td>
                                        <td><?= @$product['poisonTotal']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="table-hd">Fogging</th>
                                        <td><?= @$product['fogginUnit']; ?></td>
                                        <td><?= @$product['fogginPrice']; ?></td>
                                        <td><?= @$product['fogginTotal']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="table-hd">Other</th>
                                        <td><?= @$product['other_unit']; ?></td>
                                        <td><?= @$product['other_price']; ?></td>
                                        <td><?= @$product['other_total']; ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <th>Tax</th>
                                    <td><input class="form-control amount_fields" type="text" name="tax" value="<?= $invoice->tax; ?>" required></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <th>Processing Fee</th>
                                    <td><input class="form-control amount_fields" type="text" name="processing_fee" value="<?= $invoice->processing_fee; ?>" required></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <th>Total Amount</th>
                                    <td><span><input class="form-control" type="text" name="total_amount" id="total_amount" value="<?= $invoice->total_amount; ?>"></span></td>
                                </tr>

                            </table>

                            <div class="form-group">
                                <label for="">Payment Method</label>
                                <select class="form-control select2-field" name="payment_process" id="payment_process">
                                    <option value="">Select</option>
                                    <?php if (is_array($payment_methods) && count($payment_methods) > 0) : ?>
                                        <?php foreach ($payment_methods as $payment_method) : ?>
                                            <option value="<?= $payment_method->slug; ?>" <?= $invoice->payment_method == $payment_method->slug ? 'selected' : ''; ?>><?= $payment_method->name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="">Technician Name</label>
                                <select class="form-control select2-field" name="technician_id" required>
                                    <option value="">Select Technician</option>
                                    <?php if (is_array($technicians) && count($technicians) > 0) : ?>
                                        <?php foreach ($technicians as $technician) : ?>
                                            <option value="<?= $technician->id; ?>" <?= $invoice->technician_id == $technician->id ? 'selected' : ''; ?>><?= $technician->first_name . " " . $technician->last_name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="">Type of Service Provided</label>
                                <select name="technician_service_type" class="form-control select2-field">
                                    <option value="">Select</option>
                                    <?php if (is_array($type_of_services) && count($type_of_services) > 0) : ?>
                                        <?php foreach ($type_of_services as $type_of_service) : ?>
                                            <option <?php
                                                    if (
                                                        !empty($invoice->type_of_service_provided)
                                                        && $invoice->type_of_service_provided == $type_of_service->name
                                                    ) {
                                                        echo 'selected=selected';
                                                    }
                                                    ?> value="<?= $type_of_service->name; ?>"><?= $type_of_service->name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="">Callrail Tracking Number</label>
                                <select name="callrail_id" class="select2-field" required>
                                    <option value="">Select</option>
                                    <?php foreach ($callrail_traking_numbers as $key => $val) : ?>
                                        <option value="<?= $val->id; ?>" <?= $invoice->callrail_id == $val->id ? "selected" : '';  ?>><?= $val->tracking_phone_no; ?> - <?= $val->tracking_name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="">Client Signature</label>
                                <img src="<?= $upload_dir['baseurl'] . '/pdf/signatures/invoice/' . $invoice->sign_img; ?>" alt="">
                            </div>
                            
                            <?php if(!isset($_GET['view'])) : ?>
                            <div class="form-group">
                                <label for="">Payment Proof Doc.</label>
                                <?php if (!empty($invoice->additional_doc)) : ?>
                                    <a target="_blank" href="<?= $invoice->additional_doc ?>"><span><i class="fa fa-eye"></i></span> Show File</a>
                                <?php endif; ?>
                                <input type="file" name="additional_doc" class="form-control">
                            </div>
                            <?php endif;?>

                            <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Invoice</button>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

<?php else : ?>
    <h1>No Record Found</h1>
<?php endif; ?>

<script>
    (function($) {
        $('#updateInvoiceForm').validate({
            rules: {
                clientName: "required",
                clientPhn: "required",
                clientAddress: "required",
                startDate: "required",
                service_fee: "required",
                tax: "required",
                processing_fee: "required",
                total_amount: "required",
                payment_process: "required",
                technician_id: "required",
                technician_service_type: "required",
                callrail_id: "required",
            }
        })
        $('input[name="client_require_reservice"]').on('change', function() {
            let status = $(this).val();
            if (status == "yes") {
                $('.revisit_questions').removeClass('hidden');
            } else {
                $('.revisit_questions').addClass('hidden');
            }
        });

        function gamHiddenInput(elm, inp) {
            if (jQuery(elm).is(':checked')) {
                inp.parent().removeClass('hidden');
                inp.removeAttr('disabled', 'disabled');
            } else {
                inp.parent().addClass('hidden');
                inp.attr('disabled', 'disabled');
            }
        }

        jQuery('input[name="checkbox_other_findings"]').on('click', function() {
            let inp = $('input[name="findings_other"]');
            gamHiddenInput(this, inp);
        });

        jQuery('input[name="checkbox_other_service_description"]').on('click', function() {
            let inp = $('input[name="other_service_description"]');
            gamHiddenInput(this, inp);
        });

        jQuery('input[name="checkbox_other_area_of_service"]').on('click', function() {
            let inp = $('input[name="other_area_of_service"]');
            gamHiddenInput(this, inp);
        });

        $('input[name="warranty_recommendation"]').on('change', function() {
            let status = $(this).val();
            inp = $('.warranty_explanation');
            if (status == "yes") {
                inp.removeClass('hidden');
                inp.find('textarea').removeAttr('disabled', 'disabled');
            } else {
                inp.addClass('hidden');
                inp.find('textarea').attr('disabled', 'disabled');
            }
        });

        // call ajax to remove image from invoice
        $('.remove_extra_invoice_imgs').on('click', function() {
            let obj = $(this);
                swal.fire({
                    title: "Are you sure",
                    text: "You want to delete this image ?",
                    showCancelButton: true,
                    confirmButtonText: 'Yes, I am sure!',
                    icon: "warning",
                })
            .then((willDelete) => {
                if (willDelete.isConfirmed) {
                    $.ajax({
                        type: 'post',
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        data: {
                            action: "invoice_delete_extra_images",
                            inv_id: $('input[name="invoice_id"]').val(),
                            img_data: obj.data('img-attr'),
                            "_wpnonce": "<?= wp_create_nonce('remove_optional_invoice_images'); ?>"
                        },
                        dataType: "json",
                        beforeSend: function() {
                            showLoader('Deleting image, please wait...');
                        },
                        success: function(res) {
                            if (res.status === "success") {
                                swal.close();
                                $('.invoice_optional_images').find(`[data-img-id='${res.data.img_id}']`).remove();
                            }else{
                                swal.fire(
                                    'Oops!',
                                    data.message,
                                    'error'
                                );
                                location.reload();
                            }
                        }
                    });
                }
            });
        });

    })(jQuery);
</script>