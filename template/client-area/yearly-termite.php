<?php $data=$args['data']; ?>
<table class="table table-striped table-hover">
    <tbody>
        <tr>
            <th>Name</th>
            <td><?= $data->name; ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?= $data->address; ?></td>
        </tr>
        <tr>
            <th>Phone No.</th>
            <td><?= $data->phone_no; ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= $data->email; ?></td>
        </tr>
        <tr>
            <th>Descriptoin of structure</th>
            <td><?= $data->description_of_structure; ?></td>
        </tr>
        <tr>
            <th>Buildings Treated</th>
            <td><?= $data->buildings_treated; ?></td>
        </tr>
        <tr>
            <th>Area Treated</th>
            <td><?= $data->area_treated; ?></td>
        </tr>
        <tr>
            <th>Type of Termit</th>
            <td><?= $data->type_of_termite; ?></td>
        </tr>
        <tr>
            <th>Contract Amount</th>
            <td>$<?= $data->amount; ?></td>
        </tr>
        <tr>
            <th>Start Date</th>
            <td><?= date('d M Y',strtotime($data->start_date)); ?></td>
        </tr>
        <tr>
            <th>End Date</th>
            <td><?= date('d M Y',strtotime($data->end_date)); ?></td>
        </tr>
    </tbody>
</table>