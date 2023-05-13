<?php

$contract=$args['data'];

$card_details=[];

if(!empty($contract->card_details)){
    $card_details=json_decode($contract->card_details);
}
$upload_dir=wp_upload_dir();

?>
<div class="container">
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <div class="pull-left"><a class="btn btn-primary" href="<?= admin_url('admin.php?page=yearly-termite-contract') ?>"><span><i class="fa fa-arrow-left"></i></span> Go Back</a></div>
            <h3 class="page-header text-center">Yearly Termite Contract</h3>
            <table class="table table-striped table-hover table-bordered">
                <tbody>
                    <tr>
                        <th>Name</th>
                        <td><?= $contract->name; ?></td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td><?= $contract->address; ?></td>
                    </tr>
                    <tr>
                        <th>Phone No.</th>
                        <td><?= $contract->phone_no; ?></td>
                    </tr>

                    <tr>
                        <th>Email</th>
                        <td><?= $contract->email; ?></td>
                    </tr>

                    <tr>
                        <th>Description of structure</th>
                        <td><?= $contract->description_of_structure; ?></td>
                    </tr>

                    <tr>
                        <th>Buildings Treated</th>
                        <td><?= $contract->buildings_treated; ?></td>
                    </tr>

                    <tr>
                        <th>Area Treated</th>
                        <td><?= $contract->area_treated; ?></td>
                    </tr>

                    <tr>
                        <th>Type of Termite</th>
                        <td><?= $contract->type_of_termite; ?></td>
                    </tr>

                    <tr>
                        <th>Contract Amount</th>
                        <td>$<?= $contract->amount; ?></td>
                    </tr>

                    <tr>
                        <th>Start Date</th>
                        <td><?= date('d M Y',strtotime($contract->start_date)); ?></td>
                    </tr>

                    <tr>
                        <th>End Date</th>
                        <td><?= date('d M Y',strtotime($contract->end_date)); ?></td>
                    </tr>

                    <tr>
                        <th>Card Details</th>
                        <td>
                            <ul>
                                <li><b>Number:-</b> <?= $card_details->creditcardnumber; ?></li>
                                <li><b>Expiry Date:-</b> <?= $card_details->cc_month.$card_details->cc_year; ?></li>
                                <li><b>CVV:-</b> <?= $card_details->cccode; ?></li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <th>Signature</th>
                        <?php if(!empty($contract->pdf_path)): ?>
                            <td><a class="btn btn-primary" target="_blank" href="<?= $upload_dir['baseurl'].$contract->signature; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                        <?php else: ?>
                            <td class="text-danger">Not Available</td>
                        <?php endif; ?>
                    </tr>
                    <tr>
                        <th>Contract PDF</th>
                        <?php if(!empty($contract->pdf_path)): ?>
                            <td><a class="btn btn-primary" target="_blank" href="<?= $upload_dir['baseurl'].$contract->pdf_path; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                        <?php else: ?>
                            <td class="text-danger">Not Available</td>
                        <?php endif; ?>
                    </tr>
                    <tr>
                        <th>CallRail ID</th>
                        <td>-</td>
                    </tr>
                    <tr>
                        <th>Technician Name</th>
                        <td>-</td>
                    </tr>
                    <tr>
                        <th>Date Created</th>
                        <td><?= date('d M Y',strtotime($contract->date_cretaed)); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>