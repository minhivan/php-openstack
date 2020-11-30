<?php


namespace OpenStack\Image\v2;
use InvalidArgumentException;


class ImageV2Api
{
    protected $token;
    protected $authUrl;
    protected $port     = "9292";
    protected $api_ver  = "v2";
    protected $option;

    public function __construct(array $option){
        if(!isset($option) || empty($option) && ($option['auth']['X-Subject-Token']!= null || $option['auth']['X-Subject-Token'] != '')){
            throw new InvalidArgumentException("Parameters are required!");
        }
        $this->option   = $option;
        $this->token    = $option['auth']['X-Subject-Token'];
        $this->authUrl  = $option['host']['url'].":{$this->port}/{$this->api_ver}/";
    }

    /* Curl call API from Openstack */
    public function action($url, $method, $query = null, $nova_ver = null){
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
        ));
        $headers = array(
            "Content-Type: application/json",
            "X-Auth-Token: {$this->token}"
        );
        if($nova_ver != null){
            array_push($headers, "X-OpenStack-Nova-API-Version: {$nova_ver}");
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);


        if($method == "POST"){
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//        curl_close($curl);
//        return ($http_code>=200 && $http_code<300) ? $response : false;
        curl_close($curl);
        return $response;
    }
    public function createImage(){

    }

    public function listImage(){
        $res = $this->action($this->authUrl."images", "GET");
        if($res){
            echo $res;
            return json_decode($res,true);
        }
        else
            return $res;
    }

}