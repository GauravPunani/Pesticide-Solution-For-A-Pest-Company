jQuery.noConflict();
  jQuery(document).ready(function() {
    jQuery('#signArea').signaturePad({drawOnly:true, drawBezierCurves:true, lineTop:90});
			});
			
    jQuery("#sendform").on('click',function(e){
            var error = 0;
                var name = $('#clientName').val();
                if (name == '') {
                    error = 1;
                    $('#clientName_error_msg').html("Client name cannot be empty");
                    $('#clientName_error_msg').parent().show();
                }
                var email = $('#clientEmail').val();
                if (email == '0') {
                    error = 1;
                    $('#clientEmail_error_msg').html('You should select a country.');
                    $('#clientEmail_error_msg').parent().show();
                }
              
                // if (!($('#checkboxid').is(':checked'))) {
                //     error = 1;
                //     $('#checkboxid_error_msg').html("Please Tick the Agree to Terms of Use.");
                //     $('#checkboxid_error_msg').parent().show();
                // }
                if (error) {
                    return false;
                } else {
                  
          e.preventDefault();
                		html2canvas([document.getElementById('sign-pad')], {
					onrendered: function (canvas) {
						var canvas_img_data = canvas.toDataURL('image/png');
						var img_data = canvas_img_data.replace(/^data:image\/(png|jpg);base64,/, "");
              var input = jQuery("<input>").attr("type", "hidden").attr("name", "signimgurl").val(img_data);
                jQuery('#res-form').append(input);
						jQuery("#res-form").submit();
						//ajax call to save image inside folder
						}
					});
                }
			});