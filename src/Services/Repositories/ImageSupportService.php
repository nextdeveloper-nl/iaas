<?php

namespace NextDeveloper\IAAS\Services\Repositories;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;

class ImageSupportService
{
    public static function isImageSupportsHypervisor(RepositoryImages $image, ComputeMembers $computeMember) : bool
    {
        $hypervisor = $computeMember->hypervisor_model;

        if(in_array($hypervisor, $image->supported_virtualizations)) {
            return true;
        }

        return false;
    }

    public static function getSupportedModels($version) : array
    {
        switch ($version) {
            case 'xen8_2':
            case 'xen8-2':
                return ['XenServer 8.2'];
            case 'xen6_2':
            case 'xen6-2':
                return ['XenServer 6.2', 'XenServer 6.5', 'XenServer 7.5', 'XenServer 8.2'];
        }

        return [];
    }
}
