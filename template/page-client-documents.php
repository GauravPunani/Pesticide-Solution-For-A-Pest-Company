<?php

/* Template Name: Client Documents */

get_header();

if(empty($_GET['type']) || empty($_GET['id']) || empty($_GET['email'])) return;

$type = sanitize_text_field($_GET['type']);
$id = sanitize_text_field($_GET['id']);
$email = sanitize_text_field($_GET['email']);

$id = (new GamFunctions)->encrypt_data($id, 'd');
if(!$id) return;

switch ($type) {
    case 'invoice':
        $invoice = (new Invoice)->getInvoiceById($id);

        if(!$invoice) return;
        if($invoice->email != $email) return;

        $html_content = (new Invoice)->invoice_body($invoice);
    break;

    case 'residential_quote':
        $quote = (new Quote)->getResidentialQuoteById($id);
        if(!$quote) return;
        if($quote->clientEmail != $email) return;

        $html_content = (new Quote)->residenialQuoteBody($quote);
    break;

    case 'commercial_quote':
        $quote = (new Quote)->getCommercialQuoteById($id);
        if(!$quote) return;
        if($quote->clientEmail != $email) return;

        $html_content = (new Quote)->commercialQuoteBody($quote);
    break;
}

?>
<style>
.gam-responsive-tbl {
    border: none;
}
</style>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php if($html_content): ?>
                <?= $html_content; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
get_footer();