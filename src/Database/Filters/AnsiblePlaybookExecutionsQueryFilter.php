<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class AnsiblePlaybookExecutionsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

    public function sshUsername($value)
    {
        return $this->builder->where('ssh_username', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of sshUsername
    public function ssh_username($value)
    {
        return $this->sshUsername($value);
    }

    public function sshPassword($value)
    {
        return $this->builder->where('ssh_password', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of sshPassword
    public function ssh_password($value)
    {
        return $this->sshPassword($value);
    }

    public function lastOutput($value)
    {
        return $this->builder->where('last_output', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of lastOutput
    public function last_output($value)
    {
        return $this->lastOutput($value);
    }

    public function sshPort($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('ssh_port', $operator, $value);
    }

        //  This is an alias function of sshPort
    public function ssh_port($value)
    {
        return $this->sshPort($value);
    }

    public function executionTotalTime($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('execution_total_time', $operator, $value);
    }

        //  This is an alias function of executionTotalTime
    public function execution_total_time($value)
    {
        return $this->executionTotalTime($value);
    }

    public function resultOk($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('result_ok', $operator, $value);
    }

        //  This is an alias function of resultOk
    public function result_ok($value)
    {
        return $this->resultOk($value);
    }

    public function resultUnreachable($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('result_unreachable', $operator, $value);
    }

        //  This is an alias function of resultUnreachable
    public function result_unreachable($value)
    {
        return $this->resultUnreachable($value);
    }

    public function resultFailed($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('result_failed', $operator, $value);
    }

        //  This is an alias function of resultFailed
    public function result_failed($value)
    {
        return $this->resultFailed($value);
    }

    public function resultSkipped($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('result_skipped', $operator, $value);
    }

        //  This is an alias function of resultSkipped
    public function result_skipped($value)
    {
        return $this->resultSkipped($value);
    }

    public function resultRescued($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('result_rescued', $operator, $value);
    }

        //  This is an alias function of resultRescued
    public function result_rescued($value)
    {
        return $this->resultRescued($value);
    }

    public function resultIgnored($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('result_ignored', $operator, $value);
    }

        //  This is an alias function of resultIgnored
    public function result_ignored($value)
    {
        return $this->resultIgnored($value);
    }

    public function isExternalMachine($value)
    {
        return $this->builder->where('is_external_machine', $value);
    }

        //  This is an alias function of isExternalMachine
    public function is_external_machine($value)
    {
        return $this->isExternalMachine($value);
    }

    public function lastExecutionTimeStart($date)
    {
        return $this->builder->where('last_execution_time', '>=', $date);
    }

    public function lastExecutionTimeEnd($date)
    {
        return $this->builder->where('last_execution_time', '<=', $date);
    }

    //  This is an alias function of lastExecutionTime
    public function last_execution_time_start($value)
    {
        return $this->lastExecutionTimeStart($value);
    }

    //  This is an alias function of lastExecutionTime
    public function last_execution_time_end($value)
    {
        return $this->lastExecutionTimeEnd($value);
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


    public function iaasAnsiblePlaybookId($value)
    {
            $iaasAnsiblePlaybook = \NextDeveloper\IAAS\Database\Models\AnsiblePlaybooks::where('uuid', $value)->first();

        if($iaasAnsiblePlaybook) {
            return $this->builder->where('iaas_ansible_playbook_id', '=', $iaasAnsiblePlaybook->id);
        }
    }

        //  This is an alias function of iaasAnsiblePlaybook
    public function iaas_ansible_playbook_id($value)
    {
        return $this->iaasAnsiblePlaybook($value);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE





































}
