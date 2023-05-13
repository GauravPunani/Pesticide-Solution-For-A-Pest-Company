<?php

global $wpdb;

$quote=$wpdb->get_row("select * from {$wpdb->prefix}quotesheet where id='{$_SESSION['residential_quote_editable']['id']}'");
$callrail_traking_numbers=(new Callrail_new)->get_all_tracking_no();


?>

<?php if($quote): ?>
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <h1 class="text-center">Residential Quote</h1>
            <form action="<?= admin_url('admin-post.php'); ?>" method="post" id="residential_quote_update">

                <input type="hidden" name="action" value="residential_quote_update">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="quote_id" value="<?= $_GET['quote_id']; ?>">

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <tr>
                            <th>Client Name</th>
                            <td><input type="text" name="name" class="form-control" value="<?= $quote->clientName; ?>"></td>
                        </tr>
                        <tr>
                                <th>Technician Quote Name</th>
                                <td><input type="text" maxlength="100" value="<?= $quote->tech_diff_name; ?>" class="form-control tech_diff_name"  name="tech_diff_name"></td>
                        </tr>
                        <tr>
                            <th>Client Address</th>
                            <td><textarea name="address" id="" cols="30" rows="5" class="form-control"><?= $quote->clientAddress; ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Client Phone</th>
                            <td><input type="text" name="phone" class="form-control" value="<?= $quote->clientPhn; ?>"></td>
                        </tr>
                        <tr>
                            <th>Client Email</th>
                            <td><input type="email" name="email" id="" value="<?= $quote->clientEmail; ?>" class="form-control"></td>
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
                            <th>$Per Unit</th>
                            <th>Total $</th>
                        </tr>
                        <?php if($items): ?>
                            <?php foreach($items as $key=>$val): ?>
                                <tr>
                                    <td><?= $val->material; ?></td>
                                    <td><?= $val->material_price; ?></td>
                                    <td><?= @$val->material_perunit; ?></td>
                                    <td><?= @$val->material_total; ?></td>
                                </tr>

                            <?php endforeach; ?>
                        <?php endif; ?>

                    </table>

                    <table class="table table-hover table-striped">
                        <tr>
                                <th>Total Cost</th>
                                <td><input type="text" name="total_cost" class="form-control" value="<?= $quote->total_cost; ?>"></td>
                        </tr>
                        <tr>
                                <th>Discount with maintenance plan $</th>
                                <td><input type="text" name="discount_with_maintenance_plan" class="form-control" value="<?= $quote->discount_with_plan; ?>"></td>
                        </tr>
                        <tr>
                                <th>Maintenance price per month</th>
                                <td><input type="text" name="price_per_month" class="form-control" value="<?= $quote->maintenance_price; ?>"></td>
                        </tr>
                        <!--<tr>
                                <th>Comment</th>
                                <td><textarea name="comment" id="" cols="30" rows="10" class="form-control"><?=  $quote->comment; ?></textarea></td>
                        </tr>-->
                        <tr>
                                <th>Callrail Tracking No.</th>
                                <td>
                                    <?php if(is_array($callrail_traking_numbers) && count($callrail_traking_numbers)>0): ?>
                                        <select name="callrail_id" class="form-control">
                                        <option value="">Select</option>
                                        <?php foreach($callrail_traking_numbers as $key=>$val): ?>
                                            <option value="<?= $val->id; ?>" <?= $quote->callrail_id==$val->id ? "selected" : '';  ?> ><?= $val->tracking_phone_no; ?> - <?= $val->tracking_name; ?></option>
                                        <?php endforeach; ?>
                                        </select>
                                    <?php endif ?>
                                </td>   
                        </tr>
                    </table>

                    
                </div>

                <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Quote</button>
            </form>
        </div>
    </div>
<?php else: ?>
<h3>No Quote Found</h3>
<?php endif; ?>