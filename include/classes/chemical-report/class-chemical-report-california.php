<?php

class ChemicalReportCalifornia extends ChemicalReport{
    function __construct(){

        add_action( 'admin_post_generate_california_report', array($this,'generate_california_report') );
		add_action( 'admin_post_nopriv_generate_california_report', array($this,'generate_california_report'));

        add_action( 'admin_post_chemical_report_california', array($this,'chemical_report_california'));
        add_action( 'admin_post_nopriv_chemical_report_california', array($this,'chemical_report_california'));        

    }

	public function generate_california_report(){
		global $wpdb;

		$upload_dir=wp_upload_dir();

		$date_condition="";
		if(isset($_POST['from_date']) && isset($_POST['to_date'])){
			$date_condition=" where DATE(CRC.date) >= '{$_POST['from_date']}' and DATE(CRC.date) <= '{$_POST['to_date']}'";
		}

		$result = $wpdb->get_results("
		select CRC.client_name,CRC.client_address,CRC.date ,CC.*,C.name 
		from {$wpdb->prefix}chemicals_california CC 
		left join {$wpdb->prefix}checmical_report_california CRC  
		on CRC.id=CC.report_id 
		left join {$wpdb->prefix}chemicals C 
		on CC.product_id=C.id 
		$date_condition
		");

		if($_POST['report_type']=="csv"){
			$this->california_excel_report($result);
		}
		else{
			$pdf_data='<!DOCTYPE html>
						<html lang="en">
						<head>
							<meta charset="UTF-8">
							<meta name="viewport" content="width=device-width, initial-scale=1.0">
							<title>Document</title>
						</head>
						<body>';
							
		
			$pdf_data.="<h1 style='text-align:center;' >CALIFORNIA APPLICATOR/TECHNICIAN PESTICIDE ANNUAL REPORT</h1>";
		
			$pdf_data.="<table style='text-align:center;'>
							<thead>
								<tr>
									<th>Report Year</th>
									<th>Certification ID Number</th>
									<th>Last Name</th>
									<th>First Name</th>
									<th>Bus./Agency Reg. No.(If applicable)</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>".date('Y',strtotime($_POST['from_date']))."</td>
									<td>C1881257</td>
									<td>Migliaccio</td>
									<td>Greg</td>
									<td>15693</td>
								</tr>
							
							</tbody>
						</table>";
		
				if( !is_array($result) || empty($result)){
					$pdf_data.="<h4 style='text-align:center'><span><img style='width:30px;height:30px;' src='".$upload_dir['baseurl']."/2019/11/checkmark.png' /></span>Check Here If No Commercial Applications Were Conducted This Year</h4>";
				}
				else{
					$pdf_data.="<h4 style='text-align:center'>Check Here If No Commercial Applications Were Conducted This Year</h4>";
				}
		
			$pdf_data.="<table>
							<thead>
								<tr>
									<th>Product Name</th>
									<th>Product Quantity </th>
									<th>Unit of Measurement</th>
									<th>Target Organisms</th>
									<th>Plece of application</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>";
		
			if( is_array($result) && !empty($result)){
				foreach($result as $key=>$val){
					$pdf_data.="<tr>
									<td>$val->name</td>
									<td>$val->product_quantity</td>
									<td>$val->unit_of_measurement</td>
									<td>$val->target_organisms</td>
									<td>$val->place_of_application</td>
									<td>$val->date</td>
								</tr>";
				}
			}
		
			$pdf_data.="</tbody>
						</table>";

			$pdf_data.="</body>
						</html>";
			
			// load mpdf php sdk from vendor
			self::loadVendor();
			
			$mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
			$mpdf->WriteHTML($pdf_data);
			$mpdf->Output('Chemical Report California.pdf',"D");
			
		}

	
	}
    
    public function chemical_report_california(){

        if($_POST['pesticide_used']=="false"){
            //set session that chemical report is saved
            $_SESSION['basic-details']="true";
            wp_redirect(site_url()."/invoice/");
            exit();
        }

        global $wpdb;

		$technician_id = (new Technician_details)->get_technician_id();
		$branch_id = (new Technician_details)->getTechnicianBranchId($technician_id);
		$branch_name = (new Branches)->getBranchName($branch_id);

        $data=[
            'client_name'		=>	$_POST['client_name'],
            'client_address'	=>	$_POST['client_address'],
            'date'				=>	$_SESSION['invoice-data']['client-data']['date'],
            'business_reg_id'	=>	$_POST['business_reg']
        ];

        $wpdb->insert($wpdb->prefix."checmical_report_california",$data);
		$report_id=$wpdb->insert_id;

		// set report data as session for sending along with invoice
		$_SESSION['invoice-data']['chemical-report-data']=[
			'report_type'	=>	'california',
			'report_id'		=>	$report_id
		];


        $lastId=$wpdb->insert_id;
        // echo "last id is ".$lastId;die;
        foreach($_POST['product'] as $k=>$v){

            $checmicalData=[
                'report_id'				=>	$lastId,
                'product_id'			=>	$_POST['product'][$k]	,
                'product_quantity'		=>	$_POST['product_quantity'][$k],
                'unit_of_measurement'	=>	$_POST['unit_of_measure'][$k],
                'target_organisms'		=>	$_POST['target_oranisms'][$k],
                'place_of_application'	=>	$_POST['application_place'][$k],
            ];

            if($_POST['product_quantity'][$k]=="other" && $_POST['product_other_quantity'][$k]!="")
                $checmicalData['product_quantity']=$_POST['product_other_quantity'][$k];
            else
                $checmicalData['product_quantity']=$_POST['product_quantity'][$k];


			$res=$wpdb->insert($wpdb->prefix.'chemicals_california',$checmicalData);
			
			if($res){
				$message="Chemical Report Saved Successfully";
				$this->setFlashMessage($message,'success');
			}
			else{
				echo $message="Something went wrong, please try again later";
				$this->setFlashMessage($message,'success');
				wp_redirect($_POST['page_url']);
				return;
			}
        }
        
        $emailContent="
                <table style='border:2px dashed black; margin-top:10px; padding:10px;text-align:left;'>
                	<caption><b>Technician Details</b></caption>
                    <tr>
                        <th>Branch</th>
                        <th>Buisness Reg #</th>
                    </tr>
                    <tr>
                        <td>$branch_name</td>
                        <td>{$_POST['business_reg']}</td>
                    </tr>
                </table>

                <table style='border:2px dashed black; margin-top:10px; padding:10px;text-align:left;'>
                	<caption><b>Client Details</b></caption>
                    <tr>
                        <th>Client Name</th>
                        <th>Client Address</th>
                    </tr>
                    <tr>
                        <td>{$_POST['client_name']}</td>
                        <td>{$_POST['client_address']}</td>
                    </tr>
                </table>

                <table style='border:2px dashed black; margin-top:10px; padding:10px;text-align:left;'>
                	<caption><b>Chemical Report</b></caption>
                    <tr>
						<th>Product</th>
						<th>Product Quantity</th>
						<th>Unit Of Measure</th>
						<th>Target Organisms</th>
						<th>Application Place</th>
					</tr>";
        
				foreach($_POST['product'] as $k=>$v){
					$emailContent.="
							<tr>
								<td>{$_POST['product'][$k]}</td>
								<td>{$_POST['product_quantity'][$k]}</td>
								<td>{$_POST['unit_of_measure'][$k]}</td>
								<td>{$_POST['target_oranisms'][$k]}</td>
								<td>{$_POST['application_place'][$k]}</td>
							</tr>
					";
				}
        

        $emailContent.="</table>";

		$subject="Technician Chemical Report - California";
		$tos = [];
		$tos[] = [
			'email'	=>	'gamchemicalreportsla@gmail.com',
			'name'	=>	'GAM Office'
		];

		(new Sendgrid_child)->sendTemplateEmail($tos, $subject, $emailContent);

		(new InvoiceFlow)->callNextPageInFlow();
    }
	
	public function california_excel_report($report_data){
		
		header("Content-Disposition: attachment; filename=\"California Report.xls\"");
		header("Content-Type: application/vnd.ms-excel;");
		header("Pragma: no-cache");
		header("Expires: 0");
		$out = fopen("php://output", 'w');

		$header_line=[
			'Client Name',
			'Client Address',
			'Product Name',
			'PRODUCT Quantity',
			'Unit of Measurement',
			'Target Organisms',
			'Place of application',
			'Date',
		];

		fputcsv($out, $header_line,"\t");

		foreach ($report_data as $data)
		{
			$line=[
				$data->client_name,
				$data->client_address,
				$data->name,
				$data->product_quantity,
				$data->unit_of_measurement,
				$data->target_organisms,
				$data->place_of_application,
				$data->date,
			];
			
			fputcsv($out, $line,"\t");
		}
		fclose($out);	

	}
	
	public function chemical_report_pdf_content($report_id){
		global $wpdb;

		$report_data=$wpdb->get_results("
			select CC.*,C.name,C.slug,C.dosage_rate,C.epa_reg_no,CRC.client_name,CRC.client_address
			from {$wpdb->prefix}checmical_report_california CRC
			left join {$wpdb->prefix}chemicals_california CC
			on CRC.id=CC.report_id
			left join {$wpdb->prefix}chemicals C
			on CC.product_id=C.id
			where CRC.id='$report_id'
		");

		if(is_array($report_data) && count($report_data)>0){

			$report_html='<!DOCTYPE html>
							<html lang="en">
							<head>
								<meta charset="UTF-8">
								<meta name="viewport" content="width=device-width, initial-scale=1.0">
								<title>Document</title>
							</head>
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

								.text-center{
									text-align:center;
								}
							</style>							
							<body>
								<h3 class="text-center">Chemical Report California</h3>
								<table>
									<thead>
										<tr>
											<th>Chemical</th>
											<th>Dosage Rate</th>
											<th>Epa Reg. No.</th>
											<th>Product Quantity</th>
											<th>Unit of Measurement</th>
											<th>Target Organisms</th>
											<th>Place of Application</th>
										</tr>
									</thead>
									<tbody>';
									
			foreach ($report_data as $data) {
				$report_html.="
						<tr>
							<td>$data->name</td>
							<td>$data->dosage_rate</td>
							<td>$data->epa_reg_no</td>
							<td>$data->product_quantity</td>
							<td>$data->unit_of_measurement</td>
							<td>$data->target_organisms</td>
							<td>$data->place_of_application</td>
						</tr>";
			}

			$report_html.='
					</tbody>
					</table>
				</body>
			</html>';

			return $report_html;
		
		}
		else{
			return null;
		}
	}

	public static function getChemicals(int $report_id, array $columns = []){
		global $wpdb;

		$columns = count($columns) > 0 ? implode(',', $columns) : '*';

		return $wpdb->get_results("
			select $columns
			from {$wpdb->prefix}chemicals_california
			where report_id = '$report_id'
		");
	}

	public static function getChemicalsNewyork(array $chemical_ids, array $columns = []){
		global $wpdb;

		$columns = count($columns) > 0 ? implode(',', $columns) : '*';

		$chem_ids = implode("','", $chemical_ids);

		return $wpdb->get_results("
			select $columns
			from {$wpdb->prefix}chemicals_newyork
			where id in ('$chem_ids')
		");
	}
}

new ChemicalReportCalifornia();