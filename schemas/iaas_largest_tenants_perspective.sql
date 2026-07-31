-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_largest_tenants_perspective AS
SELECT a.id AS iaas_account_id,
    a.uuid AS iaas_account_uuid,
    a.iam_account_id,
    t.vm_count,
    t.vcpu_total,
    t.ram_total_gb,
    t.disk_count,
    round(t.storage_bytes / '1000000000'::numeric, 2) AS storage_gb,
    t.network_count,
    round(t.bandwidth_mbps / 1000.0, 2) AS bandwidth_gbps
   FROM iaas_accounts a
     CROSS JOIN LATERAL ( SELECT ( SELECT count(*) AS count
                   FROM iaas_virtual_machines vm
                  WHERE vm.iam_account_id = a.iam_account_id AND vm.deleted_at IS NULL AND vm.is_draft = false) AS vm_count,
            COALESCE(( SELECT sum(vm.cpu) AS sum
                   FROM iaas_virtual_machines vm
                  WHERE vm.iam_account_id = a.iam_account_id AND vm.deleted_at IS NULL AND vm.is_draft = false), 0::bigint) AS vcpu_total,
            COALESCE(( SELECT sum(vm.ram) AS sum
                   FROM iaas_virtual_machines vm
                  WHERE vm.iam_account_id = a.iam_account_id AND vm.deleted_at IS NULL AND vm.is_draft = false), 0::bigint) AS ram_total_gb,
            ( SELECT count(*) AS count
                   FROM iaas_virtual_disk_images vdi
                  WHERE vdi.iam_account_id = a.iam_account_id AND vdi.deleted_at IS NULL AND vdi.is_draft = false) AS disk_count,
            COALESCE(( SELECT sum(vdi.size) AS sum
                   FROM iaas_virtual_disk_images vdi
                  WHERE vdi.iam_account_id = a.iam_account_id AND vdi.deleted_at IS NULL AND vdi.is_draft = false), 0::numeric) AS storage_bytes,
            ( SELECT count(*) AS count
                   FROM iaas_networks n
                  WHERE n.iam_account_id = a.iam_account_id AND n.deleted_at IS NULL) AS network_count,
            COALESCE(( SELECT sum(n.bandwidth) AS sum
                   FROM iaas_networks n
                  WHERE n.iam_account_id = a.iam_account_id AND n.deleted_at IS NULL), 0::numeric) AS bandwidth_mbps) t
  WHERE a.deleted_at IS NULL;
