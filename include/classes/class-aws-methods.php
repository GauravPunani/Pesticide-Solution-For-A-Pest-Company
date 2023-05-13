<?php

class Aws extends GamFunctions 
{
    function __construct()
    {
        add_action('wp_ajax_aws_s3_get_object_url', array($this, 'aws_s3_get_object_url'));
        add_action('wp_ajax_nopriv_aws_s3_get_object_url', array($this, 'aws_s3_get_object_url'));
    }

    public function aws_s3_get_object_url(){
        global $wpdb;

        $this->verify_nonce_field('fbcs_nonce');

        if(empty($_POST['object_key'])) $this->response('error', 'object key is required');

        $object_key = $_POST['object_key'];

        require_once get_template_directory()."/include/classes/class-aws-s3-bucket.php";
        list($object_url, $messsage) = (new S3bucket)->getObjectUrl($object_key);
        if(!$object_url) $this->response('error', $messsage);

        $object_url = rtrim($object_url, '\n');

        $this->response('success', 'object url found!', compact('object_url'));
    }
}

new Aws();