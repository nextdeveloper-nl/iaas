<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachineMigrations;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineMigrations\VirtualMachineMigrationsUpdateRequest;
use NextDeveloper\IAAS\Database\Filters\VirtualMachineMigrationsQueryFilter;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\VirtualMachineMigrations;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\HypervisorsV2\EvacuationService;
use NextDeveloper\IAAS\Services\HypervisorsV2\XenServer_8_2\MigrationService;
use NextDeveloper\IAAS\Services\VirtualMachineMigrationsService;
use NextDeveloper\IAAS\Http\Requests\VirtualMachineMigrations\VirtualMachineMigrationsCreateRequest;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class VirtualMachineMigrationsController extends AbstractController
{
    private $model = VirtualMachineMigrations::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of virtualmachinemigrations.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  VirtualMachineMigrationsQueryFilter $filter  An object that builds search query
     * @param  Request                             $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VirtualMachineMigrationsQueryFilter $filter, Request $request)
    {
        $data = VirtualMachineMigrationsService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = VirtualMachineMigrationsService::getActions();

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * Makes the related action to the object
     *
     * @param  $objectId
     * @param  $action
     * @return array
     */
    public function doAction($objectId, $action)
    {
        $actionId = VirtualMachineMigrationsService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $virtualMachineMigrationsId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = VirtualMachineMigrationsService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method returns the list of sub objects the related object. Sub object means an object which is preowned by
     * this object.
     *
     * It can be tags, addresses, states etc.
     *
     * @param  $ref
     * @param  $subObject
     * @return void
     */
    public function relatedObjects($ref, $subObject)
    {
        $objects = VirtualMachineMigrationsService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created VirtualMachineMigrations object on database.
     *
     * @param  VirtualMachineMigrationsCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(VirtualMachineMigrationsCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineMigrationsService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineMigrations object on database.
     *
     * @param  $virtualMachineMigrationsId
     * @param  VirtualMachineMigrationsUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($virtualMachineMigrationsId, VirtualMachineMigrationsUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = VirtualMachineMigrationsService::update($virtualMachineMigrationsId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates VirtualMachineMigrations object on database.
     *
     * @param  $virtualMachineMigrationsId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($virtualMachineMigrationsId)
    {
        $model = VirtualMachineMigrationsService::delete($virtualMachineMigrationsId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    // ─────────────────────────────────────────────────────────────────────────
    // EVACUATION PLAN
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /iaas/virtual-machine-migrations/propose
     *
     * Proposes an evacuation plan without making any database changes.
     *
     * Body params:
     *   vm_uuid                  string  required  UUID of the source VirtualMachine
     *   target_compute_member_uuid string required  UUID of the target ComputeMember
     *   options                  object  optional  e.g. {"preferred_storage_type": "ssd"}
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function propose(Request $request)
    {
        $request->validate([
            'vm_uuid'                    => 'required|string',
            'target_compute_member_uuid' => 'required|string',
            'options'                    => 'sometimes|array',
        ]);

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $request->vm_uuid)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $target = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $request->target_compute_member_uuid)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $plan = EvacuationService::proposePlan($vm, $target, $request->input('options', []));

        return response()->json($plan);
    }

    /**
     * POST /iaas/virtual-machine-migrations/approve
     *
     * Approves a previously proposed plan and creates a VirtualMachineMigrations record.
     * Pass the full plan object returned by /propose as the request body under "plan".
     *
     * Body params:
     *   plan  object  required  The plan array returned by the propose endpoint
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(Request $request)
    {
        $request->validate([
            'plan'             => 'required|array',
            'plan.is_feasible' => 'required|boolean',
        ]);

        $migration = EvacuationService::approvePlan($request->input('plan'));

        return ResponsableFactory::makeResponse($this, $migration);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP-BY-STEP MIGRATION EXECUTION
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /iaas/virtual-machine-migrations/{ref}/pre-flight
     *
     * Step 1 — Verify SSH, VM power-state, target SR space, and NFS mounts.
     */
    public function preFlightChecks($ref)
    {
        $migration = $this->resolveMigration($ref);
        $service   = new MigrationService();
        $service->preFlightChecks($migration);

        return ResponsableFactory::makeResponse($this, $migration->fresh());
    }

    /**
     * POST /iaas/virtual-machine-migrations/{ref}/collect-metadata
     *
     * Step 2 — Collect VM, disk, and NIC metadata from the source host.
     */
    public function collectMetadata($ref)
    {
        $migration = $this->resolveMigration($ref);
        $service   = new MigrationService();
        $metadata  = $service->collectSourceMetadata($migration);

        return response()->json([
            'migration' => $migration->fresh(),
            'metadata'  => $metadata,
        ]);
    }

    /**
     * POST /iaas/virtual-machine-migrations/{ref}/coalesce-vhd
     *
     * Step 3 — Check for snapshots, trigger SR scan, and wait for a flat VHD.
     *
     * Body params (optional):
     *   force_delete_snapshots  bool  Allow deletion of VM snapshots to unblock coalesce
     */
    public function coalesceVhd($ref, Request $request)
    {
        $migration = $this->resolveMigration($ref);

        if ($request->boolean('force_delete_snapshots')) {
            $options = is_array($migration->options)
                ? $migration->options
                : (json_decode($migration->options, true) ?? []);

            $options['force_delete_snapshots'] = true;
            $migration->updateQuietly(['options' => json_encode($options)]);
        }

        $service = new MigrationService();
        $service->validateAndCoalesceVhd($migration);

        return ResponsableFactory::makeResponse($this, $migration->fresh());
    }

    /**
     * POST /iaas/virtual-machine-migrations/{ref}/shutdown
     *
     * Step 4 — Gracefully shut down the source VM (forced fallback after 5 min).
     */
    public function shutdown($ref)
    {
        $migration = $this->resolveMigration($ref);
        $service   = new MigrationService();
        $service->shutdownSourceVm($migration);

        return ResponsableFactory::makeResponse($this, $migration->fresh());
    }

    /**
     * POST /iaas/virtual-machine-migrations/{ref}/copy-vhd
     *
     * Step 5 — Copy VHD files from source to target storage via NFS mount + rsync.
     *
     * Body params (optional):
     *   dry_run  bool  When true, resolves and returns all SSH commands without executing them.
     *                  The command list is stored in migration.options.dry_run_commands.
     *                  Remove dry_run from options and re-POST to execute for real.
     */
    public function copyVhd($ref, Request $request)
    {
        $migration = $this->resolveMigration($ref);

        if ($request->boolean('dry_run')) {
            $options = is_array($migration->options)
                ? $migration->options
                : (json_decode($migration->options, true) ?? []);

            $options['dry_run'] = true;
            $migration->updateQuietly(['options' => json_encode($options)]);
        }

        $service = new MigrationService();
        $service->copyVhdFiles($migration);

        $fresh   = $migration->fresh();
        $options = is_array($fresh->options) ? $fresh->options : (json_decode($fresh->options, true) ?? []);

        if (!empty($options['dry_run_commands'])) {
            return response()->json([
                'dry_run'  => true,
                'migration' => $fresh,
                'commands' => $options['dry_run_commands'],
            ]);
        }

        return ResponsableFactory::makeResponse($this, $fresh);
    }

    /**
     * POST /iaas/virtual-machine-migrations/{ref}/rescan-sr
     *
     * Step 6 — Scan target SR and confirm copied VDIs are detected.
     */
    public function rescanSr($ref)
    {
        $migration  = $this->resolveMigration($ref);
        $service    = new MigrationService();
        $vdiUuidMap = $service->rescanTargetSr($migration);

        return response()->json([
            'migration'   => $migration->fresh(),
            'vdi_uuid_map' => $vdiUuidMap,
        ]);
    }

    /**
     * POST /iaas/virtual-machine-migrations/{ref}/recreate-vm
     *
     * Step 7 — Recreate VM record, VBDs, and VIFs on the target host.
     */
    public function recreateVm($ref)
    {
        $migration  = $this->resolveMigration($ref);
        $options    = is_array($migration->options)
            ? $migration->options
            : (json_decode($migration->options, true) ?? []);
        $vdiUuidMap = $options['vdi_uuid_map'] ?? [];

        $service   = new MigrationService();
        $newVmUuid = $service->recreateVmOnTarget($migration, $vdiUuidMap);

        return response()->json([
            'migration'    => $migration->fresh(),
            'target_vm_uuid' => $newVmUuid,
        ]);
    }

    /**
     * POST /iaas/virtual-machine-migrations/{ref}/validate
     *
     * Step 8 — Verify the recreated VM has correct vCPUs, memory, disk count, and NIC count.
     */
    public function validate($ref)
    {
        $migration = $this->resolveMigration($ref);
        $service   = new MigrationService();
        $summary   = $service->postMigrationValidation($migration);

        return response()->json([
            'migration'  => $migration->fresh(),
            'validation' => $summary,
        ]);
    }

    /**
     * POST /iaas/virtual-machine-migrations/{ref}/sync-db
     *
     * Step 9 — Update VirtualMachines, VirtualDiskImages, and VirtualNetworkCards
     * to reflect the target compute member, storage volumes, and networks.
     */
    public function syncDb($ref)
    {
        $migration = $this->resolveMigration($ref);
        $service   = new MigrationService();
        $service->syncDatabaseRecords($migration);

        return ResponsableFactory::makeResponse($this, $migration->fresh());
    }

    /**
     * POST /iaas/virtual-machine-migrations/{ref}/start-vm
     *
     * Step 10 — Start the VM on the target host and wait for running state.
     */
    public function startVm($ref)
    {
        $migration = $this->resolveMigration($ref);
        $service   = new MigrationService();
        $service->startVmOnTarget($migration);

        return ResponsableFactory::makeResponse($this, $migration->fresh());
    }

    /**
     * POST /iaas/virtual-machine-migrations/{ref}/run
     *
     * Orchestrates all steps end-to-end in sequence.
     */
    public function run($ref)
    {
        $migration = $this->resolveMigration($ref);
        $service   = new MigrationService();
        $service->run($migration);

        return ResponsableFactory::makeResponse($this, $migration->fresh());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function resolveMigration(string $ref): VirtualMachineMigrations
    {
        return VirtualMachineMigrationsService::getByRef($ref);
    }
}
