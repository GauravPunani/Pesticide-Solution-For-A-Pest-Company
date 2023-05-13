<?php

class ChemicalReportNewyork extends ChemicalReport{

    function __construct(){

		add_action( 'admin_post_generate_newyork_report', array($this,'generate_newyork_report') );		
		add_action( 'admin_post_nopriv_generate_newyork_report', array($this,'generate_newyork_report') );

        add_action( 'admin_post_chemical_report_newyork', array($this,'chemical_report_newyork') );
        add_action( 'admin_post_nopriv_chemical_report_newyork', array($this,'chemical_report_newyork'));

		add_action( 'admin_post_download_ny_animal_trapping_report', array($this,'download_ny_animal_trapping_report') );		
		add_action( 'admin_post_nopriv_download_ny_animal_trapping_report', array($this,'download_ny_animal_trapping_report') );
		
        add_action( 'admin_post_ny_animal_trapping_report', array($this,'ny_animal_trapping_report'));
        add_action( 'admin_post_nopriv_ny_animal_trapping_report', array($this,'ny_animal_trapping_report'));


    }

	public function newYorkCountyNames(){
		global $wpdb;
		return $wpdb->get_results("
			select county_name 
			from {$wpdb->prefix}ny_zip_county 
			group by county_name
		");
	}

	public function getCountyDataByZipCode( string $zip_code , array $columns = []){
		global $wpdb;

		$columns = count($columns) > 0 ? implode(',', $columns) : '*';

		return $wpdb->get_row("
			select $columns
			from {$wpdb->prefix}ny_zip_county 
			where zip='$zip_code'
		");
	}

	public function generate_newyork_report(){
		ini_set("memory_limit","512M");
		ini_set("pcre.backtrack_limit", "5000000");

		global $wpdb;

		$conditions=[];

		$upload_dir=wp_upload_dir();

		$certification_id=$_POST['applicator'];

		$technician_details=$wpdb->get_row("select * from {$wpdb->prefix}technician_details where certification_id='$certification_id'");

		$conditions[]=" CRNY.certification_id='$certification_id'";

		if(isset($_POST['from_date']) && !empty($_POST['from_date'])){
			$conditions[]=" DATE(CRNY.date) >='{$_POST['from_date']}'";
		}
		if(isset($_POST['to_date']) && !empty($_POST['to_date'])){
			$conditions[]=" DATE(CRNY.date) <='{$_POST['to_date']}'";
		}

		if(count($conditions)>0){
			$conditions=(new GamFunctions)->generate_query($conditions);
		}
		else{
			$conditions="";
		}

		$result = $wpdb->get_results("
		select CRNY.*,C.name,C.dosage_rate,C.epa_reg_no
		from {$wpdb->prefix}chemicals_newyork CRNY
		left join {$wpdb->prefix}chemicals C
		on CRNY.chemical_id=C.id
		$conditions
		");
		
		// echo "<pre>";print_r($result);wp_die();

		if($_POST['report_type']=="csv"){
			$this->newyork_csv_report_download($result);
		}
		else{
			
			// load mpdf php sdk from vendor
			self::loadVendor();

			$mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
	
			$pdf_data="<h3 style='text-align:center;'>New York State Department of Environmental Conservation</h3>";
			$pdf_data.="<h1 style='text-align:center;' >APPLICATOR/TECHNICIAN PESTICIDE ANNUAL REPORT</h1>";
			$pdf_data.="
					<table style='text-align:center;'>
						<thead>		
							<tr>
								<td>Report Year</td>
								<td>Certification ID Number</td>
								<td>Last Name</td>
								<td>First Name</td>
								<td>Bus./Agency Reg. No. (If applicable)</td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>".date('Y',strtotime($_POST['from_date']))."</td>
								<td>$technician_details->certification_id</td>
								<td>$technician_details->last_name</td>
								<td>$technician_details->first_name</td>
								<td>$technician_details->business_reg</td>
							</tr>
						</tbody>
					</table>";
	
			if( !is_array($result) || empty($result)){
				$pdf_data.="<h4 style='text-align:center'><span><img style='width:30px;height:30px;' src='".$upload_dir['baseurl']."/2019/11/checkmark.png' /></span>Check Here If No Commercial Applications Were Conducted This Year</h4>";
			}
			else{
				$pdf_data.="<h4 style='text-align:center'>Check Here If No Commercial Applications Were Conducted This Year</h4>";
			}
		
			$pdf_data.="
				<table>
					<thead>
						<tr>
							<td>Row #</td>
							<td>EPA REG. NUMBER</td>
							<td>PRODUCT NAME</td>
							<td>QUANTITY USED</td>
							<td>UNITS</td>
							<td>DATE OF APPL.</td>
							<td>COUNTY CODE</td>
							<td>ADDRESS</td>
							<td>MUNICIPALITY (CITY, VILLAGE, ETC.)</td>
							<td>ZIP CODE</td>
						</tr>
					</thead>
			";
			
			$mpdf->WriteHTML($pdf_data);
				
			$table_html="<tbody>";
	
			if( is_array($result) && !empty($result)){
				foreach($result as $key=>$val){
					$key++;
					$address=json_decode($val->address_of_application);
					// print_r($address);die;
					$table_html.="
							<tr>
								<td>$key</td>
								<td>$val->epa_reg_no</td>
								<td>$val->name</td>
								<td>$val->product_quantity</td>
								<td>$val->unit_of_measurement</td>
								<td>$val->date</td>
								<td>$val->county_code</td>
								<td>$address[0]</td>
								<td>$address[1]</td>
								<td>$address[2]</td>	
							</tr>
					";
				}
			}
	
			$table_html.="</tbody>";
			$table_html.="</table>";
	
			$mpdf->WriteHTML($table_html);	
		
			$record_keepin_html="<h1 style='text-align:center;'>Record Keeping Information</h1>
						<table style='text-align:center;'>
							<thead>
								<tr>
									<td>Row#</td>
									<td>Dosage Rate</td>
									<td>Method of application</td>
									<td>Target Organism</td>
									<td>Place of application</td>
								</tr>
							</thead>
			";
		
			if( is_array($result) && !empty($result)){
				$record_keepin_html.="<tbody>";
				foreach($result as $key=>$val){
					$key++;
					$record_keepin_html.="
						<tr>
							<td>$key</td>
							<td>$val->dosage_rate</td>
							<td>$val->method_of_application</td>
							<td>$val->target_organisms</td>
							<td>$val->place_of_application</td>
						</tr>
					";
				}
				$record_keepin_html.="</tbody>";
			}
		
			$record_keepin_html.="</table>";
	
			$mpdf->WriteHTML($record_keepin_html);	
			$mpdf->Output('Technician Annual Report.pdf',"D");
	
		}
	}

    public function chemical_report_newyork(){
		global $wpdb;

		if($_POST['pesticide_used']=="false"){
			//set session that chemical report is saved
			$_SESSION['basic-details']="true";
			wp_redirect(site_url()."/invoice");
		}

		$technician_id = (new Technician_details)->get_technician_id();
		$technician_details=(new Technician_details)->getTechnicianById($technician_id);
		$branch = (new Branches)->getBranchName($technician_details->branch_id);	
		$report_ids=[];

		// save all chemicals data in chemical table
		if(is_array($_POST['product']) && count($_POST['product'])>0){
			foreach($_POST['product'] as $k=>$v){

				$address=json_encode([
					$_POST['application_address'],
					$_POST['application_city'],
					$_POST['application_zip']
				]);
	
				$checmicalData=[
					'chemical_id'				=>	$_POST['product'][$k],
					'technician_id'				=>	$technician_id,
					'product_quantity'			=>	$_POST['product_quantity'][$k],
					'unit_of_measurement'		=>	$_POST['unit_of_measure'][$k],
					'date'						=>	$_SESSION['invoice-data']['client-data']['date'],
					'county_code'				=>	$_POST['county_code'],
					'address_of_application'	=>	$address,
					'method_of_application'		=>	$_POST['method_of_application'][$k],
					'target_organisms'			=>	$_POST['target_oranisms'][$k],
					'place_of_application'		=>	$_POST['application_place'][$k],
					'certification_id'			=>	$_POST['certification_id'],
				];
	
				$res=$wpdb->insert($wpdb->prefix.'chemicals_newyork',$checmicalData);
				$report_ids[]=$wpdb->insert_id;
			}
		}

		// set report data as session for sending along with invoice
		$_SESSION['invoice-data']['chemical-report-data']=[
			'report_type'	=>	'newyork',
			'report_id'		=>	$report_ids
		];


		$emailContent="
					<table style='border:2px dashed black; padding:10px;text-align:left;'>
					<caption><b>Technician Details</b></caption>
						<tr>
							<th>License No</th>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Technician State</th>
						</tr>";
		$emailContent.="
						<tr>
							<td>$technician_details->certification_id</td>
							<td>$technician_details->first_name</td>
							<td>$technician_details->last_name</td>
							<td>$branch</td>
						</tr>
					</table>
					";

		$emailContent.="
				<table style='border:2px dashed black; margin-top:10px; padding:10px;text-align:left;'>
				<caption><b>Chemical Report</b></caption>
							<tr>
								<th>Product</th>
								<th>Product Quantity</th>
								<th>Unit of Measure</th>
								<th>Date</th>
								<th>Country Code</th>
								<th>Address of application</th>
								<th>Application City</th>
								<th>Application Zip</th>
								<th>Dosage Rate</th>
								<th>Method of application</th>
								<th>Target Organisms</th>
								<th>Application Place</th>
							</tr>";

		foreach($_POST['product'] as $k=>$v){
			$emailContent.="
			<tr>
				<td>{$_POST['product'][$k]}</td>
				<td>{$_POST['product_quantity'][$k]}</td>
				<td>{$_POST['unit_of_measure'][$k]}</td>
				<td>{$_SESSION['invoice-data']['client-data']['date']}</td>
				<td>{$_POST['county_code'][$k]}</td>
				<td>{$_POST['application_address'][$k]}</td>
				<td>{$_POST['application_city'][$k]}</td>
				<td>{$_POST['application_zip'][$k]}</td>
				<td>{$_POST['dosage_rate'][$k]}</td>
				<td>{$_POST['method_of_application'][$k]}</td>
				<td>{$_POST['target_oranisms'][$k]}</td>
				<td>{$_POST['application_place'][$k]}</td>
			</tr>";
		}

		$emailContent.="</table>";

		$to=['gamchemicalreports@gmail.com'];
		$subject="Technician Chemical Report - New York";
		$tos = [];
		$tos[] = [
			'email'	=>	'gamchemicalreports@gmail.com',
			'name'	=>	'GAM Office'
		];

		(new Sendgrid_child)->sendTemplateEmail($tos, $subject, $emailContent);

		//set session that chemical report is saved
		
		$_SESSION['invoice-data']['county_code'] = $_POST['county_code'];
		
		(new InvoiceFlow)->callNextPageInFlow();
    }
    

	public function download_ny_animal_trapping_report(){

		global $wpdb;
		$data=$wpdb->get_results("select * from {$wpdb->prefix}ny_animal_trapping_report where DATE(date) >='{$_POST['start_date']}' and DATE(date) <= '{$_POST['end_date']}' ");

		if($_POST['report_type']=="csv"){
			$this->ny_animal_trapping_csv_report($data);
		}
		else{
			$chemical_report_html='
			<!DOCTYPE html>
				<html lang="en">
				<head>
					<meta charset="UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<title>New York Animal Trapping Report</title>
					<style>
						.text-center{
							text-align: center;
						}
						.text-underline{
							text-decoration: underline;
						}
					</style>
				</head>
				<body>
					<h3 class="text-center">Nuisance Wildlife Control</h3>
					<p><b>License Duration</b> October 1 <span></span> to September 30 </p>
				
					<h4 class="text-center"><b>APPLICANT INFORMATION</b></h4>
					<p>Name: <span class="text-underline">Greg Migilaccio</span> &nbsp; &nbsp; &nbsp; Address: <span class="text-underline">1850 clinton st buffalo ny 14206</span></p>
					<p>Telephone No. <span class="text-underline">516 7245785</span> &nbsp; &nbsp; &nbsp; NWCO License Number <span class="text-underline">#3082</span> &nbsp; &nbsp;Conty of Residence: <span class="text-underline">Nassau</span></p>
				
					<table>
						<thead>
							<tr>
								<th>Name and address of complaing</th>
								<th>Data performed</th>
								<th>Nuisance species</th>
								<th>Complaint Type</th>
								<th>Abatement method</th>
								<th>Area of complaint</th>
								<th>Number of traps</th>
								<th>Species and number taken</th>
								<th>Disposition of animal</th>
							</tr>
						</thead>
						<tbody>';
	
			if(is_array($data) && count($data)>0){
				foreach ($data as $key => $value) {
					$chemical_report_html.="
							<tr>
								<td>$value->name_address</td>
								<td>$value->data_performed</td>
								<td>$value->nuissance_species</td>
								<td>$value->complaint_type</td>
								<td>$value->abatement_method</td>
								<td>$value->area_of_complaint</td>
								<td>$value->no_of_traps</td>
								<td>$value->speicies_no_taken</td>
								<td>$value->desposition_of_animal</td>
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
			$mpdf->Output('New York Animal Trapping',"D");
		}
	}
    
	public function ny_animal_trapping_report(){
		global $wpdb;

		$technician_id = (new Technician_details)->get_technician_id();

		$data=[
			'technician_id'			=>	$technician_id,
			'date'					=>	date('Y-m-d'),
			'name_address'			=>	$_POST['name_address'],
			'date_performed'		=>	$_POST['date_performed'],
			'nuissance_species'		=>	$_POST['nuissance_species'],
			'complaint_type'		=>	'Inside the Home',
			'abatement_method'		=>	$_POST['abatement_method'],
			'area_of_complaint'		=>	$_POST['area_of_complaint'],
			'no_of_traps'			=>	$_POST['no_of_traps'],
			'speicies_no_taken'		=>	$_POST['speicies_no_taken'],
			'desposition_of_animal'	=>	$_POST['desposition_of_animal'],
		];

		$res = $wpdb->insert($wpdb->prefix."ny_animal_trapping_report",$data);
		
		if($res){
			$message = "Newyork animal trapping report submitted successfully";
			$this->setFlashMessage($message,'success');
		}
		else{
			$message = "Something went wrong, please try again later";
			$this->setFlashMessage($message,'danger');
			wp_redirect($_POST['page_url']);
			return;
		}

		$report_id=$wpdb->insert_id;

		// set report data as session for sending along with invoice
		$_SESSION['invoice-data']['chemical-report-data'] = [
			'report_type'	=>	'newyork_animal_trapping_report',
			'report_id'		=>	$report_id
		];

		(new InvoiceFlow)->callNextPageInFlow();
	}

	public function ny_animal_trapping_csv_report($report_data){
		header("Content-Disposition: attachment; filename=\"NY Animal Trapping Report.xls\"");
		header("Content-Type: application/vnd.ms-excel;");
		header("Pragma: no-cache");
		header("Expires: 0");
		$out = fopen("php://output", 'w');

		$header_line=[
			'Name and address of complaing',
			'Data performed',
			'Nuisance species',
			'Complaint Type',
			'Abatement method',
			'Area of complaint',
			'Number of traps',
			'Species and number taken',
			'Disposition of animal',
		];

		fputcsv($out, $header_line,"\t");

		foreach ($report_data as $data)
		{
			$line=[
				$data->name_address,
				$data->data_performed,
				$data->nuissance_species,
				$data->complaint_type,
				$data->abatement_method,
				$data->area_of_complaint,
				$data->no_of_traps,
				$data->speicies_no_taken,
				$data->desposition_of_animal,				
			];
			
			fputcsv($out, $line,"\t");
		}
		fclose($out);	
	}
	
	public function newyork_csv_report_download($report_data){

		// echo "<pre>";print_r($report_data);wp_die();

		header("Content-Disposition: attachment; filename=\"Newyork Report.xls\"");
		header("Content-Type: application/vnd.ms-excel;");
		header("Pragma: no-cache");
		header("Expires: 0");
		$out = fopen("php://output", 'w');

		$header_line=[
			'EPA Reg. No.',
			'PRODUCT NAME',
			'QUANTITY USED',
			'UNITS',
			'DATE OF APPLICATION',
			'COUNTY CODE',
			'ADDRESS',
			'MUNICIAPLITY (CITY,VILLAGE,ETC.)',
			'ZIP CODE'
		];

		fputcsv($out, $header_line,"\t");

		foreach ($report_data as $data)
		{
			$address=json_decode($data->address_of_application);

			$line=[
				$data->epa_reg_no,
				$data->name,
				$data->product_quantity,
				$data->unit_of_measurement,
				$data->date,
				$data->county_code,
				$address[0],
				$address[1],
				$address[2],
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
				select CN.*,C.name,C.dosage_rate,C.epa_reg_no
				from {$wpdb->prefix}chemicals_newyork CN
				left join {$wpdb->prefix}chemicals C
				on CN.chemical_id=C.id
				where CN.id IN ($report_ids)
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
					<h3 class="text-center">Chemical Report Newyork</h3>
					<table class="table table-striped table-hover">
						<thead>
							<tr>
								<th>Chemical</th>
								<th>Dosage Rate</th>
								<th>Epa Reg. No.</th>
								<th>Product Quantity</th>
								<th>Unit of Measurement</th>
								<th>County Code</th>
								<th>Address</th>
								<th>Method Of Application</th>
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
								<td>$data->county_code</td>
								<td>".implode(" - ",json_decode($data->address_of_application))."</td>
								<td>$data->method_of_application</td>
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
		else{
			return null;
		}


	}

	public function ny_animal_trapping_pdf_content($report_id){
		global $wpdb;

		$report_data=$wpdb->get_row("select * from {$wpdb->prefix}ny_animal_trapping_report where id='$report_id'");
		$report_html="<!DOCTYPE html>
		<html lang='en'>
			<head>
				<meta charset='UTF-8'>
				<meta name='viewport' content='width=device-width, initial-scale=1.0'>
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
				<h3 class='text-center'>Newyork Animal Trapping Report</h3>
				<table class='table table-striped table-hover'>
					<thead>
						<tr>
							<th>Name and address of complainant</th>
							<td>$report_data->name_address</td>
						</tr>
						<tr>
							<th>Data Performed</th>
							<td>".date('d M y',strtotime($report_data->date_performed))."</td>
						</tr>
						<tr>
							<th>Nuissance Species Options</th>
							<td>$report_data->nuissance_species</td>
						</tr>
						<tr>
							<th>Abatement Method</th>
							<td>$report_data->abatement_method</td>
						</tr>
						<tr>
							<th>Area of Complaint</th>
							<td>$report_data->area_of_complaint</td>
						</tr>
						<tr>
							<th>Complaint Type</th>
							<td>$report_data->complaint_type</td>
						</tr>
						<tr>	
							<th>No. of Traps</th>
							<td>$report_data->no_of_traps</td>
						</tr>
						<tr>
							<th>Species & number taken</th>
							<td>$report_data->speicies_no_taken</td>
						</tr>
						<tr>
							<th>Disposition of animal</th>
							<td>$report_data->desposition_of_animal</td>
						</tr>
					</thead>
				</table>
			</body>
			</html>
			
			";
		
		return $report_html;
	}
    

}

new ChemicalReportNewyork();