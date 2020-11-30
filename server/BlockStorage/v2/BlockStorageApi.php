<?php


namespace OpenStack\BlockStorage\v2;

use InvalidArgumentException;
use RuntimeException;

class BlockStorageApi
{
    protected $authUrl = "http://103.150.1.205:8776/v2.1/";
    protected $token;

    public function __construct(array $option){

        if(!isset($token)){
            throw new InvalidArgumentException("401 Unauthorized");
        }
        $this->token = $token;
    }

    public function action($url, $method, $query = null){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "{$method}",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "X-Auth-Token: {$this->token}"
            ),
        ));
        if($method == "POST"){
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        return ($http_code>=200 && $http_code<300) ? $response : false;

        //curl_close($curl);
        //return $response;
    }




}