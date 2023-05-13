<?php

class TekCard extends GamFunctions{

  private $security_key;

  function __construct(){
		add_action( 'admin_post_nopriv_process_payment', array($this,'process_payment') );
		add_action( 'admin_post_process_payment', array($this,'process_payment') );

		add_action( 'wp_ajax_nopriv_process_payment', array($this,'process_payment') );
		add_action( 'wp_ajax_process_payment', array($this,'process_payment') );

  }

  public function setLogin($security_key) {
    $this->security_key=$security_key;
    return $this;
  }

  public function doSale($amount, $ccnumber, $ccexp, $cvv="") {

    $query  = "";
    // Login Information
    $query .= "security_key=" . urlencode($this->security_key) . "&";

    // customer information
    $query .= "firstname=" .  urlencode($this->billing['firstname']) . "&";
    $query .= "lastname=" .   urlencode($this->billing['lastname']) . "&";
    $query .= "address1=" .   urlencode($this->billing['address1']) . "&";
    $query .= "city=- &";
    $query .= "zipcode=- &";
    $query .= "state=-&";

    // Sales Information
    $query .= "ccnumber=" . urlencode($ccnumber) . "&";
    $query .= "ccexp=" . urlencode($ccexp) . "&";
    $query .= "amount=" . urlencode(number_format($amount,2,".","")) . "&";
    $query .= "cvv=" . urlencode($cvv) . "&";
    $query .= "type=sale";
    // $query .= "test_mode=enabled";
    return $this->_doPost($query);
  }

  public function _doPost($query) {

    // $query=explode('&',$query);
    // echo '<pre>';print_r($query);wp_die();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://tekcardpayments.transactiongateway.com/api/transact.php");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_POST, 1);

    if (!($data = curl_exec($ch))) {
        return false;
    }
    curl_close($ch);
    unset($ch);

    $data = explode("&",$data);
    for($i=0;$i<count($data);$i++) {
      $rdata = explode("=",$data[$i]);
      $this->responses[$rdata[0]] = $rdata[1];
    }

    return $this->responses;
  }

  public function get_transaction_response($transaction_id,$security_key){

    $post_fields=[
      'security_key'  =>  $security_key,
      'transaction_id'  =>  $transaction_id
    ];

    $post_fields=http_build_query($post_fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://tekcardpayments.transactiongateway.com/api/query.php");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_POST, 1);

    if (!($data = curl_exec($ch))) {
        return false;
    }
    curl_close($ch);
    unset($ch);

    $testXmlSimple= new SimpleXMLElement($data);

    if (!isset($testXmlSimple->transaction)) {
      return false;
    }
    else{
      return true;
    }
  }

  public function process_payment(){

    global $wpdb;

    $this->verify_nonce_field('process_payment');

    $invoice_id = (isset($_POST['process_type']) && $_POST['process_type']=="manual_payment") ? $_POST['invoice_id'] : $_SESSION['invoice-data']['invoice_id'];

    $invoice_details=$wpdb->get_row("
      select technician_id, client_name, email,total_amount, address, branch_id
      from {$wpdb->prefix}invoices
      where id='$invoice_id'
    ");

    $name = explode(' ',trim($_POST['cardholder_name']));

    if(empty($name)){
      $name="Invoice ".$invoice_id;
    }

    $this->billing['firstname']=$name[0];
    if(array_key_exists('1',$name) && !empty($name[1])){
      $this->billing['lastname']=$name[1];
    }
    else{
      $this->billing['lastname']="-";
    }
    $this->billing['address1']=$invoice_details->address;

    $branch = (new Branches)->getBranchSlug($invoice_details->branch_id);

    $tekcard_key=$wpdb->get_var("
      select tekcard_key 
      from {$wpdb->prefix}branches 
      where slug='$branch'
    ");


    // process the card
    $res=$this->setLogin($tekcard_key)
            ->doSale($invoice_details->total_amount,$_POST['card_no'],stripslashes($_POST['expiry_date']),$_POST['card_cvv']);

    if($res['response']==1){

      // save the details in database for the transaction
      $data=[
        'transaction_id'  =>  $res['transactionid'],
        'invoice_id'      =>  $invoice_id,
        'amount'          =>  $invoice_details->total_amount,
        'cardholder_name' =>  $_POST['cardholder_name'],
      ];

      $wpdb->insert($wpdb->prefix."tekcard_payments",$data);

      // send payment recipet to client as well
      $message=$this->payment_receipt_content($res['transactionid']);
      $subject="GAM Eexterminating Service Payment Receipt";

      // $tos = [];

      $tos[] = [
          'email' =>  $invoice_details->email,
          'name'  =>  $invoice_details->client_name
      ];

      $tos[] = [
          'email' =>  'gamexterminatingbilling@gmail.com',
          'name'  =>  'GAM Office'
      ];      

      (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $message, '', '', 'stripe_receipt');

      (new InvoiceFlow)->callNextPageInFlow(false);

      $this->response('success','Card Charged Successfully',['transaction_id'=>$res['transactionid']]);
    }
    else{
      $message="";
      switch ($res['response_code']) {
        case '300':
          $message=$res['responsetext'];
        break;
        case '200':
          $message="Transaction was declined by processor";
        break;
        case '200':
          $message="Transaction was declined by processor";
        break;
        case '202':
          $message="Insufficient funds";
        break;
        case '201':
          $message="Do not honor";
        break;
        case '220':
          $message="Incorrect payment information";
        break;
        case '224':
          $message="Invalid expiration date";
        break;
        
        default:
          $message="Something went wrong while processing card";
        break;
      }
      $this->response('error',$message,$res);
    }

  }

  public function payment_receipt_content($transaction_id){
    global $wpdb;

    $payment_receipt = $wpdb->get_row("
      select TP.*,I.client_name,I.address,T.first_name,T.last_name 
      from {$wpdb->prefix}tekcard_payments TP
      left join {$wpdb->prefix}invoices I
      on TP.invoice_id=I.id
      left join {$wpdb->prefix}technician_details T
      on I.technician_id=T.id
      where TP.transaction_id='$transaction_id'
    ");

    $receipt_html = '
			<!DOCTYPE html>
				<html lang="en">
				<head>
					<meta charset="UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<title>Document</title>
				</head>
				<body>
					<style>
						table{
							font-family: arial, sans-serif;
							border-collapse: collapse;
							width: 100%;
						}

						td, th{
							border: 1px solid #dddddd;
							text-align: left;
							padding: 8px;
						}

						tr:nth-child(even){
							background-color: #dddddd;
						}
					</style>    
    ';

    $receipt_html .= "
        <table class='table table-striped table-hover'>    
            <caption>Gam Pesticide Service Payment</caption>
          <tbody>
            <tr>
                <th>Transaction ID</th>
                <td>$transaction_id</td>
            </tr>
            <tr>
                <th>Name (as on invoice)</th>
                <td>$payment_receipt->client_name</td>
            </tr>
            <tr>
                <th>Cardholder Name</th>
                <td>".(empty($payment_receipt->cardholder_name) ? $payment_receipt->client_name : $payment_receipt->cardholder_name)."</td>
            </tr>
            <tr>
                <th>Address</th>
                <td>$payment_receipt->address</td>
            </tr>
            <tr>
                <th>Amount Charged</th>
                <td>".(new GamFunctions)->beautify_amount_field($payment_receipt->amount)."</td>
            </tr>
            <tr>
                <th>Date Charged</th>
                <td>".date('d M Y',strtotime($payment_receipt->created_at))."</td>
            </tr>
            <tr>
                <th>Service Technician</th>
                <td>$payment_receipt->first_name $payment_receipt->last_name</td>
            </tr>
          </tbody>
        </table>
    ";

		$receipt_html .= "
				</body>
			</html>";

      return $receipt_html;
  }

  public function paymentMethods( array $columns = []){
    global $wpdb;

    $columns = (count($columns) > 0) ? implode(',', $columns) : '*';

    return $wpdb->get_results("
      select $columns 
      from {$wpdb->prefix}payment_methods
    ");
  }
}

$gw = new TekCard();