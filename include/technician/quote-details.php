<?php
global $wpdb;
$quote=$wpdb->get_row("
select Q.*, TD.first_name, TD.last_name
from {$wpdb->prefix}quotesheet Q
left join {$wpdb->prefix}technician_details TD
on Q.technician_id = TD.id
where id='{$_GET['quote_id']}'");
?>

<?php if($quote): ?>
    <div class="row">
        <div class="col-md-offset-2 col-md-8">

            <p class="text-right"><button class="btn btn-primary" data-toggle="modal" data-target="#codeverification"><span><i class="fa fa-edit"></i></span> Edit Quote</button></p>
        
            <h1 class="text-center">Quote #<?= $quote->id; ?></h1>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <tr>
                        <th>Client Name</th>
                        <td><?= $quote->clientName; ?></td>
                    </tr>
                    <tr>
                        <th>Client Address</th>
                        <td><?= $quote->clientAddress; ?></td>
                    </tr>
                    <tr>
                        <th>Client Phone</th>
                        <td><?= $quote->clientPhn; ?></td>
                    </tr>
                    <tr>
                        <th>Client Email</th>
                        <td><?= $quote->clientEmail; ?></td>
                    </tr>
                    <tr>
                        <th>Technician Name</th>
                        <td><?= $quote->first_name." ".$quote->last_name; ?></td>
                    </tr>
                </table>
                <table class="table table-striped table-hover">
                    <caption>Services</caption>
                    <tr>
                        <th>Service Provided</th>
                        <th>Price</th>
                    </tr>
                    <?php
                        $services=json_decode($quote->service);
                        $items=json_decode($quote->items);
                        // echo "<pre>";print_r($items);wp_die();

                        if($services){
                            foreach($services as $key=> $val){
                                ?>
                                <tr>
                                    <td><?= $val->service; ?></td>
                                    <td><?= $val->price; ?></td>
                                </tr>
                                <?php
                            }
                        }
                    ?>
                </table>
                <table class="table table-striped table-hover">
                    <caption sytle="">Items</caption>
                    <tr>
                        <th>Material</th>
                        <th>Amount</th>
                    </tr>
                    <?php if($items): ?>
                        <?php foreach($items as $key=>$val): ?>
                            <tr>
                                <td><?= $val->material; ?></td>
                                <td><?= $val->material_price; ?></td>
                            </tr>

                        <?php endforeach; ?>
                    <?php endif; ?>

                </table>
                <table class="table table-striped table-hover">
                    <tr>
                            <th>Total Cost</th>
                            <td><?= $quote->total_cost; ?></td>
                    </tr>
                    <tr>
                            <th>DISCOUNT WITH MAINTENANCE PLAN $</th>
                            <td><?= $quote->discount_with_plan; ?></td>
                    </tr>
                    <tr>
                            <th>MAINTENANCE PRICE PER MONTH</th>
                            <td><?= $quote->maintenance_price; ?></td>
                    </tr>
                    <tr>
                            <th>START DATE:</th>
                            <td><?= date('d M Y',strtotime($quote->start_date)); ?></td>
                    </tr>
                    <tr>
                            <th>END Date</th>
                            <td><?= date('d M Y',strtotime($quote->end_date)); ?></td>
                    </tr>
                    <tr>
                            <th>Comment</th>
                            <td><?=  $quote->comment; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
        <!-- Modal -->
    <div id="codeverification" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Quote</h4>
            </div>
            <div class="modal-body">
                <div class="error-box"></div>
                <div class="confirmation-box">
                    <form action="" id="confirmation_form">
                        <?php wp_nonce_field('insert_technician_edit_code'); ?>
                        <input type="hidden" name="action" value="insert_technician_edit_code">
                        <input type="hidden" name="type" value="residential_quote">
                        <input type="hidden" name="id" value="<?= $_GET['quote_id']; ?>">
                        <input type="hidden" name="name" value="<?= trim($user->first_name." ".$user->last_name); ?>">
                        <p>You need permission from office by requesting a code to edit quote</p>
                        <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> <span id="confirm_submit_btn">Request Code</span></button>                
                            
                    </form>
                </div>
                <div class="verification-box hidden">
                    <form action="" id="code_verification_form">
                        <?php wp_nonce_field('verify_technician_edit_code'); ?>
                        <input type="hidden" name="action" value="verify_technician_edit_code">
                        <input type="hidden" name="type" value="residential_quote">
                        <input type="hidden" name="id" value="<?= $_GET['quote_id']; ?>">
                        <input type="hidden" name="name" value="<?= trim($user->first_name." ".$user->last_name); ?>">
                        <div class="form-group">
                                <label for="">Please enter the verification code</label>
                                <input type="text" name="code" maxlength="6" class="form-control">
                        </div>
                        <button id="verification_submit_btn" class="btn btn-primary">Verify & Submit</button>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
            </div>

        </div>
    </div>    

<?php endif; ?>

?>
