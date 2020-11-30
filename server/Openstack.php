<?php

declare(strict_types=1);

namespace OpenStack;

use InvalidArgumentException;
use OpenStack\Compute\v2\ComputeV2Api;
use OpenStack\Identity\v3\IdentityV3Api;
use OpenStack\BlockStorage\v2\BlockStorageApi;
use OpenStack\Image\v2\ImageV2Api;

require_once 'Identity/v3/IdentityV3Api.php';
require_once 'Compute/v2/ComputeV2Api.php';
require_once 'BlockStorage/v2/BlockStorageApi.php';
require_once 'Image/v2/ImageV2Api.php';
class Openstack
{
    private $builder;
    protected $option = array(
        'host'      => [
            'url'   => 'http://103.150.1.205'
        ],
        'user'      => [
            'name'      => 'admin',
            'id'        => '0e0cd0e395ae422eafbdbc94c1bd7db4',
            'password'  => '7a46b3db97b24045',
        ],
        'auth'      => [
            'X-Subject-Token'   => '',
            'X-Auth-Token'      => 'gAAAAABfq4YO5MZ51_nD4ypP1DMSdd-StU6jbLIXphS0DTMGhuSFFJcHY-aqOw0y_XLsjwo747dpVhCTHSGewlZDVglj5B5DSeJuD4sTlvYeGpLGlEO9KPQOEtnZlov413ekFhXF_by27f8HyljuZo25zEk90VM-Cg',
        ],
        'scope'     => [
            'project'   => ['id' => 'f1151da00e6d4e108773b7886ea3fcfd']
        ],
    );

    /**
     * @param string $tokenParams
     */

    public function __construct(string $tokenParams = null){
        if(!isset($this->option) || empty($this->option)){
            throw new InvalidArgumentException("Parameters are required!");
        }
        $this->getDefaultIdentity($this->option);
        if($tokenParams != null){
            $this->option['auth']['X-Subject-Token'] = $tokenParams;
        }
    }
    private function getDefaultIdentity(array $option){
        if(!isset($option['user']['name'])){
            throw new InvalidArgumentException("'username' is a required option");
        }
        if(!isset($option['user']['password'])){
            throw new InvalidArgumentException("'password' is a required option");
        }
        return $option['checkDefaultIdentity'] = true;
    }

    public function identityV3(): IdentityV3Api {
        return new Identity\v3\IdentityV3Api($this->option);
    }

    public function computeV2(): ComputeV2Api {
        return new Compute\v2\ComputeV2Api($this->option);
    }
    public function blockStorageV2(): BlockStorageApi {
        return new BlockStorageApi($this->option);
    }
    public function imageV2(): ImageV2Api {
        return new ImageV2Api($this->option);
    }


}