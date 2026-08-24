<?php

namespace NextDeveloper\IAAS\Elasticsearch;

use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Database\Models\Accounts;
use NextDeveloper\IAM\Database\Models\Users;

/**
 * Builds the ES document for one VirtualMachines row. Field-for-field the same as
 * AbstractVirtualMachinesTransformer::transform() EXCEPT the ~9 FK->UUID fields
 * (iaas_cloud_node_id, iam_account_id, template_id, ...), which are deliberately NOT
 * resolved here for display purposes - see docs/elasticsearch/plan.md section 4 for why
 * (transform()'s FK lookups run through the *related* model's own AuthorizationScope,
 * so the same field can legitimately resolve differently per viewer; a single indexed
 * document can't represent that, so those fields are resolved live, per request, in
 * VirtualMachinesElasticTransformer instead).
 *
 * The FK columns ARE still stored here, as the related object's public UUID - that's a
 * separate, unscoped concern: search/filter plumbing (the query translator's term()
 * filters, and the authorization resolver's iam_account_id/iam_user_id matching), not
 * customer-facing display data. Resolving them unscoped at index time is correct and
 * safe for that purpose - it is never read back out for the API response body.
 */
class VirtualMachinesElasticDocumentBuilder
{
    public static function build(VirtualMachines $model): array
    {
        return [
            'id' => $model->uuid,
            '_internal_id' => $model->id,

            'name' => $model->name,
            'username' => $model->username,
            'hostname' => $model->hostname,
            'description' => $model->description,
            'os' => $model->os,
            'distro' => $model->distro,
            'version' => $model->version,
            'domain_type' => $model->domain_type,
            'status' => $model->status,
            'cpu' => $model->cpu,
            //  Stored raw (MB, matching the DB column) - the query translator's cpu/ram
            //  filters compare against this same raw value, and the display-side GB
            //  conversion (ram / 1024) happens in VirtualMachinesElasticTransformer,
            //  same as AbstractVirtualMachinesTransformer::transform() does today.
            'ram' => $model->ram,
            'is_winrm_enabled' => (bool) $model->is_winrm_enabled,
            'available_operations' => $model->available_operations,
            'current_operations' => $model->current_operations,
            'blocked_operations' => $model->blocked_operations,
            'console_data' => $model->console_data,
            'is_snapshot' => (bool) $model->is_snapshot,
            'is_lost' => (bool) $model->is_lost,
            'is_locked' => (bool) $model->is_locked,
            'last_metadata_request' => optional($model->last_metadata_request)->toIso8601String(),
            'features' => $model->features,
            'hypervisor_uuid' => $model->hypervisor_uuid,
            'hypervisor_data' => $model->hypervisor_data,
            'tags' => $model->tags ?? [],
            'created_at' => optional($model->created_at)->toIso8601String(),
            'updated_at' => optional($model->updated_at)->toIso8601String(),
            'deleted_at' => optional($model->deleted_at)->toIso8601String(),
            'is_draft' => (bool) $model->is_draft,
            'lock_password' => $model->lock_password,
            'is_template' => (bool) $model->is_template,
            'auto_backup_interval' => $model->auto_backup_interval,
            'auto_backup_time' => $model->auto_backup_time,
            'snapshot_of_virtual_machine' => $model->snapshot_of_virtual_machine,
            'post_boot_script' => $model->post_boot_script,
            'tokens' => $model->tokens,
            'agent_latest_ping' => optional($model->agent_latest_ping)->toIso8601String(),
            'is_pending_update' => (bool) $model->is_pending_update,

            //  FK fields - search/filter plumbing only, see class docblock. Resolved
            //  unscoped: this is index-time infrastructure, not a viewer-facing decision.
            'iaas_cloud_node_id' => static::uuidOf(CloudNodes::class, $model->iaas_cloud_node_id),
            'iaas_compute_member_id' => static::uuidOf(ComputeMembers::class, $model->iaas_compute_member_id),
            'iam_account_id' => static::uuidOf(Accounts::class, $model->iam_account_id),
            'iam_user_id' => static::uuidOf(Users::class, $model->iam_user_id),
            'template_id' => static::uuidOf(VirtualMachines::class, $model->template_id),
            'common_domain_id' => static::uuidOf(\NextDeveloper\Commons\Database\Models\Domains::class, $model->common_domain_id),
            'iaas_repository_image_id' => static::uuidOf(RepositoryImages::class, $model->iaas_repository_image_id),
            'iaas_compute_pool_id' => static::uuidOf(ComputePools::class, $model->iaas_compute_pool_id),
            'backup_repository_id' => static::uuidOf(Repositories::class, $model->backup_repository_id),
        ];
    }

    private static function uuidOf(string $modelClass, ?int $id): ?string
    {
        if (!$id) {
            return null;
        }

        return $modelClass::withoutGlobalScopes()->where('id', $id)->value('uuid');
    }
}
