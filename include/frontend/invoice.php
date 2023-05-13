<?php
global $wpdb;

$technician = new Technician_Details;

$technician_id = $technician->get_technician_id();
$technician_branch = $technician->getTechnicianBranchSlug($technician_id);

$payment_methods = (new TekCard)->paymentMethods();
$newyork_counties = (new Invoice)->getNewyorkCounties();

$invoice_flow = new InvoiceFlow();

$sales_tax_rate = $invoice_flow->getSalesTaxRate();
$service_fee = $invoice_flow->getServiceFee();
$event_payment_method = $invoice_flow->getPaymentMethod();
$client_name = $invoice_flow->getClientName();
$client_location = $invoice_flow->getClientAddress();
$client_phone_no = $invoice_flow->getClientPhoneNo();
$client_email = $invoice_flow->getClientEmail();
$is_tax_exempted = $invoice_flow->isTaxExempted();

$gam_service = new GamServices;

$type_of_services = $gam_service->getTypeOfServices();
$service_descriptions = $gam_service->getServiceDescriptions();
$area_of_services = $gam_service->getAreaOfService();
$findings = $gam_service->getFindinds();

?>

<style>
   .salestax__editConfirmContainer {
      display: inline-block;
      cursor: pointer;
   }

   .salestax__editContainer {
      display: inline-block;
      cursor: pointer;
   }

   input#sales_tax_rate {
      width: 40px;
      display: inline-block;
   }
   .plus_minus_icon{
      font-size: 25px;
      margin: 5px;
   }
</style>
<div class="container">
   <form method="post" id="invoice_form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" name="res-form" class="res-form reset-form" enctype="multipart/form-data">
      <?php (new GamFunctions)->getFlashMessage(); ?>
      <?php wp_nonce_field('invoice_form'); ?>

      <div class="row">
         <div class="col-sm-12">
            <button type="button" class="btn btn-danger btn-sm pull-right" id="reset_invoice_page"><span><i class="fa fa-refresh"></i></span> Restart the page</button>
         </div>
      </div>

      <input type="hidden" name="action" value="invoice_form">
      <input type="hidden" name="signimgurl">
      <input type="hidden" name="callrail_id" value="unknown">
      <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

      <h2 class="form-head">Invoice</h2>
      <div class="event_error text-danger text-left hidden"></div>
      <div class="row">
         <!-- EVENT DATE -->
         <div class="col-sm-12">
            <?php list($start_date, $end_date) = (new GamFunctions)->x_week_range(date('Y-m-d'), 'sunday'); ?>

            <!-- NAME -->
            <div class="form-group">
               <label for=" name">Client name </label>
               <input type="text" id="clientName" maxlength="100" class="form-control client_name" value="<?= $client_name; ?>" name="clientName" placeholder="Name">
               <span id="clientName_error_msg"></span>
            </div>

            <!-- ADDRESS -->
            <div class="form-group">
               <label for="address"><span><i class="fa fa-address-o"></i></span> Address </label>
               <input type="text" class="form-control client_address" id="clientAddress" name="clientAddress" value="<?= $client_location; ?>">
            </div>

            <!-- PHONE NO. -->
            <div class="form-group">
               <label for="number"><span><i class="fa fa-phone"></i></span> Phone No.</label>
               <input type="tel" class="form-control" id="client_phone_no" name="clientPhn" placeholder="Phone Number" value="<?= $client_phone_no; ?>">
               <span><input type="checkbox" id="no_phone_to_offer"> Client does not have phone to offer?</span>
               <small><span><i>(Please do not select this if client has phone, this may result in your route being frozen)</i></span></small>
               <div class="form-group hidden multiple_phone_inv_group">
                  <label for="multiple-Email"><span><i class="fa fa-phone"></i></span> Phone <?= (new GamFunctions)->validPhoneAlertMessage(); ?></label>
                  <div class="row">
                     <div class="col-sm-11">
                        <input type="tel" placeholder="Enter Phone No" name="multiple_inv_phone[0]" id="multiple_inv_phone_0" required class="form-control multiple_inv_phone">
                     </div>
                     <div class="col-sm-1">
                        <i class="fa fa-plus-circle add_more_phone_icon plus_minus_icon" aria-hidden="true"></i>
                     </div>
                  </div>
               </div>
               <div class="form-group">
                  <span><input type="checkbox" id="multiple_phone_to_offer"> Want to add multiple phone no click here ?</span>
               </div>

            </div>

            <!-- CLIENT EMAIL  -->
            <div class="form-group">
               <label for="Email"><span><i class="fa fa-envelope"></i></span> Email <?= (new GamFunctions)->fakeEmailAlertMessage(); ?></label>
               <input type="email" class="form-control client_email" id="Email" name="clientEmail" placeholder="Email" value="<?= $client_email; ?>">
               <span><input type="checkbox" id="no_email_to_offer"> Client does not have email to offer?</span>
               <small><span><i>(Please do not select this if client has email, this may result in your route being frozen)</i></span></small>
               <div class="form-group hidden multiple_emails_inv_group">
                  <label for="multiple-Email"><span><i class="fa fa-envelope"></i></span> Email <?= (new GamFunctions)->validEmailAlertMessage(); ?></label>
                  <div class="row">
                     <div class="col-sm-11">
                        <input type="text" placeholder="Enter Email Address" name="multiple_inv_emails[0]" id="multiple_inv_emails_0" required class="form-control multiple_inv_emails">
                     </div>
                     <div class="col-sm-1">
                        <i class="fa fa-plus-circle add_more_email_icon plus_minus_icon" aria-hidden="true"></i>
                     </div>
                  </div>
               </div>
               <div class="form-group">
                  <span><input type="checkbox" id="multiple_email_to_offer"> Want to add multiple emails click here ?</span>
               </div>
            </div>

            <!-- PAYMENT METHODS  -->
            <div class="form-group check">
               <label for="ckeck">Payment Method</label>
               <select name="payment_process" id="payment_process" class="form-control">
                  <option value="">Select</option>
                  <?php if (is_array($payment_methods) && count($payment_methods) > 0) : ?>
                     <?php foreach ($payment_methods as $payment_method) : ?>
                        <option value="<?= $payment_method->slug; ?>" <?= $payment_method->slug == $event_payment_method ? 'selected' : ''; ?>><?= $payment_method->name; ?></option>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
            </div>

            <!-- CHECK PICTURE -->
            <div class="form-group hidden check_picture">
               <label for="">Upload Picture of Check</label>
               <input type="file" accept=".png,.jpg,.jpeg" name="check_image" class="form-control">
            </div>

            <!-- SERVICE FEE  -->
            <div class="form-group">
               <label for="fee">Service fee</label>
               <input type="text" class="form-control numberonly amount" value="<?= is_numeric($service_fee) ? $service_fee : ''; ?>" maxlength="10" name="serviceFee">
            </div>

            <!-- FINDINGS -->
            <div class="form-group">
               <label for="">Findings</label>
               <select name="findings[]" class="form-control select2-field" multiple>
                  <?php if (is_array($findings) && count($findings) > 0) : ?>
                     <?php foreach ($findings as $findings) : ?>
                        <option value="<?= $findings->name; ?>"><?= $findings->name; ?></option>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
            </div>

            <div class="checkbox">
               <label><input name="checkbox_other_findings" type="checkbox" value="yes">Other: (option to write something if not listed above)</label>
            </div>

            <div class="form-group hidden">
               <label for="">Other (Findings)</label>
               <input type="text" class="form-control" name="findings_other">
            </div>

            <!-- TYPE OF SERVICE  -->
            <div class="form-group">
               <label for="">Type of service</label>
               <select name="type_of_service" class="form-control select2-field">
                  <option value="">Select</option>
                  <?php if (is_array($type_of_services) && count($type_of_services) > 0) : ?>
                     <?php foreach ($type_of_services as $type_of_service) : ?>
                        <option value="<?= $type_of_service->name; ?>"><?= $type_of_service->name; ?></option>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
            </div>

            <!-- SERVICE DESCRIPTION -->
            <div class="form-group">
               <label for="">Service Description</label>
               <select name="service_description[]" class="form-control select2-field" multiple>
                  <?php if (is_array($service_descriptions) && count($service_descriptions) > 0) : ?>
                     <?php foreach ($service_descriptions as $service_description) : ?>
                        <option value="<?= $service_description->name; ?>"><?= $service_description->name; ?></option>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
            </div>

            <div class="checkbox">
               <label><input name="checkbox_other_service_description" type="checkbox" value="yes">Other: (option to write something if not listed above)</label>
            </div>

            <div class="form-group hidden">
               <label for="">Other (Service Description)</label>
               <input type="text" class="form-control" name="other_service_description">
            </div>

            <!-- area of service/inspection -->
            <div class="form-group">
               <label for="">Area of service/inspection</label>
               <select name="area_of_service[]" class="form-control select2-field" multiple>
                  <?php if (is_array($area_of_services) && count($area_of_services) > 0) : ?>
                     <?php foreach ($area_of_services as $area_of_service) : ?>
                        <option value="<?= $area_of_service->name; ?>"><?= $area_of_service->name; ?></option>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
            </div>

            <div class="checkbox">
               <label><input name="checkbox_other_area_of_service" type="checkbox" value="yes">Other: (option to write something if not listed above)</label>
            </div>

            <div class="form-group hidden">
               <label for="">Other (Area of service/inspection)</label>
               <input type="text" class="form-control" name="other_area_of_service">
            </div>

            <!-- FILE UPLOAD OPTIONAL  -->
            <div class="form-group">
               <label for="">Upload Images (optional) <small><i>These images will be sent to client as well</i></small></label>
               <input accept="image/*" type="file" name="optional_images[]" class="form-control" multiple>
            </div>

            <?php if ($invoice_flow->getClientInterestedInMaintenance() == "no" || !$invoice_flow->canBypassMaintenanceStep()) : ?>

               <!-- follow up / service fee -->
               <div class="form-group">
                  <label for="">Do you recommend reservice ?</label>
                  <label class="radio-inline"><input type="radio" value="yes" name="client_require_reservice">Yes</label>
                  <label class="radio-inline"><input type="radio" value="no" name="client_require_reservice">No</label>
               </div>

               <div class="revisit_questions hidden">

                  <div class="form-group">
                     <label for="">Minimum number of reservices recommended</label>
                     <input type="text" class="form-control numberonly" name="total_reservices">
                  </div>

                  <div class="form-group">
                     <label for="">Revisit frequency</label>
                     <p>
                        <b>Every</b>
                        <input style="width:50px" type="number" min="1" class="numberonly" name="revisit_frequency_unit">
                        <select name="revisit_frequency_timeperiod" id="">
                           <option value="days">Day(s)</option>
                           <option value="weeks">Week(s)</option>
                           <option value="months">Month(s)</option>
                        </select>

                        <span><small>for e.g. Every 1 month , Every 2 week etc..</small></span>
                     </p>
                  </div>

                  <div class="form-group">
                     <label for="">Cost per revisit</label>
                     <input type="text" class="form-control numberonly" name="follow_up_fee" placeholder="e.g. 100">
                  </div>

               </div>

               <!-- Warranty Question -->
               <div class="form-group">
                  <label for="">Is warranty included ?</label>
                  <label class="radio-inline"><input type="radio" value="yes" name="warranty_recommendation">Yes</label>
                  <label class="radio-inline"><input type="radio" value="no" name="warranty_recommendation">No</label>
               </div>

               <div class="form-group warranty_explanation hidden">
                  <label for="">Please explain warranty</label>
                  <textarea name="warranty_explanation" cols="30" rows="5" class="form-control"></textarea>
               </div>
            <?php endif; ?>

            <!-- ADDITIONAL NOTES -->
            <div class="form-group">
               <label for="">Any additional Notes/Comment to be sent to client</label>
               <textarea name="client_notes" cols="30" rows="5" class="form-control"></textarea>
            </div>

            <!-----SERVICES PROVIDED WITH COST --->
            <div class="form-group">
               <table class="price-chart price-chart-services">
                  <thead>
                     <tr>
                        <th></th>
                        <th style=" font-size: 12px;line-height: 14px;text-align: center;">Units</th>
                        <th style=" font-size: 12px;line-height: 14px;text-align: center;">Price per unit</th>
                        <th style="font-size: 12px;line-height: 14px;text-align: center;">Total (optional) </th>
                        <th></th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <th class="table-hd">Bait station</th>
                        <input type="hidden" name="product[0][name]" value="Bait Station">
                        <td><input type="text" class="form-control numberonly" name="product[0][Unit]" id="bail_stationUnit" /></td>
                        <td><input type="text" class="form-control numberonly" name="product[0][Price]" id="bail_stationPrice" /></td>
                        <td><input type="text" class="form-control numberonly extra_total amount" name="product[0][Total]" id="bail_stationTotal" /></td>
                        <td></td>
                     </tr>
                  </tbody>
               </table>

               <div class="dropdown pull-right">
                  <button class="btn btn-primary" type="button" data-toggle="dropdown">Add More
                     <span class="caret"></span></button>
                  <ul class="dropdown-menu extra-services">
                     <li data-index="1" data-slug="glue_board"><a href="javascript:void(0)">Glue Boards</a></li>
                     <li data-index="1" data-slug="rat_bait_station"><a href="javascript:void(0)">Rat bait station</a></li>
                     <li data-index="1" data-slug="mice_bait_station"><a href="javascript:void(0)">Mice bait station</a></li>
                     <li data-index="1" data-slug="nuvan_strip"><a href="javascript:void(0)">Nuvan strip</a></li>
                     <li data-index="1" data-slug="fly_stick"><a href="javascript:void(0)">Fly stick</a></li>
                     <li data-index="2" data-slug="mouse_snap"><a href="javascript:void(0)">Mouse snap traps</a></li>
                     <li data-index="3" data-slug="rat_snap"><a href="javascript:void(0)">Rat snap traps</a></li>
                     <li data-index="4" data-slug="hole_seal"><a href="javascript:void(0)">Hole sealing</a></li>
                     <li data-index="5" data-slug="tin_cats"><a href="javascript:void(0)">Tin cats</a></li>
                     <li data-index="6" data-slug="poision"><a href="javascript:void(0)">Poison</a></li>
                     <li data-index="7" data-slug="foggin"><a href="javascript:void(0)">Fogging</a></li>
                     <li data-index="8" data-slug="other"><a href="javascript:void(0)">Other</a></li>
                  </ul>
               </div>

               <table class="price-chart">
                  <tbody>
                     <tr>
                        <td class="text"><span for="tax"><strong><small> Sub Total </small></strong></td>
                        <td class="text"><input id="subtotal" name="subtotal" type="text" maxlength="10" placeholder="$" class="form-control numberonly amount" readonly></td>
                     </tr>

                     <!-- SALES TAX  -->
                     <tr class="salestax__container">

                        <td class="text"><span for="tax">

                              <strong><small> Sales Tax </small></strong>

                              @<input type="text" name="sales_tax_rate" class="numberonly" id="sales_tax_rate" value="<?= $is_tax_exempted ?  '0' : $sales_tax_rate; ?>" readonly <?= $is_tax_exempted ? 'disabled' : ''; ?>>%

                              <?php if (!$is_tax_exempted) : ?>
                                 <div class="salestax__editContainer">
                                    <span><i class="fa fa-edit"></i></span>
                                 </div>

                                 <div class="salestax__editConfirmContainer hidden">
                                    <span><i class="fa fa-check"></i></span>
                                 </div>
                              <?php endif; ?>

                        </td>

                        <td class="text"><input name="sales_tax_amount" type="text" maxlength="10" placeholder="$" class="form-control numberonly" readonly></td>
                     </tr>

                     <tr class="processing_fee">
                        <td class="text">
                           <span for="tax" class="text-small"><strong><small>Processing Fees @ 3% <a class="text-danger" href="javascript:void(0)" data-toggle="tooltip" title="Processing Fee Applicable on Credit Card Transacton only"><small><i class="fa fa-exclamation-triangle"></i></small></a> </strong></span>
                        </td>
                        <td class="text">
                           <input id="processing_fee" name="processing_fee" type="text" maxlength="10" placeholder="$" class="form-control numberonly amount" readonly>
                        </td>
                     </tr>
                     <tr>
                        <td class="text tx-2">
                           <span for="tax"><strong><small> Total amount<span class="text-danger">*</small></span></strong></span>
                        </td>
                        <td class="text tx-2"><input maxlength="10" name="total_amount" placeholder="$" type="text" class="form-control numberonly" readonly></td>
                     </tr>
                  </tbody>
               </table>
            </div>

            <?php if ($is_tax_exempted) : ?>
               <div class="notice notice-success">
                  <p>This client is tax exempted</p>
               </div>
            <?php endif; ?>

            <!-- CLIENT ACKKNOWLEDGEMENT -->

            <div class="panel-group">
               <div class="panel panel-default">
                  <div class="panel-heading">
                     <a data-toggle="collapse" href="#collapse1"><span><i class="fa fa-expand"></i></span> LIMITS OF LIABILITY</a>
                  </div>
                  <div id="collapse1" class="panel-collapse collapse">
                     <div class="panel-body">
                        <p>Although Gam Exterminating will exercise reasonable care in performing services under this Contract, Gam Exterminating will not be liable for injuries or damage to persons, property, birds, animals, or vegetation, except those damages resulting from gross negligence by Gam Exterminating. Further, under no circumstances will Gam Exterminating be responsible for any injury, disease or illness caused, or allegedly caused, by bites, stings or contamination of bed bugs or any other insects, spiders, dust-mites, mosquitoes, or fleas. Gam Exterminating’s representatives are not medically trained to diagnose bed bug borne illnesses or diseases. Please consult your physician for any medical diagnosis. To the fullest extent permitted by law, Gam Exterminating will not be liable for personal injury, death, property damage, loss of use, loss of income or any other damages whatsoever, including consequential and incidental damages, arising from this service Gam Exterminating’s liability is specifically limited to the labor and products necessary to help reduce pest activity.</p>
                     </div>
                  </div>
               </div>
            </div>

            <div class="panel-group">
               <div class="panel panel-default">
                  <div class="panel-heading">
                     <a data-toggle="collapse" href="#collapse2"><span><i class="fa fa-expand"></i></span> No Refund Policy</a>
                  </div>
                  <div id="collapse2" class="panel-collapse collapse">
                     <div class="panel-body">
                        <p>Upon GAM Exterminating providing a pest control service, all payments are acknownledged by client to be final and non-refundable. GAM Exterminating guarentees a professional pest control service to be rendered, however results can never truly be guarenteed as many factors, chronic issues, and need for repeat service may occur. By signing this invoice and submitting payment you acknownledge you have received a professsional pest control service and that this payment is non-refundable.</p>
                     </div>
                  </div>
               </div>
            </div>

            <!----------check-box---->
            <div class="checkbox">
               <label><input type="checkbox" name="term_check">By signing this invoice client acknowledges they have been offered and received product labels for all products used during this service.</label>
            </div>
            <div class="checkbox">
               <label><input id="client-unable-to-sign" type="checkbox" value="true" name="client-unable-to-sign">Client unable to sign?</label>
            </div>

            <!----------manager name---->
            <div class="checkbox">
               <label><input name="checkbox_manager_name" type="checkbox" value="yes">Manager Name</label>
            </div>

            <div class="form-group hidden">
               <label for="">Enter Manager Name</label>
               <input type="text" class="form-control" name="manager_name">
            </div>
         </div>
      </div>

      <!----------c-name+sign---->
      <div class="form-group last-dsc signature-area">
         <div class="row">
            <div class="col-40 col-md-offset-2 c-name">
               <div id="signArea">
                  <label for="sign">Client Signature</label>
                  <div class="sig sigWrapper" style="height:auto;">
                     <div class="typed"></div>
                     <canvas class="sign-pad" id="sign-pad" width="300" height="100"></canvas>
                     <button type="button" onclick="clearCanvas()" class="btn btn-danger">Clear Signature</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="form-group">
         <div class="row text-center">
            <input type="submit" id="sendform" name="sendform" value="Submit" class="btn btn-lg btn-red">
         </div>
      </div>
   </form>
</div>
<!-- Modal -->
<div id="office_confirm" class="modal fade" role="dialog">
   <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
         <div class="modal-body">
            <p>you must confirm this with the office don't just guess! Have you confirmed verbally?</p>
            <div class="row text-center">
               <div class="col-sm-12">
                  <button data-anwser="no" onclick="check_with_office(this)" class="btn btn-danger">No</button>
                  &nbsp;
                  <button data-anwser="yes" onclick="check_with_office(this)" class="btn btn-Primary">Yes</button>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>