<?php

$calendars = Calendar::getSystemCalendars();
$callrail_accounts = (new Callrail_new)->getAllCallrailAccounts();

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Create Branch</h3>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="create_branch_form" action="<?= admin_url('admin-post.php'); ?>" method="post">

                        <?php wp_nonce_field('create_branch'); ?>

                        <input type="hidden" name="action" value="create_branch">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        
                        <!-- Branch Name  -->
                        <div class="form-group">
                            <label for="branch_name">Branch Name</label>
                            <input type="text" class="form-control" name="branch_name" id="branch_name">
                        </div>

                        <!-- Callrail Account No.  -->
                        <div class="form-group">
                            <label for="callrail_id">Callrail Account</label>
                            <select name="callrail_id" id="callrail_id" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($callrail_accounts) && count($callrail_accounts) > 0): ?>
                                    <?php foreach($callrail_accounts as $callrail_account): ?>
                                        <option value="<?= $callrail_account->id; ?>"><?= $callrail_account->email; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Review Link -->
                        <div class="form-group">
                            <label for="review_link">Review Link <small><i>(if not provided, new york branch review link will be used)</i></small></label>
                            <input type="text" class="form-control" name="review_link" id="review_link">
                        </div>

                        <!-- Tekcard Key -->
                        <div class="form-group">
                            <label for="tekcard_key">Tekcard Key</label>
                            <input type="password" class="form-control" name="tekcard_key" id="tekcard_key">
                        </div>

                        <!-- Branch Calendar -->
                        <div class="form-group">
                            <label for="calendar_id">Branch Calendar</label>
                            <select name="calendar_id" id="calendar_id" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($calendars) && count($calendars) > 0): ?>
                                    <?php foreach($calendars as $calendar): ?>
                                        <option value="<?= $calendar->id; ?>"><?= $calendar->email; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Branch Address</label>
                            <input type="text" class="form-control" name="address">
                        </div>

                        <!-- SUBMIT BUTTON  -->
                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Branch</button>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#create_branch_form').validate({
                rules: {
                    branch_name: "required",
                    callrail_id: "required",
                    tekcard_key: "required",
                    calendar_id: "required",
                    address: "required",
                }
            })
        });
    })(jQuery);
</script>