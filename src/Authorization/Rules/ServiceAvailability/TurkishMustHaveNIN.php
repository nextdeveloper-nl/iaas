<?php
namespace NextDeveloper\IAAS\Authorization\Rules\ServiceAvailability;

use NextDeveloper\Commons\Authorization\Rules\AbstractRules;

class TurkishMustHaveNIN implements AbstractRules
{
    public static function can(\NextDeveloper\IAM\Database\Models\Users $users): bool
    {
        return self::hasNin($users);
    }

    public static function get(\NextDeveloper\IAM\Database\Models\Users $users): bool
    {
        return self::hasNin($users);
    }

    public static function create(\NextDeveloper\IAM\Database\Models\Users $users): bool
    {
        return self::hasNin($users);
    }

    public static function update(\NextDeveloper\IAM\Database\Models\Users $users): bool
    {
        return self::hasNin($users);
    }

    public static function delete(\NextDeveloper\IAM\Database\Models\Users $users): bool
    {
        return self::hasNin($users);
    }

    public static function run(\NextDeveloper\Commons\Actions\AbstractAction $action, \NextDeveloper\IAM\Database\Models\Users $users): bool
    {
        return self::hasNin($users);
    }

    private static function hasNin(\NextDeveloper\IAM\Database\Models\Users $users) {
        if(!$users->nin)
            return false;

        return true;
    }
}
