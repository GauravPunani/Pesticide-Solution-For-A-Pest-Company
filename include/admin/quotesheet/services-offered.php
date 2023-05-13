<?php

global $wwpdb;

$services_offered = (new Quote)->servicesOffered();

?>

<div class="container">
    <div class="row">
        <?php (new GamFunctions)->getFlashMessage(); ?>
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Tags</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($services_offered) && count($services_offered) > 0): ?>
                                <?php foreach($services_offered as $service): ?>
                                    <tr>
                                        <td><?= $service->name; ?></td>
                                        <td><?= $service->tags; ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                    <li><a onclick="addTags(<?= $service->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-plus"></i></span> Add Tags</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SERVICE TAGS MODAL  -->
<div id="tagsModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add Service Offered Tags</h4>
            </div>
            <div class="modal-body">
                <form id="tagsForm" action="<?= esc_url( admin_url('admin-post.php') ); ?>" method="post">
                    
                    <?php wp_nonce_field('quote_add_service_tags'); ?>
                    <input type="hidden" name="action" value="quote_add_service_tags">
                    <input type="hidden" name="service_id">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <div class="form-group">
                        <select name="tags[]" class="form-control select2-tags" multiple>
                            <option value="roaches">Roches</option>
                            <option value="crawling_insect">Crawling Insect</option>
                            <option value="general_spray">General Spray</option>
                            <option value="flying_insect">Flying Insect</option>
                            <option value="termite">Termite</option>
                            <option value="rodents">Rodents</option>

                        </select>
                    </div>

                    <button  class="btn btn-primary"><span><i class="fa fa-comment"></i></span> Add Tags</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><span><i class="fa fa-times"></i></span> Close</button>
            </div>
        </div>

    </div>
</div>

<script>
    const addTags = (service_id) => {
        jQuery('#tagsModal').modal('show');
        jQuery('#tagsForm input[name="service_id"]').val(service_id);
    }

    (function($){
        $(document).ready(function(){
            $(".select2-tags").select2({
                width: '100%',
                tags: true
            });
        })
    })(jQuery);
</script>