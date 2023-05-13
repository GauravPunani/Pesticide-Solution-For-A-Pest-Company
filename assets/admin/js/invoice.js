(function($){
    
    $(document).ready(function(){

        $('input[name="anmial_trapping_cage"]').on('change',function(){
            if($(this).val()=="yes"){
                $('.cages_data').removeClass('hidden');
            }
            else{
                $('.cages_data').addClass('hidden');
            }
        });

        $('#invoice_email_form').on('submit',function(e){
            e.preventDefault();
            console.log('sending email');
            
            $.ajax({
                type:"post",
                url:my_ajax_object.ajax_url,
                data:$(this).serialize(),
                dataType:"json",
                beforeSend:function(){
                    $('#invoice_email_submit_btn').attr('disabled',true);
                    $('#invoice_email_submit_span').text('Sending...').attr('disabled',true);
                },
                success:function(data){
                    $('#invoice_email_submit_span').text('Send Invoice');
                    $('#invoice_email_submit_btn').attr('disabled',false);
                    if(data.status=="success"){
                        alert('Inovice sent to client email successfully');
                    }
                    else{
                        alert('Somehting Went wrong, please try agin later');
                    }
    
                    $('#invoice_email_modal').modal('hide');
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert('Something went wrong, please try again later');
                    $('#invoice_email_submit_span').text('Send Invoice');
                    $('#invoice_email_submit_btn').attr('disabled',false);
                    console.log(xhr.responseText);
                    console.log(xhr.status);
                    console.log(xhr.thrownError);
                }
            })
        });
    
    
        $(".invoice_paid").on('change',function(){
            console.log('invoice changed');
            
            let invoice_id=$(this).attr('data-invoice-id'); // if checkbox is checked
    
            let checked;
    
            if(this.checked){
                checked=true;
            }
            else{
                checked=false;
            }
    
            $.ajax({
                type:'POST',
                url:my_ajax_object.ajax_url,
                data:{
                    action:'update_invoice_status',
                    invoice_id:invoice_id,
                    invoice_paid:checked
                },
                success:function(data){
                    console.log(data);
                }
            });
    
    
            console.log(invoice_id) 
        });
    
        $('.client_refused').on('change',function(){
    
                let client_refused_type=$(this).attr('data-refused-type');
                let invoice_id=$(this).attr('data-invoice-id');
    
                let checked;
    
                if(this.checked){
                    checked=true;
                }
                else{
                    checked=false;
                }
    
                $.ajax({
                    type:'POST',
                    url:my_ajax_object.ajax_url,
                    data:{
                        action:'update_invoice_client_status',
                        invoice_id:invoice_id,
                        type:client_refused_type,
                        checked:checked
                    },
                    success:function(data){
                        console.log(data);
                    }
                });
        });
    
        $('.office_sent_bill').on('change',function(){
            console.log('in there');
            let invoice_id=$(this).attr('data-invoice-id');
    
            let checked;
    
            if(this.checked){
                checked='true';
            }
            else{
                checked='false';
            }
    
            $.ajax({
                type:'POST',
                url:my_ajax_object.ajax_url,
                data:{
                    action:'update_invoice_office_sent_bill',
                    invoice_id:invoice_id,
                    office_sent_bill:checked
                },
                success:function(data){
                    console.log(data);
                }
            });
            
    
        });
    
    
        $('.mymodal').click(function(){
    
            let invoice_id=$(this).attr('data-invoice-id');
    
            $('#invoice_id').val(invoice_id);
    
            $('#commentModal').modal({
                show: 'true'
            }); 
    
        });
    
        $('.select-invoices').on('change',function(){
    
            let checked;
    
            if(this.checked){
                checked='true';
            }
            else{
                checked='false';
            }
            console.log($(this).attr('data-invoice-id'));
            
    
            $.ajax({
                type:'POST',
                url:my_ajax_object.ajax_url,
                dataType:"json",
                data:{
                    action:"select_invoice",
                    invoice_id:$(this).attr('data-invoice-id'),
                    checked:checked
                },
                success:function(data){
                    console.log(data);
                }
            });
    
    
        });
    
    
        $(document).on('click','.openmodal',function(){
            console.log('openmodal called');
            
            let model_id=$(this).attr('data-model-id');
    
            $(`#${model_id}`).modal({
                show: 'true'
            }); 
        });
    
    
        $('.genereate_statement').click(function(){
    
            var html_div = $(this).attr('data-div-class');
            $(`.${html_div}`).html("<p>Loading... Please wait</p>");
            $.ajax({
                type:'POST',
                url:my_ajax_object.ajax_url,
                data:{
                    action:'get_selected_invoices',
                },
                dataType:'json',
                success:function(res){
                    console.log(res);
    
                    switch (res.status) {
                        case 'error':
                            $(`.${html_div}`).html("<p>No Record Found, please make sure you've selected atleast one invoice</p>");
                            break;
                        case 'success':
                                let total_obj = res.data.map(a => a.total_amount);
                                let total_amount=total_obj.reduce((a, b) => parseInt(a)+ parseInt(b), 0);
                                let data_html="";
    
                                data_html+=`<input type='hidden' name='branch_id' value='${res.data[0].branch_id}' />`;
    
                                $.each(res.data,function(key,value){
                                    data_html+=`
                                        <input type='hidden' name='invoice[${parseInt(key)}][invoice_no]' value="${value.invoice_no}" />
                                        <p class="text-center font-weight-bold">Invoice #${parseInt(key)+1}</p>

                                        <div class="form-group">
                                            <label class="control-label col-sm-3" >Date of service:</label>                                        
                                            <div class="col-sm-9">
                                                <input type="text" name="invoice[${parseInt(key)}][date]" class="form-control" placeholder="Enter Date" value="${value.date}">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-sm-3" >Amount:</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="invoice[${parseInt(key)}][amount]" class="form-control"  placeholder="Enter amount" value="${value.total_amount}">
                                            </div>
                                        </div>
                                    `;
                                });
                
                                data_html+="<h4>Basic Details</h4>";
                
                                data_html+=`
                                    <div class="form-group">
                                        <label class="control-label col-sm-4">Name</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="name" class="form-control"  placeholder="Enter Name" value="${res.data[0].client_name}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-sm-4" for="totalamount">Total Amount:</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="total_amount" class="form-control"  placeholder="Enter total amount" value="${total_amount}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-sm-4" for="address">Address</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="address" class="form-control"  placeholder="Enter address" value="${res.data[0].address}">
                                        </div>
                                    </div>
                                `;

                                data_html+=`<button class="btn btn-primary"><span><i class="fa fa-file"></i></span> Generate Mini Statement</button>`;
                
                                $(`.${html_div}`).html(data_html);
                            break;
                        default:
                            break;
                    }
                    
    
    
    
                }
            });
        });
    
        $('.docupload').click(function(){
            console.log('doc upload called');
            
            let invoice_id=$(this).attr('data-invoice-id');
            let input_id=$(this).attr('data-input-id');
            console.log(invoice_id+" input id"+input_id);
            
            $(`#${input_id}`).val(invoice_id);
        });
    
    
        $('.ministatements').click(function(){
            let invoice_id=$(this).attr('data-invoice-id');
    
            if(invoice_id!==""){
                $('.mini_statement_content').html('<p>Loading Statementes...</p>');
                $.ajax({
                    type:"post",
                    url:my_ajax_object.ajax_url,
                    data:{
                        action:'list_invoice_statements',
                        invoice_id:invoice_id,
                    },
                    dataType:'json',
                    success:function(data){
                        if(data.status=="success"){
                            let statement_html='<table class="table table-striped table-responsive">'
                            statement_html+=`
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Mini Statement</th>
                                    </tr>
                            `;
                            $.each(data.data,function(index,value){
                                 statement_html+=`
                                                <tr>
                                                    <td>${value.invoice_id}</td>
                                                    <td>${value.date}</td>
                                                    <td>\$${value.amount}</td>
                                                    <td><a target="_blank" href="${my_ajax_object.upload_dir_url}${value.pdf_path}">View Statement</a></td>
                                                </tr>
                                 
                                 `;
                            })
    
                            statement_html+='</table>';
                            $('.mini_statement_content').html(statement_html);
                        }
                        else{
                            $('.mini_statement_content').html("<p> No Mini Statement found for the invoice </p>");
                        }
                        console.log(data);
                    }
    
                })
            }
        });

        $('input[name="calculation_type"]').on('change',function(){
            let type=$(this).val();

            if(type=="invoice"){
                $('.invoice-form').removeClass('hidden');
                $('.quote-form').addClass('hidden');
            }
            else{
                $('.invoice-form').addClass('hidden');
                $('.quote-form').removeClass('hidden');                
            }
        });

        $('.amount_fields').on('change',function(){
            console.log('in method');
            let service_fee=$('input[name="service_fee"]').val();
            let tax=$('input[name="tax"]').val();
            let processing_fee=$('input[name="processing_fee"]').val();

            let total_amount=parseFloat(parseFloat(service_fee)+parseFloat(tax)+parseFloat(processing_fee)).toFixed(2);

            $('input[name="total_amount"]').val(total_amount);


        });

    
    
        $('.checkcalendarevents').click(function(){
    
            $('.calendar-content,.calendar_pending_events,.summary-content').html("<tr><td colspan='4'><div class='loader'></div></td></tr>");                        
    
    
            let technician_id=$('#technician_id').val();
            let from_date=$('#from_date').val();
            let to_date=$('#to_date').val();
    
            $.ajax({
                type:'post',
                dataType:'json',
                url:my_ajax_object.ajax_url,
                data:{
                    action:"get_calendar_events_by_technician",
                    technician_id:technician_id,
                    from_date:from_date,
                    to_date:to_date
                },
                success:function(data){
                    console.log(data);
    
                    if(data.status=="success"){
    
                        data=data.data;
    
                        var date='';
                        
    
                        let calendar_html=`<tbody>
                         <tr>
                                                <th>Summary</th>
                                                <th>Location</th>
                                                <th>Date</th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
    `;
                        let calendar_pending_events=`<tbody>
                                            <tr>
                                               <th>Summary</th>
                                               <th>Location</th>
                                               <th>Date</th>
                                           </tr>`;
    
    
                        if(!$.isEmptyObject(data.calendar)){
    
                            var event_counter={
                                invoice:0,
                                residential:0,
                                commercial:0,
                                pending:0,
                                events:Object.keys(data.calendar).length
                            }
    
                            $.each(data.calendar,function(key,value){
    
                                if(value.start.dateTime!=null && value.start.dateTime!=undefined ){
                                    date=new Date(value.start.dateTime);
                                    date=('0' + date.getDate()).slice(-2)+"-"+('0' + (date.getMonth()+1)).slice(-2)+"-"+date.getFullYear();
                
                                }
    
                                let invoice_id=false;
                                let commercial_quote=false;
                                let residential_quote=false;
                                // calendar-content
                                calendar_html+=`<tr>
                                                    <td>${value.summary!=null ? value.summary : ''}</td>
                                                    <td>${value.location!=null ? value.location : ''}</td>
                                                    <td>${date}</td>
                                `;
    
                                let any_event_pending=true;
                                
    
    
                                if(!$.isEmptyObject(data.invoice_events)){
                                    data.invoice_events.filter(function(el){
                                        if(el.calendar_event_id==value.id){
                                            console.log('invoice id matched '+value.id);
                                            invoice_id=el.id;
                                            any_event_pending=false;
                                            event_counter.invoice++;
                                        }
                                    });    
                                }else{
                                    console.log('invoice data empty');
                                }
                                if(!$.isEmptyObject(data.commercial_quote_events)){
                                    data.commercial_quote_events.filter(function(el){
                                        if(el.calendar_event_id==value.id){
                                            console.log('commercial id matched '+value.id);
                                            commercial_quote=el.id;
                                            any_event_pending=false;
                                            event_counter.commercial++;
                                        }
                                    });    
                                }
                                else{
                                    console.log('commerical data empty');
                                    
                                }
                                if(!$.isEmptyObject(data.residential_quote_events)){
                                    data.residential_quote_events.filter(function(el){
                                        if(el.calendar_event_id==value.id){
                                            console.log('residential id matched '+value.id);
                                            residential_quote=el.id;
                                            any_event_pending=false;
                                            event_counter.residential++;
                                        }
                                    });    
                                }else{
                                    console.log('residential data empty');
    
                                }
                                calendar_html+=`<td>`;
    
                                    if(invoice_id)
                                        calendar_html+=`<a target="_blank" href="/wp-admin/admin.php?page=invoice&invoice_id=${invoice_id}">Invoice Found</a>`;
                                    else
                                        calendar_html+=`<span>No Invoice Found</span>`;
    
                                calendar_html+=`</td>`;
    
                                calendar_html+=`<td>`;
    
                                    if(commercial_quote)
                                        calendar_html+=`<a target="_blank" href="/wp-admin/admin.php?page=commercial-quotesheet&quote_id=${commercial_quote}">Commercial Quote Found</a>`;
                                    else
                                        calendar_html+=`<span>No Commerial Found</span>`;
    
                                calendar_html+=`</td>`;
    
    
                                calendar_html+=`<td>`;
    
                                    if(residential_quote)
                                        calendar_html+=`<a target="_blank" href="/wp-admin/admin.php?page=resdiential-quotesheet&quote_id=${residential_quote}">Residential Quote Found</a>`;
                                    else
                                        calendar_html+=`<span>No Residential Quote Found</span>`;
    
                                calendar_html+='</td>';
                                
                                calendar_html+='</tr>';
    
    
                                if(any_event_pending){
                                    event_counter.pending++;
                                    calendar_pending_events+=`  <tr>
                                                                    <td>${value.summary!=null ? value.summary : ''}</td>
                                                                    <td>${value.location!=null ? value.location : ''}</td>
                                                                    <td>${date}</td>
                                                                </tr>`
                                                                ;
                                }
    
    
                            });
                            calendar_html+="</tbody>";
                            calendar_pending_events+="</tbody>";
    
    
                            // summary table 
    
                            var summary_html=`<tbody>
                                                    <tr>
                                                        <th><span><i class="fa fa-calendar"></i></span> Total Calendar Events</th>
                                                        <td>${event_counter.events}</td>
                                                    </tr>
                                                    <tr>
                                                        <th><span><i class="fa fa-file"></i></span> Total Invoices</th>
                                                        <td>${event_counter.invoice}</td>
                                                    </tr>
                                                    <tr>
                                                        <th><span><i class="fa fa-building-o"></i></span> Total Commercial Quotes</th>
                                                        <td>${event_counter.commercial}</td>
                                                    </tr>
                                                    <tr>
                                                        <th><span><i class="fa fa-home"></i></span> Total Residential Quotes</th>
                                                        <td>${event_counter.residential}</td>
                                                    </tr>
                                                    <tr>
                                                        <th><span><i class="fa fa-clock-o"></i></span> Total Pending Events</th>
                                                        <td>${event_counter.pending}</td>
                                                    </tr>
                                                </tbody>`;
    
                            console.log(event_counter);
                            
                            $('.calendar-content').html(calendar_html);
                            $('.calendar_pending_events').html(calendar_pending_events);
                            $('.summary-content').html(summary_html);
        
                        }
                        else{
                            $('.calendar-content').html("<tr><td colspan='4'>No Event Found</td></tr>");                        
                            console.log('calendar or db evnts are empty');
                            
                        }    
                    }
    
                    
                }
            })
    
        });
    
    });
    
})(jQuery);

function openEmailBox(ref){
    
    // set the fields for email 

    let email=jQuery(ref).attr('data-email');
    let invoice_id=jQuery(ref).attr('data-invoice-id');

    console.log('email is '+email)
    console.log('id is '+invoice_id)

    jQuery('#invoice_email_form input[name="client_email"]').val(email);
    jQuery('#invoice_email_form input[name="invoice_id"]').val(invoice_id);
    jQuery('#invoice_email_modal').modal('show');
}


function arrayColumn(array, columnName) {
    return array.map(function(value,index) {
        return value[columnName];
    })
}
