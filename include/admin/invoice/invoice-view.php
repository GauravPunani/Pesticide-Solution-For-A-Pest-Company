<?php

global $wpdb;

if (!isset($_GET['invoice_id'])  || empty($_GET['invoice_id'])) {
    echo "<h1>No Record Found";
    exit();
}

$invoice_id = esc_html($_GET['invoice_id']);

$invoice = $wpdb->get_row("
        select * from 
        {$wpdb->prefix}invoices 
        where id= '$invoice_id'
    ");
$tech_name = (new Technician_details)->getTechnicianName($invoice->technician_id);
$branch_name = (new Branches)->getBranchName($invoice->branch_id);
?>
<?php if ($invoice && !is_null($invoice)) : ?>
    <div class="invoice_table_wrapper">
        <h1>Invoice</h1>
        <div class="table-responsive">
            <table class="invoice_table">
                <tr>
                    <th>Client Name</th>
                    <td><?= $invoice->client_name; ?></td>
                </tr>
                <tr>
                    <th>Telephone number</th>
                    <td><?= $invoice->phone_no; ?></td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td><?= $invoice->address; ?></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td><?= $invoice->date; ?></td>

                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= $invoice->email; ?></td>
                </tr>
                <tr>
                    <th>Service fee</th>
                    <td>$<?= $invoice->service_fee; ?></td>
                </tr>
                <tr>
                    <th>Notes</th>
                    <td><?= nl2br($invoice->client_notes); ?></td>
                </tr>
                <tr>
                    <th>Findings</th>
                    <td><?= $invoice->findings; ?></td>
                </tr>
                <tr>
                    <th>Service Description </th>
                    <td><?= $invoice->service_description; ?></td>
                </tr>
                <tr>
                    <th>Area of service/inspection </th>
                    <td><?= $invoice->area_of_service; ?></td>
                </tr>
                <tr>
                    <th>Is warranty included ?</th>
                    <td><?= !empty($invoice->warranty_explanation) ? $invoice->warranty_explanation: '---'; ?></td>
                </tr>
                <tr>
                    <th>Uploaded Images</th>
                    <td>
                        <?php if (!empty($invoice->optional_images)) : $images = json_decode($invoice->optional_images);
                        ?>
                            <div class="invoice_optional_images row">
                                <?php for ($i = 0; $i < count($images); $i++) : ?>
                                    <div class="col-md-3">
                                        <a target="_blank" href=<?= $images[$i]->url; ?>><img style="cursor:zoom-in;height:100px;width:100px" src="<?= $images[$i]->url; ?>">
                                        </a>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php 
        if (!empty($invoice->reservice_id)) : $reserviceData = (new Invoice)->getReserviceData($invoice->reservice_id);?>
			<table class='table table-striped table-hover'>
					<h1>Reservice Information</h1>
					<tbody>
						<tr>
							<th>Total Reservices Recommended</th>
							<td><?= $reserviceData->total_reservices;?></th>
						</tr>
						<tr>
							<th>Reservice Frequency</th>
							<td>Every <?= $reserviceData->revisit_frequency_unit;?>  <?= $reserviceData->revisit_frequency_timeperiod;?></th>
						</tr>
						<tr>
							<th>Reservice Fee</th>
							<td> <?= (new GamFunctions)->beautify_amount_field($reserviceData->reservice_fee);?></th>
						</tr>
					</tbody>
				</table>
		<?php endif;?>

        <table class="invoice_table invoice_mid_tbl">
            <tr class="invoice_mid_tbl_tr">
                <th></th>
                <th>Units</th>
                <th>Price Per Unit</th>
                <th>Total</th>
            </tr>
            <?php $product = json_decode($invoice->product_used, true); ?>
            <?php if (array_key_exists('0', (array)$product)) : ?>
                <?php foreach ($product as $key => $val) : ?>
                    <tr>
                        <th class="table-hd"><?= $val['name']; ?></th>
                        <td><?= $val['Unit']; ?></td>
                        <td>$<?= $val['Price']; ?></td>
                        <td>$<?= $val['Total']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <th class="table-hd">Bait station</th>
                    <td><?= $product['bail_stationUnit']; ?></td>
                    <td><?= $product['bail_stationPrice']; ?></td>
                    <td> <?= $product['bail_stationTotal']; ?></td>
                </tr>

                <tr>
                    <th class="table-hd">Glue boards</th>
                    <td> <?= $product['glue_boardUnit']; ?></td>
                    <td> <?= $product['glue_boardPrice']; ?></td>
                    <td> <?= $product['glue_boardTotal']; ?></td>
                </tr>
                <tr>
                    <th class="table-hd">Mouse snap traps</th>
                    <td> <?= $product['mouse_snapUnit']; ?></td>
                    <td> <?= $product['mouse_snapPrice']; ?></td>
                    <td> <?= $product['mouse_snapTotal']; ?></td>
                </tr>
                <tr>
                    <th class="table-hd">Rat snap traps</th>
                    <td><?= $product['rat_snapUnit']; ?></td>
                    <td><?= $product['rat_snapPrice']; ?></td>
                    <td><?= $product['rat_snapTotal']; ?></td>
                </tr>
                <tr>
                    <th class="table-hd">Hole sealing</th>
                    <td><?= $product['hole_sealUnit']; ?></td>
                    <td><?= $product['hole_sealPrice']; ?></td>
                    <td> <?= $product['hole_sealTotal']; ?></td>
                </tr>
                <tr>
                    <th class="table-hd">Tin cats</th>
                    <td> <?= $product['tin_catsUnit']; ?></td>
                    <td> <?= $product['tin_catsPrice']; ?></td>
                    <td> <?= $product['tin_catsTotal']; ?></td>
                </tr>
                <tr>
                    <th class="table-hd">Poison</th>
                    <td> <?= $product['poisonUnit']; ?></td>
                    <td> <?= $product['poisonPrice']; ?></td>
                    <td> <?= $product['poisonTotal']; ?></td>
                </tr>
                <tr>
                    <th class="table-hd">Fogging</th>
                    <td> <?= $product['fogginUnit']; ?></td>
                    <td> <?= $product['fogginPrice']; ?></td>
                    <td> <?= $product['fogginTotal']; ?></td>
                </tr>
                <tr>
                    <th class="table-hd">Other</th>
                    <td><?= $product['other_unit']; ?></td>
                    <td><?= $product['other_price']; ?></td>
                    <td><?= $product['other_total']; ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td></td>
                <td></td>
                <th>Tax</th>
                <td>$<?= $invoice->tax; ?></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <th>Total Amount</th>
                <td>$<?= $invoice->total_amount; ?></< /td>
            </tr>

        </table>
        <?php $upload_dir = wp_upload_dir(); ?>
        <table class="invoice_table">
            <tr>
                <th>Payment Method</th>
                <td><?= ucwords(str_replace('_', ' ', $invoice->payment_method)); ?></td>
            </tr>
            <tr>
                <th>Check Picture</th>
                <td>
                    <?php if ($invoice->payment_method == "check" && !empty($invoice->check_image)) : ?>
                        <a target="_blank" class="btn btn-primary" href="<?= $invoice->check_image; ?>"><span><i class="fa fa-eye"></i></span> View</a>
                    <?php else : ?>
                        N/A
                    <?php endif; ?>

                </td>
            </tr>
            <tr>
                <th>Technician Name</th>
                <td><?= $tech_name; ?></td>
            </tr>
            <tr>
                <th>Branch</th>
                <td><?= $branch_name; ?></td>
            </tr>
            <tr>
                <th>Type of Service Provided</th>
                <td><?= $invoice->type_of_service_provided; ?></td>
            </tr>
            <tr>
                <th>Client Signature</th>
                <?php if (!empty($invoice->sign_img)) : ?>
                    <td> <img src="<?= $upload_dir['baseurl'] . "/" . $invoice->sign_img; ?>" alt=""></td>
                <?php else : ?>
                    <td>No Signature Found</td>
                <?php endif; ?>
            </tr>
            <tr>
                <th>Additional Doc</th>
                <?php if (!empty($invoice->additional_doc)) : ?>
                    <td><a target="_blank" href="<?= $invoice->additional_doc; ?>">View Document</a></td>
                <?php else : ?>
                    <td>No Additional Doc</td>
                <?php endif; ?>
            </tr>
        </table>
    </div>
<?php else : ?>
    <h1>No Record Found</h1>
<?php endif; ?>