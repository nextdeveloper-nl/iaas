-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_cloud_nodes_performance AS
SELECT n.id,
    n.uuid,
    n.name,
    n.is_active,
    n.is_alive,
    n.is_in_maintenance,
    d.name AS datacenter_name,
    t.vm_count,
    t.compute_vcpu_total,
    t.compute_vcpu_used,
        CASE
            WHEN t.compute_vcpu_total > 0 THEN round(t.compute_vcpu_used::numeric / t.compute_vcpu_total::numeric * 100::numeric, 1)
            ELSE NULL::numeric
        END AS compute_vcpu_pct,
        CASE
            WHEN t.compute_vcpu_total = 0 THEN 'green'::text
            WHEN (t.compute_vcpu_used::numeric / t.compute_vcpu_total::numeric) < 0.70 THEN 'green'::text
            WHEN (t.compute_vcpu_used::numeric / t.compute_vcpu_total::numeric) < 0.90 THEN 'yellow'::text
            ELSE 'red'::text
        END AS compute_vcpu_health,
    t.compute_alarm_count,
    t.memory_total_gb,
    t.memory_used_gb,
        CASE
            WHEN t.memory_total_gb > 0::numeric THEN round(t.memory_used_gb / t.memory_total_gb * 100::numeric, 1)
            ELSE NULL::numeric
        END AS memory_pct,
        CASE
            WHEN t.memory_total_gb = 0::numeric THEN 'green'::text
            WHEN (t.memory_used_gb / t.memory_total_gb) < 0.70 THEN 'green'::text
            WHEN (t.memory_used_gb / t.memory_total_gb) < 0.90 THEN 'yellow'::text
            ELSE 'red'::text
        END AS memory_health,
    t.storage_total_gb,
    t.storage_used_gb,
        CASE
            WHEN t.storage_total_gb > 0 THEN round(t.storage_used_gb::numeric / t.storage_total_gb::numeric * 100::numeric, 1)
            ELSE NULL::numeric
        END AS storage_pct,
        CASE
            WHEN t.storage_total_gb = 0 THEN 'green'::text
            WHEN (t.storage_used_gb::numeric / t.storage_total_gb::numeric) < 0.70 THEN 'green'::text
            WHEN (t.storage_used_gb::numeric / t.storage_total_gb::numeric) < 0.90 THEN 'yellow'::text
            ELSE 'red'::text
        END AS storage_health,
    t.storage_alarm_count,
    t.network_alarm_count,
        CASE
            WHEN t.network_alarm_count = 0 THEN 'green'::text
            ELSE 'red'::text
        END AS network_health
   FROM iaas_cloud_nodes n
     LEFT JOIN iaas_datacenters d ON d.id = n.iaas_datacenter_id AND d.deleted_at IS NULL
     CROSS JOIN LATERAL ( SELECT ( SELECT count(*) AS count
                   FROM iaas_virtual_machines vm
                  WHERE vm.iaas_cloud_node_id = n.id AND vm.deleted_at IS NULL AND vm.is_draft = false) AS vm_count,
            COALESCE(( SELECT sum(cm.total_cpu * cm.max_overbooking_ratio) AS sum
                   FROM iaas_compute_pools cp
                     JOIN iaas_compute_members cm ON cm.iaas_compute_pool_id = cp.id AND cm.deleted_at IS NULL AND cm.is_alive = true AND cm.is_in_maintenance = false
                  WHERE cp.iaas_cloud_node_id = n.id AND cp.deleted_at IS NULL), 0::bigint) AS compute_vcpu_total,
            COALESCE(( SELECT sum(cm.used_cpu) AS sum
                   FROM iaas_compute_pools cp
                     JOIN iaas_compute_members cm ON cm.iaas_compute_pool_id = cp.id AND cm.deleted_at IS NULL AND cm.is_alive = true AND cm.is_in_maintenance = false
                  WHERE cp.iaas_cloud_node_id = n.id AND cp.deleted_at IS NULL), 0::bigint) AS compute_vcpu_used,
            COALESCE(( SELECT sum(cm.total_ram) AS sum
                   FROM iaas_compute_pools cp
                     JOIN iaas_compute_members cm ON cm.iaas_compute_pool_id = cp.id AND cm.deleted_at IS NULL AND cm.is_alive = true AND cm.is_in_maintenance = false
                  WHERE cp.iaas_cloud_node_id = n.id AND cp.deleted_at IS NULL), 0::bigint)::numeric AS memory_total_gb,
            COALESCE(( SELECT sum(cm.used_ram) AS sum
                   FROM iaas_compute_pools cp
                     JOIN iaas_compute_members cm ON cm.iaas_compute_pool_id = cp.id AND cm.deleted_at IS NULL AND cm.is_alive = true AND cm.is_in_maintenance = false
                  WHERE cp.iaas_cloud_node_id = n.id AND cp.deleted_at IS NULL), 0::bigint)::numeric AS memory_used_gb,
            COALESCE(( SELECT sum(sm.total_disk) AS sum
                   FROM iaas_storage_pools sp
                     JOIN iaas_storage_members sm ON sm.iaas_storage_pool_id = sp.id AND sm.deleted_at IS NULL AND sm.is_alive = true AND sm.is_maintenance = false
                  WHERE sp.iaas_cloud_node_id = n.id AND sp.deleted_at IS NULL), 0::bigint) AS storage_total_gb,
            COALESCE(( SELECT sum(sm.used_disk) AS sum
                   FROM iaas_storage_pools sp
                     JOIN iaas_storage_members sm ON sm.iaas_storage_pool_id = sp.id AND sm.deleted_at IS NULL AND sm.is_alive = true AND sm.is_maintenance = false
                  WHERE sp.iaas_cloud_node_id = n.id AND sp.deleted_at IS NULL), 0::bigint) AS storage_used_gb,
            COALESCE(( SELECT count(*) AS count
                   FROM iaas_compute_pools cp
                     JOIN iaas_compute_members cm ON cm.iaas_compute_pool_id = cp.id AND cm.deleted_at IS NULL
                     JOIN iaas_health_checks hc ON hc.object_id = cm.id AND hc.object_type = 'NextDeveloper\IAAS\ComputeMembers'::text AND hc.deleted_at IS NULL AND (hc.check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text]))
                  WHERE cp.iaas_cloud_node_id = n.id AND cp.deleted_at IS NULL), 0::bigint) AS compute_alarm_count,
            COALESCE(( SELECT count(*) AS count
                   FROM iaas_storage_pools sp
                     JOIN iaas_storage_members sm ON sm.iaas_storage_pool_id = sp.id AND sm.deleted_at IS NULL
                     JOIN iaas_health_checks hc ON hc.object_id = sm.id AND hc.object_type = 'NextDeveloper\IAAS\StorageMembers'::text AND hc.deleted_at IS NULL AND (hc.check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text]))
                  WHERE sp.iaas_cloud_node_id = n.id AND sp.deleted_at IS NULL), 0::bigint) AS storage_alarm_count,
            COALESCE(( SELECT count(*) AS count
                   FROM iaas_network_pools np
                     JOIN iaas_network_members nm ON nm.iaas_network_pool_id = np.id AND nm.deleted_at IS NULL
                     JOIN iaas_health_checks hc ON hc.object_id = nm.id AND hc.object_type = 'NextDeveloper\IAAS\NetworkMembers'::text AND hc.deleted_at IS NULL AND (hc.check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text]))
                  WHERE np.iaas_cloud_node_id = n.id AND np.deleted_at IS NULL), 0::bigint) AS network_alarm_count) t
  WHERE n.deleted_at IS NULL;
