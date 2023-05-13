(function($){
    $(document).ready(function(){

        $(".client_phone_no").on('change',function(){
            if($('.client_phone_no').val().length >= 10){
                fetchCallrailType($(this).val());
            }
        });
        
        // onchange of technician appointement 
        $('body').on('change','.technician_appointment',function(){

            // first reset the fields so the invoice don't show old data 
            clearFormEventFields();
    
            let client_name= $('option:selected', this).attr('data-client-name');
            let client_location=$('option:selected', this).attr('data-client-location');
            let client_phone_no=$('option:selected', this).attr('data-phone-no');
            let client_email=$('option:selected', this).attr('data-client-email');
    
            if(client_name !== ""){
                $('.client_name').val(client_name);
            }

            if(client_location !== "" && client_location !== 'null' && client_location !== 'undefined'){
                $('.client_address').val(client_location);
            }

            if(client_phone_no !== "" && client_phone_no !== 'null' && client_phone_no !== 'undefined'){
                $('.client_phone_no').val(client_phone_no);
            }

            if(client_email !== "" && client_email !== 'null' &&  client_email !== 'undefined'){
                $('.client_email').val(client_email);
            }
    
            const phone_no = $('option:selected', this).attr('data-phone-no');
    
            if(phone_no !== "" && phone_no !== "undefined" && phone_no !== null && phone_no.length >= 10){
                fetchCallrailType(phone_no);
            }
        });

        $('#no_email_to_offer').on('click', function(){
            if(jQuery(this).is(':checked')){
                jQuery(this).closest('.form-group').find('#clientEmail').val('').attr('disabled', true);
            }
            else{
                jQuery(this).closest('.form-group').find('#clientEmail').attr('disabled', false);
            }
        })

        $('#no_phone_to_offer').on('click', function(){
            if(jQuery(this).is(':checked')){
                jQuery(this).closest('.form-group').find('#client_phone').val('').attr('disabled', true);
            }
            else{
                jQuery(this).closest('.form-group').find('#client_phone').attr('disabled', false);
            }
        })

    })
})(jQuery);

function clearFormEventFields(){
    jQuery('.client_name').val('');
    jQuery('.client_address').val('');
    jQuery('.client_phone_no').val('');
    jQuery('.client_email').val('');
}

function fetchCallrailType(phone_no){

    // check first if recurring or not 
    let recurring=$('#technician_appointment option:selected').attr('data-recurring-client');
    let lead_source=$('#technician_appointment option:selected').attr('data-lead-source');

    if(lead_source!==""){
        $('input[name="callrail_id"]').val(lead_source);
    }
    else if(recurring=="true"){
        $('input[name="callrail_id"]').val('reoccuring_customer');
    }
    else{
        getCallrailIdFromCallrail(phone_no);
    }

}
