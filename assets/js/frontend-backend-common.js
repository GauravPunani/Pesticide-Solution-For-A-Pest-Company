(function($){
    $(document).ready(function(){

        $('.select2-select-invoice').select2({
            width: '100%',
            minimumInputLength:4,
            ajax:{
                url:fbcs.ajax_url,
                dataType: 'json',
                method:"post",
                data: function (params) {
                    const query = {
                        search: params.term,
                        type: 'public',
                        action:"search_invoice_by_ajax"
                    }

                    // Query parameters will be ?search=[term]&type=public
                    return query;
                }                    
                // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
            }
        });
        
    })

})(jQuery);

// this method fetch query string from url
function gamGetUrlVars() {
    var vars = [],
        hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

// this method check if events are pending for previous days in week
function fetchCalendarEvents(
    event_check=true, 
    appointement_date, 
    ref_element = '', 
    error_class = '', 
    format = 'data-attributes',
    emp_id = '',
    event_id = ''
    ){
    console.log('in frontend backend common script');
    if(error_class==''){
        error_class='event_error';
    }
    
    // hide the previous error 
    jQuery(`.${error_class}`).addClass('hidden');           

    // reset the technician events 
    jQuery('#technician_appointment').html("<option value=''>Select</option>");

    jQuery.ajax({
        type:"post",
        url:fbcs.ajax_url,
        dataType:"json",
        data:{
            action:"get_technician_events",
            appointement_date,
            event_check,
            emp_id,
            event_id
        },
        beforeSend: function() {
            if(emp_id != '' && typeof staff_event_id === 'undefined'){
                showLoader('Processing please wait ...');
            }
        },
        success:function(data){
            console.log(data);

            if(swal.isVisible()){
                swal.close();
            }

            let events_html="<option value=''>Select</option>";

            if(data.status == "error"){

                jQuery(`.${error_class}`).html(`<p class="text-danger error_message">${data.message}</p>`).removeClass('hidden');

                if(!$.isEmptyObject(data.data)){
                    let event_error_html='<ul class="errors_list">';
                    jQuery.each(data.data,function(key,value){
                        event_error_html+=`<li>${value.summary} - ${value.start.dateTime!=null || value.start.dateTime!=undefined  ? value.start.dateTime.split('T')[0] : "" }</li>`;
                    });
                    event_error_html+='</ul>';
                    jQuery(`.${error_class}`).append(event_error_html);
                }

                if(ref_element == ""){
                    jQuery('.calendar_events').html(events_html);
                }
                else{
                    jQuery(ref_element).html(events_html);
                }    

                return false;
            }

            if(format == "data-attributes"){   
                jQuery.each(data.data,function(key,value){

                    let tax_rate = zip_code = '';

                    if(value.hasOwnProperty("tax_rate")){
                        tax_rate = value.tax_rate;
                    }
                    if(value.hasOwnProperty("zip_code")){
                        zip_code = value.zip_code;
                    }

                    events_html+=`
                        <option 
                            data-maintenance-bypass="${value.maintenance_bypass}" 
                            data-chemical-bypass="${value.chemical_bypass}" 
                            data-zip-code="${zip_code}" 
                            data-sale-tax-rate="${tax_rate}" 
                            data-service-fee="${value.service_fee}" 
                            data-payment-method="${value.payment_method}" 
                            data-phone-no="${value.phone_no}" 
                            data-client-email="${value.client_email}" 
                            data-client-name="${value.client_name}" 
                            data-client-location="${value.location}" 
                            data-recurring-client="${value.recurring}" 
                            data-lead-source="${value.lead_source}" 
                            data-tax-exempt="${value.tax_exempt}" 
                            value="${value.id}">${value.summary}
                        </option>
                    `;
                });
            }
            else{
                jQuery.each(data.data,function(key,value){

                    let tax_rate = zip_code = '';

                    if(value.hasOwnProperty("tax_rate")){
                        tax_rate=value.tax_rate;
                    }
                    if(value.hasOwnProperty("zip_code")){
                        zip_code=value.zip_code;
                    }

                    let event_data={
                        "calendar_id": value.id,
                        "title": value.summary,
                        "maintenance_bypass": value.maintenance_bypass,
                        "chemical-bypass": value.chemical_bypass,
                        "prospect_event": value.prospect_event,
                        "zip-code": zip_code,
                        "sale-tax": tax_rate,
                        "phone-no": value.phone_no,
                        "client-email": value.client_email,
                        "client-name": value.client_name,
                        "client-location": value.location,
                        "recurring-client": value.recurring,
                        "lead-source": value.lead_source,
                        "service_fee": value.service_fee,
                        "payment_method": value.payment_method,
                        "tax_exempt": value.tax_exempt,
                        "invoice_label": value.invoice_label,
                        "staff_invoice":value.staff_invoice
                    }

                    event_data=JSON.stringify(event_data);

                    events_html+=`
                        <option value='${event_data}'>${value.summary}</option>
                    `;

                });
            }

            if(ref_element == ""){
                jQuery('.calendar_events').html(events_html);
            }
            else{
                jQuery(ref_element).html(events_html);
            }
            
            if(emp_id != ''){
                $('#invoice_flow_get_events').val(appointement_date);
            }
        }
    });

}

function generateDocsHtml(docs){
    try {
        docs = jQuery.parseJSON(docs);

        console.log(docs);

        docs_html = `
            <table class='table table-striped table-hover'>
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>File Url</th>
                    </tr>
                </thead>
                <tbody>
            `;

        jQuery.each(docs,function(index,value){

            let file_name;
            let file_url;
    
            if("file_name" in value) file_name = value.file_name;
            if("name" in value) file_name = value.name;
    
            if("file_url" in value) file_url = value.file_url;
            if("url" in value) file_url = value.url;

            docs_html += `
                <tr>
                    <td>${file_name}</td>
                    <td><a target='_blank' href='${file_url}'><span><i class='fa fa-eye'></i></span> Show</a></td>
                </tr>
            `;
        });

        docs_html += `
            </tbody>
            </table>            
        `;

        return docs_html;  
    }
    catch (error) {
        return 'No Document Found';
    }
}

function getAnimalCageOfficeNotes(record_id){
    console.log('nonce object in function', fbcs);
    jQuery.ajax({
        type: "post",
        url: fbcs.ajax_url,
        dataType: "json",
        data: {
            action: "get_animal_cage_office_notes",
            record_id,
            "_wpnonce": fbcs.nonce
        },
        beforeSend: function(){
            jQuery('.cage_office_notes').html(`<div class="loader"></div>`);
            jQuery('#cageOfficeNotes').modal('show');
        },
        success: function(data){
            console.log('data returned', data);
            if(data.status === "success"){
                const notes = data.data.notes;
                

                if(notes.length > 0){

                    let note_html = '<ul>';

                    jQuery.each(notes, function(key, value){

                        const note = value.notes.replace(/(?:\r\n|\r|\n)/g, '<br>');

                        note_html+= `
                            <li> - ${note} - ${value.created_at}</li>
                        `;
                    });

                    note_html+= '</ul>';

                    console.log('note_html is' + note_html);
                    jQuery('.cage_office_notes').html(note_html);
                }
                else{
                    jQuery('.cage_office_notes').html('<p>No previous note found</p>');
                }


            }
            else{
                jQuery('.cage_office_notes').html(data.message);
            }

        }
    });
}

function resetFilters(form_id){
    jQuery(`#${form_id} input[type=text]`).val('');
    jQuery(`#${form_id} input[type=date]`).val('');
    jQuery(`#${form_id} input[type=week]`).val('');
    jQuery(`#${form_id} .select2-field`).val(null).trigger('change');
}

function arrayToCsvDownload(data, name){
    let csvHeader = "data:text/csv;charset=utf-8," 
    let csvBody = data.map(e => e.join("\t")).join("\n");
    csvBody = csvBody.replaceAll('#', '');
    let csvContent = csvHeader + csvBody;
    var encodedUri = encodeURI(csvContent);
    var link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `${name}.csv`);
    document.body.appendChild(link); // Required for FF

    link.click(); // This will download the data file named "my_data.csv".
}

// for chanding alert behaviour
window.alert = function(msg, title, type, params) {
    var title = (title == null) ? 'Aviso' : title;
    var type = (type == null) ? 'warning' : type;
    new swal({
        html: msg
    });
};

const showLoader = (message) => {
    swal.fire({
        html: `<div>${message}</div>`,
        showConfirmButton: false,
        allowOutsideClick: false
    });
}

const removeTrainingVideo = () => {
    let video = document.getElementById("gamTrainingVideoPlayer");
    video.pause();
    video.currentTime = 0;
}

const viewVideo = (videoKey) => {
    let video_html;
    let MaterialVideoModal = jQuery("#diplayTrainingMaterialVideoModal");
    jQuery.ajax({
        type: "post",
        url: fbcs.ajax_url,
        dataType: "json",
        data: {
            action: "aws_s3_get_object_url",
            object_key: videoKey,
            "_wpnonce": fbcs.nonce
        },
        beforeSend: function(){
            showLoader('fetching video, please wait...');
        },
        success: function(data){
            if(data.status === "success"){
                swal.close();
                video_html = `
                    <video id="gamTrainingVideoPlayer" width="100%" height="240" controls autoplay>
                        <source src="${data.data.object_url}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                `;
                MaterialVideoModal.find('.modal-body').html(video_html);
                MaterialVideoModal.modal('show');
            }
            else{
                new swal('Oops!', data.message, 'error');
            }
        },
        error : function(){
            new swal('Oops!', 'Something went wrong, please try again later');
        }
    })
}