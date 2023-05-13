<?php

class ChemicalReportFlorida extends ChemicalReport{
    function __construct(){

		add_action( 'admin_post_download_chemical_report_florida', array($this,'download_chemical_report_florida'));
		add_action( 'admin_post_nopriv_download_chemical_report_florida', array($this,'download_chemical_report_florida') );

		add_action( 'admin_post_florida_chemical_report', array($this,'florida_chemical_report'));
        add_action( 'admin_post_nopriv_florida_chemical_report', array($this,'florida_chemical_report'));        

    }

	public function download_chemical_report_florida(){
		global $wpdb;

		$data=$wpdb->get_results("
		select FCR.*,TD.first_name,TD.last_name,C.name 
		from {$wpdb->prefix}florida_chemical_report FCR 
		left join {$wpdb->prefix}technician_details TD
		on FCR.technician_id=TD.id
		left join {$wpdb->prefix}chemicals C
		on FCR.product_id=C.id
		where DATE(FCR.date) >='{$_POST['start_date']}' and DATE(FCR.date) <= '{$_POST['end_date']}' 
		order by FCR.date ASC 
		");

		// echo "<pre>";print_r($data);wp_die();

		if($_POST['report_type']=="csv"){
			$this->florida_csv_report($data);
		}
		else{
			$chemical_report_html='
			<!DOCTYPE html>
			<html lang="en">
				<head>
					<meta charset="UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<title>Chemical Report Florida</title>
					<style>
						.text-center{
							text-align:center;
						}
						.text-underline{
							text-decoration:underline;
						}
					</style>
				</head>
				<body>
					<p class="text-center">Florida Department of Agriculture and Consumer Services</p>
					<p class="text-center">Division of Agricultural Environmental Services</p>
					<p class="text-center"><b>SUGGESTED PESTICIDE RECORDKEEPING FORM</b></p>
					<p class="text-center"><small>Telephone Number (850) 617-7880</small></p>
					<p>FDACS recommends recordkeeping for all pesticide applications regulated by Chapter 487, F.S., using this form or similar format. When properly completed, this form meets the recordkeeping requirements for restricted use pesticides and the central posting requirements for the federal Worker Protection Standard.</p>
				
					<p>Licensed Applicator (R) <span class="text-underline">Greg Migliaccio</span>  License No. (R) <span class="text-underline">JF260648</span>  Property Owner Authorizing Application (R) <span class="text-underline"></span></p>
					
					<table>
						<thead>
							<tr>
								<th>Date</th>
								<th>Acutal Applicator</th>
								<th>Location/Description of Treatement Site</th>
								<th>Total Size of Treatment</th>
								<th>Pesticide Brand Name</th>
								<th>Total Amt. of Pesticide Applied </th>
								<th>Application Mehtod</th>
								<th>Restricted Entery Interval</th>
							</tr>
						</thead>
						<tbody>';
	
				
				if(is_array($data) && count($data)>0){
					foreach ($data as $key => $value) {
						$chemical_report_html.="
								<tr>
									<td>$value->date</td>
									<td>Greg Migilacio</td>
									<td>$value->description_of_treatment</td>
									<td>$value->size_of_treatment</td>
									<td>$value->name</td>
									<td>$value->amount_of_pesticide</td>
									<td>$value->method_of_application</td>
									<td>12 Hours</td>
								</tr>
						";
					}
				}
	
				$chemical_report_html.='
						</tbody>
					</table>
				</body>
				</html>';
	
		// load mpdf php sdk from vendor
		self::loadVendor();		
		
		$mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
		$mpdf->WriteHTML($chemical_report_html);
		$mpdf->Output('Chemical Report Florida',"D");
		}
	}

	public function florida_csv_report($report_data){

		header("Content-Disposition: attachment; filename=\"Florida Report.xls\"");
		header("Content-Type: application/vnd.ms-excel;");
		header("Pragma: no-cache");
		header("Expires: 0");
		$out = fopen("php://output", 'w');

		$header_line=[
			'Date',
			'Actual Applicator',
			'Location/Description of Treatement Site',
			'Total Size of Treatement',
			'Pesticide Brand Name',
			'Total Amt. of Pesticide Applied',
			'Application Method',
			'Restricted Entry Interval',
		];

		fputcsv($out, $header_line,"\t");

		foreach ($report_data as $data)
		{
			$line=[
				$data->date,
				"Greg Migilacio",
				$data->description_of_treatment,
				$data->size_of_treatment,
				$data->name,
				$data->amount_of_pesticide,
				$data->method_of_application,
				"12 Hours"
			];
			
			fputcsv($out, $line,"\t");
		}
		fclose($out);	
	}
    
	public function florida_chemical_report(){
		global $wpdb;

		$page_url = site_url()."/invoice";
		if(!is_array($_POST['product']) || count($_POST['product']) <= 0) $this->sendErrorMessage($page_url);

		$report_ids=[];
		$technician_id=(new Technician_details)->get_technician_id();

		$this->beginTransaction();

		foreach ($_POST['product'] as $product) {

			$chemical_data=[
				'technician_id'				=>	$technician_id,
				'description_of_treatment'	=>	$_POST['description_of_treatment'],
				'size_of_treatment'			=>	$product['size_of_treatment'],
				'product_id'				=>	$product['product'],
				'amount_of_pesticide'		=>	$product['amount_of_pesticide'],
				'unit_of_measure'			=>	$product['unit_of_measure'],
				'method_of_application'		=>	$product['method_of_application'],
				'date'						=>	$_SESSION['invoice-data']['client-data']['date']
			];
			
			$response = $wpdb->insert($wpdb->prefix."florida_chemical_report",$chemical_data);
			if(!$response) $this->rollBackTransaction($page_url);
			$report_ids[] = $wpdb->insert_id;
		}

		$this->commitTransaction();
			
		// set report data as session for sending along with invoice
		$_SESSION['invoice-data']['chemical-report-data']=[
			'report_type'	=>	'florida',
			'report_id'		=>	$report_ids
		];

		(new InvoiceFlow)->callNextPageInFlow();
	}
	
	public function chemical_report_pdf_content(array $report_ids){
		global $wpdb;

		if(count($report_ids)>0){
			$report_ids="'" . implode ( "', '", $report_ids ) . "'";

			$report_data=$wpdb->get_results("
				select FCR.*,C.name,C.dosage_rate,C.epa_reg_no
				from {$wpdb->prefix}florida_chemical_report FCR
				left join {$wpdb->prefix}chemicals C
				on FCR.product_id=C.id
				where FCR.id IN ($report_ids)
			");

			if(is_array($report_data) && count($report_data)>0){
				$report_html ='<!DOCTYPE html>
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
					<h3 class="text-center">Chemical Report Florida</h3>
					<table>
						<thead>
							<tr>
								<th>Chemical</th>
								<th>Dosage Rate</th>
								<th>Epa Reg. No.</th>
								<th>Application Site</th>
								<th>Size of Treatement</th>
								<th>Amount of Pesticide</th>
								<th>Method of Application</th>
								<th>Date</th>
							</tr>
						</thead>
						<tbody>';

				foreach ($report_data as $data) {
					$report_html.="
							<tr>
								<td>$data->name</td>
								<td>$data->dosage_rate</td>
								<td>$data->epa_reg_no</td>
								<td>$data->description_of_treatment</td>
								<td>$data->size_of_treatment</td>
								<td>$data->amount_of_pesticide</td>
								<td>$data->method_of_application</td>
								<td>".date('d M Y',strtotime($data->date))."</td>
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
		else{
			return null;
		}
	}		
}

new ChemicalReportFlorida();