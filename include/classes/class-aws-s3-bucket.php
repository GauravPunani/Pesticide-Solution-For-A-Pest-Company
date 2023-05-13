<?php

require_once get_template_directory()."/libraries/vendor/autoload.php";

use Aws\S3\S3Client;
use MicrosoftAzure\Storage\Blob\Models\Blob;

class S3bucket extends GamFunctions{

    

    function __construct(){

        $this->bucket = "gamexterminating";
        $this->aws_key = esc_attr( get_option('gam_s3bucket_api_key') );
        $this->aws_secret = esc_attr( get_option('gam_s3bucket_access_key') );

        $this->s3 = new S3Client([
            'version' => 'latest',
            'region'  => 'us-west-1',
            'credentials' => [
                'key' => $this->aws_key,
                'secret' => $this->aws_secret,
            ],
        ]);
    }

    public function uploadObject(string $file_name, string $file_path, string $file_type):array
    {
        if($file_type == "vehicle_condition"){
            $folder = "vehicle-condition";       
        }
        else{
            return [false, 'file type not found'];
        }
        
        $file_extension = pathinfo($file_name);

        $key = $folder."/".date('Ymdhis')."-".$this->quickRandom(6).".".$file_extension['extension'];

        try {
            $this->s3->putObject([
                'Bucket'        => $this->bucket,
                'Key'           => $key,
                'SourceFile'    => $file_path,
                'ACL'           => 'private',
            ]);
            return [$key, null];
        } catch (Aws\S3\Exception\S3Exception $e) {
            return [false, $e->getMessage()];
        }
    }

    public function deleteAWS3BucketObject(string $key){
        try {
            $del_date = $this->s3->deleteObject([
                'Bucket'        => $this->bucket,
                'Key'           => $key
            ]);
            return $del_date;
            //return [$key, null];
        } catch (Aws\S3\Exception\S3Exception $e) {
            return [false, $e->getMessage()];
        }
    }

    public function getObjectUrl(string $key):array{
        try{

            $cmd = $this->s3->getCommand('GetObject', [
                'Bucket' => 'gamexterminating',
                'Key'    => $key
            ]);
            $signed_url = $this->s3->createPresignedRequest($cmd, '+1 hour');
            return [$signed_url->getUri()."\n", null];
        }
        catch(Exception $e){
            return [false, $e->getMessage()];
        }
    }

    public function createMultiPartUpload(string $key){
        
        $response = $this->s3->createMultipartUpload([
            'Bucket' => $this->bucket,
            'Key'    => $key
        ]);

        return $response['UploadId'];
    }

    public function uploadPart(string $key, string $uploadId, int $partNumber, Blob $blob){
        $result = $this->s3->uploadPart(array(
            'Bucket'     => $this->bucket,
            'Key'        => $key,
            'UploadId'   => $uploadId,
            'PartNumber' => $partNumber,
            'Body'       => $blob,
        ));
        $parts[] = array(
            'PartNumber' => $partNumber++,
            'ETag'       => $result['ETag'],
        );        
    }
}