<?php
global $wpdb;
$quote_id=$args['data'];
$quote=$wpdb->get_row("
    select Q.*, TD.first_name, TD.last_name
    from {$wpdb->prefix}quotesheet Q
    left join {$wpdb->prefix}technician_details TD
    on Q.technician_id = TD.id
    where Q.id='$quote_id'
");
?>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php if($quote): ?>
                        <h3 class="page-header">Residential Quote</h3>
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
                                <th>Technician</th>
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
                                if($services){
                                    foreach($services as $key=> $val){
                                        ?>
                                        <tr>
                                            <td><?= $val->service; ?></td>
                                            <td>$<?= $val->price; ?></td>
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
                                <td>$<?= $quote->total_cost; ?></td>
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
                                <th>Client Notes From Technician</th>
                                <td><?= nl2br($quote->notes_for_client); ?></td>
                            </tr>
                            <tr>
                                <th>Office Notes From Technician</th>
                                <td><?= nl2br($quote->tech_notes_for_office); ?></td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <h3 class="text-center text-danger">No Record Found</h3>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


