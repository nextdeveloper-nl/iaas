<?php

namespace NextDeveloper\IAAS\Elasticsearch;

use NextDeveloper\Commons\Elasticsearch\Filters\AbstractElasticQueryTranslator;

/**
 * ES-DSL counterpart of VirtualMachinesQueryFilter - field-by-field port, kept as a
 * hand-maintained sibling rather than trying to share one class with the generated
 * DB filter (see AbstractElasticQueryTranslator). Will drift if the generator adds
 * fields to VirtualMachinesQueryFilter; flagged in the plan for a follow-up test that
 * asserts the two field lists stay in sync.
 *
 * Two deliberate differences from VirtualMachinesQueryFilter, not oversights:
 *
 * - FK fields (iaasCloudNodeId, templateId, etc.) are a direct term() match on the
 *   UUID the client sent, not a DB lookup-then-filter-by-internal-id - the ES document
 *   already stores these fields as public UUIDs (see VirtualMachinesElasticDocumentBuilder),
 *   so there is no internal id to resolve to.
 * - Every snake_case FK alias here (iaas_cloud_node_id, template_id, common_domain_id,
 *   iaas_repository_image_id, iaas_compute_pool_id, backup_repository_id) correctly
 *   delegates to its camelCase method. In VirtualMachinesQueryFilter the equivalent
 *   aliases call a method that doesn't exist (e.g. template_id() calls $this->template(),
 *   but only templateId() is defined) - a real, pre-existing "call to undefined method"
 *   bug in the DB path, unrelated to this migration. Reproducing that would just mean
 *   the ES path also 500s on those params, which isn't parity, so it isn't mirrored here.
 * - cpu()/ram()/snapshotOfVirtualMachine() DO mirror a pre-existing DB-path bug: the
 *   leading </> comparator character is never actually parsed out (the condition that's
 *   supposed to detect it, `$operator != '<' || $operator != '>'`, is always true), so
 *   the DB path always filters with plain equality against the raw value. Mirrored here
 *   deliberately so the parallel-run diff tool (see rollout plan) compares like with
 *   like instead of flagging every </>-prefixed cpu/ram filter as a false regression.
 */
class VirtualMachinesElasticQueryTranslator extends AbstractElasticQueryTranslator
{
    /**
     * Fields mapped as text+keyword (see VirtualMachinesIndexMapping) aren't sortable
     * directly - ES rejects sorting on a plain `text` field without fielddata enabled.
     * Sorting has to go through the .keyword sub-field instead.
     */
    private const TEXT_FIELDS = [
        'name', 'username', 'hostname', 'description', 'os', 'distro', 'version',
        'lock_password', 'auto_backup_interval', 'auto_backup_time', 'post_boot_script',
    ];

    public function order($value): void
    {
        foreach (explode(',', $value) as $item) {
            if (str_contains($item, '|')) {
                [$column, $direction] = explode('|', $item);
            } else {
                $column = $item;
                $direction = 'asc';
            }

            if (in_array($column, self::TEXT_FIELDS, true)) {
                $column .= '.keyword';
            }

            $this->sortClauses[] = [$column => strtolower($direction)];
        }
    }

    public function tags($value): void
    {
        $this->terms('tags', array_map('trim', explode(',', $value)));
    }

    public function name($value): void
    {
        $this->matchPhrase('name', $value);
    }

    public function username($value): void
    {
        $this->matchPhrase('username', $value);
    }

    //  password intentionally has no ES field/filter - it's not indexed (encrypted
    //  ciphertext, never exposed in the API response - see AbstractVirtualMachinesTransformer)
    //  and filtering by it in the DB path today is already meaningless (ilike against
    //  ciphertext never usefully matches anything).

    public function hostname($value): void
    {
        $this->matchPhrase('hostname', $value);
    }

    public function description($value): void
    {
        $this->matchPhrase('description', $value);
    }

    public function os($value): void
    {
        $this->matchPhrase('os', $value);
    }

    public function distro($value): void
    {
        $this->matchPhrase('distro', $value);
    }

    public function version($value): void
    {
        $this->matchPhrase('version', $value);
    }

    public function domainType($value): void
    {
        $this->matchPhrase('domain_type', $value);
    }

    public function domain_type($value): void
    {
        $this->domainType($value);
    }

    public function status($value): void
    {
        $this->matchPhrase('status', $value);
    }

    public function lockPassword($value): void
    {
        $this->matchPhrase('lock_password', $value);
    }

    public function lock_password($value): void
    {
        $this->lockPassword($value);
    }

    public function autoBackupInterval($value): void
    {
        $this->matchPhrase('auto_backup_interval', $value);
    }

    public function auto_backup_interval($value): void
    {
        $this->autoBackupInterval($value);
    }

    public function autoBackupTime($value): void
    {
        $this->matchPhrase('auto_backup_time', $value);
    }

    public function auto_backup_time($value): void
    {
        $this->autoBackupTime($value);
    }

    public function postBootScript($value): void
    {
        $this->matchPhrase('post_boot_script', $value);
    }

    public function post_boot_script($value): void
    {
        $this->postBootScript($value);
    }

    public function cpu($value): void
    {
        $this->term('cpu', $value);
    }

    public function ram($value): void
    {
        $this->term('ram', $value);
    }

    public function snapshotOfVirtualMachine($value): void
    {
        $this->term('snapshot_of_virtual_machine', $value);
    }

    public function snapshot_of_virtual_machine($value): void
    {
        $this->snapshotOfVirtualMachine($value);
    }

    public function isWinrmEnabled($value): void
    {
        $this->term('is_winrm_enabled', filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }

    public function is_winrm_enabled($value): void
    {
        $this->isWinrmEnabled($value);
    }

    public function isSnapshot($value): void
    {
        $this->term('is_snapshot', filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }

    public function is_snapshot($value): void
    {
        $this->isSnapshot($value);
    }

    public function isLost($value): void
    {
        $this->term('is_lost', filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }

    public function is_lost($value): void
    {
        $this->isLost($value);
    }

    public function isLocked($value): void
    {
        $this->term('is_locked', filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }

    public function is_locked($value): void
    {
        $this->isLocked($value);
    }

    public function isDraft($value): void
    {
        $this->term('is_draft', filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }

    public function is_draft($value): void
    {
        $this->isDraft($value);
    }

    public function isTemplate($value): void
    {
        $this->term('is_template', filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }

    public function is_template($value): void
    {
        $this->isTemplate($value);
    }

    public function lastMetadataRequestStart($date): void
    {
        $this->range('last_metadata_request', 'gte', $date);
    }

    public function lastMetadataRequestEnd($date): void
    {
        $this->range('last_metadata_request', 'lte', $date);
    }

    public function last_metadata_request_start($value): void
    {
        $this->lastMetadataRequestStart($value);
    }

    public function last_metadata_request_end($value): void
    {
        $this->lastMetadataRequestEnd($value);
    }

    public function createdAtStart($date): void
    {
        $this->range('created_at', 'gte', $date);
    }

    public function createdAtEnd($date): void
    {
        $this->range('created_at', 'lte', $date);
    }

    public function created_at_start($value): void
    {
        $this->createdAtStart($value);
    }

    public function created_at_end($value): void
    {
        $this->createdAtEnd($value);
    }

    public function updatedAtStart($date): void
    {
        $this->range('updated_at', 'gte', $date);
    }

    public function updatedAtEnd($date): void
    {
        $this->range('updated_at', 'lte', $date);
    }

    public function updated_at_start($value): void
    {
        $this->updatedAtStart($value);
    }

    public function updated_at_end($value): void
    {
        $this->updatedAtEnd($value);
    }

    public function deletedAtStart($date): void
    {
        $this->range('deleted_at', 'gte', $date);
    }

    public function deletedAtEnd($date): void
    {
        $this->range('deleted_at', 'lte', $date);
    }

    public function deleted_at_start($value): void
    {
        $this->deletedAtStart($value);
    }

    public function deleted_at_end($value): void
    {
        $this->deletedAtEnd($value);
    }

    public function iaasCloudNodeId($value): void
    {
        $this->term('iaas_cloud_node_id', $value);
    }

    public function iaas_cloud_node_id($value): void
    {
        $this->iaasCloudNodeId($value);
    }

    public function iaasComputeMemberId($value): void
    {
        $this->term('iaas_compute_member_id', $value);
    }

    public function iaas_compute_member_id($value): void
    {
        $this->iaasComputeMemberId($value);
    }

    public function iamAccountId($value): void
    {
        $this->term('iam_account_id', $value);
    }

    public function iamUserId($value): void
    {
        $this->term('iam_user_id', $value);
    }

    public function templateId($value): void
    {
        $this->term('template_id', $value);
    }

    public function template_id($value): void
    {
        $this->templateId($value);
    }

    public function commonDomainId($value): void
    {
        $this->term('common_domain_id', $value);
    }

    public function common_domain_id($value): void
    {
        $this->commonDomainId($value);
    }

    public function iaasRepositoryImageId($value): void
    {
        $this->term('iaas_repository_image_id', $value);
    }

    public function iaas_repository_image_id($value): void
    {
        $this->iaasRepositoryImageId($value);
    }

    public function iaasComputePoolId($value): void
    {
        $this->term('iaas_compute_pool_id', $value);
    }

    public function iaas_compute_pool_id($value): void
    {
        $this->iaasComputePoolId($value);
    }

    public function backupRepositoryId($value): void
    {
        $this->term('backup_repository_id', $value);
    }

    public function backup_repository_id($value): void
    {
        $this->backupRepositoryId($value);
    }
}
