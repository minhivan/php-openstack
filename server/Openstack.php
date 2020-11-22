<?php

declare(strict_types=1);

namespace OpenStack;

use OpenStack\Identity\v3\Api;

require_once 'Identity/v3/Api.php';
require_once 'Compute/v2/Api.php';

class Openstack
{
    private $builder;
    protected $token;

    /**
     * @param array $option
     *
     * $options['username']         = (string)            Your OpenStack username        [REQUIRED]
     *         ['password']         = (string)            Your OpenStack password        [REQUIRED]
     *         ['tenantId']         = (string)            Your tenant ID                 [REQUIRED if tenantName omitted]
     *         ['tenantName']       = (string)            Your tenant name               [REQUIRED if tenantId omitted]
     *         ['debugLog']         = (bool)              Whether to enable HTTP logging [OPTIONAL]
     *         ['logger']           = (LoggerInterface)   Must set if debugLog is true   [OPTIONAL]
     *         ['messageFormatter'] = (MessageFormatter)  Must set if debugLog is true   [OPTIONAL]
     *         ['cachedToken']      = (array)             Cached token credential        [OPTIONAL]
     */

    public function __construct(array $option = []){
        if(!isset($option) || empty($option)){
            throw new \InvalidArgumentException("Parameters are required!");
        }
        $this->getDefaultIdentity($option);
//        if(!isset($option['identityService'])){
//            $option['identityService'] = $this->getDefaultIdentity($option);
//        }
        $this->token = $option['auth']['X-Subject-Token'];
    }
    private function getDefaultIdentity(array $option){
        if(!isset($option['user']['name'])){
            throw new \InvalidArgumentException("'username' is a required option");
        }
        if(!isset($option['user']['password'])){
            throw new \InvalidArgumentException("'password' is a required option");
        }
        return $option['checkDefaultIdentity'] = true;
    }

    public function identityV3() {
        return new Identity\v3\Api;
    }

    public function computeV2(){
        return new Compute\v2\Api($this->token);
    }

}