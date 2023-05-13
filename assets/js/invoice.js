let sign_area = true;
let signaturePad;
let sales_tax_rate;

(function ($) {
    $(document).ready(function () {

        console.log('calling calculate_invoice_total on page load');
        calculate_invoice_total();        

        updateSalesTaxRate($('#sales_tax_rate').val());

        $('.salestax__editConfirmContainer').on('click', function(){
            $('.salestax__editContainer').toggleClass('hidden');
            $('.salestax__editConfirmContainer').toggleClass('hidden');
            $('#sales_tax_rate').attr('readonly', true);
        });

        $('.salestax__editContainer').on('click',function(){
            $('.salestax__editContainer').toggleClass('hidden');
            $('.salestax__editConfirmContainer').toggleClass('hidden');
            $('#sales_tax_rate').attr('readonly', false).focus();
        });

        jQuery('input[name="checkbox_other_service_description"]').on('click', function(){
            if(jQuery(this).is(':checked')){
                $('input[name="other_service_description"]').parent().removeClass('hidden');
            }
            else{
                $('input[name="other_service_description"]').parent().addClass('hidden');
            }
        });

        jQuery('input[name="checkbox_manager_name"]').on('click', function(){
            if(jQuery(this).is(':checked')){
                $('input[name="manager_name"]').parent().removeClass('hidden');
            }
            else{
                $('input[name="manager_name"]').parent().addClass('hidden');
            }
        });

        jQuery('input[name="checkbox_other_area_of_service"]').on('click', function(){
            if(jQuery(this).is(':checked')){
                $('input[name="other_area_of_service"]').parent().removeClass('hidden');
            }
            else{
                $('input[name="other_area_of_service"]').parent().addClass('hidden');
            }
        });

        jQuery('input[name="checkbox_other_findings"]').on('click', function(){
            if(jQuery(this).is(':checked')){
                $('input[name="findings_other"]').parent().removeClass('hidden');
            }
            else{
                $('input[name="findings_other"]').parent().addClass('hidden');
            }
        });

        $('input[name="client_require_reservice"]').on('change', function(){
            let status = $(this).val();
            if(status == "yes"){
                $('.revisit_questions').removeClass('hidden');
            }
            else{
                $('.revisit_questions').addClass('hidden');                
            }
        });

        $('input[name="warranty_recommendation"]').on('change', function(){
            let status = $(this).val();
            if(status == "yes"){
                $('.warranty_explanation').removeClass('hidden');
            }
            else{
                $('.warranty_explanation').addClass('hidden');                
            }
        });
        
        $("#invoice_form").validate({
            rules: {
                clientName: "required",
                "total_amount": {
                    required: true,
                    number: true
                },
                clientPhn: "required",
                clientAddress: "required",
                clientEmail: {
                    email: true,
                    required: true,
                    remote:{
                        url : my_ajax_object.ajax_url,
                        data:{
                            action : "check_for_banned_email",
                            email : function(){
                                return $('#invoice_form input[name="clientEmail"]').val()
                            }
                        },
                        type: "post"
                    }                    
                },
                payment_process: "required",
                check_image: "required",
                lead_source: "required",
                lead_source_other: "required",
                technician_appointment: "required",
                client_response: "required",
                term_check: "required",
                state_county: "required",
                interested_for_quote: "required",
                quote_amount: "required",
                serviceFee: "required",
                sales_tax_rate: "required",
                sales_tax_amount: "required",
                "findings[]":{
                    required: function(){
                        if($('input[name="checkbox_other_findings"]').is(':checked')){
                            return false;
                        }
                        else{
                            return true;
                        }                        
                    }
                },
                findings_other: "required",
                type_of_service: "required",
                "service_description[]": {
                    required: function(){
                        if($('input[name="checkbox_other_service_description"]').is(':checked')){
                            return false;
                        }
                        else{
                            return true;
                        }                        
                    }
                },
                "multiple_inv_emails[0]": {
                    email: true,
                    required: true     
                },
                "multiple_inv_phone[0]": {
                    required: true     
                },
                other_service_description: "required",
                "area_of_service[]": {
                    required: function(){
                        if($('input[name="checkbox_other_area_of_service"]').is(':checked')){
                            return false;
                        }
                        else{
                            return true;
                        }                        
                    }
                },
                other_area_of_service: "required",
                manager_name: "required",
                client_require_reservice: "required",
                total_reservices: "required",
                revisit_frequency_unit: "required",
                revisit_frequency_timeperiod: "required",
                follow_up_fee: "required",
                warranty_explanation: "required",
                additional_notes: "required",
            },
            messages:{
                clientEmail :{
                    remote : ERROR_MESSAGES.invalid_email
                }
            },
            errorPlacement: function (error, element) {
                if (element[0].type == "checkbox") {
                    error.insertBefore(element);
                } else {
                    error.insertAfter(element)
                }
            },
            errorElement: 'div',
            submitHandler: function (form) {

                // if client able to sign ,then only ask for sign him 
                if (sign_area) {
                    if (signaturePad.isEmpty()) {
                        alert('please fill the signature pad first');
                        return false;
                    } else {
                        //disable the submit button
                        $('#sendform').prop('disabled', true).val('processing...');

                        let data = signaturePad.toDataURL('image/png');
                        let img_data = data.replace(/^data:image\/(png|jpg);base64,/, "");

                        $('input[name="signimgurl"]').val(img_data);
                        return true;
                    }

                } else {
                    return true;
                }
            }

        });

        if ($('#sign-pad').length) {
            signaturePad = new SignaturePad(document.getElementById('sign-pad'), {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });
        }

        $('#no_email_to_offer').on('click', function () {
            if ($(this).is(':checked')){
                $('input[name="clientEmail"]').val('').attr('disabled', true);
                $('#multiple_email_to_offer').attr('disabled', true);
            }else{
                $('input[name="clientEmail"]').attr('disabled', false);
                $('#multiple_email_to_offer').attr('disabled', false);
            }
        })

        $('#no_phone_to_offer').on('click', function () {
            if ($(this).is(':checked')){
                $('input[name="clientPhn"]').val('').attr('disabled', true);
                $('#multiple_phone_to_offer').attr('disabled', true);
            }else{
                $('input[name="clientPhn"]').attr('disabled', false);
                $('#multiple_phone_to_offer').attr('disabled', false);
            }
        })

        $('#multiple_email_to_offer').on('click', function () {
            if ($(this).is(':checked'))
                $('.multiple_emails_inv_group').removeClass('hidden');
            else
                $('.multiple_emails_inv_group').addClass('hidden');
                $('.multiple_inv_emails').val('');
        })

        $('#multiple_phone_to_offer').on('click', function () {
            if ($(this).is(':checked'))
                $('.multiple_phone_inv_group').removeClass('hidden');
            else
                $('.multiple_phone_inv_group').addClass('hidden');
                $('.multiple_inv_phone').val('');
        })

        // In invoice option to add multiple inputs for email
        var maxField = 10; //Input fields increment limitation
        var addButton_email = $('.add_more_email_icon'); //Add button selector
        var addButton_phone = $('.add_more_phone_icon'); //Add button selector
        var wrapper_email = $('.multiple_emails_inv_group'); //Input field wrapper
        var wrapper_phone = $('.multiple_phone_inv_group'); //Input field wrapper
        var x = 0; //Initial field counter is 1
        
        add_repeater_inv_row(addButton_email,maxField,wrapper_email,'email');
        remove_repeater_inv_row(wrapper_email);

        add_repeater_inv_row(addButton_phone,maxField,wrapper_phone);
        remove_repeater_inv_row(wrapper_phone);


        //Once add button is clicked
        function add_repeater_inv_row(addButton,maxField,wrapper,field=''){
            //Once add button is clicked
            $(addButton).click(function(){
                //Check maximum number of input fields
                if(x < maxField){ 
                    x++; //Increment field counter
                    if(field == 'email'){
                         $(wrapper).append('<div class="row" style="margin-top:10px;"><div class="col-sm-11"><input placeholder="Enter Email Address" type="text" name="multiple_inv_emails['+x+']" required id="multiple_inv_emails_'+x+'" class="form-control multiple_inv_emails"></div><div class="col-sm-1"><i class="fa fa-minus-circle remove_more_email_icon plus_minus_icon" aria-hidden="true"></i></div></div>');
                    }else{
                        $(wrapper).append('<div class="row" style="margin-top:10px;"><div class="col-sm-11"><input placeholder="Enter Phone No" type="text" name="multiple_inv_phone['+x+']" required id="multiple_inv_phone_'+x+'" class="form-control multiple_inv_phone"></div><div class="col-sm-1"><i class="fa fa-minus-circle remove_more_email_icon plus_minus_icon" aria-hidden="true"></i></div></div>');
                    }
                }
            });
        }
        
        //Once remove button is clicked
        function remove_repeater_inv_row(wrapper){
            $(wrapper).on('click', '.remove_more_email_icon', function(e){
                e.preventDefault();
                $(this).parent().parent('.row').remove(); //Remove field html
                x--; //Decrement field counter
            });
        }
        //end

        $('ul.extra-services li').on('click', function () {

            let item_slug = $(this).attr('data-slug');
            let item_name = $(this).text();
            let item_index = $(this).attr('data-index');


            let item_html = `
                <tr>
                    <th class="table-hd">${item_name} <input type="hidden" name="product[${item_index}][name]" value="${item_name}" /></th>
                    <td><input type="text" class="form-control numberonly"  name="product[${item_index}][Unit]" id="${item_slug}Unit" /></td>
                    <td><input type="text" class="form-control numberonly"  name="product[${item_index}][Price]" id="${item_slug}Price" /></td>
                    <td><input type="text" class="form-control numberonly extra_total amount"  name="product[${item_index}][Total]" id="${item_slug}Total" /></td>
                    <td><a href="javascript:void(0)" class="text-danger" onclick="removeItem(this,'${item_slug}')"><span><i class="fa fa-remove"></i></span></a></td>
                </tr>
            `;

            $('.price-chart-services').append(item_html);
            $(this).addClass('hidden');

        });

        $(document).on('change', '.amount, #sales_tax_rate', () => calculate_invoice_total());

        $('#sales_tax_rate').on('change', function () {
            updateSalesTaxRate($(this).val())
        });

        $('#payment_process').on('change', function () {

            let payment_type = $(this).find(":selected").val();

            if (payment_type == "office_to_bill_client" || payment_type == "client_on_autopay") {
                $('#office_confirm').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: true
                });
            }

            // if payment method is check, show option to upload check picture
            if (payment_type == "check") {
                $('.check_picture').removeClass('hidden');
            } else {
                $('.check_picture').addClass('hidden');
            }

            // recalculate the total
            calculate_invoice_total();
        });

        $('#client-unable-to-sign').on('change', function () {
            if ($(this).is(':checked')) {
                $('.signature-area').hide();
                sign_area = false;
            } else {
                $('.signature-area').show();
                sign_area = true;
            }
        });

        $('input[name="anmial_trapping_cage"]').on('change', function () {
            if ($(this).val() == "yes")
                $('.cages_data').removeClass('hidden');
            else
                $('.cages_data').addClass('hidden');
        });

        $("#client_phone_no").on('change',function(){
            if($('#client_phone_no').val().length >= 10){
                fetchCallrailType($(this).val());
            }
        });

    });
})(jQuery);

function updateSalesTaxRate(tax_rate) {

    if(tax_rate === "" || tax_rate === null || tax_rate === undefined) return;

    // remove the % sign if any in front of tax rate 
    sales_tax_rate = tax_rate.replace('%', '');

    // set the sales_tax_rate variable
    sales_tax_rate = parseFloat(sales_tax_rate);

    // update in dom 
    jQuery('#sales_tax_rate').val(`${sales_tax_rate}`);

    // call calculate invoice total, it will run only if tax rate and other things are set
    calculate_invoice_total();
}

function calculate_invoice_total() {

    // get service fee
    let service_fee = getServiceFee();
    let extra_total = getExtraTotal();

    if (service_fee === "" || service_fee === null || service_fee === undefined) return;
    if (sales_tax_rate === "" || sales_tax_rate === null || sales_tax_rate === undefined) return;

    let subtotal = parseFloat(service_fee) + parseFloat(extra_total);
    let total_sales_tax = 0;
    let processing_fee = 0;
    let payment_type = jQuery('#payment_process').val();

    jQuery('#subtotal').val(parseFloat(subtotal).toFixed(2));

    total_sales_tax = parseFloat((parseFloat(subtotal) * parseFloat(sales_tax_rate)) / 100);

    // if payment method is credit card add 3% processing fees also
    if (jQuery('#payment_process').val() == "credit_card") {
        processing_fee = parseFloat((parseFloat(service_fee) * 3) / 100);
        jQuery('.processing_fee').show();
    } else {
        jQuery('.processing_fee').hide();
    }

    jQuery('#processing_fee').val(parseFloat(processing_fee).toFixed(2));

    jQuery('input[name="sales_tax_amount"]').val(total_sales_tax.toFixed(2));
    let total_amount = parseFloat(subtotal) + parseFloat(total_sales_tax) + parseFloat(processing_fee);

    jQuery('input[name="total_amount"]').val(total_amount.toFixed(2));
}

function getServiceFee(){
    let service_fee = jQuery('input[name="serviceFee"]').val();

    if(service_fee === "" || service_fee === null || service_fee == undefined) return null;

    return parseFloat(service_fee);
}

function getExtraTotal(){
    let extra_total = 0;

    jQuery('.extra_total').each(function (key, value) {
        if (jQuery(value).val() != "") {
            extra_total = parseFloat(extra_total) + parseFloat(jQuery(value).val());
        }
    });

    if(extra_total === "" || extra_total === null || extra_total === undefined) return null;

    return parseFloat(extra_total);
}

function removeItem(ref, item_slug) {
    jQuery(ref).parent().parent().remove();

    jQuery('ul.extra-services li').each(function (index, value) {
        if (jQuery(this).attr('data-slug') == item_slug) {
            jQuery(this).removeClass('hidden');
        }
    });

    // recalculate the invoice total as well 
    calculate_invoice_total();

}

function clearCanvas() {
    signaturePad.clear();
}

function check_with_office(obj) {
    let ans = jQuery(obj).attr('data-anwser');

    if (ans == "yes") {
        jQuery('#office_confirm').modal('toggle');
        jQuery('.modal-backdrop').remove();
    } else {
        jQuery('#payment_process').prop('selectedIndex', 0);
        jQuery('#office_confirm').modal('toggle');
        jQuery('.modal-backdrop').remove();
    }
}

function fetchCallrailType(phone_no){

    // call ajax to get recurring and lead source status from session 
    $.ajax({
        type:'post',
        url:my_ajax_object.ajax_url,
        data: {
            action: "invoice_flow_get_callrail_type",
            phone_no
        },
        dataType: "json",
        beforeSend: function(){

        },
        success: function(data){

            if(data.status === "success"){
                const lead_data = data.data;
                const recurring = lead_data.recurring_status;
                const lead_source = lead_data.lead_source;

                if(recurring == "true"){
                    $('input[name="callrail_id"]').val("reoccuring_customer");
                }
                else if(lead_source == "cold_call"){
                    $('input[name="callrail_id"]').val(lead_source);
                }
                else{
                    getCallrailIdFromCallrail(phone_no);
                }


            }
            else{
                getCallrailIdFromCallrail(phone_no);  
            }
        }
    });
}