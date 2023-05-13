<?php
    global $wpdb;
    $quote=$wpdb->get_row("select * from {$wpdb->prefix}commercial_quotesheet where id='{$_SESSION['commercial_quote_editable']['id']}'");
    $callrail_traking_numbers=(new Callrail_new)->get_all_tracking_no();

    
?>
<?php if($quote): ?>

<div class="row">
    <div class="col-md-offset-2 col-md-8">

        <p class="text-right"><button class="btn btn-primary" data-toggle="modal" data-target="#codeverification"><span><i class="fa fa-edit"></i></span> Edit Quote</button></p>
        <form action="<?= admin_url('admin-post.php') ?>" method="post">
            <input type="hidden" name="action" value="update_commercial_quote">
            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
            <input type="hidden" name="quote_id" value="<?= $_GET['quote_id']; ?>">
            <h1 class="text-center">Commercial Quote #<?= $quote->id; ?></h1>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <tr>
                        <th>Client Name</th>
                        <td><input type="text" class="form-control" name="client_name" value="<?= $quote->client_name; ?>"></td>
                    </tr>
                    <tr>
                        <th>Client Address</th>
                        <td><textarea name="client_address" id="" class="form-control" cols="30" rows="5"><?= $quote->client_address; ?></textarea></td>
                    </tr>
                    <tr>
                        <th>Decision Maker Name</th>
                        <td><input type="text" name="decision_maker_name" class="form-control" value="<?= $quote->decision_maker_name; ?>"></td>
                    </tr>
                    <tr>
                        <th>Client Phone</th>
                        <td><input type="text" class="form-control" value="<?= $quote->client_phone; ?>" name="client_phone"></td>
                    </tr>
                    <tr>
                        <th>Client Email</th>
                        <td><input type="email" name="clientEmail" id="" value="<?= $quote->clientEmail; ?>" class="form-control"></td>
                    </tr>
                </table>
                <table class="table table-striped table-hover">
                    <tr>
                            <th>NO. OF TIMES</th>
                            <td><input type="text" class="form-control" name="no_of_times" value="<?= $quote->no_of_times; ?>"></td>
                    </tr>
                    <tr>
                            <th>INITIAL COST</th>
                            <td><input type="text" class="form-control" name="initial_cost" value="<?= $quote->initial_cost; ?>"></td>
                    </tr>
                    <tr>
                            <th>COST PER VISIT</th>
                            <td><input type="text" class="form-control" name="cost_per_visit" value="<?= $quote->cost_per_visit; ?>"></td>
                    </tr>
                    <tr>
                            <th>QUOTE PDF</th>
                            <td><a class="btn btn-primary" target="_blank" href="<?= $upload_dir['baseurl'].$quote->pdf_path; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                    </tr>
                    <tr>
                            <th>DATE</th>
                            <td><input type="text" class="form-control" name="event_date" value="<?=  $quote->date; ?>"></td>
                    </tr>
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
<h1>No Quote Found</h1>
<?php endif; ?>