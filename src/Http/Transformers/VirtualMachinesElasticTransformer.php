<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachinesTransformer;

/**
 * Used instead of VirtualMachinesTransformer when VirtualMachinesService::get() served
 * the list from Elasticsearch (see VirtualMachinesController::index() and
 * VirtualMachinesService::isElasticReadEnabled()). Extends the normal transformer so
 * the inherited availableIncludes/include*() methods still work unchanged (they query
 * Postgres live, on demand, only when a client explicitly requests an include) -
 * overrides only transform().
 *
 * The plain fields come straight from the ES-hydrated model (no extra Postgres
 * lookups - the actual perf win beyond just the list query itself). The ~9 FK->UUID
 * fields still run the same live, per-viewer resolveForeignKeyFields() lookup the DB
 * path uses, deliberately - see docs/elasticsearch/plan.md section 4/6 for why those
 * can't be pre-baked into the index.
 */
class VirtualMachinesElasticTransformer extends AbstractVirtualMachinesTransformer
{
    public function transform(VirtualMachines $model)
    {
        return $this->buildPayload(array_merge(
            [
                'id' => $model->uuid,
                'name' => $model->name,
                'username' => $model->username,
                // password intentionally omitted - see AbstractVirtualMachinesTransformer.
                'hostname' => $model->hostname,
                'description' => $model->description,
                'os' => $model->os,
                'distro' => $model->distro,
                'version' => $model->version,
                'domain_type' => $model->domain_type,
                'status' => $model->status,
                'cpu' => $model->cpu,
                'ram' => $model->ram / 1024,
                'is_winrm_enabled' => $model->is_winrm_enabled,
                'available_operations' => $model->available_operations,
                'current_operations' => $model->current_operations,
                'blocked_operations' => $model->blocked_operations,
                'console_data' => $model->console_data,
                'is_snapshot' => $model->is_snapshot,
                'is_lost' => $model->is_lost,
                'is_locked' => $model->is_locked,
                'last_metadata_request' => $model->last_metadata_request,
                'features' => $model->features,
                'hypervisor_uuid' => $model->hypervisor_uuid,
                'hypervisor_data' => $model->hypervisor_data,
                'tags' => $model->tags,
                'created_at' => $model->created_at,
                'updated_at' => $model->updated_at,
                'deleted_at' => $model->deleted_at,
                'is_draft' => $model->is_draft,
                'lock_password' => $model->lock_password,
                'is_template' => $model->is_template,
                'auto_backup_interval' => $model->auto_backup_interval,
                'auto_backup_time' => $model->auto_backup_time,
                'snapshot_of_virtual_machine' => $model->snapshot_of_virtual_machine,
                'post_boot_script' => $model->post_boot_script,
                'tokens' => $model->tokens,
                'agent_latest_ping' => $model->agent_latest_ping,
                'is_pending_update' => $model->is_pending_update,
            ],
            $this->resolveForeignKeyFields($model)
        ));
    }
}
