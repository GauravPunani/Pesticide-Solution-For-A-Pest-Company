<?php

//template name: Residnetial Quote sheet
get_header();

$services = (new Quote)->quotesheetServices();
$technician_id = (new Technician_details)->get_technician_id();
$tech_name = (new Technician_details)->getTechnicianName($technician_id);
?>

<section id="content">
    <div class="container">
        <?php (new GamFunctions)->getFlashMessage(); ?>

        <form class="maintenance-forms" id="residential_quote_form">

            <span class="label label-primary"><i class="fa fa-user"></i> <?= $tech_name; ?></span>
            <h2 class="form-head text-center">RESIDENTIAL QUOTE SHEET</h2>

            <?php wp_nonce_field('residential_quotesheet'); ?>

            <div class="row">

                <div class="event_error text-danger text-left hidden"></div>

                <input type="hidden" name="action" value="residential_quotesheet">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                
                <input type="hidden" name="callrail_id" value="unknown">

                <div class="form-group">
                    <label for=" name">Event Date <span class="text-danger"><small>*</small></span></label>
                    <input type="date" max="<?= date('Y-m-d'); ?>" name="event_date" id="event_date" class="form-control">
                </div>                    

                <div class="form-group">
                    <label for=" name">Select Event/Appointment <span class="text-danger"><small>*</small></span></label>
                    <select name="technician_appointment" id="technician_appointment" class="form-control technician_appointment calendar_events">
                            <option value="">Select</option>  
                    </select>
                </div>
            
                <div class="form-group">
                    <label for="techName">Technician Quote Name <span class="text-danger"><small>(Leave blank if you don't want different name)</small></span></label>
                    <input type="text" maxlength="100" class="form-control tech_diff_name"  name="tech_diff_name" id="techDiffName">
                </div>

                <!-- licenses 24thdec2020 -->
                <div class="form-group">
                    <label for="licenses">CLIENT LICENSES<span class="text-danger"><small>*</small></span></label>
                    <select  name="licenses" id="licenses" class="form-control">
                      <optgroup label="Ny">
                        <option value="license # c1881257">license # c1881257</option>
                        <option value="NYS DEC Reg 15693">NYS DEC Reg 15693</option>
                      </optgroup>
                      <optgroup label="California">
                        <option value="operator license # 12907">operator license # 12907</option>
                        <option value="business license # 7373">business license # 7373</option>
                      </optgroup>
                    </select>

                    <!-- <label for="licenses">CLIENT LICENSES<span class="text-danger"><small>*</small></span></label> -->
                    <!-- <input  type="text" class="form-control client_licenses"  name="licenses" id="licenses"> -->
                </div>                    
                <!-- licenses -->

                <div class="form-group">
                    <label for="establishmentName">CLIENT NAME <span class="text-danger"><small>*</small></span></label>
                    <input type="text" maxlength="100" class="form-control client_name"  name="clientName" id="clientName">
                </div>

                <div class="form-group">
                    <label for="inchargeName">CLIENT ADDRESS <span class="text-danger"><small>*</small></span></label>
                    <input type="text" class="form-control client_address" name="clientAddress" id="clientAddress">
                </div>

                <div class="form-group">
                    <label for="Phone Number">CLIENT PHONE NUMBER <span class="text-danger"><small>*</small></span></label>
                <input  type="text" maxlength="12" class="form-control client_phone_no"  name="clientPhn" id="clientPhn">
                </div>

                <div class="form-group">
                    <label for="Email">CLIENT EMAIL <span class="text-danger"> <?= (new GamFunctions)->fakeEmailAlertMessage(); ?> </span></label>
                    <p class="text-danger"></p>
                    <input type="email" class="form-control client_email" name="clientEmail" id="clientEmail">
                    <p><input type="checkbox" id="no_email_to_offer"> Client does not have email to offer?</p>
                </div>

                <div class="additionalService">
                    <div class="service_0">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>SERVICE OFFERED</label>
                                <select name="service[0][service]" class="form-control">
                                    <option value="">Select</option>
                                    <?php if(is_array($services) && count($services)>0): ?>
                                        <?php foreach($services as $service): ?>
                                            <option value="<?= $service->name; ?>"><?= $service->name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>PRICE</label>
                                <input type="text" class="form-control numberonly" placeholder="Price" name="service[0][price]"  id="service_price1">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-primary add_more_services"><span><i class="fa fa-plus"></i></span> Add More</button>
                </div>

                <div class="additionalMaterial">
                    <div class="row item_0">
                        <div class="col-sm-5">
                            <div class="form-group">
                                <label>MATERIALS</label>
                                <input class="form-control" type="text" name="items[0][material]">
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <div class="form-group">
                                <label>AMOUNT</label>
                                <input class="form-control numberonly"  type="number" name="items[0][material_price]">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="button" onclick="addnewmaterial();" class="btn btn-primary" ><span><i class="fa fa-plus"></i></span> Add More</button>
                </div>

                <div class="form-group">
                    <label>Total Cost <span class="text-danger"><small>*</small></span></label>
                    <input class="form-control numberonly" type="text"  name="total_cost" id="total_cost">
                </div>

                <div class="form-group">
                    <span><b>Maintenance Plan Offered?</b></span>
                    <label class="radio-inline">
                        <input type="radio" name="maintenance_plan_offered"  value="no" checked>No                                                
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="maintenance_plan_offered"  value="yes">Yes                                                
                    </label>
                    <label class="radio-inline">
                        <a href="javascript:void(0)" class="modal_view_btn hidden" onclick="showMaintenanceModal()"><span><i class="fa fa-eye"></i> View</span></a>
                    </label>
                </div><br>

                <div class="form-group">
                    <label>Please explain in detail your quote and recommended treatment <small>(will be sent to client as well)</small></label>
                    <textarea class="form-control" rows="5" cols=60 name='notes_for_client'></textarea>                
                </div>

                <div class="form-group">
                    <label for="">Anything you want to tell the office about this quote privately please comment here</label>
                    <textarea name="office_notes" cols="30" rows="5" class="form-control"></textarea>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary submit_btn"><span><i class="fa fa-paper-plane"></i></span> Submit Quote</button>
                </div>
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
                                    <textarea class="form-control" name="discount_with_plan"  cols="30" rows="5"></textarea>
                                </div>
                            </div>
                            <div class="col-sm-12">  
                                <div class="form-group">
                                    <label>MAINTENANCE PRICE <span class="text-danger"><small>*</small></span></label>
                                    <input class="form-control numberonly" placeholder="e.g. $100" type="text" name="maintenance_price" id="maintenance_price">
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
            
        </form>

    </div>   
</section>

<script>
    let service_index = 0;
    let item_id = 0;

    let db_code_id;
    
    const clientAddress = document.getElementById('clientAddress');
    let autocomplete_client_address;

    (function($){
        $(document).ready(function(){

            $.validator.addMethod("alphanumeric", function(value, element) {
                return this.optional(element) || /^[+]*[(]{0,1}[0-9]{1,3}[)]{0,1}[-\s\./0-9]*$/i.test(value);
            }, "Only Numbers and dashes are allowed");            

            var services='<?= json_encode($services); ?>';
            services=$.parseJSON(services);

            initMap('clientAddress', (err, autoComplete) => {
               autoComplete.addListener('place_changed', function() {
                  let place = autoComplete.getPlace();
                  clientAddress.value = place.formatted_address;
                  autocomplete_client_address = clientAddress.value;
               });
            }); 

            const request_code_from_office = () => {
                $.ajax({
                    type:'post',
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    data:{
                        action:"insert_technician_edit_code",
                        type:"quote_office_confirmation",
						"_wpnonce": "<?= wp_create_nonce('insert_technician_edit_code'); ?>"
                    },
                    dataType:"json",
                    beforeSend:function(){
                    },
                    success:function(data){
                        if(data.status=="success"){
                            /*swal.fire({
                                html: `<div>${data.message}</div>`,
                            });*/
                            //let msg = '<div class="alert alert-info"><strong>'+data.message+'</strong></div>';
                            //$(msg).insertBefore("#office_code_modal label");
                            db_code_id=data.db_id;
                            console.log(db_code_id);
                        }
                        else{
                            console.log('not success');
                        }
                    }

                })
            };

            $('.date').datetimepicker({
                format: 'YYYY-MM-DD',
            });

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
            
            $.validator.addMethod("noSpace", function(value, element) { 
                return value.indexOf(" ") < 0 && value != ""; 
            }, "Space not allowed");

            $("#residential_quote_form").validate({
                    rules:{
                        event_date:"required",
                        technician_appointment:"required",
                        licenses:"required", 
                        clientName:"required",
                        clientAddress:"required",
                        clientPhn:{
                            required: true,
                            minlength: 10,
                            maxlength: 12,
                            alphanumeric: true
                        },
                        clientEmail:{
                            required: true,
                            email:true,
                            remote:{
                                url : my_ajax_object.ajax_url,
                                data:{
                                    action : "check_for_banned_email",
                                    email : function(){
                                        return $('#residential_quote_form input[name="clientEmail"]').val()
                                    }
                                },
                                type: "post"
                            }                            
                        },
                        total_cost:"required",
                        maintenance_price:"required",
                        notes_for_client:"required",
                        office_notes:"required",
                        code  : {
                            required : true,
                            minlength: 6,
                            noSpace: true,
                            remote:{
                                url:"<?= admin_url('admin-ajax.php'); ?>",
                                data:{
                                    action:"verify_technician_edit_code",
                                    "_wpnonce": "<?= wp_create_nonce('verify_technician_edit_code'); ?>",
                                    mode:"validation",
                                    db_id:function(){
                                        return db_code_id;
                                    }
                                },
                                type: "post"
                            }
                        },
                    },
                    messages:{
                        code :{
                            remote : "Code did't matched"
                        },
                        clientEmail :{
                            remote : ERROR_MESSAGES.invalid_email
                        }                        
                    },
                    submitHandler: function(form){
                        request_code_from_office();
                        tech_code_insert_popup();
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
                                <input class="form-control" type="text" name="items[${item_id}][material]">
                            </div>
                        </div>

                        <div class="col-sm-5">
                            <div class="form-group">
                                <input class="form-control" type="number" name="items[${item_id}][material_price]">
                            </div>
                        </div>


                        <div class="col-sm-2">
                            <div class="form-group">
                                <button data-item-id="${item_id}" class="btn btn-danger remove_item">-</button
                            </div>
                        </div>
                    </div>
                `;
        $(".additionalMaterial").append(material);
    }

    function cancelMaintenancePlan(){

        // set the value no on radio button
        $('input[name="maintenance_plan_offered"]').removeAttr('checked');
        $("input[name=maintenance_plan_offered][value='no']").prop('checked', true);

        // set the values blank 

        $('textarea[name="discount_with_plan"]').val('');
        $('input[name="maintenance_price"]').val('');

        // hide the modal 
        $('#maintenance_modal').modal('hide');

        // hide the modal view btn 
        $('.modal_view_btn').addClass('hidden');

    }

    function confirmMaintenancePlan(){

        let maintenance_discount=$('textarea[name="discount_with_plan"]').val();
        let maintenance_price=$('input[name="maintenance_price"]').val();

        if(maintenance_discount!="" && maintenance_price!=""){
            // hide the modal 
            $('#maintenance_modal').modal('hide');
            $('.modal_view_btn').removeClass('hidden');
        }
        else{
            // alert for filling all fields 
            alert('please fill all fields first in order to continue');
        }
    }

    function showMaintenanceModal(){
        // show the modal 
        $('#maintenance_modal').modal('show');
    }
    
    function submitTechQuoteForm(){
        let form = $("#residential_quote_form");
        if($(form).valid()){
            $.ajax({
                type: "post",
                url: "<?= admin_url('admin-ajax.php'); ?>",
                dataType: "json",
                data: $(form).serialize(),
                beforeSend: function(){
                    showLoader('Please wait while system saves the quote..')
                },
                success: function(data){
                    if(data.status == "success"){
                        new Swal({
                            title: "Success!", 
                            text: "Residential quote saved successfully", 
                            type: "success"                                        
                        }).then(function(){
                            $('#residential_quote_form')[0].reset();
                            location.reload();
                        })
                    }
                    else{
                        new Swal('Oops!', data.message, 'error');
                    }
                },
                error: function(){
                    new swal('Oops!', 'Something went wrong, please try again later', 'error');
                }
            })
        }else{
            console.log("need to validate");
        }
    }

    function tech_code_insert_popup(){

            Swal.fire({
                title: 'Please enter code',
                html: `<div class="alert alert-info"><strong>Call this office number : <a href=\"tel:8777322057\"> 8777322057<\/a> to get the code. This is for office to have interaction with client in order to sell maintenance plan.</strong></div>`,
                input: 'number',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: false,
                confirmButtonText: 'Verify & Submit',
                showLoaderOnConfirm: true,
                preConfirm: (code) => {
                    return jQuery.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        data:{
                            code: code,
                            action:"verify_technician_edit_code",
                            "_wpnonce": "<?= wp_create_nonce('verify_technician_edit_code'); ?>",
                            mode:"validation",
                            db_id: db_code_id
                        },
                        success: function(data){
                            console.log(data);
                            if(data == "true"){
                                return true;
                            }else{
                                Swal.showValidationMessage(`Code didn't match, please try again`)
                            }
                        },
                        error: function(){
                            Swal.showValidationMessage(`Something went wrong, please try again later`)
                        }
                    })
                },
                allowOutsideClick: false,
                allowEscapeKey: false,               
            })
            .then((result) => {
                if (result.isConfirmed) {
                    submitTechQuoteForm();
                }
            })
    }
</script>
<?php
get_footer();