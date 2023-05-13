var signaturePad;

(function($){

    $(document).ready(function(){

        if($('#sign-pad').length){
            signaturePad = new SignaturePad(document.getElementById('sign-pad'), {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });    
        }

        jQuery('.m_phone_no').each(function(key,value){
            jQuery(this).keyup(function() {
                jQuery.validator.addMethod("alphanumeric", function(value, element) {
                    return this.optional(element) || /^[+]*[(]{0,1}[0-9]{1,3}[)]{0,1}[-\s\./0-9]*$/i.test(value);
                }, "Numbers and dashes only");
            });
        });

        $(".m_phone_no").on('change',function(){
            console.log('in phonen no method');
            if($(this).val().length >= 10){
                getCallrailIdFromCallrail($(this).val());
            }
            else{
                console.log('client location is empty or phone no length is not proper');
            }    
        
        });

        $('select[name="cost_per_month"]').on('change',function(){
            let amount=$(this).val();

            if(amount!=""){
                if(amount == 'other'){
                    jQuery("#othercostdiv").removeClass('hidden');
                    jQuery('input[name="total_cost"]').val('');
                }
                else{
                    let total_annual_cost=parseFloat(amount)*12;
                    jQuery('input[name="total_cost"]').val(total_annual_cost)
                    jQuery("#othercostdiv").addClass('hidden');
                }
            }
            else{
                jQuery('input[name="total_cost"]').val('');
                jQuery("#othercostdiv").addClass('hidden');                
            }

        });

        $('input[name="othercost"]').on('change',function(){
            let amount=$(this).val();
            if(amount!=""){
                let total_annual_cost=parseFloat(amount)*12;
                jQuery('input[name="total_cost"]').val(total_annual_cost)
            }
            
        })        
    
    });

    
})(jQuery);

function clearCanvas(){
    signaturePad.clear();
}
 
function changedCost(){

    var val= document.getElementById("cost").value;
    if(val == 'other'){
        jQuery("#othercostdiv").removeClass('hidden');
    }
    else{
        let total_annual_cost=parseFloat(val)*12;
        jQuery('input[name="total_cost"]').val(total_annual_cost)
        jQuery("#othercostdiv").addClass('hidden');
    }
}


const maintenanceAjaxSubmit = (form) => {
    jQuery.ajax({
        type: "post",
        url: my_ajax_object.ajax_url,
        dataType: "json",
        data: jQuery(form).serialize(),
        beforeSend: function(){
           showLoader('Uploading data in system, please wait...');
        },
        success: function(data){
            if(data.status === "success"){
                new swal('Success!', data.message).then(() => {

                    jQuery(form)[0].reset();

                    if(data.data.hasOwnProperty('redirect_url')){
                        window.location.href = data.data.redirect_url;
                    }
                    else{
                        window.location.reload();
                    }
                })
            }
            else{
                new Swal('Oops!', data.message, 'error');
            }
        },
        error: function(){
           new Swal('Oops!', 'Something went wrong, please try again later', 'error');
        }
    });    
}