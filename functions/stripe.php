<?php
// if ( defined( 'XMLRPC_REQUEST' ) || defined( 'REST_REQUEST' ) || ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) || wp_doing_ajax() ) {
//   @ini_set( 'display_errors', 1 );
// }

if(!class_exists('aaa_stripe')){

    class aaa_stripe{

        private $customer_id;
        private $pm_id; //payment method
        private $product_id;
        private $plan_id;

        function __construct(){
                require_once(get_template_directory().'/stripe/vendor/autoload.php');
                // $api_key=  esc_attr( get_option('sk_upstate') );
                \Stripe\Stripe::setApiKey('sk_test_wqZ0B3YFg9L3hwK22dP8w4Jl0067fisFit');

        }

        public function createProduct($name='Monthly pesticide fee',$type='service'){
          
          $data=\Stripe\Product::create([
              'name' => $name,
              'type' => $type,
            ]);

            $this->product_id=$data->id;

            return $this;

        }

        public function createPlan($interval='month',$amount,$nickname='Monthly Plan'){
          
            $plan = \Stripe\Plan::create([
              'currency' => 'usd',
              'interval' => $interval,
              'product' => $this->product_id,
              'nickname' => $nickname,
              'amount' => $amount,
            ]);

            $this->plan_id=$plan->id;

            return $this;
        }
        

        public function createSubscription($data=[]){

            //create payment method
            //create customer
            //reterive payment method or use id from old create payment method
            //attach payment method to customer
            //create subscription for customer with the amount ,start and end date of subscription

            $this->createPaymentMethod()
                    ->createCustomer()
                    ->attachPaymentMethodToCustomer()
                    ->buildSubscription();



        }
        public function createCustomer(){

          $customer_data=[
              'email'       => $_POST['clientEmail'],
              'name'        => $_POST['clientName'],
              'phone'       => $_POST['clientPhn'],
              'description' => 'Customer for stripe recurring payment --Monthly',
          ];

          if(is_array($customer_data) && !empty($customer_data)){
              try{
                  $response = \Stripe\Customer::create($customer_data);
                  $this->customer_id=$response->id;
                  return $this;
              }
              catch(Exception $e){
                  echo  json_encode([
                    'status'=>'failed',
                    'message'=>'Error in creating customer',
                    'code'=>'stripe_customer_error'
                  ]);

                  wp_die();
              }
          }
          else{
              echo  json_encode([
                  'status'=>'failed',
                  'message'=>'No Data Recived',
                  'code'=>'no_data'
                ]);
                  wp_die();
          }
          
        }

        public function buildSubscription(){

            $items=[
                [
                    'plan'=> 'plan_GFRHrhISLS5u1H' // monthly charge the client
                ]
            ];
            try{
                  $res=\Stripe\Subscription::create([
                      'customer'                => $this->customer_id,
                      'default_payment_method'  => $this->pm_id,
                      'items'                   => $items,
                      'cancel_at'               => strtotime("+1 year"),
                  ]);

                  // $data=[
                  //   'status'=>'success',
                  //   'data'=>$res
                  // ];

                  // echo json_encode($data);wp_die();
            }
            catch(\Stripe\Exception\CardException $e) {
              // Since it's a decline, \Stripe\Exception\CardException will be caught
              $this->stripe_error($e->getError()->message,$e->getError()->code);
              
            } catch (\Stripe\Exception\RateLimitException $e) {
              // Too many requests made to the API too quickly
              $this->stripe_error($e->getError()->message,$e->getError()->code);
              
            } catch (\Stripe\Exception\InvalidRequestException $e) {
              // Invalid parameters were supplied to Stripe's API
              $this->stripe_error($e->getError()->message,$e->getError()->code);
              
            } catch (\Stripe\Exception\AuthenticationException $e) {
              // Authentication with Stripe's API failed
              // (maybe you changed API keys recently)
              $this->stripe_error($e->getError()->message,$e->getError()->code);

            } catch (\Stripe\Exception\ApiConnectionException $e) {
              // Network communication with Stripe failed
              $this->stripe_error($e->getError()->message,$e->getError()->code);
            } catch (\Stripe\Exception\ApiErrorException $e) {
              // Display a very generic error to the user, and maybe send
              // yourself an email
              $this->stripe_error($e->getError()->message,$e->getError()->code);
            } catch (Exception $e) {
              // Something else happened, completely unrelated to Stripe
              $this->stripe_error($e->getError()->message,$e->getError()->code);
            }

            return $this;

        }


        public function createPaymentMethod(){

            try{
                    $response = \Stripe\PaymentMethod::create([
                                    'type' => 'card',
                                    'card' => [
                                        'number'      => $_POST['creditcardnumber'],
                                        'exp_month'   => $_POST['cc_month'],
                                        'exp_year'    => $_POST['cc_year'],
                                        'cvc'         => $_POST['cccode'],
                                    ],
                                ]);
                    
                    $this->pm_id = $response->id;
                                    
                    return $this;
            }catch(\Stripe\Exception\CardException $e) {
                // Since it's a decline, \Stripe\Exception\CardException will be caught
                $this->stripe_error($e->getError()->message,$e->getError()->code);
                
              } catch (\Stripe\Exception\RateLimitException $e) {
                // Too many requests made to the API too quickly
                $this->stripe_error($e->getError()->message,$e->getError()->code);
                
              } catch (\Stripe\Exception\InvalidRequestException $e) {
                // Invalid parameters were supplied to Stripe's API
                $this->stripe_error($e->getError()->message,$e->getError()->code);
                
              } catch (\Stripe\Exception\AuthenticationException $e) {
                // Authentication with Stripe's API failed
                // (maybe you changed API keys recently)
                $this->stripe_error($e->getError()->message,$e->getError()->code);

              } catch (\Stripe\Exception\ApiConnectionException $e) {
                // Network communication with Stripe failed
                $this->stripe_error($e->getError()->message,$e->getError()->code);
              } catch (\Stripe\Exception\ApiErrorException $e) {
                // Display a very generic error to the user, and maybe send
                // yourself an email
                $this->stripe_error($e->getError()->message,$e->getError()->code);
              } catch (Exception $e) {
                // Something else happened, completely unrelated to Stripe
                $this->stripe_error($e->getError()->message,$e->getError()->code);
              }


            

        }

        public function attachPaymentMethodToCustomer(){

            $payment_method = \Stripe\PaymentMethod::retrieve($this->pm_id);
              $payment_method->attach([
                'customer' => $this->customer_id,
              ]);

                return $this;
        }

        private function reterivePaymentMethod($pm_id=''){

        }
        
        public function stripe_error($mesage,$status_code=''){

            $response=[
              'status'=>'failed',
              'message'=>$mesage,
              'code'=>$status_code
            ];
  
            echo json_encode($response);
            wp_die();
            
          }


    }
}

if($_SERVER['REQUEST_METHOD']=='POST'){
    if(isset($_POST) && !empty($_POST)){

        $obj=new aaa_stripe();
        $obj->createSubscription($_POST);

    }
}






