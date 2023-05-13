<?php

global $wpdb;

$technician = $args['user'];

$upload_dir = wp_upload_dir();
// echo "<pre>";print_r($technician);wp_die();

?>

<div class="row">
    <div class="col-sm-12">
        <?php (new GamFunctions)->getFlashMessage(); ?>
    </div>
    <!-- Technician Information  -->
    <div class="col-md-6">
        <table class="table table-striped table-hover">
            <caption>Technician Information</caption>
            <tbody>
                <tr>
                    <th>Name</th>
                    <td><?= $technician->first_name . " " . $technician->last_name; ?></td>
                </tr>
                <tr>
                    <th>Username</th>
                    <td><?= $technician->slug; ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= $technician->email; ?></td>
                </tr>
                <tr>
                    <th>Date of birth</th>
                    <td><?= !empty($technician->dob) ? date('d M Y', strtotime($technician->dob)) : ''; ?></td>
                </tr>
                <tr>
                    <th>Home Address</th>
                    <td><?= $technician->address; ?></td>
                </tr>
                <tr>
                    <th>Social Security</th>
                    <td><?= $technician->social_security; ?></td>
                </tr>
                <tr>
                    <th>Driver License</th>
                    <?php if (!empty($technician->driver_license)) : ?>
                        <td><a class="btn btn-primary" href="<?= $technician->driver_license; ?>" target="_blank"><span><i class="fa fa-eye"></i></span> View</a></td>
                    <?php else : ?>
                        <td class="text-danger">Not Found</td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <th>Pesticide License</th>
                    <?php if (!empty($technician->pesticide_license)) : ?>
                        <td><a class="btn btn-primary" href="<?= $technician->pesticide_license; ?>" target="_blank"><span><i class="fa fa-eye"></i></span> View</a></td>
                    <?php else : ?>
                        <td>N/A</td>
                    <?php endif; ?>
                </tr>

            </tbody>
        </table>
    </div>
    <!-- Agreement Documents  -->
    <div class="col-md-6">
        <table class="table table-striped table-hover">
            <caption>Agreement Documents</caption>
            <tbody>
                <tr>
                    <th>Independent Contract</th>
                    <?php if (!empty($technician->independent_contractor)) : ?>
                        <td><a class="btn btn-primary" target="_blank" href="<?= $upload_dir['baseurl'] . $technician->independent_contractor; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                    <?php else : ?>
                        <td class="text-danger">Not Found</td>
                    <?php endif; ?>

                </tr>
                <tr>
                    <th>Non Compete</th>
                    <?php if (!empty($technician->non_competes)) : ?>
                        <td><a class="btn btn-primary" target="_blank" href="<?= $upload_dir['baseurl'] . $technician->non_competes; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                    <?php else : ?>
                        <td class="text-danger">Not Found</td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <th>Form W9</th>
                    <?php if (!empty($technician->fw9_taxpayer)) : ?>
                        <td><a class="btn btn-primary" target="_blank" href="<?= $upload_dir['baseurl'] . $technician->fw9_taxpayer; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                    <?php else : ?>
                        <td class="text-danger">Not Found</td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <th>Salary Contract</th>
                    <?php if (!empty($technician->salary_1099_contract)) : ?>
                        <td><a class="btn btn-primary" target="_blank" href="<?= $upload_dir['baseurl'] . $technician->salary_1099_contract; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                    <?php else : ?>
                        <td class="text-danger">Not Found</td>
                    <?php endif; ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>