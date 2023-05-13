<?php
$staff_invoice = false;
if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'staff_invoice') {
    $staff_invoice = true;
    $tech_id = (new GamFunctions)->encrypt_data(@$_GET['tech'],'d');
    $date = @$_GET['date'];
    $event_id = @$_GET['event_id'];
}
?>

<?php if($staff_invoice) {
    $invoice_event_data = [
        'tech_id' => $tech_id,
        'event_date' => $date,
        'event_id' => $event_id
    ];
    $event_found = (new Invoice)->isInvoiceWithCalendarEventExist((object) $invoice_event_data);
    if($event_found){
        echo '<div class="alert alert-success"><strong><i class="fa fa-check"></i> Invoice successfully created in system no further action is needed.</strong></div>';
        wp_localize_script('frontend-backend-common-script', 'staff_event_id', ['invoice_found' => true]);
        return false;
    }
}
?>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <form method="post" id="calendar_event_form" action="<?= admin_url('admin-post.php'); ?>" class="res-form">
                <h3 class="page-header text-center">Select Calendar Event</h3>
                <input type="hidden" name="action" value="set_event_details">
                <?php if($staff_invoice) : ?>
                    <input type="hidden" name="office_staff_invoice" value=<?= json_encode([
                        'tech_id' => $tech_id,
                        'date' => $date,
                        'event_id' => $event_id
                    ]);?>>
                <?php endif;?>
                
                <div class="event_error text-danger text-left hidden"></div>

                <!-- EVENT DATE  -->
                <div class="form-group">
                    <label for="date"><span><i class="fa fa-calendar"></i></span> Calendar Event Date<span class="text-danger"><small>*</small></span></label>
                    <input class="form-control" id="invoice_flow_get_events" max="<?= date('Y-m-d'); ?>" name="event_date" type="date">
                </div>
                <!-- CALENDAR EVENTS -->
                <div class="form-group appointment_div">
                    <label for=" name">Select Appointment <span class="text-danger"><small>*</small></span></label>
                    <select name="event_data" class="form-control calendar_events">
                        <option value="">Select</option>
                    </select>
                </div>

                <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit & Continue</button>

            </form>
        </div>
    </div>
</div>

<script>
    (function($) {
        $(document).ready(function() {
            $('#calendar_event_form').validate({
                rules: {
                    event_date: "required",
                    event_data: "required",
                },
                submitHandler: function(form) {
                    let obj = $(form).attr('id');
                    event_list = $('#' + obj).find('.calendar_events').val();
                    ofc_inv = $('#' + obj).find('input[name=\'office_staff_invoice\']');
                    result = $.parseJSON(event_list);
                    result['event_date'] = $('#invoice_flow_get_events').val();
                    if (result.staff_invoice && !ofc_inv.length) {
                        jQuery.ajax({
                            type: "post",
                            url: fbcs.ajax_url,
                            dataType: "json",
                            data: {
                                action: "get_event_processed_employee",
                                event_data: result,
                            },
                            beforeSend: function() {
                                showLoader('Processing please wait ...');
                            },
                            success: function(data) {
                                if (data.status === "success") {
                                    new swal('Hurray!', data.message, 'success').then(() => {
                                        window.location.reload();
                                    })
                                } else {
                                    new swal('Oops!', data.message, 'error');
                                }
                            },
                            error: function() {
                                new swal('Oops!', 'Something went wrong, please try again later');
                            }
                        });
                        return false;
                    }else{
                        form.submit();
                    }
                }
            });
        });
    })(jQuery);
</script>