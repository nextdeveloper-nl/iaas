<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Class StatService
 *
 * This service class provides functionality to create statistics entries for various models.
 *
 * @package NextDeveloper\IAAS\Services
 */
class StatService
{
    /**
     * Creates a new state entry for the given class with the provided parameters.
     *
     * This function performs the following steps:
     * 1. Disables the global authorization scope for the class.
     * 2. Create a new entry with the provided parameters.
     * 3. Log the successful creation of the entry.
     * 4. Catches and logs any exceptions that occur during the creation process.
     *
     */
    public static function create($class, $params): void
    {
        try {
            // Disable the global authorization scope and create a new entry with the provided parameters
            $class::withoutGlobalScope(AuthorizationScope::class)
                ->create($params);

            // Log the successful creation of the entry
            Log::info("[StateService@update] State updated: class = " . $class);
        } catch (\Exception $e) {
            // Log any exceptions that occur during the creation process
            Log::error("[StateService@update] State update failed: class = " . $class);
            Log::error($e->getMessage());
        }
    }
}