<?php
global $wpdb;
$quote_id = $args['data'];
$quote_data = $wpdb->get_row("
    select * 
    from {$wpdb->prefix}quotesheet 
    where id='$quote_id'
");
$services = $wpdb->get_results("
    select * 
    from {$wpdb->prefix}quotesheet_services 
    order by name
");

$branch_slug = (new Technician_details)->getTechnicianBranchSlug($quote_data->branch_id);

$callrail_numbers = (new Callrail_new)->get_all_tracking_no($branch_slug);

$db_services=[];
if(!empty($quote_data->service)){
    $db_services = json_decode($quote_data->service,true);
}

$db_materials=[];
if(!empty($quote_data->items)){
    $db_materials = json_decode($quote_data->items,true);  
}
// give new services index a start index more than from db services last index
$service_index = count($db_services);
$material_index = count($db_materials);

?>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Edit Residential Quote Sheet</h3>
                    <form method="post" class="maintenance-forms" id="residential_quote_form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" >
                            <?php (new GamFunctions)->getFlashMessage(); ?>
                            <div class="event_error text-danger text-left hidden"></div>
							<?php wp_nonce_field('residential_quote_update'); ?>
                            <input type="hidden" name="action" value="residential_quote_update">
                            <input type="hidden" name="quote_id" value="<?= $_GET['edit-id']; ?>">
                            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                            
                            <!-- CLIENT NAME  -->
                            <div class="form-group">
                                <label for="establishmentName">CLIENT NAME <span class="text-danger"><small>*</small></span></label>
                                <input type="text" maxlength="100" value="<?= $quote_data->clientName; ?>" class="form-control client_name"  name="clientName">
                            </div>

                            <div class="form-group">
                                <label for="establishmentName">Technician Quote Name<span class="text-danger"><small>*</small></span></label>
                                <input type="text" maxlength="100" value="<?= $quote_data->tech_diff_name; ?>" class="form-control tech_diff_name"  name="tech_diff_name">
                            </div>

                            <!-- CLIENT ADDRESS -->
                            <div class="form-group">
                                <label for="inchargeName">CLIENT ADDRESS <span class="text-danger"><small>*</small></span></label>
                                <textarea name="clientAddress" cols="5" id="clientAddress" class="form-control client_address"><?= $quote_data->clientAddress; ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="clientAddress">CLIENT PHONE NUMBER <span class="text-danger"><small>*</small></span></label>
                            <input  type="text" maxlength="12" class="form-control client_phone_no"  name="clientPhn" id="clientPhn" value="<?= $quote_data->clientPhn; ?>">
                            </div>

                            <div class="form-group">
                                <label for="clientAddress">CLIENT EMAIL <span class="text-danger"><small>*</small></span></label>
                                <input type="email" class="form-control client_email" name="clientEmail" value="<?= $quote_data->clientEmail; ?>">
                            </div>

                            <div class="additionalService">
                                <p class="page-header"><b>Services</b></p>
                                <?php if(is_array($db_services) && count($db_services)>0): ?>
                                    <?php foreach($db_services as $key=>$service): ?>
                                        <div class="service_<?= $key; ?>">
                                            <input type="hidden" value="<?= $service['service']; ?>" name="service[<?= $key; ?>][service]">
                                            <input type="hidden" value="<?= $service['price']; ?>" name="service[<?= $key; ?>][price]">
                                            <p><?= $service['service']." - $".$service['price']; ?> <span data-service-id="<?= $key; ?>" class="text-danger remove_service"><i class="fa fa-times"></i></span></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-primary add_more_services"><span><i class="fa fa-plus"></i></span> Add Services</button>
                            </div>

                            <div class="additionalMaterial">
                                <p class="page-header"><b>Material</b></p>
                                <?php if(is_array($db_materials) && count($db_materials)>0): ?>
                                    <?php foreach($db_materials as $key=>$material): ?>
                                        <div class="item_<?= $key; ?>">
                                            <input type="hidden" value="<?= $material['material']; ?>" name="items[<?= $key; ?>][material]">
                                            <input type="hidden" value="<?= $material['material_price']; ?>" name="items[<?= $key; ?>][material_price]">
                                            <p><?= $material['material']." - $".$material['material_price']; ?> <span data-item-id="<?= $key; ?>" class="text-danger remove_item"><i class="fa fa-times"></i></span></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <button type="button" onclick="addnewmaterial();" class="btn btn-primary" ><span><i class="fa fa-plus"></i></span> Add Material</button>
                            </div>

                            <div class="form-group">
                                <label>Total Cost <span class="text-danger"><small>*</small></span></label>
                                <input class="form-control numberonly" type="text"  name="total_cost" value="<?= $quote_data->total_cost; ?>">
                            </div>

                            <div class="form-group">
                                <span><b>Maintenance Plan Offered?</b></span>
                                <label class="radio-inline">
                                    <input type="radio" name="maintenance_plan_offered"  value="no" <?= empty($quote_data->maintenance_price) ?'checked' : ''; ?>>No
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="maintenance_plan_offered"  value="yes" <?= !empty($quote_data->maintenance_price) ?'checked' : ''; ?>>Yes
                                </label>
                                <label class="radio-inline">
                                    <a href="javascript:void(0)" class="modal_view_btn <?= empty($quote_data->maintenance_price) ?'hidden' : ''; ?>" onclick="showMaintenanceModal()"><span><i class="fa fa-eye"></i> View</span></a>
                                </label>
                            </div><br>

                            <div class="form-group">
                                <label>Please explain in detail your quote and recommended treatment</label>
                                <textarea class="form-control" rows="5" cols=60 name='notes_for_client'><?= $quote_data->notes_for_client ?></textarea>                
                            </div>
                            
                            <div class="form-group">
                                <label for="">Callrail Number</label>
                                <select name="callrail_id" class="form-control select2-field">
                                    <option value="">Select</option>
                                    <?php if(is_array($callrail_numbers) && count($callrail_numbers)>0): ?>
                                        <?php foreach($callrail_numbers as $number): ?>
                                            <option value="<?= $number->id; ?>" <?= $number->id==$quote_data->callrail_id ? 'selected' : ''; ?>><?= $number->tracking_name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Maintenace Plan Modal  -->
                            <div id="maintenance_modal" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Maintenance Plan Details</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>DISCOUNT WITH MAINTENANCE PLAN </label><br>
                                                    <small><i>please explain amount($) or percentage(%) discounted off initial price if maintenance plans taken</i></small>
                                                    <textarea class="form-control" name="discount_with_plan"  cols="30" rows="5"><?= $quote_data->discount_with_plan; ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">  
                                                <div class="form-group">
                                                    <label>MAINTENANCE PRICE <span class="text-danger"><small>*</small></span></label>
                                                    <input class="form-control numberonly" value="<?= $quote_data->maintenance_price; ?>" placeholder="e.g. $100" type="text" name="maintenance_price" id="maintenance_price">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-danger" type="button" onclick="cancelMaintenancePlan()"><span><i class="fa fa-times"></i></span> Cancel</button>
                                            <button type="button" onclick="confirmMaintenancePlan()" class="btn btn-primary"><span><i class="fa fa-check"></i></span> Confirm</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Quote</button>
                            </div> 

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>

    var service_index = <?= $service_index; ?>;
    var item_id = <?= $material_index; ?>;

    var db_code_id=0;

    (function($){
        $(document).ready(function(){
            var services='<?= json_encode($services); ?>';
            services=$.parseJSON(services);
            console.log(services);

            $('body').on('click','.add_more_services',function(e){
                service_index=++service_index;
                let services_html=`<div class="row service_${service_index}">
                        <div class="col-sm-5">
                            <div class="form-group">                                    
                                <select name="service[${service_index}][service]" class="form-control">
                                    <option value="">Select</option>`;
                    
                    $.each(services,function(index,data){
                        services_html+=`<option value="${data.name}">${data.name}</option>`;
                    });

                    services_html+=`</select>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Price" name="service[${service_index}][price]" required id="service_price1">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <div class="service_button">
                                    <button data-service-id='${service_index}' type="button" class="btn btn-danger remove_service">-</button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                
                $(".additionalService").append(services_html);
                
                
            });

            $('body').on('click','.remove_service',function(e){
                let service_id=$(this).attr('data-service-id');
                $(`.service_${service_id}`).remove();
            });

            $('body').on('click','.remove_item',function(e){
                let item_id=$(this).attr('data-item-id');
                $(`.item_${item_id}`).remove();
            });

            $('input[name="maintenance_plan_offered"]').on('change',function(){
                if($(this).val()=="yes"){
                    $('#maintenance_modal').modal('show');
                }else{
                    $('#maintenance_modal').modal('hide');
                }
            });
            
            $('#maintenance_modal').modal({backdrop:'static',keyboard:false,show:false});

            // $('#clientPhn').keyup(function() {
            //     jQuery.validator.addMethod("alphanumeric", function(value, element) {
            //         return this.optional(element) || /^[+]*[(]{0,1}[0-9]{1,3}[)]{0,1}[-\s\./0-9]*$/i.test(value);
            //     }, "Only Numbers and dashes are allowed");
            // });

            jQuery.validator.addMethod("noSpace", function(value, element) { 
                return value.indexOf(" ") < 0 && value != ""; 
            }, "Space not allowed");
            

            $("#residential_quote_form").validate({
                    rules:{
                        clientName:"required",
                        clientAddress:"required",
                        clientPhn:{
                            required: true,
                            minlength: 10,
                            maxlength: 12,
                        },
                        clientEmail:{
                            email:true
                        },
                        total_cost:"required",
                        maintenance_price:"required",
                        start_date:"required",
                        end_date:"required",
                    },
                    messages:{
                        code :{
                            remote : "Code did't matched"
                        }
                    }
                    
            });

        });
    })(jQuery);

    function addnewmaterial(){
        item_id=++item_id;
        let material=`      
                    <div class="item_${item_id}"> 
                        <div class="col-sm-5">
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Material" name="items[${item_id}][material]" id="material1">
                            </div>
                        </div>

                        <div class="col-sm-5">
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Material Price" name="items[${item_id}][material_price]" id="material_price1">
                            </div>
                        </div>


                        <div class="col-sm-2">
                            <div class="form-group">
                                <button data-item-id="${item_id}" class="btn btn-danger remove_item">-</button
                            </div>
                        </div>
                    </div>
                `;
        jQuery(".additionalMaterial").append(material);
    }

    function cancelMaintenancePlan(){

        // set the value no on radio button
        jQuery('input[name="maintenance_plan_offered"]').removeAttr('checked');
        jQuery("input[name=maintenance_plan_offered][value='no']").prop('checked', true);

        // set the values blank 

        jQuery('textarea[name="discount_with_plan"]').val('');
        jQuery('input[name="maintenance_price"]').val('');

        // hide the modal 
        jQuery('#maintenance_modal').modal('hide');

        // hide the modal view btn 
        jQuery('.modal_view_btn').addClass('hidden');

    }

    function confirmMaintenancePlan(){

        let maintenance_discount = jQuery('textarea[name="discount_with_plan"]').val();
        let maintenance_price = jQuery('input[name="maintenance_price"]').val();

        if(maintenance_discount!="" && maintenance_price!=""){
            // hide the modal 
            jQuery('#maintenance_modal').modal('hide');
            jQuery('.modal_view_btn').removeClass('hidden');
        }
        else{
            // alert for filling all fields 
            alert('please fill all fields first in order to continue');
        }
    }

    function showMaintenanceModal(){
        // show the modal 
        jQuery('#maintenance_modal').modal('show');
        
    }

</script>