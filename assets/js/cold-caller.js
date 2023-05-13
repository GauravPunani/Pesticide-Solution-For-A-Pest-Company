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

        $('#performance_form').on('submit',function(e){
            e.preventDefault();

            $.ajax({
                type:"post",
                url:my_ajax_object.ajax_url,
                data:$(this).serialize(),
                dataType:"json",
                beforeSend:function(){
                    $('.perfomance_html').html(`<div class="loader"></div>`);
                },
                success:function(data){
                    if(data.status=="success"){
                        $('.perfomance_html').html(data.data);
                    }
                }
            });
        });
        
        $('#performance_form').validate({
            rules:{
                cold_caller:"required",
                from_date:"required",
                to_date:"required",
                year:"required",
                month:"required",
            }
        });

        $('input[name="date_type"]').on('change',function(){
            if($(this).val()=="date_range"){
                $('.date_range').removeClass('hidden');
                $('.year_month').addClass('hidden');
            }
            else{
                $('.date_range').addClass('hidden');
                $('.year_month').removeClass('hidden');
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
