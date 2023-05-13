<?php

use mikehaertl\pdftk\Pdf;

class TermitePaperWork extends GamFunctions
{

    function __construct()
    {
        add_action('admin_post_send_termite_paper_work_to_client', array($this, 'send_termite_paper_work_to_client'));
        add_action('admin_post_nopriv_send_termite_paper_work_to_client', array($this, 'send_termite_paper_work_to_client'));

        add_action('admin_post_skip_termite_page', array($this, 'skip_termite_page'));
        add_action('admin_post_nopriv_skip_termite_page', array($this, 'skip_termite_page'));

        add_action('admin_post_florida_wood_inspection_report', array($this, 'florida_wood_inspection_report'));
        add_action('admin_post_nopriv_florida_wood_inspection_report', array($this, 'florida_wood_inspection_report'));

        add_action('admin_post_termite_paperwork_certificate_new', array($this, 'termite_paperwork_certificate_new'));
        add_action('admin_post_nopriv_termite_paperwork_certificate_new', array($this, 'termite_paperwork_certificate_new'));

        add_action('admin_post_termite_graph_new', array($this, 'termite_graph_new'));
        add_action('admin_post_nopriv_termite_graph_new', array($this, 'termite_graph_new'));

        add_action('admin_post_florida_consent_form_new', array($this, 'florida_consent_form_new'));
        add_action('admin_post_nopriv_florida_consent_form_new', array($this, 'florida_consent_form_new'));

        add_action('admin_post_npma33_form', array($this, 'npma33_form'));
        add_action('admin_post_nopriv_npma33_form', array($this, 'npma33_form'));
    }

    public function npma33_form()
    {
        global $wpdb;

        // check for nonce field first
        $this->verify_nonce_field('npma33_form');

        $upload_dir = wp_upload_dir();

        $pdf_data = [
            'NPMA-33-1-1'    =>  "Gam Services USA Inc",
            'NPMA-33-1-2'    =>  "JB293622",
            'NPMA-33-1-3'    =>  date('Y-m-d'),
            'NPMA-33-1-4'    =>  $_POST['address_of_property'],
            'NPMA-33-1-5'    =>  $_POST['inspector_name'],
            'NPMA-33-1-6'    =>  $_POST['structures_inspected'],
            'NPMA-33-2Chk'   =>  $_POST['evidence_of_wood_destroying'],
            'NPMA-33-2b-1Exp2'    =>  $_POST['evidence_type'],
            'NPMA-33-3Chk'             =>  $_POST['treatement_recommendation'],
            'NPMA-33-3a-Exp1-2'        =>  $_POST['recommendation_note'],
            'NPMA-33-5-Exp2'           =>  $_POST['comments'],
        ];

        if (is_array($_POST['obstruction_and_inacessible_areas']) && count($_POST['obstruction_and_inacessible_areas']) > 0) {
            foreach ($_POST['obstruction_and_inacessible_areas'] as $key => $value) {

                if (array_key_exists('type', $value)) {
                    if ($value['type'] == "Attic") {
                        $pdf_data['NPMA-33-4Chk-4'] = "Yes";
                        $pdf_data['NPMA-33-4-4Exp'] = $value['note'];
                    } else if ($value['type'] == "Exterior") {
                        $pdf_data['NPMA-33-4Chk-6'] = "Yes";
                        $pdf_data['NPMA-33-4-6Exp'] = $value['note'];
                    } else if ($value['type'] == "Crawlspace") {
                        $pdf_data['NPMA-33-4Chk-2'] = "Yes";
                        $pdf_data['NPMA-33-4-2Exp'] = $value['note'];
                    } else if ($value['type'] == "Basement") {
                        $pdf_data['NPMA-33-4Chk-1'] = "Yes";
                        $pdf_data['NPMA-33-4-1Exp'] = $value['note'];
                    } else if ($value['type'] == "Main Leve") {
                        $pdf_data['NPMA-33-4Chk-3'] = "Yes";
                        $pdf_data['NPMA-33-4-3Exp'] = $value['note'];
                    } else if ($value['type'] == "Garage") {
                        $pdf_data['NPMA-33-4Chk-5'] = "Yes";
                        $pdf_data['NPMA-33-4-5Exp'] = $value['note'];
                    } else if ($value['type'] == "Porch") {
                        $pdf_data['NPMA-33-4Chk-7'] = "Yes";
                        $pdf_data['NPMA-33-4-7Exp'] = $value['note'];
                    } else if ($value['type'] == "Addition") {
                        $pdf_data['NPMA-33-4Chk-8'] = "Yes";
                        $pdf_data['NPMA-33-4-8Exp'] = $value['note'];
                    } else if ($value['type'] == "Other") {
                        $pdf_data['NPMA-33-4Chk-9'] = "Yes";
                        $pdf_data['NPMA-33-4-9Exp'] = $value['note'];
                    }
                }
            }
        }

        // insert data into database
        $data = [
            'technician_id'                         => (new Technician_details)->get_technician_id(),
            'inspector_name'                        =>  $_POST['inspector_name'],
            'address_of_property'                   =>  $_POST['address_of_property'],
            'structures_inspected'                  =>  $_POST['structures_inspected'],
            'report_requested_by'                   =>  $_POST['inspection_and_report_requested_by'],
            'client_name'                           =>  $_POST['client_name'],
            'client_email'                          =>  $_POST['client_email'],
            'report_sent_to'                        =>  $_POST['report_sent_to'],
            'evidence_of_wood_destroying'           =>  $_POST['evidence_of_wood_destroying'],
            'obstruction_and_inacessible_areas'     =>  json_encode($_POST['obstruction_and_inacessible_areas']),
            'treatement_recommendation'             =>  $_POST['treatement_recommendation'],
            'recommendation_note'                   =>  $_POST['recommendation_note'],
            'additional_comment'                    =>  $_POST['comments'],
            'date_of_inspection'                    =>  $_POST['inspection_date'],
            'date_created'                          =>  date('Y-m-d'),
            'form_type'                             =>  $_POST['npma_form']
        ];


        if ($_POST['evidence_of_wood_destroying'] == "yes") {

            $pdf_data['Check Box8'] = "Yes";

            if ($_POST['evidence_type'] == "option1") {
                $pdf_data['NPMA-33-2b-1'] = "Yes";
                $pdf_data['NPMA-33-2b-1Exp2'] = $_POST['evidence_description_and_location'];
            }
            if ($_POST['evidence_type'] == "option2") {
                $pdf_data['NPMA-33-2b-2'] = "Yes";
                $pdf_data['NPMA-33-2b-2Exp2'] = $_POST['evidence_description_and_location'];
            }
            if ($_POST['evidence_type'] == "option3") {
                $pdf_data['NPMA-33-2b-3'] = "Yes";
                $pdf_data['NPMA-33-2b-3Exp2'] = $_POST['evidence_description_and_location'];
            }

            $data['evidence_type'] = $_POST['evidence_type'];
            $data['evidence_description_and_location'] = $_POST['evidence_description_and_location'];
        } else {
            $pdf_data['Check Box7'] = "Yes";
        }

        // echo "<pre>";print_r($pdf_data);wp_die();

        $input_file_path = get_template_directory() . "/assets/pdf/NPMA33.pdf";
        $path = "/pdf/npma/";

        $directory_path = $upload_dir['basedir'] . $path;
        $db_saving_path = $path . date('Y/m/d/') . date('his') . "_" . $this->quickRandom(6) . ".pdf";
        $this->genreate_saving_directory($directory_path);

        $output_file_path = $upload_dir['basedir'] . $db_saving_path;

        // load pdftk php sdk from vendor 
        self::loadVendor();

        // Fill form with data array
        $pdf = new Pdf($input_file_path);
        $result = $pdf->fillForm($pdf_data)
            ->needAppearances()
            ->flatten()
            ->saveAs($output_file_path);

        // echo "<pre>";print_r($pdf_data);wp_die();    

        $data['pdf_link'] = $db_saving_path;

        $insert_res = $wpdb->insert($wpdb->prefix . "npma", $data);

        // send report to client on his email address
        $files = [];
        $files[0]['file'] = $output_file_path;
        $files[0]['type'] = "application/pdf";

        $files[0]['name'] = "NPMA-33 FORM";
        $subject = "Wood Destroying Insect Inspection Report";
        $message = "<p>Here is your Wood Destroying Insect Inspection Report from Gam Exterminating Services.</p>";

        $tos = [];
        $tos[] = [
            'email' =>  $_POST['client_email'],
            'name'  =>  $_POST['client_name']
        ];

        // echo "<pre>";print_r($subject);wp_die();


        // send invoice pdf with message to client
        $res = (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $message, $files);

        if ($res['status'] == "success") {

            $message = "NPMA-33 Form submitted successfully and sent to client as well";
            $this->setFlashMessage($message, 'success');
        } else {
            if ($insert_res) {
                $message = "NPMA-33 Form submitted successfully but there was some error sending report to client";
                $this->setFlashMessage($message, 'warning');
            } else {
                $message = "Something went wrong, please try again later";
                $this->setFlashMessage($message, 'danger');
            }
        }

        if (isset($_POST['page_url'])) {
            wp_redirect($_POST['page_url']);
        }
    }

    public function florida_consent_form_template($data, $imgpath)
    {

        $template = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>
        </head>
        <style>
            body{
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }
            .text-center{
                text-align:center;
            }
            .col-md-6 {
                display: inline-block;
                width: 45%;
            }
            p{
                font-size: 14px;
                line-height: 1.5;
                margin: 25px 0 20px;
            }
        </style>
        <body>';

        $template .= '
        <p class="text-center">Florida Department of Agriculture and Consumer Services <br> Division of Agricultural Environmental Services</p>

        <h3 class="text-center">Consumer Constent Form</h3>

        <p class="text-center"><small>Rule 5E-14.105, F.A.C.<br>Telephone: (850) 617-7996; Fax: (850) 617-7981</small></p>

        <p>A pest control company must give you a written contract prior to any preventative or corrective treatment of each woad-destroying organism. Unless issued for pre-construction treatment, this contract must be provided to you before any work is done and before any payment is made so that you have an opportunity to thoroughly read it and understand exactly what services are being provided.</p>

        <p>TIPS: Be sure you understand:</p>

        <ul>
            <li>All structures or building that will be included in the contract.</li>

            <li>The duration of the contract and its renewal terms. (Most contracts are for five year periods, renewable annually, but others renew perpetually.) Verify how long the renewal rate will remain the same and, if it\'s allowed to increase, does the contract disclose a basis for the renewal increase (maximum percentage, cost of living, inflation, etc.)</li>

            <li>Make sure the common name of the wood-destroying organism to be controlled by the contract is indicated and you understand which organisms are NOT covered.</li>

            <li>The contract should state whether the treatment is preventative or corrective (treating an active infestation). Verify if a treatment is to be performed or not. If not, verify that the company has appropriate insurance coverage based on inspection and not based on “work performed”</li>

            <li>The contract should state if it is a retreatment only or a retreatment and repair contract. If itis a retreatment and repair contract, make sure you understand what condition must occur to require the company to perform retreatment and/or repair. Also confirm that the maximum repair amount the company will pay is disclosed.</li>

            <li>
                Finally, determine if the contract is transferable to a new owner if you happen to sell your property and the terms associated with this. Some companies charge a fee and others just request a written notification.

                <ul>
                    <li>Rule 5E-14.105(7), Florida Administrative Code, states, “A structure shall nat be knowingly placed under a second contract for the same wood-destroying organism control or preventative treatment in disregard of the first contract, without first obtaining specific written consent signed by the property owner or authorized agent using the Consumer Consent Form(FDACS-13671 Rev. 09/16).”</li>

                    <li>| understand that | have an existing contract with <b>GAM Exterminating Services</b> to provide wood-destroying organism(s) control or preventative treatment, and | am voluntarily entering into a second contract for control or preventative treatment for the same wood-destroying organism(s), which may void the terms of the existing contract.</li>
                </ul>

            </li>
        </ul>

        <hr>

        <table>
                <tr>
                    <td><small><b>' . $data["client_name"] . '</b> <br> Print Name of Consumer</small></td>
                    <td><small><b>' . date("d M Y", strtotime($data["date"])) . '</b> <br> Date</small></td>
                </tr>
                <tr>
                    <td><small> <img src="' . $imgpath . '" /> <br> <p> Signature of Consumer </p> </small></td>
                    <td><small><b>' . $data["title"] . '</b> <br> Title</small></td>
                </tr>
                <tr>
                    <td><small><b>' . (new Technician_details)->get_technician_name() . '</b> <br> Name of Pest Control Representative</small></td>
                    <td><small><b>' . date("d M Y", strtotime($data["date_2"])) . '</b> <br> Date</small></td>
                </tr>
                <tr>
                    <td><small><b>' . (new Technician_details)->get_technician_name() . '</b> <br> Signature of Pest Control Representative</small></td>
                    <td><small><b>GAM Exterminating Services</b> <br> Company</small></td>
                </tr>
        </table>
        
        <p><small>FDACS-13671 Rev. 09/16</small></p>
        ';

        $template .= "
        </body>
        </html";


        return $template;
    }

    public function florida_consent_form_new()
    {
        global $wpdb;
        $upload_dir = wp_upload_dir();

        // save signature into system first
        list($imgpath, $imagefile, $img_url) = $this->save_signature($_POST['signimgurl'], 'florida_consent_form', $_POST['client_name']);

        $template = $this->florida_consent_form_template($_POST, $imgpath);

        $directory_path = "/pdf/florida-consent-forms/";
        $download_path = date('Y/m/d/') . $this->quickRandom(6) . ".pdf";
        $full_download_path = $directory_path . $download_path;
        $full_saving_path = $upload_dir['basedir'] . $full_download_path;
        $this->genreate_saving_directory($upload_dir['basedir'] . $directory_path);

        $data = [
            'technician_id'         => (new Technician_details)->get_technician_id(),
            'client_name'           =>  $_POST['client_name'],
            'client_email'          =>  $_POST['client_email'],
            'pdf_path'              =>  $full_download_path,
            'date_created'          =>  date('Y-m-d')
        ];

        // update the file in termite paperwork table
        $res = $wpdb->insert($wpdb->prefix . "florida_consumer_consent_form", $data);

        if (!$res) {
            $message = "Something went wrong, please try again later";
            $this->setFlashMessage($message, 'danger');
            wp_redirect($_POST['page_url']);
            return;
        }

        // load mpdf php sdk from vendor 
        self::loadVendor();

        $upload_dir = wp_upload_dir();
        $mpdf = new \Mpdf\Mpdf([
            'allow_output_buffering' => true,
            'mode' => 'utf-8',
            'format' => [250, 320]
        ]);
        $mpdf->WriteHTML($template);
        $mpdf->Output($full_saving_path, 'F');

        // send email to client
        $subject = "Florida Consumer Consent Form";
        $message = "<p>Here is your copy of Florida Consumer Consent Form from Gam Exterminating.</p>";
        $tos = [];
        $tos[] = [
            'email' =>  $_POST['client_email'],
            'name'  =>  $_POST['client_name']
        ];

        $files = [];
        $files[0]['file'] = $full_saving_path;
        $files[0]['type'] = "application/pdf";
        $files[0]['name'] = "Florida Conumer Consent Form";

        $res = (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $message, $files);

        if ($res['status'] == "success") {
            $message = "Termite Graph Data sent to client successfully";
            $this->setFlashMessage($message, 'success');
        } else {
            $message = "Error sending email to client, please try again later or check for data in backend";
            $this->setFlashMessage($message, 'danger');
        }

        // redirect to termite paperwork page at last
        if (isset($_POST['page_url']) && !empty($_POST['page_url'])) {
            wp_redirect($_POST['page_url']);
        } else {
            wp_redirect("/termite-paperwork?paper-id={$_POST['paper-id']}");
        }
    }

    public function termite_graph_new()
    {
        global $wpdb;

        $paper_id = '';
        $upload_dir = wp_upload_dir();

        if (isset($_FILES['termite_graph']) && is_array($_FILES['termite_graph'])) {

            $directory_path = "/images/termite-graph/";
            $download_path = date('Y/m/d/') . $this->quickRandom(6) . ".png";
            $full_download_path = $directory_path . $download_path;
            $full_saving_path = $upload_dir['basedir'] . $full_download_path;
            $this->genreate_saving_directory($upload_dir['basedir'] . $directory_path);

            // upload file on server
            file_put_contents($full_saving_path, file_get_contents($_FILES["termite_graph"]["tmp_name"]));

            $data = [
                'technician_id'         => (new Technician_details)->get_technician_id(),
                'client_name'           =>  $_POST['client_name'],
                'client_email'          =>  $_POST['client_email'],
                'termite_graph'         =>  $full_download_path,
            ];

            // update the data in database
            $final_status = $wpdb->insert($wpdb->prefix . "termite_graph", $data);
            if (!$final_status) $this->sendErrorMessage($_POST['page_url']);


            // send the termite graph to client
            $subject = "Termite Graph";
            $message = "<p>Here is your copy of termite graph from Gam Exterminating.</p>";
            $tos = [];
            $tos[] = [
                'email' =>  $_POST['client_email'],
                'name'  =>  $_POST['client_name']
            ];

            $files = [];
            $files[0]['file'] = $full_saving_path;
            $type = pathinfo($files[0]['file'], PATHINFO_EXTENSION);
            $files[0]['type'] = "image/$type";
            $files[0]['name'] = "Termite Graph";

            $res = (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $message, $files);

            if ($res['status'] == "success") {
                $message = "Termite Graph Data sent to client successfully";
                $this->setFlashMessage($message, 'success');
            } else {
                $message = "Something went wront, please try again later";
                $this->setFlashMessage($message, 'danger');
            }
        } else {
            $message = "Something went wront, please try again later";
            $this->setFlashMessage($message, 'danger');
        }

        // redirect to the page without id
        if (isset($_POST['page_url']) && !empty($_POST['page_url'])) {
            wp_redirect($_POST['page_url']);
        } else {
            wp_redirect("/termite-paperwork?paper-id={$_POST['paper_id']}");
        }
    }

    public function florida_wood_inspection_report()
    {
        global $wpdb;

        // check for nonce field first
        $this->verify_nonce_field('florida_wood_inspection_report');

        $upload_dir = wp_upload_dir();

        $pdf_data = [
            'Inspectors Name and Identification Card Number'    =>  $_POST['inspector_name'],
            'Address of Property Inspected'                     =>  $_POST['address_of_property'],
            'Structures on Property Inspected'                  =>  $_POST['structures_inspected'],
            'Inspection and Report requested by'                =>  $_POST['inspection_and_report_requested_by'],
            'Report Sent to Requestor and to'                   =>  $_POST['report_sent_to'],
            'notice_of_inspection'                              =>  $_POST['notice_of_inspection'],
            'comments'                                          =>  $_POST['comments'],
            'Date'                                              =>  date('d M Y'),
            'Date of Inspection'                                =>  $_POST['inspection_date'],
        ];

        if (is_array($_POST['obstruction_and_inacessible_areas']) && count($_POST['obstruction_and_inacessible_areas']) > 0) {
            foreach ($_POST['obstruction_and_inacessible_areas'] as $key => $value) {

                if (array_key_exists('type', $value)) {
                    if ($value['type'] == "Attic") {
                        $pdf_data['attic_checkbox'] = "Yes";
                        $pdf_data['attic'] = $value['note'];
                    } else if ($value['type'] == "Interior") {
                        $pdf_data['interior_checkbox'] = "Yes";
                        $pdf_data['interior'] = $value['note'];
                    } else if ($value['type'] == "Exterior") {
                        $pdf_data['exterior_checkbox'] = "Yes";
                        $pdf_data['exterior_note'] = $value['note'];
                    } else if ($value['type'] == "Crawlspace") {
                        $pdf_data['crawlspace_checkbox'] = "Yes";
                        $pdf_data['crawlspace_note'] = $value['note'];
                    } else if ($value['type'] == "Crawlspace") {
                        $pdf_data['other_checkbox'] = "Yes";
                        $pdf_data['other_note'] = $value['note'];
                    }
                }
            }
        }

        // insert data into database
        $data = [
            'technician_id'                         => (new Technician_details)->get_technician_id(),
            'inspector_name'                        =>  $_POST['inspector_name'],
            'address_of_property'                   =>  $_POST['address_of_property'],
            'structures_inspected'                  =>  $_POST['structures_inspected'],
            'report_requested_by'                   =>  $_POST['inspection_and_report_requested_by'],
            'client_name'                           =>  $_POST['client_name'],
            'client_email'                          =>  $_POST['client_email'],
            'report_sent_to'                        =>  $_POST['report_sent_to'],
            'evidence_of_wood_destroying'           =>  $_POST['evidence_of_wood_destroying'],
            'obstruction_and_inacessible_areas'     =>  json_encode($_POST['obstruction_and_inacessible_areas']),
            'previously_treated'                    =>  $_POST['previously_treated'],
            'notice_of_inspection'                  =>  $_POST['notice_of_inspection'],
            'structure_treated_at_inspection'       =>  $_POST['structure_treated_at_inspection'],
            'additional_comment'                    =>  $_POST['comments'],
            'date_of_inspection'                    =>  $_POST['inspection_date'],
            'form_type'                             =>  $_POST['npma_form'],
            'date_created'                          =>  date('Y-m-d')
        ];

        if ($_POST['evidence_of_wood_destroying'] == "yes") {

            $pdf_data['Check Box8'] = "Yes";

            if ($_POST['evidence_type'] == "option1") {
                $pdf_data['Check Box9'] = "Yes";
                $pdf_data['live_wdo'] = $_POST['evidence_description_and_location'];
            }
            if ($_POST['evidence_type'] == "option2") {
                $pdf_data['Check Box10'] = "Yes";
                $pdf_data['evidence'] = $_POST['evidence_description_and_location'];
            }
            if ($_POST['evidence_type'] == "option3") {
                $pdf_data['Check Box11'] = "Yes";
                $pdf_data['damage'] = $_POST['evidence_description_and_location'];
            }

            $data['evidence_type'] = $_POST['evidence_type'];
            $data['evidence_description_and_location'] = $_POST['evidence_description_and_location'];
        } else {
            $pdf_data['Check Box7'] = "Yes";
        }

        if ($_POST['previously_treated'] == "yes") {

            $first100 = substr($_POST['previous_treatement_note'], 0, 100);
            $theRest = substr($_POST['previous_treatement_note'], 100);

            $pdf_data['Check Box17'] = "Yes";
            $pdf_data['evidence_desc_part_1'] = $first100;
            $pdf_data['evidence_desc_part_2'] = $theRest;

            $data['previous_treatement_note'] = $_POST['previous_treatement_note'];
        } else {
            $pdf_data['Check Box18'] = "Yes";
        }

        if ($_POST['structure_treated_at_inspection'] == "yes") {

            $data['common_name_of_organism_treated'] = $_POST['common_name_of_organism_treated'];
            $data['name_of_pesticide_used'] = $_POST['name_of_pesticide_used'];
            $data['terms_and_condition_of_treatement'] = $_POST['terms_and_condition_of_treatement'];
            $data['method_of_treatement'] = $_POST['method_of_treatement'];
            $data['treatement_notice_location'] = $_POST['treatement_notice_location'];

            $pdf_data['Check Box19'] = "Yes";
            $pdf_data['Common name of organism treated'] = $_POST['common_name_of_organism_treated'];
            $pdf_data['Name of Pesticide Used'] = $_POST['name_of_pesticide_used'];
            $pdf_data['Terms and Conditions of Treatment'] = $_POST['terms_and_condition_of_treatement'];
            $pdf_data['treatement_notice_location'] = $_POST['treatement_notice_location'];

            if ($_POST['method_of_treatement'] == "option1") {
                $pdf_data['Check Box21'] = "Yes";
            }
            if ($_POST['method_of_treatement'] == "option2") {
                $pdf_data['Check Box22'] = "Yes";
            }
        } else {
            $pdf_data = [
                'Check Box20'   =>  'Yes'
            ];
        }

        // echo "<pre>";print_r($pdf_data);wp_die();
        $input_file_path = get_template_directory() . "/assets/pdf/florida_report_new.pdf";
        $path = "/pdf/florida-wood-inspection-report/";
        $directory_path = $upload_dir['basedir'] . $path;
        $db_saving_path = $path . date('Y/m/d/') . date('his') . "_" . $this->quickRandom(6) . ".pdf";
        $this->genreate_saving_directory($directory_path);

        $output_file_path = $upload_dir['basedir'] . $db_saving_path;

        // load pdftk php sdk from vendor 
        self::loadVendor();

        // Fill form with data array
        $pdf = new Pdf($input_file_path);
        $result = $pdf->fillForm($pdf_data)
            ->needAppearances()
            ->flatten()
            ->saveAs($output_file_path);

        $data['pdf_link'] = $db_saving_path;

        $insert_res = $wpdb->insert($wpdb->prefix . "npma", $data);

        // send repor to client on his email address
        $files = [];
        $files[0]['file'] = $output_file_path;
        $files[0]['type'] = "application/pdf";

        if ($_POST['npma_form']) {
            $files[0]['name'] = "NPMA-33 FORM";
            $subject = "Wood Destroying Insect Inspection Report";
            $message = "<p>Here is your Wood Destroying Insect Inspection Report from Gam Exterminating Services.</p>";
        } else {
            $files[0]['name'] = "Florida Wood Inspection Report";
            $subject = "Florida Wood Inspection Report";
            $message = "<p>Here is your Florida Wood Inspection Report from Gam Exterminating Services.</p>";
        }

        $tos = [];
        $tos[] = [
            'email' =>  $_POST['client_email'],
            'name'  =>  $_POST['client_name']
        ];

        // send invoice pdf with message to client
        $res = (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $message, $files);

        if ($res['status'] == "success") {
            $message = "Wood inspection report submitted successfully and sent to client as well";
            $this->setFlashMessage($message, 'success');
        } else {
            if ($insert_res) {
                $message = "Wood inspection report submitted successfully but there was some error sending report to client";
                $this->setFlashMessage($message, 'warning');
            } else {
                $message = "Something went wrong, please try again later";
                $this->setFlashMessage($message, 'danger');
            }
        }

        if (isset($_POST['page_url'])) {
            wp_redirect($_POST['page_url']);
        }
    }

    public function skip_termite_page()
    {

        global $wpdb;
        $paper_id = '';

        $status = true;

        if (isset($_POST['type']) && !empty($_POST['type'])) {
            switch ($_POST['type']) {

                case 'npma33':
                    // create new record with blank data
                    $data = [
                        'npma'          =>  'skipped',
                        'status'        =>  'in_process',
                        'technician_id' => (new Technician_details)->get_technician_id()
                    ];
                    $wpdb->insert($wpdb->prefix . "termite_paperwork", $data);
                    $paper_id = (new GamFunctions)->encrypt_data($wpdb->insert_id, 'e');
                    break;

                case 'certificate':
                    if (isset($_POST['paper-id']) && !empty($_POST['paper-id'])) {

                        $paper_id = $_POST['paper-id'];
                        $db_paper_id = (new GamFunctions)->encrypt_data($_POST['paper-id'], 'd');

                        if ($db_paper_id) {
                            $data = [
                                'certificate'   =>  'skipped'
                            ];
                            $where_data = [
                                'id'    =>  $db_paper_id
                            ];
                            $wpdb->update($wpdb->prefix . "termite_paperwork", $data, $where_data);
                        } else {
                            $status = false;
                        }
                    } else {
                        $status = false;
                    }
                    break;

                case 'termite_graph':
                    if (isset($_POST['paper-id']) && !empty($_POST['paper-id'])) {

                        $paper_id = $_POST['paper-id'];
                        $db_paper_id = (new GamFunctions)->encrypt_data($_POST['paper-id'], 'd');

                        if ($db_paper_id) {
                            $data = [
                                'termite_graph'   =>  'skipped'
                            ];
                            $where_data = [
                                'id'    =>  $db_paper_id
                            ];
                            $wpdb->update($wpdb->prefix . "termite_paperwork", $data, $where_data);
                        } else {
                            $status = false;
                        }
                    } else {
                        $status = false;
                    }

                    break;

                case 'florida_consumer_consent_form':
                    if (isset($_POST['paper-id']) && !empty($_POST['paper-id'])) {

                        $paper_id = $_POST['paper-id'];
                        $db_paper_id = (new GamFunctions)->encrypt_data($_POST['paper-id'], 'd');

                        if ($db_paper_id) {
                            $data = [
                                'florida_consumer_consent_form'   =>  'skipped'
                            ];
                            $where_data = [
                                'id'    =>  $db_paper_id
                            ];
                            $wpdb->update($wpdb->prefix . "termite_paperwork", $data, $where_data);
                        } else {
                            $status = false;
                        }
                    } else {
                        $status = false;
                    }

                    break;

                default:
                    break;
            }
        } else {
            $status = false;
        }

        if (!$status) {
            $message = "Something went wrong, please try again later";
            $this->setFlashMessage($message, 'danger');
        }

        wp_redirect("/termite-paperwork?paper-id=$paper_id");
    }

    public function send_termite_paper_work_to_client()
    {

        global $wpdb;

        $status = true;

        if (isset($_POST['paper-id']) && !empty($_POST['paper-id']) && isset($_POST['client_email']) && !empty($_POST['client_email'])) {

            // decrypt the paper id 
            $paper_id = (new GamFunctions)->encrypt_data($_POST['paper-id'], 'd');

            if ($paper_id) {

                $status = $this->send_termite_paper_work_files_to_client($paper_id, $_POST['client_email']);

                if ($status != "error") {
                    $email_status = "sent";
                } else {
                    $email_status = "failed";
                    $status = false;
                }

                // update email and status in database for termite paperwork
                $data = [
                    'client_name'   =>  $_POST['client_name'],
                    'client_email'  =>  $_POST['client_email'],
                    'email_status'  =>  $email_status,
                    'status'        =>  'finished'
                ];
                $wpdb->update($wpdb->prefix . "termite_paperwork", $data, ['id' => $paper_id]);
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }

        if (!$status) {
            $message = "Something went wrong, please try again later";
            $this->setFlashMessage($message, 'danger');
        }

        // redirect to the termite paperwork page
        if (isset($_POST['page_url']) && !empty($_POST['page_url'])) {
            wp_redirect($_POST['page_url']);
        } else {
            wp_redirect('/termite-papwerwork?paper-id' . $_POST['paper-id']);
        }
    }

    public function get_paperwork_files($paper_id)
    {

        global $wpdb;

        $data = $wpdb->get_row("select 
        {$wpdb->prefix}termite_paperwork.*, 
        {$wpdb->prefix}npma.pdf_link,
        {$wpdb->prefix}certificate.certificate_pdf
        from {$wpdb->prefix}termite_paperwork
        LEFT JOIN {$wpdb->prefix}npma
        ON {$wpdb->prefix}npma.id={$wpdb->prefix}termite_paperwork.npma
        LEFT JOIN {$wpdb->prefix}certificate
        ON {$wpdb->prefix}certificate.id={$wpdb->prefix}termite_paperwork.certificate
        where {$wpdb->prefix}termite_paperwork.id='$paper_id'");

        return $data;
    }

    public function send_termite_paper_work_files_to_client($paper_id, $client_email)
    {

        global $wpdb;

        $upload_dir = wp_upload_dir();

        $data = $this->get_paperwork_files($paper_id);

        $files = [];

        if (!empty($data->termite_graph) && $data->termite_graph != "skipped") {
            $files[0]['file'] = $upload_dir['basedir'] . $data->termite_graph;
            $type = pathinfo($files[0]['file'], PATHINFO_EXTENSION);
            $files[0]['type'] = "image/$type";
            $files[0]['name'] = "Termite Graph";
        }

        if (!empty($data->florida_consumer_consent_form) && $data->florida_consumer_consent_form != "skipped") {
            $files[1]['file'] = $upload_dir['basedir'] . $data->florida_consumer_consent_form;
            $files[1]['type'] = "application/pdf";
            $files[1]['name'] = "Florida Consumer Consent";
        }

        if (!empty($data->certificate_pdf) && $data->certificate_pdf != "skipped") {
            $files[2]['file'] = $upload_dir['basedir'] . $data->certificate_pdf;
            $files[2]['type'] = "application/pdf";
            $files[2]['name'] = "Certificate";
        }

        if (!empty($data->pdf_link) && $data->pdf_link != "skipped") {
            $files[3]['file'] = $upload_dir['basedir'] . $data->pdf_link;
            $files[3]['type'] = "application/pdf";
            $files[3]['name'] = "NPMA33";
        }

        // echo "<pre>";print_r($files);wp_die();
        $to = [$client_email];
        $subject = "Termite Paperwork";
        $message = "Thank you for your buisness with GAM Exterminating. Please Find Attached documents related to the paperwork.";

        $tos = [];
        $tos[] = [
            'email' =>  $client_email,
            'name'  =>  $data->client_name
        ];

        return (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $message, $files);
    }

    public function termite_paperwork_certificate_new()
    {
        global $wpdb;

        $upload_dir = wp_upload_dir();

        // save the signature into uploads folder
        list($imgpath, $imagefile, $img_url) = $this->save_signature($_POST['signimgurl'], 'certificate', $_POST['client_name']);

        $directory_path = "/pdf/certificates/";
        $download_path = date('Y/m/d/') . $this->quickRandom(6) . ".pdf";
        $full_download_path = $directory_path . $download_path;
        $full_saving_path = $upload_dir['basedir'] . $full_download_path;
        $this->genreate_saving_directory($upload_dir['basedir'] . $directory_path);

        // load mpdf php sdk from vendor 
        self::loadVendor();

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->SetDocTemplate(get_template_directory() . '/assets/pdf/certificate.pdf', false);
        $date_of_tretement = date('d M Y', strtotime($_POST['date_of_treatement']));

        $mpdf->WriteFixedPosHTML("<small><b>{$_POST['building_address']}</b></small>", 80, 98, 100, 100, 'auto');
        $mpdf->WriteFixedPosHTML("<small><b>$date_of_tretement</b></small>", 110, 123, 100, 100, 'auto');
        $mpdf->WriteFixedPosHTML("<small><b>{$_POST['method_of_treatement']}</b></small>", 120, 131, 100, 100, 'auto');
        $mpdf->WriteFixedPosHTML("<img src='$imgpath' width='220px' height='50px' />", 130, 140, 100, 100, 'auto');
        $mpdf->Output($full_saving_path, 'F');

        $subject = "Termite Certificate";
        $message = "Here is your copy of Gam Exterminating Termite Certificate.";
        $tos = [];
        $tos[] = [
            'email' =>  $_POST['client_email'],
            'name'  =>  $_POST['client_name']
        ];

        $files = [];
        $files[0]['file'] = $full_saving_path;
        $files[0]['type'] = "application/pdf";
        $files[0]['name'] = "Termite Certificate";

        // send paperwork to client
        $res = (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $message, $files);

        if ($res['status'] == "success") {
            $message = "Termite Certificate PDF sent to client successfully";
            $this->setFlashMessage($message, 'success');
        } else {
            $message = "Sorry system was unable to send email to client, please check in system for certificate data";
            $this->setFlashMessage($message, 'danger');
        }

        // save the certificate data into the database
        $data = [
            'technician_id'         => (new Technician_details)->get_technician_id(),
            'client_name'           =>  $_POST['client_name'],
            'client_email'          =>  $_POST['client_email'],
            'building_address'      =>  $_POST['building_address'],
            'date_of_treatement'    =>  $_POST['date_of_treatement'],
            'method_of_treatement'  =>  $_POST['method_of_treatement'],
            'tech_sign'             =>  $img_url,
            'certificate_pdf'       =>  $full_download_path,
            'date_created'          =>  date('Y-m-d')
        ];
        $db_insert_status = $wpdb->insert($wpdb->prefix . "certificate", $data);

        // redirect to certificate page
        if (isset($_POST['page_url']) && !empty($_POST['page_url'])) {
            wp_redirect($_POST['page_url']);
        } else {
            wp_redirect("/termite-paperwork?paper-id=" . $_POST['paper_id']);
        }
    }
}

new TermitePaperWork();
