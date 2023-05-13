<?php

class ContractTemplates extends GamFunctions{

    public function yearly_termite_template($data){

        $upload_dir=wp_upload_dir();

        list($imgpath,$image_file,$img_url)=$this->save_signature($_POST["signimgurl"],'maintenance',$data['name']);


        $emailContent=$this->letter_head();

        if($data['buildings_treated']=="other"){
            $buildings_treated=$data['buildings_treated_other'];
        }
        else{
            $buildings_treated=$data['buildings_treated'];
        }


        $emailContent.="<table>";

        $emailContent.="<caption><b>YEARLY TERMITE CONTRACT</b></caption>";

        $emailContent.="
                    <tr>
                        <th>Name</th>
                        <td>{$data['name']}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{$data['address']}</td>
                    </tr>
                    <tr>
                        <th>Phone No.</th>
                        <td>{$data['phone_no']}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{$data['email']}</td>
                    </tr>
                    <tr>
                        <th>Descriptoin of structure</th>
                        <td>{$data['description_of_structure']}</td>
                    </tr>
                    <tr>
                        <th>Buildings Treated</th>
                        <td>$buildings_treated</td>
                    </tr>
                    <tr>
                        <th>Area Treated</th>
                        <td>{$data['area_treated']}</td>
                    </tr>
                    <tr>
                        <th>Type of Termit</th>
                        <td>{$data['type_of_termite']}</td>
                    </tr>
                    <tr>
                        <th>Contract Amount</th>
                        <td>\${$data['amount']}</td>
                    </tr>
                    <tr>
                        <th>Start Date</th>
                        <td>".date('d M Y',strtotime($data['start_date']))."</td>
                    </tr>
                    <tr>
                        <th>End Date</th>
                        <td>".date('d M Y',strtotime($data['end_date']))."</td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <th>Credit Card No</th>
                        <th>Expiration</th>
                        <th>Security Code</th>
                    </tr>
                    <tr>
                        <td>{$_POST['card_details']['creditcardnumber']}</td>
                        <td>{$_POST['card_details']['cc_month']}/{$_POST['card_details']['cc_year']}</td>
                        <td>{$_POST['card_details']['cccode']}</td>
                    </tr>
                </table>";
        
        $emailContent.=$this->yearly_termite_generate_conditions($data['amount']);
    
        $emailContent.="\n <p style='font-size:12px; font-style:italic;'><img style='width:20px;height:20px;' src='".$upload_dir['baseurl']."/2019/11/checkmark.png' />I understand this is a 12 month commitment for the property I have listed, and I am responsible for the full value of this contract. I understand my card will be billed monthly for the amount stated above.</p>";
    
        $emailContent.="<div style='float:left;width: 40%;margin: 8% 5% auto;font-size:22px;'><img src='".$imgpath."'/>";
    
        $emailContent.=$this->letter_footer();

        return [$emailContent,$img_url];

    }

    public function yearly_termite_generate_conditions($amount=''){

        $template="
        <h3 class='page-header'>General Conditions</h3>

        <p>This contract between Gam Exterminating and Customer covers only the primary structure/  areas listed above.</p>

        <p>For the sum of <b><span class='contract_amount'>[CONTRACT_AMOUNT]</span></b>, GAM will provide the necessary and appropriate service to protect the identified structure(s) against the infestation of termites. This Contract does not cover any infestation of, or damage by, any other wood destroying organism other than those identified here  in above. This contract does not cover any structural damage to home. This contract will award a guarentee of no termites for a period of 1 year at the specified property, otherwise GAM Exterminating shall return for reservice to remedy any such relevant problem at no additional charges. Proof of termites must be evident in order to issue reservice if requested.
        For the sum of <b><span class='contract_amount'>[CONTRACT_AMOUNT]</span></b>, client may renew there contract for year 2 and beyond. This is subject to a termite inspection prior to issuance.</p>

        <ul>
            <li>
                This contract shall terminate upon transfer of ownership of the described structure
                -Customer warrants full cooperation during the term of this contract, and agrees to maintain the treated area(s)  free from any factors contributing to infestation, such as wood, trash, lumber, direct wood-soil contact, or standing water under pier type structure. Customer agrees  to notify Central Termite and Pest Control of and to eliminate faulty plumbing, leaks, and dampness from drains, condensation or leaks from the roof or otherwise  onto, or under said area(s) treated. Specifically, if faulty roofs are the cause of creating termite damage in any form, the cost of repairs will be the sole responsibility  of the owner. GAM reserves the right to terminate the contract if Customer fails to correct any condition, including, but not limited  to, the conditions listed above, which contribute or may contribute to infestation. is not responsible for any damage caused to the structure(s) treated as a  result of any said conditions. GAM shall be released from any further obligation under the Contract upon notice of termination to Customer. 
                -GAMs liability under the Contract shall be terminated and excused from the performance of any obligations  under this Contract should GAM be prevented or delayed from fulfilling its responsibilities under terms of this Contract by reasons or  circumstances reasonably beyond its control, including, but not limited to, acts of war, whether declared or undeclared, acts of any duly constituted government  authority, strikes, “acts of God” or refusal of Customer to allow Central Termite and Pest Control access to the structure(s) for the purpose of inspecting or carrying  out other terms and conditions of this Contract. 
            </li>

            <li> 
                GAM is not responsible for the repair of either visible damage (noted on the attached graph) or of hidden damage existing as of the date of this Contract. The attached graph covers only those areas that were visible, accessible and unobstructed at the time of inspection and does not cover areas such as, but not limited  to, enclosed or inaccessible areas concealed by wall coverings, floor coverings, ceilings, furniture, equipment, appliances, stored articles, or any portion of the  structure in which inspection would necessitate removing or defacing any part of the structure because damage may be present in areas which are inaccessible to a  visual inspection. Central Termite does not guarantee the damage disclosed on the attached graph represents all of the existing damage as of the date of this  Contract. The graph is not to scale.
            </li>  

            <li>
                GAM shall not be responsible for any damage to the structure(s) caused by wood destroying  organisms or insects whether visible or hidden, or  any cost or expenses incurred by Customer as a result of such damage, or any damage caused by or related  to any of the conditions described above. If at any time termite damage is discovered, treatment shall be rendered.
            </li>

            <li>
                GAM Exerminatings liability under this contract will be terminated if GAM is prevented from fulfilling its responsibilities under the terms of  this Contract by circumstances or caused beyond the control of Gam Exterminating.
            </li>

        </ul>";

        if(!empty($amount)){
            $template=str_replace("[CONTRACT_AMOUNT]","$".$amount,$template);
        }

        return $template;
    }

    public function letter_head(){

        $upload_dir=wp_upload_dir();

        $template='<!DOCTYPE html>
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
                        .text-center{
                            text-align:center;
                        }
                    </style>';

        $template.='<img style="max-width:100%; margin-bottom: 2%;" src="'.$upload_dir['baseurl'].'/2019/10/GAM-Exterminating-logo-2.png"/>';

        return $template;

        
    }

    public function letter_footer(){

        $template="
                </body>
            </html>";

        return $template;


    }

}