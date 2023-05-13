<?php

if(!isset($args['vehicle'])) return '<p class="text-danger">No Vehicle Found</p>';
$vehicle_data = $args['vehicle'];

$vehicle_status = (new CarCenter)->getStatusById($vehicle_data->status_id);


?>

<table class="table table-striped table-hover">
    <caption>Vehicle Information</caption>
    <tr>
        <th>year</th>
        <td><?= $vehicle_data->year; ?></td>
        <th>Make</th>
        <td><?= $vehicle_data->make; ?></td>
    </tr>
    <tr>
        <th>Model</th>
        <td><?= $vehicle_data->model; ?></td>
        <th>Plate No.</th>
        <td><?= $vehicle_data->plate_number; ?></td>
    </tr>
    <tr>
        <th>VIN Number</th>
        <td><?= $vehicle_data->vin_number; ?></td>
        <th>Parking Address</th>
        <td><?= $vehicle_data->parking_address; ?></td>
    </tr>
    <tr>
        <th>Owner</th>
        <td><?= $vehicle_data->owner; ?></td>
        <th>Created At</th>
        <td><?= $vehicle_data->created_at; ?></td>
    </tr>
    <tr>
        <th>Status</th>
        <td><?= $vehicle_status->name; ?></td>
        <th>Status Description</th>
        <td><?= $vehicle_data->status_description; ?></td>
    </tr>
    <tr>
        <th>Color</th>
        <td><?= ucfirst($vehicle_data->color); ?></td>
    </tr>
</table>

<table class="table table-striped table-hover">
    <caption>Vehicle Documents</caption>
    <tr>
        <th>Registration Document</th>
        <td>
            <?php if(empty($vehicle_data->registration_document)): ?>
                <p class="text-danger">No Document Found</p>
            <?php else: ?>
                <a target="_blank" class="btn btn-primary" href="<?= $vehicle_data->registration_document ?>"><span><i class="fa fa-eye"></i></span> View</a>
            <?php endif; ?>
        </td>
        <th>Insurance Document</th>
        <td>
            <?php if(empty($vehicle_data->insurance_document)): ?>
                <p class="text-danger">No Document Found</p>
            <?php else: ?>
                <a target="_blank" class="btn btn-primary" href="<?= $vehicle_data->insurance_document ?>"><span><i class="fa fa-eye"></i></span> View</a>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Registration Expirty Date</th>
        <td><?= !empty($vehicle_data->registration_expiry_date) ? date('d M Y', strtotime($vehicle_data->registration_expiry_date)) : '-' ?></td>
        <th>Insurance Expirty Date</th>
        <td><?= !empty($vehicle_data->insurance_expiry_date) ? date('d M Y', strtotime($vehicle_data->insurance_expiry_date)) : '-' ?></td>
    </tr>
    <tr>
        <th>Pesticide Decal Proof</th>
        <?php if((new CarCenter)->isPesticideDecalApplicable($vehicle_data->id)): ?>
            <?php if(empty($vehicle_data->pesticide_decal)): ?>
                <td class="text-danger">Not Uploaded Yet</td>
            <?php else: ?>
                <td><a class="btn btn-primary" target="_blank" href="<?= $vehicle_data->pesticide_decal; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
            <?php endif; ?>
        <?php else: ?>
            <td class="text-success">Not Applicable</td>
        <?php endif; ?>
    </tr>
</table>

<table class="table table-striped table-hover">
    <caption>Mileage Information</caption>
    <tr>
        <th>Current Mileage</th>
        <td>
            <?= $vehicle_data->current_mileage; ?>
        </td>
        <th>Last Break Change Mileage</th>
        <td>
            <?= $vehicle_data->last_break_change_mileage; ?>
        </td>
    </tr>
    <tr>
        <th>Last Oil Change Mileage</th>
        <td>
            <?= $vehicle_data->last_oil_change_mileage; ?>
        </td>
    </tr>
</table>