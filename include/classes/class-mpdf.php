<?php

// MPDF USED TO GENERATE PDF FILES WITH DATA

class Mpdf extends GamFunctions{

    function __construct(){
        // load mpdf php sdk from vendor 
        self::loadVendor();
    }

    public function save_file_with_content($content,$file_path){
        $mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
        $mpdf->WriteHTML($content);
        $mpdf->Output($file_path,"F");
    }

    public function saveMiniStatementPdf(string $mini_statment_html){

        try{
            $upload_dir = wp_upload_dir();

            $file_name=date('Y/m/').uniqid().".pdf"; //file name
            $dir_path=$upload_dir['basedir']."/pdf/statements/";

            // generates the saving directory path if not existed (year/month)
            $this->genreate_saving_directory($dir_path);
            $file_path=$dir_path.$file_name; // file name for mdpf to save the file in storage
            $db_dir_path="/pdf/statements/".$file_name; // for referenct in db

            // for saving mini statement in database
            $mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
            $mpdf->WriteHTML($mini_statment_html);
            $mpdf->Output($file_path,"F");

            return [$db_dir_path, $file_path];
        }
        catch(Exception $e){
            return [false, false];
        }
    }

    public function downloadMiniStatement(string $mini_statment_html){
        $mpdf = new \Mpdf\Mpdf(['allow_output_buffering' => true]);
        $mpdf->WriteHTML($mini_statment_html);
        $mpdf->Output('Mini Statement.pdf',"D");
    }

    public function generateInvoicePdf(int $invoice_id){
        $upload_dir = wp_upload_dir();

        $invoice = (new Invoice)->getInvoiceById($invoice_id);
        $template = (new Invoice)->invoicePdfContent($invoice);

        // generate pdf and send to client
        $invoice_upload_path = $upload_dir['basedir']."/pdf/invoice/temp/invoice_".date('d-m-y-h-i-s').".pdf";
        $this->save_file_with_content($template,$invoice_upload_path);

        return $invoice_upload_path;
    }

}

new Mpdf();