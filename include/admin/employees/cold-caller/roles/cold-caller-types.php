<?php

global $wpdb;

$cold_caller_types = $wpdb->get_results("select * from {$wpdb->prefix}cold_caller_types");

?>

<div class="container">
    <div class="row">
        <?php (new GamFunctions)->getFlashMessage(); ?>
        <div class="col-sm-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Cold Caller Types</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($cold_caller_types) && count($cold_caller_types) > 0): ?>
                                <?php foreach($cold_caller_types as $cold_caller_type): ?>
                                    <tr>
                                        <td><?= $cold_caller_type->name; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Create New Type</h3>
                    <form id="createTypeForm" action="<?= admin_url('admin-post.php') ?>" method="post">
                        
                        <?php wp_nonce_field('cc_create_cc_type'); ?>
                        <input type="hidden" name="action" value="cc_create_cc_type">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                        <div class="form-group">
                            <label for="">Title</label>
                            <input type="text" class="form-control" name="title">
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Type</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    (function($){
        $(document).ready(function(){
            $('#createTypeForm').validate({
                rules:{
                    title: "required"
                }
            })
        })
    })(jQuery);
</script>