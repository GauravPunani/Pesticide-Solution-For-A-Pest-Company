<div class="card full_width table-responsive">
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Cold Caller</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone No.</th>
                    <th>Address</th>
                    <th>Invoice No.</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(is_array($leads) && count($leads)>0): ?>
                    <?php foreach($leads as $lead): ?>
                        <tr>
                            <td><?= $lead->display_name; ?></td>
                            <td><?= $lead->name; ?></td>
                            <td><?= $lead->email; ?></td>
                            <td><?= $lead->phone; ?></td>
                            <td><?= $lead->address; ?></td>
                            <td><span class="lead_id_<?=$lead->id; ?>"><?= $lead->invoice_no; ?></span></td>
                            <td><?= date('d M Y',strtotime($lead->date)); ?></td>
                            <td>
                                <button data-lead-id="<?= $lead->id; ?>" class="btn btn-primary link_invoice"><span><i class="fa fa-paperclip"></i></span> Link Invoice</button>
                                <button data-lead-id="<?= $lead->id; ?>" class="btn btn-danger delete_lead"><span><i class="fa fa-trash"></i></span></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No Lead Found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
