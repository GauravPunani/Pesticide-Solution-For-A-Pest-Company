<?php

class Bitly extends GamFunctions{

    private $access_token = 'e2d1c7c4070da54fd853496218e0b3e96dcc86aa';
    private $api_end_point = "https://api-ssl.bitly.com/v4/shorten";


    function __construct(){

    }

    public function shortenLink(string $link){
        $request_headers = [
            "Authorization: Bearer $this->access_token",
            "Content-Type: application/json"
        ];

        $post_fields = [
            "long_url" => $link
        ];

        $post_fields = json_encode($post_fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_end_point);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        $data = curl_exec($ch);
        if (curl_errno($ch)) return false;

        // Show me the result
        curl_close($ch);

        $json= json_decode($data, true);

        return array_key_exists('link', $json) ? $json['link'] : false;
    }
}

new Bitly();