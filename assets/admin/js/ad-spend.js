(function($){
    $(document).ready(function() {

        $('#filter_by_callrail select[name="location"],#filter_by_callrail select[name="branch"]').on('change',function(){
            
            let location=$(this).val();
            let account=$('select[name="account"]').val();

            console.log('location is'+location);
            $.ajax({

                type:"post",
                url:my_ajax_object.ajax_url,
                data:{
                    action:"get_tracking_no_by_location",
                    location:location,
                    account:account,
                    "_wpnonce": my_ajax_object.nonce
                },
                dataType:"html",
                beforeSend:function(){
                    //freeze the page untill we get the data
                    $('#tracking_ids').html('');
                    $('.tracking_phone_no').addClass('hidden');
                    $('.loader-box').addClass('loader');
                },
                success:function(data){

                    $('#tracking_ids').html(data);

                    $('.tracking_phone_no').removeClass('hidden');
                    $('.loader-box').removeClass('loader');

                    
                }
            })

        });


        $('#filter_by_callrail input[name="date_type"]').on('change',function(){
            let type=$(this).val();

            console.log('type is'+type);

            if(type=="date_range"){
                $('.date-range-box').removeClass('hidden');
                $('.week-box').addClass('hidden');

                $('input[name="from_date"]').attr('disabled',false);
                $('input[name="to_date"]').attr('disabled',false);
                $('input[name="week"]').attr('disabled',true);

            }
            else{
                $('.date-range-box').addClass('hidden');
                $('.week-box').removeClass('hidden');

                $('input[name="from_date"]').attr('disabled',true);
                $('input[name="to_date"]').attr('disabled',true);
                $('input[name="week"]').attr('disabled',false);

            }
        });
        
        // select all tracking number by click on checkbox 
        $(document).on('click','#select_all',function(){

            if($(this).prop("checked") == true){
                    console.log('select all')
                $('.tracking_no_checkboxes').prop('checked',true)

            }
            else{
                    console.log('not select all')
                $('.tracking_no_checkboxes').prop('checked',false)
            }

        });
        
    });
})(jQuery);