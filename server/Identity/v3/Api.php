<?php


namespace OpenStack\Identity\v3;


class Api
{
    /** @var string */
    protected $token;

    /* Perform authentication with scoped authorization */
    public function generateTokenWithScoped(array $option){
        $params     = array(
            'auth' => array(
                'identity' => array(
                    'methods'     => array('password'),
                    'password'    => array(
                        'user'    => array(
                            'id'       => $option['user']['id'],
                            'password' => $option['user']['password']
                        )
                    )
                ),
                'scope' => array(
                    'project' => array(
                        'id'  => $option['scope']['project']['id'],
                    )
                ),
            )
        );
        $build_query    = json_encode($params);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $option['authUrl'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $build_query,
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
            throw new \RuntimeException("Error when retrieve header response");
        }
        curl_close($curl);
        // Return value;
        return json_encode(array('id' => $this->token));
    }


}