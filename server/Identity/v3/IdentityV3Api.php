<?php


namespace OpenStack\Identity\v3;

use RuntimeException;
use InvalidArgumentException;


class IdentityV3Api
{
    /** @var string */
    protected $token;
    protected $authUrl;
    protected $port     = "5000";
    protected $api_ver  = "v3";
    protected $option;

    public function __construct(array $option){
        if(!isset($option) || empty($option)){
            throw new InvalidArgumentException("Parameters are required!");
        }
        $this->option   = $option;
        $this->authUrl  = $option['host']['url'].":{$this->port}/{$this->api_ver}/";
    }

    public function getToken($url, $method, $query = null){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $query,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
            CURLOPT_HEADER => 1,
        ));
        $response       = curl_exec($curl);
        $header_size    = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header         = substr($response, 0, $header_size);

        preg_match("/X-Subject-Token.+/", $header,  $matches);
        if(is_array($matches)){
            $res_token      = str_replace(' ', '',explode(": ", $matches[0]));
            // Save token
            $this->token    = trim(preg_replace('/\s\s+/', ' ', $res_token[1]));
            //echo substr($response, $header_size);
        }

        else{
            throw new RuntimeException("Error when retrieve header response");
        }
        curl_close($curl);
        // Return value;
        return array(
            'token' => array(
                'value' => $this->token
            )
        );
    }

    /* Perform authentication with scoped authorization */
    public function generateTokenWithScoped(){
        $params     = array(
            'auth' => array(
                'identity' => array(
                    'methods'     => array('password'),
                    'password'    => array(
                        'user'    => array(
                            'id'       => $this->option['user']['id'],
                            'password' => $this->option['user']['password']
                        )
                    )
                ),
                'scope' => array(
                    'project' => array(
                        'id'  => $this->option['scope']['project']['id'],
                    )
                ),
            )
        );
        $build_query    = json_encode($params);
        $url = $this->authUrl."auth/tokens";
        return $this->getToken($url, "POST", $build_query);
    }


}