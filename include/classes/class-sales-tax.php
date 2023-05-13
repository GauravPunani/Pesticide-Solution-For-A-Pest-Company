<?php


class Saletax extends GamFunctions
{
    function __construct(){
        add_action( 'admin_post_download_sales_tax_log', array($this,'download_sales_tax_log') );
		add_action( 'admin_post_download_sales_tax_log', array($this,'download_sales_tax_log') );

    }

    public function download_sales_tax_log(){
        // echo "<pre>";print_r($_POST);wp_die();
        global $wpdb;

        $search_array=[];

        if(isset($_POST['branch_id']) && !empty($_POST['branch_id'])){
            if($_POST['branch_id']!="all"){
                $search_array['branch_id']=$_POST['branch_id'];
            }
        }

        if(isset($_POST['from_date']) && !empty($_POST['from_date'])){
            $search_array['date >']=$_POST['from_date'];
        }
        if(isset($_POST['to_date']) && !empty($_POST['to_date'])){
            $search_array['date <']=$_POST['to_date'];
        }
        if(isset($_POST['payment_method']) && !empty($_POST['payment_method'])){
            $search_array['payment_method']=$_POST['payment_method'];
        }

        $search_string=(new GamFunctions)->generate_search_string($search_array);

        $invoices=$wpdb->get_results("
            select client_name,address,tax 
            from {$wpdb->prefix}invoices 
            $search_string
        ");

        $total_sales_tax=$wpdb->get_row("
            select SUM(tax) as total_sales_tax 
            from {$wpdb->prefix}invoices 
            $search_string
        ");

        $total_rows= $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->prefix}invoices
            $search_string
        ");

        if(count((array) $invoices)>0){
            // echo "<pre>";print_r($_POST);wp_die();
            $message='<!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Document</title>
                            <style>
                                .text-center{
                                    text-align:center;
                                }
                                .text-left td{
                                    text-align:left;
                                }
                            </style>
                        </head>
                        <body>';

            $message.="<h1 class='text-center'><b>Gamex Sales Tax Report</b></h1>";                        
            $message.="<h2 class='text-center'>Summary</h2>";                        

            $message.='<table class="text-left">
                        <tbody>';

            if(isset($_POST['branch_id']) && !empty($_POST['branch_id'])){
                $branch_id = esc_html($_POST['branch_id']);
                $branch_name = (new Branches)->getBranchName($branch_id);
                $message.="<tr>
                                <th>Branch</th>
                                <td>$branch_name</td>
                            </tr>";
            }
            if(isset($_POST['payment_method']) && !empty($_POST['payment_method'])){
                $message.="<tr>
                                <th>Payment Method</th>
                                <td>".$_POST['payment_method']."</td>
                            </tr>";
            }
            if(isset($_POST['from_date']) && !empty($_POST['from_date'])){
                $from_date=date('d M Y',strtotime($_POST['from_date']));
                $message.="<tr>
                                <th>From Date</th>
                                <td>$from_date</td>
                            </tr>";
            }
            if(isset($_POST['to_date']) && !empty($_POST['to_date'])){
                $to_date=date('d M Y',strtotime($_POST['from_date']));

                $message.="<tr>
                                <th>To Date</th>
                                <td>$to_date</td>
                            </tr>";
            }
            $message.="<tr>
                            <th>Total Salex Tax</th>
                            <td>".$this->beautify_amount_field($total_sales_tax->total_sales_tax)."</td>
                        </tr>";
            $message.="<tr>
                            <th>Total Invoices</th>
                            <td>$total_rows</td>
                        </tr>";

                            
            $message.='</tbody>
                        </table>';


            $message.='<h2 class="text-center">Sales Tax Logs</h2>';

            $message.='<table class="text-left">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Client Address</th>
                                <th>Sales Tax</th>
                            </tr>
                        </thead>
                        <tbody>';

            foreach ($invoices as $key => $invoice) {
                $message.="<tr>
                                <td>$invoice->client_name</td>
                                <td>$invoice->address</td>
                                <td>".$this->beautify_amount_field($invoice->tax)."</td>
                            </tr>";
            }

            $message.='</tbody>
                        </table>';



            $message.='</body>
                        </html>';

            // echo $message;wp_die();

            // load mpdf php sdk from vendor 
            self::loadVendor();
            
			$mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
			$mpdf->WriteHTML($message);
			$mpdf->Output('Sales Tax Report.pdf',"D");

        }
        

    }
}

new Saletax();
