<div class="container-fluid">
    

        <form method="post" action="options.php">
            <?php settings_fields( 'gam-settings' ); ?>
            <?php do_settings_sections( 'gam-settings' ); ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="card full_width table-responsive">
                        <div class="card-body">
                            <h3 class="page-header">Social Media Links</h3>
                            <div class="form-group">
                                <label for="">Facebook</label>
                                <input type="text" class="form-control" name="facebook_url" value="<?php echo esc_attr( get_option('facebook_url') ); ?>">
                            </div>
                            <div class="form-group">
                                <label for="">Twitter</label>
                                <input type="text" class="form-control" name="twitter_url" value="<?php echo esc_attr( get_option('twitter_url') ); ?>">
                            </div>
                            <div class="form-group">
                                <label for="">Instagram</label>
                                <input type="text" class="form-control" name="instagram_url" value="<?php echo esc_attr( get_option('instagram_url') ); ?>">
                            </div>
                            <?php submit_button(); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card full_width table-responsive">
                        <div class="card-body">
                            <h3 class="page-header">Company Information</h3>

                            <div class="form-group">
                                <label for="">Company Name</label>
                                <input type="text" class="form-control" name="gam_company_name" value="<?php echo esc_attr( get_option('gam_company_name') ); ?>">
                            </div>

                            <div class="form-group">
                                <label for="">Main Address</label>
                                <p><small><i>Used as fallback address for branches for which there is no address.</i></small></p>
                                <input type="text" class="form-control" name="gam_main_address" value="<?php echo esc_attr( get_option('gam_main_address') ); ?>">
                            </div>

                            <div class="form-group">
                                <label for="">Residential Quote Code Phone Number</label>
                                <p><small><i>Technician will call to this phone number when he needs code from office in order to submit residential contract</i></small></p>
                                <input type="text" class="form-control" name="gam_company_phone_no" value="<?php echo esc_attr( get_option('gam_company_phone_no') ); ?>">
                            </div>

                            <div class="form-group">
                                <label for="">Company Email</label>
                                <input type="text" class="form-control" name="gam_company_email" value="<?php echo esc_attr( get_option('gam_company_email') ); ?>">
                            </div>

                            <?php submit_button(); ?>                        

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">

                <div class="col-md-6">
                    <div class="card full_width table-responsive">
                        <div class="card-body">
                            <h3 class="page-header">Office Timing</h3>

                            <div class="form-group">
                                <label for="">Start Time</label>
                                <input class="form-control" type="time" name="gam_office_start_time" value="<?php echo esc_attr( get_option('gam_office_start_time') ); ?>">
                            </div>

                            <div class="form-group">
                                <label for="">End Time</label>
                                <input class="form-control" type="time" name="gam_office_end_time" value="<?php echo esc_attr( get_option('gam_office_end_time') ); ?>">
                            </div>

                            <?php submit_button(); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card full_width table-responsive">
                        <div class="card-body">
                            <h3 class="page-header">Sendgrid Details</h3>

                            <div class="form-group">
                                <label for="">Default Email Template ID</label>
                                <input type="text" class="form-control" name="gam_sg_template_id" value="<?php echo esc_attr( get_option('gam_sg_template_id') ); ?>">
                            </div>

                            <div class="form-group">
                                <label for="">Email Sending API Key</label>
                                <input type="text" class="form-control" name="gam_email_api_key" value="<?php echo esc_attr( get_option('gam_email_api_key') ); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="">Email Validation Api Key</label>
                                <input type="text" class="form-control" name="gam_email_validation_api_key" value="<?= get_option('gam_email_validation_api_key'); ?>">
                            </div>

                            <div class="form-group">
                                <label for="">ASM ID</label>
                                <input type="text" class="form-control" name="gam_sg_asm_id" value="<?= get_option('gam_sg_asm_id'); ?>">
                            </div>

                            <?php submit_button(); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card full_width table-responsive">
                        <div class="card-body">
                            <h3 class="page-header">AWS S3 Bucket Details</h3>

                            <div class="form-group">
                                <label for="">S3 bucket api key</label>
                                <input type="text" class="form-control" name="gam_s3bucket_api_key" value="<?php echo esc_attr( get_option('gam_s3bucket_api_key') ); ?>">
                            </div>

                            <div class="form-group">
                                <label for="">S3 bucket access key</label>
                                <input type="text" class="form-control" name="gam_s3bucket_access_key" value="<?php echo esc_attr( get_option('gam_s3bucket_access_key') ); ?>">
                            </div>

                            <?php submit_button(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    
</div>