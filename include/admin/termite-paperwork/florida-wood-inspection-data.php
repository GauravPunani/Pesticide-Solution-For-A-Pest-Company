<?php
global $wpdb;

$report_id=$args['id'];

$report_data=$wpdb->get_row("select * from {$wpdb->prefix}npma where id='$report_id' ");
$upload_dir=wp_upload_dir();

$o_i_a=json_decode($report_data->obstruction_and_inacessible_areas);
if ($o_i_a === null && json_last_error() !== JSON_ERROR_NONE) {
    $o_i_a=$report_data->obstruction_and_inacessible_areas;
}

?>

<div class="container">
    <div class="col-md-12">
        <div class="card full_width table-responsive">
            <div class="card-body">
                <button onclick="javascript:window.history.back()" class="btn btn-primary"><span><i class="fa fa-arrow-left"></i></span> Go Back</button>
                <h4 class="page-header">NPMA33</h4>
                <table class="table table-striped table-hover">
                    <tbody>
                        <tr>
                            <th>Client Name</th>
                            <td><?= $report_data->client_name; ?></td>
                            <th>Client Email</th>
                            <td><?= $report_data->client_email; ?></td>
                        </tr>
                        <tr>
                            <th>Report Sent to Requestor and to:</th>
                            <td><?= $report_data->report_sent_to; ?></td>
                            <th>Inspection & Report Requested By</th>
                            <td><?= $report_data->report_requested_by; ?></td>
                        </tr>
                        <tr>
                            <th>Date of Inspection</th>
                            <td><?= !empty($report_data->date_of_inspection) ? date('d M Y',strtotime($report_data->date_of_inspection)) : ''; ?></td>
                            <th>Property Address</th>
                            <td><?= nl2br($report_data->address_of_property); ?></td>
                        </tr>
                        <tr>
                            <th>Evidence of wood destroying</th>
                            <td><?= $report_data->evidence_of_wood_destroying; ?></td>
                            <th>Evidence type</th>
                            <td><?= $report_data->evidence_type; ?></td>
                        </tr>
                        <tr>
                            <th>Evidence type</th>
                            <td><?= $report_data->evidence_type; ?></td>
                            <th>Evidence description & Location</th>
                            <td><?= $report_data->evidence_description_and_location; ?></td>
                        </tr>
                        <tr>
                            <th>Previously Treated</th>
                            <td><?= $report_data->previously_treated; ?></td>
                            <th>Previous Treatement Note</th>
                            <td><?= $report_data->previous_treatement_note; ?></td>
                        </tr>
                        <tr>
                            <th>Treatement Recommendation</th>
                            <td><?= $report_data->treatement_recommendation; ?></td>
                            <th>Recommendation Note</th>
                            <td><?= $report_data->recommendation_note; ?></td>
                        </tr>
                        <tr>
                            <th>Obstruction & Inaccessible Areas</th>
                            <td>
                                <?php if(is_array($o_i_a) && count($o_i_a)>0): ?>
                                    <ul>
                                    <?php foreach($o_i_a as $val): ?>
                                        <?php if(array_key_exists('type',$val)): ?>
                                        <li><?= $val->type." : ".$val->note; ?></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <?= $o_i_a; ?>
                                <?php endif; ?>
                            </td>
                            <th>Additional Comment</th>
                            <td><?= $report_data->additional_comment; ?></td>
                        </tr>
                        <tr>
                            <th>Notice of Inspection</th>
                            <td><?= $report_data->notice_of_inspection; ?></td>
                            <th>Structure treated at inspection ?</th>
                            <td><?= $report_data->structure_treated_at_inspection; ?></td>
                        </tr>
                        <tr>
                            <th>Common name of organism treated</th>
                            <td><?= $report_data->common_name_of_organism_treated; ?></td>
                            <th>Name of pesticide used</th>
                            <td><?= $report_data->name_of_pesticide_used; ?></td>
                        </tr>
                        <tr>
                            <th>Terms & Condition of treatment</th>
                            <td><?= $report_data->terms_and_condition_of_treatement; ?></td>
                            <th>Method of treatement</th>
                            <td><?= $report_data->method_of_treatement; ?></td>
                        </tr>
                        <tr>
                            <th>Treatement Notice Location</th>
                            <td><?= $report_data->treatement_notice_location; ?></td>
                            <th><span><i class="fa fa-file-pdf-o"></i></span> NPMA33 PDF File</th>
                            <td><a target="_blank" class="btn btn-primary" href="<?= $upload_dir['baseurl'].$report_data->pdf_link; ?>"><span><i class="fa fa-eye"></i> View</span></a> </td>
                        </tr>
                        <tr>
                            <th>Strutures Inspected</th>
                            <td><?= $report_data->structures_inspected; ?></td>                        
                            <th>Date Created</th>
                            <td><?= !empty($report_data->date_created) ? date('d M Y',strtotime($report_data->date_created)) : ''; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>