<div class="container">
    <div class="row">
        <div class="col-sm-12 col-md-offset-2 col-md-8">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <div class="form-group">
                        <label for="">Select Week</label>
                        <input type="week" class="form-control" name="week">
                    </div>
                    <div class="pending-or-genreate-button-area"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

(function($){
    $(document).ready(function($){

        $(document).on('submit','#weekly_alert_generate_form',function(e){
            e.preventDefault();
            $('.submit_btn').attr('disabled',true);
            $(this)[0].submit(); 
        })

        $('input[name="week"]').on('change',function(){

            let week=$(this).val();

            $.ajax({
                    type:"post",
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    dataType:"html",
                    data:{
                        action:"get_week_unattributed_invoices",
                        week:week,
						"_wpnonce": "<?= wp_create_nonce('get_week_unattributed_invoices'); ?>"
                    },
                    beforeSend:function(){
                        $('.pending-or-genreate-button-area').html('<div class="loader"></div>');
                    },
                    success:function(data){
                        $('.pending-or-genreate-button-area').html(data);
                    },
                    error : function(XMLHttpRequest, textStatus, errorThrown) {
                        console.log(errorThrown);
                    }                    
                });
        });
    })
})(jQuery);

</script>