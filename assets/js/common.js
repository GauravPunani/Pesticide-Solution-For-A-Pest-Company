const ERROR_MESSAGES = {
    invalid_email : 'Provided email is not valid, but if you think email is valid then please ask office to mark this email as valid in system.'
};
(function($){
    $(document).ready(function(){

        $('#confirmation_form').on('submit',function(e){
            e.preventDefault();
            $.ajax({
                type:"post",
                url:my_ajax_object.ajax_url,
                dataType:"json",
                data:$(this).serialize(),
                beforeSend:function(){
                    // show the progress 
                    $('#confirm_submit_btn').text('Requesting...please wait'); 
                },
                success:function(data){
                    if(data.status=="success"){
                        // show the code box for confirmation 
                        $('.confirmation-box').hide();

                        // set the code db id as well for verification
                        $('.verification-box').removeClass('hidden');

                        // set the last insert id form 
                        $('input[name="db_id"]').val(data.db_id);
                    }
                }
            });
        });

        $('#code_verification_form').on('submit',function(e){
            e.preventDefault();
            $.ajax({
                type:"post",
                url:my_ajax_object.ajax_url,
                data:$(this).serialize(),
                dataType:"json",
                beforeSend:function(){
                    $('.error-box').html("");
                    $('#verification_submit_btn').attr('disabled',true);
                    $('#verification_submit_btn').text('Verifying....');
                    
                },
                success:function(data){
                    if(data.status=="success"){
                        console.log('code matched');
                        location.reload();
                        // set the session and show the edit form
                    }
                    else{
                        // display the error 
                        console.log('code did not matched');
                        $('.error-box').html("<p class='text-danger'>Verification code did't matched, try again with correct code</p>");
                        $('#verification_submit_btn').text('Verify & submit').attr('disabled',false);
                        
                    }
                }
            })
        });
        
        $('.select2-field').select2({
            width:'100%'
        });

        $('[data-toggle="tooltip"]').tooltip();

        $('#reset_invoice_page').click(function(){
            
            if (! $('.reset-form input[name="action"]').length) {
                $('<input>', {
                    type: "hidden",
                    name: "action",
                    value:  "reset_invoice_form"
                }).appendTo('.reset-form')
            }
            else{
                $('.reset-form input[name="action"]').val('reset_invoice_form');
            }

            $('.reset-form input[type="submit"]').attr('formnovalidate','formnovalidate');
            
            $('.reset-form')[0].submit();        
            return true;
        });

        $('#event_date, .date').on('change', function() {
            const date = $(this).val();
            fetchCalendarEvents(true, date);
        });
        
        $('#invoice_flow_get_events').on('change', function() {
            const date = $(this).val();
            fetchCalendarEvents(true, date, '', '', 'json_form');
        });


        //run ajax if invoice process completed by office staff
        let action = gamGetUrlVars()['action'];
        tech_id = gamGetUrlVars()['tech'];
        date = gamGetUrlVars()['date'];
        event_id = gamGetUrlVars()['event_id'];
        if (action != '' && action == 'staff_invoice') {
            if (tech_id != '' && date != '') {
                fetchCalendarEvents(true, date , '', '', 'json_form',tech_id,event_id);
            }
        }
    });
    
})(jQuery);

function getCallrailIdFromCallrail(phone_no){
    $.ajax({
        type: "post",
        url: my_ajax_object.ajax_url,
        dataType: "json",
        data: {
                action:"get_lead_source_from_callrail",
                phone_no:phone_no
        },
        beforeSend: function(){
            // disable the submit button 
            $('.submit_btn').prop('disabled', true);
        },
        success: function(data){

            if(data.status == "success"){
                console.log('linking callrail id');
                $('input[name="callrail_id"]').val(data.data.callrail_id);
            }
            else{
                $('input[name="callrail_id"]').val('unknown');
            }

            $('.submit_btn').prop('disabled', false);                
        }
    });
}

