(function($){

    $(document).ready(function(){

        $('.select2-field').select2({
            width:'100%'
        });


        $('.week_date').on('change',function(){
            console.log(' week changed');
            
            let week_date=$(this).val();
    
            if(week_date!="" && week_date!=undefined){
                // request server for week start & and date     
    
                $.ajax({
                    type:"post",
                    url:my_ajax_object.ajax_url,
                    data:{
                        action:"get_week_dates",
                        week:week_date
                    },
                    dataType:"json",
                    beforeSend:function(){
                        $('.week_dates').html("");
                    },
                    success:function(data){
                        if(data.status=="success"){
                            $('.week_dates').html(data.data);
                        }
                    }
                });
            }
            else{
                $('.week_dates').html("No week selected");
            }
    
        });

        $('#ads_spent_form select[name="tracking_location"]').on('change',function(){

            let location=$(this).val();
            console.log('location='+location);
        
            $('#ads_spent_form select[name="tracking_id"] > option').each(function(key,val){

                    if($(val).val()!=""){
                        if($(val).attr('data-location')==location){
                            $(val).removeClass('hidden');
                        }
                        else{
                            $(val).addClass('hidden');
            
                        }    
                    }
        
            });
        
        
        });

        $('.notice-dismiss').on('click',function(){

            let notice_id=$(this).parent().attr('data-notice-id');
            console.log(notice_id);

            if(notice_id!="undefined" && notice_id!=undefined && notice_id!=""){
                // change the status to 0using ajax
                $.ajax({
                    type:"post",
                    url:my_ajax_object.ajax_url,
                    data:{
                        action:"change_notice_status",
                        notice_id:notice_id
                    },
                    dataType:"json",
                    success:function(data){
                        if(data.status=="success"){
                            console.log('status changed');
                        }
                        else{
                            console.log('something went wrong');
                        }
                    }
                })
            }
        
        });
        
        $('input[name="search_keyword"]').on('keyup',function(){

            let search_length=$(this).val().length;
        
            if(search_length>0){
                $('select[name="tracking_location"],select[name="tracking_id"]').attr('disabled',true)
            }
            else{
                $('select[name="tracking_location"],select[name="tracking_id"]').attr('disabled',false)
            }
        });

        $('.open_docs_modal').on('click',function(){
            let docs_json=$(this).attr('data-docs');
            const docs_html = generateDocsHtml(docs_json);
            $('.deposit_docs').html(docs_html);
            $('#docs_modal').modal('show');

        });

        $('.upload_proof_of_reimbursement').on('click',function(){
            let reimbursement_id=$(this).attr('data-reimbursement-id');

            $('input[name="reimbursement_id"]').val(reimbursement_id);
            $('#proof_of_reimbursement').modal('show');
        });
        
        $('.reimbursement_checkbox').on('click',function(){
            let reimbursement_id=$(this).attr('data-reimbursement-id');
            let checked=false;

            if($(this).prop('checked')==true){
                checked=true;
            }

            $.ajax({
                type:"post",
                url:my_ajax_object.ajax_url,
                dataType: "json",
                data:{
                    action:"update_reimbursement_status",
                    checked:checked,
                    reimbursement_id:reimbursement_id
                },
                beforeSend:function(){
                    showLoader('Processing request, please wait...');
                },
                success:function(data){
                    if (data.status === "success") {
                        new swal('Success!', data.message, 'success').then(() => {
                            window.location.reload();
                        })
                    } else {
                        new Swal('Oops!', data.message, 'error');
                    }
                },
                error: function() {
                    new Swal('Oops!', 'Something went wrong, please try again later', 'error');
                }
            })
        });

        $('#signature_email_form').on('submit',function(e){
            e.preventDefault();
            $.ajax({
                type:'post',
                url:my_ajax_object.ajax_url,
                data:$(this).serialize(),
                dataType:"json",
                beforeSend:function(){
                    $('.email_btn').text('Sending Email...').attr('disabled',true);
                },
                success:function(data){
                    if(data.status=="success"){
                        alert('Email Sent Successfully');
                        $('#email_sending_modal').modal('hide');
                    }
                    else{
                        alert('Something Went Wrong, please try again later');
                    }
                    $('.email_btn').text('Send Email').attr('disabled',false);
                }
            })
        });


        $('.send_contract_for_sign').on('click',function(){
            let client_email=$(this).attr('data-email');
            let contract_id=$(this).attr('data-contract-id');
            let contract_type=$(this).attr('data-contract-type');

            $('input[name="client_email"]').val(client_email);
            $('input[name="contract_id"]').val(contract_id);
            $('input[name="contract_type"]').val(contract_type);

            $('#email_sending_modal').modal('show');
        });

		$.each($('.numberonly'),function(key,value){
			setInputFilter(value, function(val) {
				return /^\d*\.?\d*$/.test(val); // Allow digits and '.' only, using a RegExp
				});
		});        
    });


})(jQuery);

// enable button on animal tracker and client database table
function enable_btn_on_client_select(inp,btn) {
    let atLeastOneIsChecked = jQuery('input[name="'+inp+'"]:checked');
    if (atLeastOneIsChecked.length > 0) {
        jQuery(btn).removeAttr('disabled', 'disabled');
        return true;
    } else {
        jQuery(btn).attr('disabled', 'disabled');
        return false;
    }
}

function setInputFilter(textbox, inputFilter) {
	["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function(event) {
	  textbox.addEventListener(event, function() {
            if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
            } else if (this.hasOwnProperty("oldValue")) {
                this.value = this.oldValue;
                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
            } else {
                this.value = "";
            }
	    });
	});
}