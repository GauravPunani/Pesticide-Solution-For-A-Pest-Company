(function($){

    $(document).ready(function(){

        // check for tech login session if expired
        setInterval(function() {
            console.log('checking session');
            $.ajax({
                type:"post",
                url:my_ajax_object.ajax_url,
                dataType:"json",
                data:{
                    action:"check_for_tech_session"
                },
                success:function(data){
                    if(data.status=="error"){
                        alert("Your login session has been expired, you'll be redirected to login page to login again");
                        window.location.reload();
                    }
                }        
            })
        }, 60 * 2000);

        $('.hide_notification').on('click',function(){
            let notification_id=$(this).attr('data-notification-id');
            let ref=$(this);
            $.ajax({
                url:my_ajax_object.ajax_url,
                type:'post',
                data:{
                    action:"hide_technician_notification",
                    notification_id:notification_id
                },
                dataType:"json",
                beforeSend:function(){

                },
                success:function(data){
                    ref.parent().parent().fadeOut();
                }
            })
        })

        $('.open_docs_modal').on('click',function(){
            let docs_json=$(this).attr('data-docs');
            const docs_html = generateDocsHtml(docs_json);
            $('.deposit_docs').html(docs_html);
            $('#docs_modal').modal('show');
        });

        $('#mileage_proof_form').validate({
            rules:{
                milage:{
                    required: true,
                    digits: true
                },
                mileage_proof:"required",
            }
        });

        $('#oil_change_form').validate({
            rules:{
                last_oil_change_mileage:{
                    required: true,
                    digits: true
                },
                mileage_proof:"required",
                oil_change_proof:"required",
            }
        });

        $('#break_pad_change_form').validate({
            rules:{
                break_pad_change_mileage:{
                    required: true,
                    digits: true
                },
                mileage_proof:"required",
                break_pad_change_proof:"required",
            }
        });

        $('#vehicle_information').validate({
            rules:{
                year:"required",
                make:"required",
                model:"required",
                plate_number:"required",
                vin_number:"required",
                parking_address:"required",
                last_break_change_mileage: {
                    required: true,
                    digits: true
                },
                last_oil_change_mileage: {
                    required: true,
                    digits: true
                },
                current_mileage: {
                    required: true,
                    digits: true
                },
            }
        });

    });
    
})(jQuery);
