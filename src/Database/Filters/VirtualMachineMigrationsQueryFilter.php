<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                                    

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VirtualMachineMigrationsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function stepMessage($value)
    {
        return $this->builder->where('step_message', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of stepMessage
    public function step_message($value)
    {
        return $this->stepMessage($value);
    }
        
    public function errorMessage($value)
    {
        return $this->builder->where('error_message', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of errorMessage
    public function error_message($value)
    {
        return $this->errorMessage($value);
    }
    
    public function progress($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('progress', $operator, $value);
    }

    
    public function startedAtStart($date)
    {
        return $this->builder->where('started_at', '>=', $date);
    }

    public function startedAtEnd($date)
    {
        return $this->builder->where('started_at', '<=', $date);
    }

    //  This is an alias function of startedAt
    public function started_at_start($value)
    {
        return $this->startedAtStart($value);
    }

    //  This is an alias function of startedAt
    public function started_at_end($value)
    {
        return $this->startedAtEnd($value);
    }

    public function completedAtStart($date)
    {
        return $this->builder->where('completed_at', '>=', $date);
    }

    public function completedAtEnd($date)
    {
        return $this->builder->where('completed_at', '<=', $date);
    }

    //  This is an alias function of completedAt
    public function completed_at_start($value)
    {
        return $this->completedAtStart($value);
    }

    //  This is an alias function of completedAt
    public function completed_at_end($value)
    {
        return $this->completedAtEnd($value);
    }

    public function createdAtStart($date)
    {
        return $this->builder->where('created_at', '>=', $date);
    }

    public function createdAtEnd($date)
    {
        return $this->builder->where('created_at', '<=', $date);
    }

    //  This is an alias function of createdAt
    public function created_at_start($value)
    {
        return $this->createdAtStart($value);
    }

    //  This is an alias function of createdAt
    public function created_at_end($value)
    {
        return $this->createdAtEnd($value);
    }

    public function updatedAtStart($date)
    {
        return $this->builder->where('updated_at', '>=', $date);
    }

    public function updatedAtEnd($date)
    {
        return $this->builder->where('updated_at', '<=', $date);
    }

    //  This is an alias function of updatedAt
    public function updated_at_start($value)
    {
        return $this->updatedAtStart($value);
    }

    //  This is an alias function of updatedAt
    public function updated_at_end($value)
    {
        return $this->updatedAtEnd($value);
    }

    public function deletedAtStart($date)
    {
        return $this->builder->where('deleted_at', '>=', $date);
    }

    public function deletedAtEnd($date)
    {
        return $this->builder->where('deleted_at', '<=', $date);
    }

    //  This is an alias function of deletedAt
    public function deleted_at_start($value)
    {
        return $this->deletedAtStart($value);
    }

    //  This is an alias function of deletedAt
    public function deleted_at_end($value)
    {
        return $this->deletedAtEnd($value);
    }

    public function iaasVirtualMachineId($value)
    {
            $iaasVirtualMachine = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('uuid', $value)->first();

        if($iaasVirtualMachine) {
            return $this->builder->where('iaas_virtual_machine_id', '=', $iaasVirtualMachine->id);
        }
    }

        //  This is an alias function of iaasVirtualMachine
    public function iaas_virtual_machine_id($value)
    {
        return $this->iaasVirtualMachine($value);
    }
    
    public function sourceIaasComputeMemberId($value)
    {
            $sourceIaasComputeMember = \NextDeveloper\\Database\Models\SourceIaasComputeMembers::where('uuid', $value)->first();

        if($sourceIaasComputeMember) {
            return $this->builder->where('source_iaas_compute_member_id', '=', $sourceIaasComputeMember->id);
        }
    }

        //  This is an alias function of sourceIaasComputeMember
    public function source_iaas_compute_member_id($value)
    {
        return $this->sourceIaasComputeMember($value);
    }
    
    public function targetIaasComputeMemberId($value)
    {
            $targetIaasComputeMember = \NextDeveloper\\Database\Models\TargetIaasComputeMembers::where('uuid', $value)->first();

        if($targetIaasComputeMember) {
            return $this->builder->where('target_iaas_compute_member_id', '=', $targetIaasComputeMember->id);
        }
    }

        //  This is an alias function of targetIaasComputeMember
    public function target_iaas_compute_member_id($value)
    {
        return $this->targetIaasComputeMember($value);
    }
    
    public function sourceIaasStorageVolumeId($value)
    {
            $sourceIaasStorageVolume = \NextDeveloper\\Database\Models\SourceIaasStorageVolumes::where('uuid', $value)->first();

        if($sourceIaasStorageVolume) {
            return $this->builder->where('source_iaas_storage_volume_id', '=', $sourceIaasStorageVolume->id);
        }
    }

        //  This is an alias function of sourceIaasStorageVolume
    public function source_iaas_storage_volume_id($value)
    {
        return $this->sourceIaasStorageVolume($value);
    }
    
    public function targetIaasStorageVolumeId($value)
    {
            $targetIaasStorageVolume = \NextDeveloper\\Database\Models\TargetIaasStorageVolumes::where('uuid', $value)->first();

        if($targetIaasStorageVolume) {
            return $this->builder->where('target_iaas_storage_volume_id', '=', $targetIaasStorageVolume->id);
        }
    }

        //  This is an alias function of targetIaasStorageVolume
    public function target_iaas_storage_volume_id($value)
    {
        return $this->targetIaasStorageVolume($value);
    }
    
    public function sourceIaasStorageMemberId($value)
    {
            $sourceIaasStorageMember = \NextDeveloper\\Database\Models\SourceIaasStorageMembers::where('uuid', $value)->first();

        if($sourceIaasStorageMember) {
            return $this->builder->where('source_iaas_storage_member_id', '=', $sourceIaasStorageMember->id);
        }
    }

        //  This is an alias function of sourceIaasStorageMember
    public function source_iaas_storage_member_id($value)
    {
        return $this->sourceIaasStorageMember($value);
    }
    
    public function targetIaasStorageMemberId($value)
    {
            $targetIaasStorageMember = \NextDeveloper\\Database\Models\TargetIaasStorageMembers::where('uuid', $value)->first();

        if($targetIaasStorageMember) {
            return $this->builder->where('target_iaas_storage_member_id', '=', $targetIaasStorageMember->id);
        }
    }

        //  This is an alias function of targetIaasStorageMember
    public function target_iaas_storage_member_id($value)
    {
        return $this->targetIaasStorageMember($value);
    }
    
    public function iamAccountId($value)
    {
            $iamAccount = \NextDeveloper\IAM\Database\Models\Accounts::where('uuid', $value)->first();

        if($iamAccount) {
            return $this->builder->where('iam_account_id', '=', $iamAccount->id);
        }
    }

    
    public function iamUserId($value)
    {
            $iamUser = \NextDeveloper\IAM\Database\Models\Users::where('uuid', $value)->first();

        if($iamUser) {
            return $this->builder->where('iam_user_id', '=', $iamUser->id);
        }
    }

    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
