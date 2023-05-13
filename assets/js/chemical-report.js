jQuery.noConflict();

var productIndex=0;
var ajax_status=false;


function remove_product(obj,id){
    $('.product_'+id).remove();
}

function skip_chemical_report(){

    //add popup for code verification with back office
    jQuery('#codemodal').modal({
        backdrop: 'static', 
        keyboard: false,
        show:true
    });
    return;
}

function opne_technician_skip_model(){
    $('#technician_bypass').modal('show');
}


(function($){
    $(document).ready(function(){

        $(document).on('change','#calendar_events_florida',function(){
            let branch=$('option:selected',this).attr('data-client-location');
            $('input[name="description_of_treatment"]').val(branch);
        });

        $(document).on('change','#calendar_events_newyork',function(){

            let address=$('option:selected',this).attr('data-client-location');
            let zipcode=$('option:selected',this).attr('data-zip-code');
        
            $('input[name="application_address"]').val(address);
            $('input[name="application_zip"]').val(zipcode);
        
            $('.location-box').html('<b>Location :- </b> '+address);
        
        });
        
        $(document).on('change','#calendar_events_california',function(){

            let client_name=$('option:selected',this).attr('data-client-name');
            let address=$('option:selected',this).attr('data-client-location');
        
            $('input[name="client_name"]').val(client_name);
            $('input[name="client_address"]').val(address);
        
        });
        
        $(document).on('change','#calendar_events_newjersey',function(){

            let address=$('option:selected',this).attr('data-client-location');
            $('textarea[name="address"]').val(address);
        
        });        

        $('#reset_invoice_checkout').click(function(){
            $('<input>').attr({
                type: 'hidden',
                name: 'action',
                value: 'reset_invoice_form',
            }).prependTo('form');

            $('input[type="submit"]').attr('formnovalidate','formnovalidate');
            
            $('form')[0].submit();        
            return true;
        });

        $(document).on('change','.product_quantity',function(){

            if($(this).val()=="other")
                    $(this).parent().next().removeClass('hidden');
            else
                    $(this).parent().next().addClass('hidden'); 
                    
        });

        $("form").on("click", ".add-product", function (e) {

            let branch = $(this).attr('data-location');
            let product_html = '';
            productIndex++;

            switch (branch) {
                case 'newyork':
                    branch="ny_metro";
                break;
                
                case 'california':
                    branch="california";
                break;

                case 'newjersey':
                    branch="newjersey";
                break;

                case 'florida':
                    branch="florida";
                break;

                case 'texas':
                    branch="texas";
                break;
            
                default:
                    break;
            }

            if(branch != ""){
                $.ajax({
                    type:"post",
                    url:my_ajax_object.ajax_url,
                    data:{
                        action:"get_chemical_reports_addon",
                        branch:branch,
                        index:productIndex
                    },
                    dataType:"html",
                    success:function(data){
                        $('.product-related-details').append(data);
                    }
                });
                $('.product-related-details').append(product_html);
            }
        });

        $('#technician_state').on('change',function(){
            productIndex = 0;
            let branch = $(this).val();
            let chemical_location = ''

            switch (branch) {
                case 'New York':
                    $('input[type="submit"]').prop("disabled", true);
                    $('#chemical_report_form').val('chemical_report_newyork');
                    chemical_location='ny_metro';    
                break;

                case 'New Jersey':
                    $('input[type="submit"]').prop("disabled", true);
                    $('#chemical_report_form').val('newjersey_chemical_report');
                    chemical_location="new_jersey";
                break;
                default:
                    chemical_location='';
                break;
            }

            if(chemical_location!=''){
                // get data from file and render 
                $.ajax({
                    type:"post",
                    url:my_ajax_object.ajax_url,
                    data:{
                        action:"get_chemical_reports_data",
                        branch:chemical_location,
                        index:productIndex
                    },
                    dataType:"html",
                    beforeSend:function(){
                        $('.location-based-data').html('<div class="loader"></div>');
                    },
                    success:function(data){
                        $('.location-based-data').html(data);
                        $('input[type="submit"]').prop("disabled", false);
                    }

                });

            }
            else{
                $('#chemical_report_form').val('technician_location_form');
                $('.location-based-data').html("");
                $('input[type="submit"]').prop("disabled", true);   
            }

        });

        $(document).on('change','input[name="certification_id"]',function(){
                if($(this).val()=="other"){
                    $('.other-license-data').show();
                }
                else{
                    $('.other-license-data').hide();                            
                }

        });

        $('.bypass_appointement').on('change',function(){

            let bypass = $('option:selected', this).attr('data-chemical-bypass');
            let event_id=$(this).val();

            if(bypass=="true"){

                // check in database if this id was used before to bypass event 
                $('.bypass-response').html(`<div class="loader"></div>`);
                $.ajax({
                    type:"post",
                    url:my_ajax_object.ajax_url,
                    data:{
                        action:"check_bypassed_events",
                        event_id:event_id,
                        type:"chemical_report"
                    },
                    dataType:"json",
                    success:function(data){
                        if(data.status=="no_event_found"){
                            $('.bypass-response').html(`<p class='text-green'>You can bypass this event by <button class="btn btn-primary">clicking here</button></p>`);
                        }
                        else if(data.status=="success") {
                            $('.bypass-response').html(`<p class='text-danger'>Sorry, you can't bypass this event because it was used before to bypass a chemical report</p>`);

                        }
                    }

                })
    
            }
            else{
                $('.bypass-response').html(`<p class='text-danger'>Sorry, You can't bypass this event</p>`);
            }
        });
        
        $("#technician_location_form").validate({
            rules: {
                // simple rule, converted to {required:true}
                technician_state: "required",
                reporting_year: "required",
                certification_id: "required",
                business_reg: "required",
                certification_first_name: "required",
                certification_last_name: "required",
                certification_license_no:{
                    required:true,
                    maxlength:10,
                    minlength:8,

                },
                "product_quantity[0]": "required",
                "product_other_quantity[0]": "required",
                "product[0]": "required",
                "unit_of_measure[0]": "required",
                "date[0]": "required",
                "country_code[0]": "required",
                "application_address[0]": "required",
                "application_city[0]": "required",
                "application_zip[0]": "required",
                "dosage_rate[0]": "required",
                "method_of_application[0]": "required",
                "target_oranisms[0]": "required",
                "application_place[0]": "required",                    
                "client_name": "required",                    
                client_address: "required",
                address: "required",
                time: "required",
                applicator_name: "required",
                county_code: "required",
            }
        });

        $("#code_basic_details").validate({
            rules:{
                code_name:"required",
            },
            submitHandler: function(form) {
                if(ajax_status) return false;

                var name=$('#code_name').val();

                //update field for verification form
                $('#codeverify_name').val(name);

                //send data to server and save in database
                ajax_status=true;
                $('#sendcode').text('Processing...').prop('disabled',true);
                $.ajax({
                    type:"post",
                    url:my_ajax_object.ajax_url,
                    dataType:"json",
                    data:{
                        action:"insert_technician_code",
                        name: name,
                    },
                    success:function(data){
                    if(data.status=="success"){
                        $('.code-basic-details').remove();
                        $('.code-verification').removeClass('hidden').fadeIn();
                    }else{
                        $('#error-info').html("<p class='error'>Something Went Wrong. Please try again later</p>");
                    }
                        
                    }
                });

            }
        });

        $('#codeverification').validate({
            rules:{
                code:{
                    required:true,
                    minlength:6,
                    maxlength:6,
                }
            },
            submitHandler: function(form) {

                $('#verify_code').text('Verifying...').prop('disabled',true);
                $('.error-info').html('');
                //send code to server and verify the code
                $.ajax({
                    type:"post",
                    url:my_ajax_object.ajax_url,
                    dataType:"json",
                    data:{
                        action:"verify_technician_code",
                        name:$('#codeverify_name').val(),
                        code:$('#code').val(),
                    },
                    success:function(data){
                        console.log(data);
                        if(data.status=="success"){
                            // return true;
                            $('#verify_code').text('Redirecting...');
                            form.submit();
                        }
                        else{
                            $('#verify_code').text('Verify code').prop('disabled',false);
                            $('.error-info').html('<p class="error">Verification Code did\'t matched. Please try again with correct code</p>');
                        }
                    }
                });

            }
        });

        $('#skip_chemcial_report_by_event').on('submit',function(e){
            e.preventDefault();
            let bypass=$('#technician_bypass_events').find("option:selected").attr('data-chemical-bypass');
            if(bypass=="true"){
                $(this)[0].submit();
            }
            else{
                console.log('bypass is false'); 
            }
        });
        

    });
})(jQuery);