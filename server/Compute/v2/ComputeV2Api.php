<?php


namespace OpenStack\Compute\v2;


use InvalidArgumentException;
use RuntimeException;

class ComputeV2Api
{
    protected $token;
    protected $authUrl;
    protected $port     = "8774";
    protected $api_ver  = "v2.1";
    protected $option;

    protected $image    = array(
        'centos' => '2fe580e1-101d-4c12-ac31-b0dbcd57b23a',
        'ubuntu' => '11b6a689-99d6-4f36-8061-88e82c904fb0',
        'cirros'   => '208de092-ed6d-4e22-9fc8-76a0b71871e7',
    );
    protected $flavors  = array(
        'micro'     => '1',
        'small'     => '2',
        'medium'    => '3',
        'large'     => '4',
        'xlarge'    => '5',
        'pop'       => '6'
    );

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
        curl_close($curl);
        return ($http_code>=200 && $http_code<300) ? $response : false;
//        curl_close($curl);
//        return $response;
    }

    /* FLAVORS
     * Action: get Flavors, create Flavors, delete flavors
     */
    public function getFlavors(){
        $res = $this->action($this->authUrl."flavors", "GET");
        if($res){
            return json_decode($res,true);
        }
        else
            return $res;
    }

    /* Host limit */
    public function hostLimits(){
        $res = $this->action($this->authUrl."limits", "GET");
        if($res){
            return json_decode($res,true);
        }
        else
            return $res;
    }

    /* CRUD SERVER
     * Action: get server details, create, delete, update
     */
    public function listServersDetails(){
        $res = $this->action($this->authUrl."servers/detail", "GET");
        if($res){
            return json_decode($res,true);
        }
        else
            return $res;
    }

    public function getServerDetails($server_id){
        $res = $this->action($this->authUrl."servers/{$server_id}", "GET");
        if($res){
            return json_decode($res,true);
        }
        else
            return $res;
    }

    /* Create Server
     * postParams['server_type'], postParams['server_size'], postParams['metadata']
     */
    public function createServer($postParams){
        $request_body    = array (
            'server' =>
                array (
                    'name' => md5(uniqid(rand())),
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
        if($res){
            $return_arr = json_decode($res,true);
            return $return_arr['server']['id'];
        }
        else
            return false;
    }

    public function deleteServer($server_id){
        $res = $this->action($this->authUrl."servers/{$server_id}","DELETE");
        if($res){
            //echo $res;
            return true;
        }
        else
            return $res;
    }

    /* Update server */
    public function updateServer($postParams){
        //$params =
        //$res = $this->action();
    }

    public function getServerMeta($server_id){
        $res = $this->action($this->authUrl."servers/{$server_id}/metadata", "GET");
        if($res){
            //echo $res;
            return json_decode($res,true);
        }
        else
            return $res;
    }

    /* Update server metadata (Server name + label)
        postParams['server_name'], postParams['server_label']
    */
    public function updateServerMeta(array $postParams){
        $params = array();
        foreach ($postParams as $key => $val){
            if($key == "server_id")
                continue;
            $params['metadata'][$key] = $val;
        }

        $res = $this->action($this->authUrl."servers/{$postParams['server_id']}/metadata", "POST", json_encode($params));
        if($res){
            //echo $res;
            return json_decode($res,true);
        }
        else
            return $res;
    }

    public function getServerIp($server_id){
        $res = $this->action($this->authUrl."servers/{$server_id}/ips", "GET");
        if($res){
            //echo $res;
            return json_decode($res,true);
        }
        else
            return $res;
    }

    public function getServerDiagnostics($server_id){
        $res = $this->action($this->authUrl."servers/{$server_id}/diagnostics", "GET", null, "2.48");
        if($res){
            return json_decode($res,true);
        }
        else
            return $res;
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
            //echo $res;
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
            //echo $res;
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
            //echo $res;
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
            //echo $res;
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
            //echo $res;
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
            //echo $res;
            return json_decode($res,true);
        }
        else
            return $res;
    }

    public function pauseServer($server_id){
        $request_body = json_encode(array (
            'pause' => NULL,
        ));
        $res = $this->action($this->authUrl."servers/{$server_id}/action","POST",$request_body);
        if($res){
            echo $res;
            return true;
        }
        else
            return $res;
    }

    public function unpauseServer($server_id){
        $request_body = json_encode(array (
            'unpause' => NULL,
        ));
        $res = $this->action($this->authUrl."servers/{$server_id}/action","POST",$request_body);
        if($res){
            //echo $res;
            return true;
        }
        else
            return $res;
    }


    /* SERVER HARD ACTION */

    /* Rebuild server
     * postParams['server_id'], postParams['imageRef'], postParams['name']
     */
    public function rebuildServer(array $postParams){
        $arr = array (
            'rebuild' =>
                array (
                    'imageRef' => $this->image[$postParams['imageRef']],
                ),
        );
        if(isset($postParams['name'])){
            $arr['rebuild']['name'] = $postParams['name'];
        }

        $request_body = json_encode($arr);
        $res = $this->action($this->authUrl."servers/{$postParams['server_id']}/action","POST",$request_body);
        if($res){
           // echo $res;
            return true;
        }
        else
            return $res;
    }

    /* Resize server
     * postParams['idServer'], postParams['idFlavor']
     */
    public function resizeServer(array $postParams){
        $rs = false;
        $request_body = json_encode(array (
            'resize' =>
                array (
                    'flavorRef' => $postParams['idFlavor'],
                    'OS-DCF:diskConfig' => 'AUTO',
                ),
        ));
        $res = $this->action($this->authUrl."servers/{$postParams['server_id']}/action","POST",$request_body);
        unset($request_body);
        if($res){
            $request_body = json_encode(array (
                'confirmResize' => NULL,
            ));
            $confirm = $this->action($this->authUrl."servers/{$postParams['server_id']}/action","POST",$request_body);
            if($confirm){
                $rs = true;
            }
        }
        return $rs;
    }

    public function lockServer($server_id){
        $rs = false;
        $request_body = json_encode(array (
            'lock' => NULL,
        ));
        $res = $this->action($this->authUrl."servers/{$server_id}/action","POST",$request_body);
        if($res)
            $rs = true;
        return $rs;
    }

    public function unlockServer($server_id){
        $rs = false;
        $request_body = json_encode(array (
            'unlock' => NULL,
        ));
        $res = $this->action($this->authUrl."servers/{$server_id}/action","POST",$request_body);
        if($res)
            $rs = true;
        return $rs;
    }

    /* Keypair & snapshots & backup
     *
     */
    public function generateSSHKeypair(){
        $request_body = json_encode(array (
            'keypair' =>
                array (
                    'name' => 'keypair_'.md5(uniqid(rand()))
                ),
        ));

        $res = $this->action($this->authUrl."os-keypairs","POST",$request_body);
        if($res != false){
            echo $res;
            return json_decode($res);
        }else{
            return false;
        }
    }

    public function addSSHKeypair($key){
        $request_body = json_encode(array (
            'keypair' =>
                array (
                    'name'          => 'keypair'.md5(uniqid(rand())),
                    'public_key'    => $key
                ),
        ));

        $res = $this->action($this->authUrl."os-keypairs","POST",$request_body);
        if($res != false){
            return json_decode($res);
        }else{
            return false;
        }
    }

    public function createSnapshot(){
        
    }

    /* Backup server
     * postParams['backup_name'], postParams['server_id']
     */
    public function createBackup(array $postParams){
        $request_body = json_encode(array (
            'createBackup' =>
                array (
                    'name' => $postParams['backup_name'],
                    'backup_type' => '',
                    'rotation' => '1',
                ),
        ));

        $res = $this->action($this->authUrl."servers/{$postParams['server_id']}/action","POST",$request_body);
        if($res != false){
            return true;
        }else
            return $res;
    }

    function listBackup($server_id){

    }


}

