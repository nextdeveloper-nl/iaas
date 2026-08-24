<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * Used instead of VirtualMachinesTransformer when VirtualMachinesService::get() served
 * the list from Elasticsearch (see VirtualMachinesController::index() and
 * VirtualMachinesService::isElasticReadEnabled()). Extends the real concrete
 * transformer (not just AbstractVirtualMachinesTransformer) so its __construct()
 * (adds the virtualNetworkCards include) and includeVirtualNetworkCards() override are
 * inherited unchanged - overrides only transform().
 *
 * VirtualMachinesTransformer::transform() does more than the abstract base: it
 * resolves snapshot_of_virtual_machine to a UUID (a 10th live FK lookup, same
 * per-viewer AuthorizationScope caveat as the other 9 - see
 * docs/elasticsearch/plan.md section 4), derives service_roles from features, and
 * strips hypervisor_uuid/hypervisor_data/console_data from the response. All of that
 * is mirrored here rather than calling parent::transform(), since the parent's version
 * starts from the same live-lookup-heavy base transform() this class deliberately
 * avoids for the plain fields.
 *
 * The plain fields come straight from the ES-hydrated model (no extra Postgres
 * lookups - the actual perf win beyond just the list query itself). The FK->UUID
 * fields (the original 9 plus snapshot_of_virtual_machine) still run the same live,
 * per-viewer lookups the DB path uses, deliberately.
 */
class VirtualMachinesElasticTransformer extends VirtualMachinesTransformer
{
    public function transform(VirtualMachines $model)
    {
        $transformed = array_merge(
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
                'is_snapshot' => $model->is_snapshot,
                'is_lost' => $model->is_lost,
                'is_locked' => $model->is_locked,
                'last_metadata_request' => $model->last_metadata_request,
                'features' => $model->features,
                'tags' => $model->tags,
                'created_at' => $model->created_at,
                'updated_at' => $model->updated_at,
                'deleted_at' => $model->deleted_at,
                'is_draft' => $model->is_draft,
                'lock_password' => $model->lock_password,
                'is_template' => $model->is_template,
                'auto_backup_interval' => $model->auto_backup_interval,
                'auto_backup_time' => $model->auto_backup_time,
                'post_boot_script' => $model->post_boot_script,
                'tokens' => $model->tokens,
                'agent_latest_ping' => $model->agent_latest_ping,
                'is_pending_update' => $model->is_pending_update,
            ],
            $this->resolveForeignKeyFields($model)
        );

        //  Mirrors VirtualMachinesTransformer::transform()'s snapshot_of_virtual_machine
        //  resolution - same live, per-viewer AuthorizationScope lookup as the other FK
        //  fields, not pre-baked into the ES document for the same reason.
        $snapshotSource = VirtualMachines::where('id', $model->snapshot_of_virtual_machine)->first();
        $transformed['snapshot_of_virtual_machine'] = $snapshotSource ? $snapshotSource->uuid : null;

        $transformed['service_roles'] = $model->features['service_roles'] ?? [];

        return $this->buildPayload($transformed);
    }
}
