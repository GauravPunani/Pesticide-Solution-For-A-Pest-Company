<?php

class ChemicalReportNewjersey extends ChemicalReport{
    function __construct(){

		add_action( 'admin_post_download_chemical_report_newjersey', array($this,'download_chemical_report_newjersey') );		
		add_action( 'admin_post_nopriv_download_chemical_report_newjersey', array($this,'download_chemical_report_newjersey') );

		add_action( 'admin_post_newjersey_chemical_report', array($this,'newjersey_chemical_report'));
		add_action( 'admin_post_nopriv_newjersey_chemical_report', array($this,'newjersey_chemical_report'));
    }

	public function download_chemical_report_newjersey(){

		global $wpdb;
		$data=$wpdb->get_results("
		select NJCR.*,TD.first_name,TD.last_name,C.name,C.epa_reg_no,C.dosage_rate 
		from {$wpdb->prefix}newjersey_chemical_report NJCR 
		left join {$wpdb->prefix}technician_details TD
		on NJCR.technician_id=TD.id 
		left join {$wpdb->prefix}chemicals C
		on NJCR.product_id=C.id
		where DATE(date_created) >='{$_POST['start_date']}' and DATE(date_created) <= '{$_POST['end_date']}' 
		order by NJCR.date_created asc
		");

		// echo "<pre>";print_r($data);wp_die();

		if($_POST['report_type']=="csv"){
			$this->newjersey_csv_report($data);
		}
		else{
			$chemical_report_html='
			<!DOCTYPE html>
				<html lang="en">
				<head>
					<meta charset="UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<title>Chemical Report New Jersey</title>
					<style>
						.text-center{
							text-align: center;
						}
					</style>
				</head>
				<body>
					<p class="text-center"><b>COMMERCIAL APPLICATOR USE RECORDS FORM</b></p>
					<table>
						<thead>
							<tr>
								<th>Place Of Application</th>
								<th>Application Date</th>
								<th>Pesticides Applied</th>
								<th>EPA Reg. No.</th>
								<th>Recipe</th>
								<th>Total Applied</th>
								<th>Application Site</th>
								<th>Applicator Name & Reg. #</th>
							</tr>
						</thead>
						<tbody>
			
				';
	
			if(is_array($data) && count($data)>0){
				foreach ($data as $key => $value) {
					$date=date('d M Y',strtotime($value->date_created));
					$applicator_site='';
	
					if(!empty($value->applicator_site)){
						try{
							$applicator_site=json_decode($value->applicator_site);
							$applicator_site=implode(',',$applicator_site);
						}
						catch(Exception $e){
							$applicator_site=$value->applicator_site;
						}
	
					}
					$chemical_report_html.="
							<tr>
								<td>$value->place_of_application</td>
								<td>$date</td>
								<td>$value->name</td>
								<td>$value->epa_reg_no</td>
								<td>$value->dosage_rate</td>
								<td>$value->total_applied</td>
								<td>$applicator_site</td>
								<td>Greg Migilacio #JF260648</td>
							</tr>
					";
				}
			}
			$chemical_report_html.='
						</tbody>
					</table>
				</body>
			</html>		
			';
	
            // load mpdf php sdk from vendor
            self::loadVendor();
						
			$mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
			$mpdf->WriteHTML($chemical_report_html);
			$mpdf->Output('Chemical Report newersey',"D");
		}
	}

	public function newjersey_chemical_report(){
		global $wpdb;

		$page_url = site_url()."/invoice";

		if(!is_array($_POST['product']) || count($_POST['product']) <= 0) $this->sendErrorMessage($page_url);

		$products = $_POST['product'];

		$report_ids=[];

		$columns = [
			'technician_id',
			'place_of_application',
			'product_id',
			'total_applied',
			'measurement_unit',
			'applicator_site',
			'date_created',
		];

        // imploed columns into a string 
        $columns=implode(',',$columns);

		$product_data = [];

		foreach($products as $product){

			$product_data[] =[
				$_SESSION['technician_id'],
				$_POST['address'],
				$product['product'],
				$product['total_applied'],
				$product['unit_of_measure'],
				json_encode($product['applicator_site']),
				date('Y-m-d'),
			];
		}

        // add bracket,quote and commas in fields
        $product_data = array_map(function($product_data){
            return "('" . implode ( "', '", $product_data ) . "')"; 
        }, $product_data);

        // convert array to string by separting with comma to insert in to the database as values
        $product_data = implode(', ',$product_data);

        $sql = "INSERT INTO {$wpdb->prefix}newjersey_chemical_report ($columns) 
            VALUES $product_data";

        $res = $wpdb->query($sql);
		if(!$res) $this->sendErrorMessage($page_url);

		$last_insert_id = $wpdb->insert_id;
		$report_ids[] = $last_insert_id;

		for($i = 1; $i < count($_POST['product']); $i++){
			$last_insert_id = (int) $last_insert_id + 1;
			$report_ids[] = $last_insert_id;
		}

		// set report data as session for sending along with invoice
		$_SESSION['invoice-data']['chemical-report-data']=[
			'report_type'	=>	'newjersey',
			'report_id'		=>	$report_ids
		];

		$message = "Chemical report generated successfully";
		$this->setFlashMessage($message, 'success');

		(new InvoiceFlow)->callNextPageInFlow();
	}
	
	public function newjersey_csv_report($report_data){
		header("Content-Disposition: attachment; filename=\"Newjersey Report.xls\"");
		header("Content-Type: application/vnd.ms-excel;");
		header("Pragma: no-cache");
		header("Expires: 0");
		$out = fopen("php://output", 'w');

		$header_line=[
			'Place of application',
			'Applicaiton Date',
			'Pesticide Applied',
			'EPA Reg. No.',
			'Recipe',
			'Total Applied',
			'Application Site',
			'Applicator Name & Reg. #',
		];

		fputcsv($out, $header_line,"\t");

		foreach ($report_data as $data)
		{
			$line=[
				$data->place_of_application,
				date('d M Y',strtotime($data->date_created)),
				$data->name,
				$data->epa_reg_no,
				$data->dosage_rate,
				$data->total_applied,
				$data->applicator_site,
				"Greg Migilacio #JF260648",
			];
			
			fputcsv($out, $line,"\t");
		}
		fclose($out);
	}
	
	public function chemical_report_pdf_content(array $report_ids){
		global $wpdb;

		if(count($report_ids)>0){
			$report_ids="'" . implode ( "', '", $report_ids ) . "'";

			$report_data=$wpdb->get_results("
				select NCR.*,C.name,C.dosage_rate,C.epa_reg_no
				from {$wpdb->prefix}newjersey_chemical_report NCR
				left join {$wpdb->prefix}chemicals C
				on NCR.product_id=C.id
				where NCR.id IN ($report_ids)
			");

			// echo "<pre>";print_r($report_data);wp_die();

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
					<h3 class="text-center">Chemical Report New Jersey</h3>
					<table>
						<thead>
							<tr>
								<th>Chemical</th>
								<th>Dosage Rate</th>
								<th>Epa Reg. No.</th>
								<th>Place of Application</th>
								<th>Total Applied</th>
								<th>Applicator Site</th>
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
								<td>$data->place_of_application</td>
								<td>$data->total_applied</td>
								<td>".implode(",",json_decode($data->applicator_site))."</td>
								<td>".date('d M Y',strtotime($data->date_created))."</td>
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

new ChemicalReportNewjersey();