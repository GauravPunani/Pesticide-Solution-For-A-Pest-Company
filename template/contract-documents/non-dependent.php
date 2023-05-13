<?php
    $technician_id = (new Technician_details)->get_technician_id();
    $tech_name = (new Technician_details)->getTechnicianName($technician_id);

?>
<h3 class="text-center">Non-Compete Agreement</h3> 
<p>This Non-Compete Agreement is entered into between <b><?= $tech_name; ?></b> and Gam Services Usa Inc on the <?= date('d'); ?> day of <?= date('M'); ?> in the year <?= date('Y'); ?></p>

<p>WHEREAS, the Company is in the business of Pest control services.</p>

<p>WHEREAS, <b><?= $tech_name; ?></b> and Gam Services Usa Inc have entered into a formal Employment agreement where the <b><?= $tech_name; ?></b> will perform duties related to their position as technician and WHEREAS, the <b><?= $tech_name; ?></b> agrees to the restrictions described herein as binding.</p>

<p>THEREFORE, Gam Services Usa Inc and the <b><?= $tech_name; ?></b> agree to the following terms:</p>

<p><b>NON-COMPETITION.</b> For the entire duration of this agreement, and for infinite period of time after Gam Services Usa Inc relationship with <b><?= $tech_name; ?></b> has been terminated for any reason, <b><?= $tech_name; ?></b> will not solicate any Gam Services Usa Inc clients for the purposes of pest control services  for there own firm or new employers firm. This includes refering such business to another firm despite not holding employment at that that firm. <b><?= $tech_name; ?></b> shall not solicate the help of other current Gam Services Usa Inc <b><?= $tech_name; ?></b>s or employees for the purpose of benefit for there own or other pest control firm. <b><?= $tech_name; ?></b> must not reveal, share, sell, copy or use any intellectual property, trade secrets, or proprietary software or information for any reason unless for purpose of working for Gam Services Usa Inc.</p>

<p><b>EMPLOYEE ACKNOWLEDGEMENTS.</b> The Employee acknowledges that they have been provided with the opportunity to negotiate this agreement, have had the opportunity to seek legal counsel before signing this agreement, and that the restrictions imposed are fair and necessary for the Company’s business interests. Finally, the Employee agrees that these restrictions are reasonable and do not constitute a threat to their livelihood.</p>

<p><b>APPLICABLE LAW.</b> This agreement and its interpretation shall be governed by the laws of [state, province or territory].</p>

<p><b>IN WITNESS WHEREOF,</b> both parties agree to these terms and give their consent and authority to this agreement below.</p>

<p><b><?= $tech_name; ?></b> Signature</p>

<p>Date : <?= date('d M Y'); ?></p>