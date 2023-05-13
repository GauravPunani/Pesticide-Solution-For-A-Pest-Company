<?php /* Template Name: Return Animal Cage */
get_header();

$message = '';
$valid = true;
global $wpdb;
if (
    isset($_GET['address']) && isset($_GET['action']) 
    && !empty($_GET['address']) && !empty($_GET['action'])) {
    $address = (new GamFunctions)->encrypt_data($_GET['address'], 'd');
    $action = (new GamFunctions)->encrypt_data($_GET['action'], 'd');

    // if url param is malformed
    if (!empty($address) && !empty($action)) {
        $record = (new AnimalCageTracker)->getAddressRecord($address);
        if(!empty($record->due_cage_retrieved)){
            $message = "We have already saved your decision no further action needed from your end.";
        }else{
            if($action == 'yes' || $action == 'no'){
                // address id is valid time to update status in DB
                $data = ['due_cage_retrieved' =>  $action];
                $response = $wpdb->update($wpdb->prefix."cage_address", $data, ['id' => $address]);
                if($response === false){
                    $message = $wpdb->last_error;
                    $valid = false;
                }
            }
            if($valid){
                switch ($action) {
                    case "yes":
                        $message = "We have saved your decision to <b>{$action}</b> one of our staff person will contact you asap so you can schedule a time to send someone back to your home to retrieve the cage we set at the home.";
                    break;
                    case "no":
                        $message = "We have saved your decision to <b>{$action}</b> if you want to extend your cage pickup date one of our staff person will contact you.";
                    break;
                }
            }
        }
    } else {
        wp_safe_redirect(home_url());
        exit;
    }
} else {
    wp_safe_redirect(home_url());
    exit;
}
?>
<section id="content">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="res-form maintenance-forms">
                    <?php if($valid) : ?>
                        <h2 class="form-head">Thanks for your response</h2>
                        <p class="text-center alert alert-info"><?= $message;?></p>
                    <?php else : ?>
                        <h2 class="form-head">Opps ! something went wrong</h2>
                        <p class="text-center alert alert-info"><?= $message;?></p>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
get_footer();
