<?php

$payment_methods = (new Tekcard)->paymentMethods(['slug']);

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">System Codes</h3>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Meaning</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>@cb</td>
                                <td>Chemical Bypass</td>
                                <td>This code is used for chemical report bypass. Insert this code in calendar event description to make it possible for technician to bypass chemical report.</td>
                            </tr>
                            <tr>
                                <td>@mb</td>
                                <td>Maintenance Bypass</td>
                                <td>This code is used for invoice maintenance step bypsas. Use of this code also mean that client is already on maintnenac plan and will be treated as reocurring client on invoice page.</td>
                            </tr>
                            <tr>
                                <td>@cc</td>
                                <td>Cold Call</td>
                                <td>This code is used for setting invoice lead source as cold call</td>
                            </tr>
                            <tr>
                                <td>#PHONE_NO</td>
                                <td>Client Phone Auto Fetch</td>
                                <td>
                                    <p>Use # in front of client phone no. in calendar event description for system to autopick client number on invoice</p>
                                    <p><b>Example: </b> <kbd>#123-456-7890</kbd></p>
                                </td>
                            </tr>
                            <tr>
                                <td>e:EMAIL</td>
                                <td>Client Email Auto Fetch</td>
                                <td>
                                    <p>Use @ in front of client email in calendar event description for system to autopick client email on invoice</p>
                                    <p><b>Example: </b> <kbd>e:test@gmail.com</kbd></p>
                                </td>
                            </tr>
                            <tr>
                                <td>@pm="METHOD_NAME"</td>
                                <td>Payment Method</td>
                                <td>
                                    <p>This code will set the payment method on invoice form automatically</p>
                                    <p><b>Available Options</b></p>
                                    <?php if(is_array($payment_methods) && count($payment_methods) > 0): ?>
                                        <?php foreach($payment_methods as $payment_method): ?>
                                            <kbd>@pm="<?= $payment_method->slug; ?>"</kbd> |
                                        <?php endforeach; ?>
                                    <?php endif; ?>


                                </td>
                            </tr>
                            <tr>
                                <td>@sf="AMOUNT"</td>
                                <td>Service Fee</td>
                                <td>
                                    <p>This code will set service fee field automatically on invoice form</p>
                                    <p><b>Example</b> <kbd>@sf="100"</kbd> | <kbd>@sf="70.65"</kbd></p>
                                </td>
                            </tr>
                            <tr>
                                <td>@te</td>
                                <td>Tax Exempt</td>
                                <td>
                                    <p>This code will exempt the sales tax on invoice and will inform the technician as well on invoice that client is tax exempted</p>
                                </td>
                            </tr>
                            <tr>
                                <td>@joshclient</td>
                                <td>Josh Client</td>
                                <td>
                                    <p>If this code added up in calendar event description and while processing invoice found in event description then the invoice will be marked as Josh client</p>
                                </td>
                            </tr>
                            <tr>
                                <td>@joshclient</td>
                                <td>Jacob Client</td>
                                <td>
                                    <p>If this code added up in calendar event description and while processing invoice found in event description then the invoice will be marked as Jacob client</p>
                                </td>
                            </tr>
                            <tr>
                                <td>@ofc_inv</td>
                                <td>Office Invoice</td>
                                <td>
                                    <p>If this code added up in calendar event description and while processing invoice found in event description then the invoice will be assign to office staff to process invoice on behalf of technician</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>