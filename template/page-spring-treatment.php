<?php /* Template Name: Spring Treatment */
get_header();

$message = '';
$valid = true;
global $wpdb;
if (
    isset($_GET['id']) && isset($_GET['action']) 
    && !empty($_GET['id']) && !empty($_GET['action'])) {
    $id = (new GamFunctions)->encrypt_data($_GET['id'], 'd');
    $action = (new GamFunctions)->encrypt_data($_GET['action'], 'd');
    
    // if url param is malformed
    if (!empty($id) && !empty($action)) {
        $record = (new Emails)->getClientEmailRecord($id);
        if(!empty($record->answer)){
            $message = "Your decision is already saved no further action needed from your end.";
        }else{
            if($action == 'yes' || $action == 'no'){
                // address id is valid time to update status in DB
                $data = ['answer' =>  $action, 'answer_date' => date('Y-m-d H:i:s')];
                $response = $wpdb->update($wpdb->prefix."emails", $data, ['id' => $id]);
                if($response === false){
                    $message = $wpdb->last_error;
                    $valid = false;
                }
            }
            if($valid){
                switch ($action) {
                    case "yes":
                        (new OfficeTasks)->remindStaffOnSpringTreatmentIntrest(['name' => $record->name, 'email' => $record->email]);
                        $message = "We have saved your decision to <b>$action</b> one of our staff person will contact you asap";
                    break;
                    case "no":
                        $message = "We have saved your decision to <b>$action</b> if done by mistake <b><a href='/contact-us'>click here to contact us</a></b> one of our staff person will contact you asap";
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
                        <h2 class="form-head">Thank You</h2>
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