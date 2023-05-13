<?php
    $calendars = Calendar::getSystemCalendars();
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <h3 class="page-header">System Calendars</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>name</th>
                                <th>Email</th>
                                <th>Token Path</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($calendars) && count($calendars)>0): ?>
                                <?php foreach($calendars as $calendar): ?>
                                    <tr>
                                        <td><?= $calendar->name; ?></td>
                                        <td><?= $calendar->email; ?></td>
                                        <td><?= $calendar->token_path; ?></td>
                                        <td><?= date('d M Y',strtotime($calendar->date_created)); ?></td>
                                        <th>
                                        <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                    <li><a onclick="updateAccessToken(<?= $calendar->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-edit"></i></span> Update Access Token</a></li>
                                                </ul>
                                        </div>
                                        </th>
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

<!-- UPDATE ACCESS TOKEN MODAL -->
<div id="updateAccessTokenModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Update Access Token</h4>
            </div>
            <div class="modal-body">
                <form id="updateAccessTokenForm" action="<?= admin_url('admin-post.php') ?>" method="post">
                                    
                    <?php wp_nonce_field('update_access_token'); ?>
                    <input type="hidden" name="action" value="update_access_token">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <input type="hidden" name="calendar_id">

                    <div class="form-group">
                        <label for="">Calendar Auth Code</label>
                        <p><a target="_blank" href="<?= (new Calendar)->createAuthUrl(); ?>">Click Here</a> to get redirected to google calendar authorisation page and authorise calendar in order to get the auth code</p>
                        <input type="text" class="form-control" name="auth_code">
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Update Access Token</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<script>
    function updateAccessToken(calendar_id){
        jQuery('#updateAccessTokenForm input[name="calendar_id"]').val(calendar_id);
        jQuery('#updateAccessTokenModal').modal('show');
    }

    (function($){
        $('#updateAccessTokenForm').validate({
            rules: {
                auth_code: {
                    required: true,
                    minlength: 62
                }
            }
        })
    })(jQuery);
</script>