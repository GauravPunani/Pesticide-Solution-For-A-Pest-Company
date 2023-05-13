<?php

(new Navigation)->cold_caller_pay(@$_GET['tab']);

if(isset($_GET['tab']) && !empty($_GET['tab'])){
    switch ($_GET['tab']) {
        case 'pending-payments':
            get_template_part('include/admin/cold-caller-pay/pending-payments');
        break;
        case 'payment-calculation':
            get_template_part('include/admin/cold-caller-pay/payment-calculation');
        break;
        case 'proof-of-payment':
            get_template_part('include/admin/cold-caller-pay/proof-of-payment');
        break;
        case 'payment_structure':
            get_template_part('include/admin/cold-caller-pay/payment-structure');
        break;
        
        default:
            get_template_part('include/admin/cold-caller-pay/pending-payments');
        break;
    }
}
else{
    get_template_part('include/admin/cold-caller-pay/pending-payments');
}

?>

