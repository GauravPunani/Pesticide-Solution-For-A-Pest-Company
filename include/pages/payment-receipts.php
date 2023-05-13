<?php
    $id=$_GET['payment_id'];
    //make db request
    global $wpdb;
    $table_name = $wpdb->prefix . "client_payments";
    $user_data=$wpdb->get_row( "SELECT * FROM $table_name WHERE `payment_id`='$id' ");  
    // echo "<pre>"; print_r($user_data);die;
    get_header(); 
        ?>
        <div class="container">
            <div class="payment-box">
                <h1>Payment Successfull</h1>
                <table class="table table-striped">
                    <tr>
                        <td>Payment ID</td>
                        <td><?= $user_data->payment_id; ?></td>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td><?= ucwords($user_data->name); ?></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><?= $user_data->email; ?></td>
                    </tr>
                    <tr>
                        <td>Address</td>
                        <td><?= $user_data->address; ?></td>
                    </tr>
                    <tr>
                        <td>Phone No.</td>
                        <td><?= $user_data->phoneno; ?></td>
                    </tr>
                    <tr>
                        <td>Amount Paid</td>
                        <td>$<?= $user_data->amount; ?></td>
                    </tr>
                    <tr>
                        <td>Payment Method</td>
                        <td>Credit Card</td>
                    </tr>
                    <tr>
                        <td>Name on card</td>
                        <td><?= $user_data->card_name; ?></td>
                    </tr>
                </table>
                <a href="<?= get_home_url(); ?>">Return to Home Page</a>
            </div>
        </div>
        <style>
        .payment-box {
            margin: 15px;
            border: 2px dashed;
            padding: 10px;
        }
        </style>
<?php

get_footer();