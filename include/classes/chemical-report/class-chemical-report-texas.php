<?php

class ChemicalReportTexas extends ChemicalReport{
    function __construct(){

		add_action( 'admin_post_texas_csv_report', array($this,'texas_csv_report') );		
		add_action( 'admin_post_nopriv_texas_csv_report', array($this,'texas_csv_report') );

		add_action( 'admin_post_texas_chemical_report', array($this,'texas_chemical_report'));
        add_action( 'admin_post_nopriv_texas_chemical_report', array($this,'texas_chemical_report'));

    }

	public function texas_csv_report(){
		global $wpdb;

		$report_data=$wpdb->get_results("
		select TC.*,TCR.date,TCR.place_of_application,TCR.applicator_name,TCR.to_application_made,C.name,C.epa_reg_no,C.dosage_rate
		from {$wpdb->prefix}texas_chemicals TC
		left join {$wpdb->prefix}texas_chemical_report TCR
		on TC.report_id=TCR.id
		left join {$wpdb->prefix}chemicals C
		on TC.product_id=C.id
		where DATE(TCR.date) >='{$_POST['from_date']}' and DATE(TCR.date) <='{$_POST['to_date']}'
		");

		header("Content-Disposition: attachment; filename=\"Texas Report.xls\"");
		header("Content-Type: application/vnd.ms-excel;");
		header("Pragma: no-cache");
		header("Expires: 0");
		$out = fopen("php://output", 'w');

		$header_line=[
			'Product',
			'Dosage Rate',
			'Epa. Reg. No.',
			'Place of application',
			'Applicator Name',
			'To Application Made',
			'Application Site',
			'Wind Direction',
			'Wind Velocity',
			'Air Temprature',
			'Target Pest',
			'Type of Equipment',
			'Date',
		];

		fputcsv($out, $header_line,"\t");

		foreach ($report_data as $data)
		{
			$line=[
				$data->name,
				$data->dosage_rate,
				$data->epa_reg_no,
				$data->place_of_application,
				$data->applicator_name,
				$data->to_application_made,
				$data->application_site,
				$data->wind_direction,
				$data->wind_velocity,
				$data->air_temprature,
				$data->target_pest,
				$data->type_of_equipment,
				date("d M Y",strtotime($data->date)),
			];
			
			fputcsv($out, $line,"\t");
		}
		fclose($out);



	}
    
	public function texas_chemical_report(){
		global $wpdb;

		$res1=$res2=false;

		// first save client/event details 
		$report_data=[
			'date'					=>	$_SESSION['invoice-data']['client-data']['date'],
			'time'					=>	$_POST['time'],	
			'place_of_application'	=>	$_POST['address'],	
			'applicator_name'		=>	$_POST['applicator_name'],	
			'to_application_made'	=>	(new Technician_details)->get_technician_name(),	
		];

		$res1=$wpdb->insert($wpdb->prefix."texas_chemical_report",$report_data);

		$report_id=$wpdb->insert_id;

		// set report data as session for sending along with invoice
		$_SESSION['invoice-data']['chemical-report-data']=[
			'report_type'	=>	'texas',
			'report_id'		=>	$report_id
		];

		if(is_array($_POST['product']) && count($_POST['product']) >0){
			foreach($_POST['product'] as $product){
				$chemical_data=[
					'report_id'				=>	$report_id,
					'product_id'			=>	$product['product'],
					'wind_direction'		=>	$product['wind_direction'],
					'wind_velocity'			=>	$product['wind_velocity'],
					'air_temprature'		=>	$product['air_temprature'],
					'target_pest'			=>	$product['target_pest'],
					'type_of_equipment'		=>	$product['method_of_application'],
				];
		
				if(is_array($product['applicator_site']) && count($product['applicator_site']) >0){
					$chemical_data['application_site']=implode(',',$product['applicator_site']);
				}
		
				$res2=$wpdb->insert($wpdb->prefix."texas_chemicals",$chemical_data);
			}
		}

		if($res1 && $res2){
			$message="Chemical Report Saved Successfully";
			$this->setFlashMessage($message,'success');
		}
		else{
			$this->sendErrorMessage($_POST['page_url']);
		}

		(new InvoiceFlow)->callNextPageInFlow();
	}
	
	public function chemical_report_pdf_content($report_id){
		global $wpdb;

		$report_data=$wpdb->get_results("
			select C.name,C.slug,C.dosage_rate,C.epa_reg_no,TCR.place_of_application,TC.application_site,TC.wind_direction,TC.wind_velocity,TC.air_temprature,TC.target_pest,TC.type_of_equipment
			from {$wpdb->prefix}texas_chemical_report TCR
			left join {$wpdb->prefix}texas_chemicals TC
			on TCR.id=TC.report_id
			left join {$wpdb->prefix}chemicals C
			on TC.product_id=C.id
			where TCR.id='$report_id'
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
								<h3 class="text-center">Chemical Report Texas</h3>
								<table>
									<thead>
										<tr>
											<th>Chemical</th>
											<th>Dosage Rate</th>
											<th>Epa Reg. No.</th>
											<th>Application Site</th>
											<th>Wind Direction</th>
											<th>Wind Velocity</th>
											<th>Air Temprature</th>
											<th>Target Pest</th>
											<th>Type of Equipment</th>
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
							<td>$data->application_site</td>
							<td>$data->unit_of_measurement</td>
							<td>$data->wind_direction</td>
							<td>$data->wind_velocity</td>
							<td>$data->air_temprature</td>
							<td>$data->target_pest</td>
							<td>$data->type_of_equipment</td>
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
}

new ChemicalReportTexas();