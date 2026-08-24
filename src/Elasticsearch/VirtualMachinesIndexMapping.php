<?php

namespace NextDeveloper\IAAS\Elasticsearch;

/**
 * ES mapping for the VirtualMachines pilot index. Used by the reindex command to create
 * a new versioned index (see the alias/versioning scheme in ReindexVirtualMachines).
 */
class VirtualMachinesIndexMapping
{
    public static function settings(): array
    {
        return [
            'number_of_shards' => 1,
            'number_of_replicas' => 1,
        ];
    }

    public static function mappings(): array
    {
        $textWithKeyword = [
            'type' => 'text',
            'fields' => [
                'keyword' => ['type' => 'keyword', 'ignore_above' => 256],
            ],
        ];

        //  Opaque, variable-shape JSON blobs - stored and returned in _source, never
        //  indexed/queried, so they can't explode the mapping with dynamic sub-fields.
        $opaqueJson = ['type' => 'object', 'enabled' => false];

        return [
            'properties' => [
                //  Public identifier - the VM's own uuid, also used as the ES _id.
                'id' => ['type' => 'keyword'],

                //  Internal Postgres bigint id - not exposed to API consumers. Needed so
                //  the rehydrated model's Fractal includes (states/media/comments/...)
                //  can filter side tables by object_id = this value.
                '_internal_id' => ['type' => 'long'],

                'name' => $textWithKeyword,
                'username' => $textWithKeyword,
                'hostname' => $textWithKeyword,
                'description' => $textWithKeyword,
                'os' => $textWithKeyword,
                'distro' => $textWithKeyword,
                'version' => $textWithKeyword,
                //  keyword, not text+keyword like the fields above - these read as
                //  enum-like values in practice; the DB path's ilike substring match on
                //  them is a plan-level open question (see docs/elasticsearch/plan.md).
                'domain_type' => ['type' => 'keyword'],
                'status' => ['type' => 'keyword'],
                'lock_password' => $textWithKeyword,
                'auto_backup_interval' => $textWithKeyword,
                'auto_backup_time' => $textWithKeyword,
                'post_boot_script' => $textWithKeyword,

                'cpu' => ['type' => 'integer'],
                'ram' => ['type' => 'integer'],
                'snapshot_of_virtual_machine' => ['type' => 'integer'],

                'is_winrm_enabled' => ['type' => 'boolean'],
                'is_snapshot' => ['type' => 'boolean'],
                'is_lost' => ['type' => 'boolean'],
                'is_locked' => ['type' => 'boolean'],
                'is_draft' => ['type' => 'boolean'],
                'is_template' => ['type' => 'boolean'],
                'is_pending_update' => ['type' => 'boolean'],

                'tags' => ['type' => 'keyword'],

                //  FK fields - stored as the related object's public UUID (there is no
                //  cross-index join, so the internal bigint id is useless here). Used
                //  both by the query translator's filters and by the authorization
                //  resolver's iam_account_id/iam_user_id terms.
                'iaas_cloud_node_id' => ['type' => 'keyword'],
                'iaas_compute_member_id' => ['type' => 'keyword'],
                'iam_account_id' => ['type' => 'keyword'],
                'iam_user_id' => ['type' => 'keyword'],
                'template_id' => ['type' => 'keyword'],
                'common_domain_id' => ['type' => 'keyword'],
                'iaas_repository_image_id' => ['type' => 'keyword'],
                'iaas_compute_pool_id' => ['type' => 'keyword'],
                'backup_repository_id' => ['type' => 'keyword'],
                'hypervisor_uuid' => ['type' => 'keyword'],

                'last_metadata_request' => ['type' => 'date'],
                'agent_latest_ping' => ['type' => 'date'],
                'created_at' => ['type' => 'date'],
                'updated_at' => ['type' => 'date'],
                'deleted_at' => ['type' => 'date'],

                'available_operations' => $opaqueJson,
                'current_operations' => $opaqueJson,
                'blocked_operations' => $opaqueJson,
                'console_data' => $opaqueJson,
                'features' => $opaqueJson,
                'hypervisor_data' => $opaqueJson,
                'tokens' => $opaqueJson,
            ],
        ];
    }
}
