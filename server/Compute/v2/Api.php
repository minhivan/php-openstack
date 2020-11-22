<?php


namespace OpenStack\Compute\v2;


class Api
{
    protected $authUrl = "http://103.150.1.205:8774/v2.1/";
    protected $token;
    protected $image = array(
        'centos' => '2fe580e1-101d-4c12-ac31-b0dbcd57b23a',
        'ubuntu' => '11b6a689-99d6-4f36-8061-88e82c904fb0',
        'cirros'   => '208de092-ed6d-4e22-9fc8-76a0b71871e7',
    );
    protected $flavors = array(
        'micro'     => '1',
        'small'     => '2',
        'medium'    => '3',
        'large'     => '4'
    );


    public function __construct($token){
        if(!isset($token)){
            throw new \InvalidArgumentException("Parameters are required!");
        }
        $this->token = $token;
    }

    /* Curl call API from Openstack */
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
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return ($httpcode>=200 && $httpcode<300) ? $response : false;
//        curl_close($curl);
//        return $response;
    }
    /* FLAVORS */
    /*
     * Action: get Flavors, create Flavors, delete flavors
     */

    public function getFlavors(){
        $res = $this->action($this->authUrl."flavors", "GET");
        echo $res;
        return $res;
    }


    /* CRUD SERVER */
    /*
     * Action: get server details, create, delete, update
     */
    public function getAllServer(){
        $res = $this->action($this->authUrl."servers/detail", "GET");
        if($res){
            return json_decode($res,true);
        }
        else
            return $res;
    }

    public function getServerDetails($server_id){
        $res = $this->action($this->authUrl."servers/".$server_id, "GET");
        if($res){
            return json_decode($res,true);
        }
        else
            return $res;
    }

    public function createServer($postParams){
        $request_body    = array (
            'server' =>
                array (
                    'name' => uniqid(),
                    'imageRef'  => $this->image[$postParams['server_type']],
                    'flavorRef' => $this->flavors[$postParams['server_size']],
                    'metadata'  => $postParams['metadata'],
                    'networks'  => array (
                        0 =>
                            array (
                                'uuid' => 'ae3fd5fe-c8c2-4e02-87b7-7cf6d7f0cf6d',
                            ),
                    )
                )
        );

        $request = json_encode($request_body);
        $res = $this->action($this->authUrl."servers","POST", $request);
        if($res != false){
            $return_arr = json_decode($res,true);
            return $return_arr['server']['id'];
        }
        else
            return false;
    }

    public function deleteServer(array $option, $postParams){
        $res    = $this->action($option['authUrl'], "DELETE", $option['auth']['X-Subject-Token']);
        if($res){
            echo $res;
            return true;
        }
        else
            return $res;
    }

    public function updateServer(array $option, $postParams){

    }

    /* SERVER SOFT ACTION */
    /*
     * Action : start, stop, restart, reboot, suspend, resume, console, pause
     */
    public function startServer($server_id){
        $request_body  = json_encode(array (
            'os-start' => NULL,
        ));
        $res = $this->action($this->authUrl."servers/{$server_id}/action","POST",$request_body);
        if($res){
            echo $res;
            return true;
        }
        else
            return $res;
    }

    public function stopServer($server_id){
        $request_body   = json_encode(array (
            'os-stop' => NULL,
        ));
        $res = $this->action($this->authUrl."servers/{$server_id}/action","POST",$request_body);
        if($res){
            echo $res;
            return true;
        }
        else
            return $res;
    }

    public function rebootServer($server_id){
        $request_body = json_encode(array (
            'reboot' =>
                array (
                    'type' => 'HARD',
                ),
        ));
        $res = $this->action($this->authUrl."servers/{$server_id}/action","POST",$request_body);
        if($res){
            echo $res;
            return true;
        }
        else
            return $res;
    }

    public function suspendServer($server_id){
        $request_body = json_encode(array (
            'suspend' => NULL,
        ));
        $res = $this->action($this->authUrl."servers/{$server_id}/action","POST",$request_body);
        if($res){
            echo $res;
            return true;
        }
        else
            return $res;
    }

    public function resumeSuspendServer($server_id){
        $request_body = json_encode(array (
            'resume' => NULL,
        ));
        $res = $this->action($this->authUrl."servers/{$server_id}/action","POST",$request_body);
        if($res){
            echo $res;
            return true;
        }
        else
            return $res;
    }

    public function getVNCConsole($server_id){
        $request_body = json_encode(array (
            'os-getVNCConsole' =>
                array (
                    'type' => 'novnc',
                ),
        ));
        $res = $this->action($this->authUrl."servers/{$server_id}/action","POST",$request_body);
        if($res){

        }
        echo $res;
        return $res;
    }

    public function pauseServer(array $option){
        $request_body = json_encode(array (
            'pause' => NULL,
        ));
        $res = $this->action($option['authUrl'], "POST", $option['auth']['X-Subject-Token'],$request_body);
        if($res){
            echo $res;
            return true;
        }
        else
            return $res;
    }

    public function unpauseServer(array $option){
        $request_body = json_encode(array (
            'unpause' => NULL,
        ));
        $res = $this->action($option['authUrl'], "POST", $option['auth']['X-Subject-Token'],$request_body);
        if($res){
            echo $res;
            return true;
        }
        else
            return $res;
    }


    /* SERVER HARD ACTION*/
    /*
     * Action: rebuild, lock, unlock, resize,
     */

    /* Params['server_id'], params[' */
    public function rebuildServer(array $option, $params){
        $request_body = json_encode(array (
            'rebuild' =>
                array (
                    'imageRef' => '',
                    'name' => '',
                ),
        ));
        $res = $this->action($option['authUrl'], "POST", $option['auth']['X-Subject-Token'],$request_body);
        if($res){
            echo $res;
            return true;
        }
        else
            return $res;
    }

    /*
     * postParams['idServer']
     * postParams['idFlavor']
     */
    public function resizeServer(array $option, $postParams){
        $rs = false;
        $request_body = json_encode(array (
            'resize' =>
                array (
                    'flavorRef' => $postParams['idFlavor'],
                    'OS-DCF:diskConfig' => 'AUTO',
                ),
        ));
        $res = $this->action($option['authUrl'], "POST", $option['auth']['X-Subject-Token'],$request_body);
        unset($request_body);
        if($res){
            $request_body = json_encode(array (
                'confirmResize' => NULL,
            ));
            $confirm = $this->action($option['authUrl'], "POST", $option['auth']['X-Subject-Token'],$request_body);
            if($confirm){
                $rs = true;
            }
        }
        return $rs;
    }

    


}

