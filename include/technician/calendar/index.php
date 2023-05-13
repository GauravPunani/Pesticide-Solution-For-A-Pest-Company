<div class="row">
    <h3 class="page-header">Calendar Events</h3>
    <div class="col-md-6">
        <form id="fetchEventsForm">
            <div class="form-group">
                <label for="">Select Date</label>
                <input type="date" name="date" class="form-control" value="<?= date('Y-m-d'); ?>" max="<?= date('Y-m-d', strtotime('+1 days')); ?>">
            </div>
            <button class="btn btn-primary"><span><i class="fa fa-eye"></i></span> Show Events</button>
        </form>
    </div>
    <br>
    <div class="col-sm-12 calendarEventsBox mt-4"></div>
</div>

<script>

    (function($){
        $(document).ready(function(){
            $('#fetchEventsForm').validate({
                rules: {
                    date: "required"
                },
                submitHandler: function(){
                    const date = $('#fetchEventsForm input[name="date"]').val();
                    renderPendingEventsList(date);
                    return false;
                }
            })
        })
    })(jQuery);

    const renderPendingEventsList = (date) => {
        jQuery.ajax({
            type: 'post',
            url: "<?= admin_url('admin-ajax.php'); ?>",
            data:{
                action: "getTechnicianCalendarEventsListing",
                "_wpnonce": "<?= wp_create_nonce('getTechnicianCalendarEventsListing'); ?>",
                date
            },
            dataType: 'html',
            beforeSend: function(){
                jQuery('.calendarEventsBox').html('<div class="loader"></div>');
            },
            success: function(events_listing){
                jQuery('.calendarEventsBox').html(events_listing);
            },
            error: function(){
                jQuery('.calendarEventsBox').html(`<p class="text-danger">Something went wrong, please try again later</p>`);
            }
        })
    }

</script>