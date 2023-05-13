<?php
global $wpdb;

if (empty($args['quote_id'])) return;

$quote_id = $args['quote_id'];

$quote = (new Quote)->getCommercialQuoteById($quote_id);

if (!$quote) return;

$callrail_traking_numbers = (new Callrail_new)->get_all_tracking_no();
$visit_frequency = (new Quote)->visit_frequency();
$visit_duration = (new Quote)->visit_duration();
$custom_frequency = false;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="card full_width table-responsive">
            <div class="card-body">
                <?php (new GamFunctions)->getFlashMessage(); ?>
                <h3 class="page-header">Edit Commercial Quote</h3>
                <form id="updateQuoteForm" action="<?= admin_url('admin-post.php') ?>" method="post">

                    <?php wp_nonce_field('update_commercial_quote'); ?>
                    <input type="hidden" name="action" value="update_commercial_quote">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <input type="hidden" name="quote_id" value="<?= $quote_id; ?>">

                    <table class="table table-striped table-hover">
                        <caption>Client Information</caption>
                        <tbody>
                            <tr>
                                <th>Client Name</th>
                                <td><input type="text" class="form-control" name="client_name" value="<?= $quote->client_name; ?>"></td>

                                <th>Client Address</th>
                                <td><input type="text" class="form-control" name="client_address" value="<?= $quote->client_address; ?>"></td>
                            </tr>
                            <tr>
                                <th>Decision Maker Name</th>
                                <td><input type="text" name="decision_maker_name" class="form-control" value="<?= $quote->decision_maker_name; ?>"></td>
                                <th>Client Phone</th>
                                <td><input type="text" class="form-control" value="<?= $quote->client_phone; ?>" name="client_phone"></td>
                            </tr>
                            <tr>
                                <th>Client Email</th>
                                <td><input type="email" name="clientEmail" id="" value="<?= $quote->clientEmail; ?>" class="form-control"></td>


                                <th>Technician Quote Name</th>
                                <td><input type="text" maxlength="100" value="<?= $quote->tech_diff_name; ?>" class="form-control tech_diff_name"  name="tech_diff_name"></td>

                            </tr>
                            
                        </tbody>
                    </table>

                    <table class="table table-striped table-hover">
                        <caption>Quote Information</caption>
                        <tbody>
                            <tr>
                                <th>INITIAL COST</th>
                                <td><input type="text" class="form-control" name="initial_cost" value="<?= $quote->initial_cost; ?>"></td>

                                <th>COST PER VISIT</th>
                                <td><input type="text" class="form-control" name="cost_per_visit" value="<?= $quote->cost_per_visit; ?>"></td>

                                <th>Callrail Tracking No.</th>
                                <td>
                                    <?php if (is_array($callrail_traking_numbers) && count($callrail_traking_numbers) > 0) : ?>
                                        <select name="callrail_id" class="form-control">
                                            <option value="">Select</option>
                                            <?php foreach ($callrail_traking_numbers as $key => $val) : ?>
                                                <option value="<?= $val->id; ?>" <?= $quote->callrail_id == $val->id ? "selected" : '';  ?>><?= $val->tracking_phone_no; ?> - <?= $val->tracking_name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Frequency of visits ?</th>
                                <td>
                                    <select name="visits_duration_recurring" class="form-control">
                                        <option value="">Select</option>
                                        <?php
                                        $visit = $quote->visits_duration;
                                        if (!array_key_exists($visit, $visit_frequency)) {
                                            $duration = (!empty($visit) ? $visit : 'months');
                                            $visit = 'custom';
                                            $custom_frequency = true;
                                        }
                                        foreach ($visit_frequency as $k => $val) : ?>
                                            <option value="<?= $k; ?>" <?= $visit == $k ? "selected" : '';  ?>><?= $val; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr class="single_no_of_visit <?php if (!$custom_frequency) : ?>hidden<?php endif; ?>">
                                <th>NO. OF TIMES</th>
                                <td>
                                    <input type="text" min="1" class="form-control numberonly" placeholder="Visit frequency for e.g : 1,2,3 etc..." name="no_of_times" value="<?= $quote->no_of_times; ?>" id="no_of_times">
                                </td>

                                <th>Per</th>
                                <td>
                                    <select name="visits_duration_fixed" class="form-control">
                                        <option value="">Select</option>
                                        <?php
                                        if ($duration) $quote->visits_duration = $duration;
                                        foreach ($visit_duration as $k => $val) : ?>
                                            <option value="<?= $k; ?>" <?= $quote->visits_duration == $k ? "selected" : '';  ?>><?= $val; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table table-striped table-hover">
                        <tbody>
                            <tr>
                                <th>CLIENT NOTES</th>
                                <td>
                                    <textarea name="notes_for_client" id="" cols="90" rows="5" class="form-control"><?= $quote->notes_for_client; ?></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Quote</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        $(document).ready(function() {
            $('select[name="visits_duration_recurring"]').on('change', function() {
                if ($(this).val() == "custom")
                    $('.single_no_of_visit').removeClass('hidden');
                else
                    $('.single_no_of_visit').addClass('hidden');
            });
            $('#updateQuoteForm').validate({
                rules: {
                    client_name: "required",
                    client_address: "required",
                    decision_maker_name: "required",
                    client_phone: "required",
                    clientEmail: "required",
                    initial_cost: "required",
                    cost_per_visit: "required",
                    event_date: "required",
                    callrail_id: "required",
                    visits_duration_recurring: "required",
                    visits_duration_fixed: "required",
                    no_of_times: {
                        number: true,
                        required: true
                    }
                }
            })
        })
    })(jQuery);
</script>