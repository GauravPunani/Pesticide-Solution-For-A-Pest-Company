<?php

/* Template Name: Termite Graph */
get_header();
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <!-- TERMITE GRAPH FORM  -->
            <form class="res-form" enctype="multipart/form-data" method="post" id="termite_graph_form" action="<?= admin_url('admin-post.php'); ?>">
                <h2 class="text-center form-head">Termite Graph Work</h2>
                <?php wp_nonce_field('termite_graph_work'); ?>
                <?php (new GamFunctions)->getFlashMessage(); ?>
                <input type="hidden" name="action" value="termite_graph_new">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                <div class="form-group">
                    <label for="">Client Name</label>
                    <input type="text" class="form-control" name="client_name">
                </div>

                <div class="form-group">
                    <label for="">Client Email</label>
                    <input type="email" class="form-control" name="client_email">
                </div>

                <div class="form-group">
                    <label for="">Please upload termite graph file</label>
                    <input type="file" class="form-control" name="termite_graph" accept="image/x-png,image/gif,image/jpeg,image/jpg">
                </div>

                <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Upload File & submit</button>
            </form>
        </div>
    </div>
</div>


<script>
    (function($){
        $(document).ready(function(){

            $('#termite_graph_form').validate({
                rules:{
                    client_name:"required",
                    client_email:"required",
                    termite_graph:"required"
                }
            });
        })
    })(jQuery);
</script>

<?php
get_footer(); ?>