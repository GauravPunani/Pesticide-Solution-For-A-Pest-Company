<div class="container res-form">
    <div class="row">
        <div class="col-sm-12">
            <?php (new GamFunctions)->getFlashMessage(); ?>
            <form action="<?= admin_url('admin-post.php'); ?>" method="post" enctype='multipart/form-data'>
                <h2 class="text-center">Normal Notes</h2>
                
                <div class="notice notice-info">
                    <p>Normal notes do not list any special notes event to upload notes for. In order to upload special notes, use "special notes" page. Special notes are required for @sn event in order to clear the event from your account.</p>
                </div>


                <input type="hidden" name="action" value="office_notes">
                <input type="hidden" name="type" value="invoice">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                <div class="form-group">
                    <label for="">Select a Date</label>
                    <input type="date" class="form-control" max="<?= date('Y-m-d'); ?>" name="date" required>
                </div>

                <div class="form-fields-area"></div>

            </form>
        </div>
    </div>
</div>

<script>
(function($){
    $(document).ready(function(){
        $('input[name="date"]').on('change',function(){
            let date=$(this).val();

            // call ajax for date client and notes box or error if events are not done yet for the date 
            $.ajax({
                type:"post",
                url:"<?= admin_url('admin-ajax.php'); ?>",
                data:{
                    action:"get_normal_notes_input",
                    date,
					"_wpnonce": "<?= wp_create_nonce('get_normal_notes_input'); ?>"
                },
                dataType:"html",
                beforeSend:function(){
                    $('.form-fields-area').html("<div class='loader'></div>");
                },
                success:function(data){
                    $('.form-fields-area').html(data);
                }
            })
        })
    })
})(jQuery);
</script>