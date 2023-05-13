(function($){
    $(document).ready(function(){

        $('.delete_lead').on('click',function(){

            if(confirm('Are you sure you want to delete this lead ?')){

                let ref=$(this);
                let lead_id=$(this).attr('data-lead-id');
                
                $.ajax({
                    type:"post",
                    url:my_ajax_object.ajax_url,
                    dataType:"json",
                    data:{
                        action:"delete_lead",
                        lead_id:lead_id
                    },
                    beforeSend:function(){
                    },
                    success:function(data){

                        if(data.status=="success"){
                            ref.parent().parent().fadeOut();
                        }
                        else{
                            alert('Something went wrong, please try again later')
                        }

                        $('.submit_btn').text('Link Invoice');
                        $('.submit_btn').attr('disabled',false);

                        $('#link_invoice_modal').modal('hide');


                    }
                });

            }

        });

        $('#link_invoice_form').on('submit',function(e){
            e.preventDefault();
            $.ajax({
                type:"post",
                url:my_ajax_object.ajax_url,
                dataType:"json",
                data:$(this).serialize(),
                beforeSend:function(){
                    $('.submit_btn').text('submitting');
                    $('.submit_btn').attr('disabled',true);
                },
                success:function(data){

                    if(data.status=="success"){
                        alert('Invoice linked successfully');
                        
                        let lead_id=$('input[name="lead_id"]').val();
                        let invoice_no=$('select[name="invoice_no"]').val();
                        $(`.lead_id_${lead_id}`).text(invoice_no);

                    }
                    else{
                        $('.error-div').text(data.message);
                    }

                    $('.submit_btn').text('Link Invoice');
                    $('.submit_btn').attr('disabled',false);

                    $('#link_invoice_modal').modal('hide');


                }
            });
        });

        $('.link_invoice').on('click',function(){

            let lead_id=$(this).attr('data-lead-id');
            console.log('lead id '+lead_id);

            $('input[name="lead_id"]').val(lead_id);
            $('#link_invoice_modal').modal('show');
        });

        $('#performance_form').validate({
            rules:{
                cold_caller_id:"required",
                from_date:"required",
                to_date:"required",
                year:"required",
                month:"required",
            },
            submitHandler: function(form){
                $.ajax({
                    type:"post",
                    url:my_ajax_object.ajax_url,
                    data:$(form).serialize(),
                    dataType:"html",
                    beforeSend:function(){
                        $('.perfomance_html').html(`<div class="loader"></div>`);
                    },
                    success:function(data){
                        $('.perfomance_html').html(data)
                    }
                });                
            }
        });

        $('input[name="date_type"]').on('change',function(){
            const date_type = $(this).val();

            if(date_type ==" date_range"){
                $('.date_range').removeClass('hidden');
                $('.year_month').addClass('hidden');
            }
            else if(date_type == "year_month"){
                $('.date_range').addClass('hidden');
                $('.year_month').removeClass('hidden');
            }
            else if(date_type == "all_time"){
                $('.date_range').addClass('hidden');
                $('.year_month').addClass('hidden');
            }
            else{
                $('.date_range').removeClass('hidden');
                $('.year_month').addClass('hidden');
            }
        });

        $.validator.addMethod("noSpace", function(value, element) { 
        return value.indexOf(" ") < 0 && value != ""; 
        }, "Space not allowed");

        $('#create_cold_caller_form').validate({
            rules:{
                name:'required',
                email:{
                    required : true,
                    email:true,
                    remote:{
                        url : my_ajax_object.ajax_url,
                        data:{
                            action : "check_for_email"                     
                        },
                        type: "post"
                    }
                },
                phone:'required',
                password : {
                    minlength : 5
                },
                password_confirm : {
                    minlength : 5,
                    equalTo : "#password"
                },
                username  : {
                    required : true,
                    minlength: 6,
                    noSpace: true,
                    remote:{
                        url : my_ajax_object.ajax_url,
                        data:{
                            action : "check_for_username_wp"                     
                        },
                        type: "post"
                    }
                },
                                    
            },
            messages:{
                password_confirm:{
                    equalTo:"password & verify password field do not match"
                },
                username :{
                    remote : "Username already taken"
                },
                email :{
                    remote : "Email already exist"
                }
            }
        });


    });
})(jQuery);
