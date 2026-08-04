-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_kpi_performance AS
SELECT active_clouds,
    active_clouds - active_clouds_prev AS active_clouds_delta,
        CASE
            WHEN active_clouds_prev > 0 THEN round((active_clouds - active_clouds_prev)::numeric / active_clouds_prev::numeric * 100::numeric, 1)
            ELSE NULL::numeric
        END AS active_clouds_delta_pct,
    compute_vcpus,
    compute_vcpus - compute_vcpus_prev AS compute_vcpus_delta,
        CASE
            WHEN compute_vcpus_prev > 0 THEN round((compute_vcpus - compute_vcpus_prev)::numeric / compute_vcpus_prev::numeric * 100::numeric, 1)
            ELSE NULL::numeric
        END AS compute_vcpus_delta_pct,
    round(storage_bytes / '1000000000000000'::numeric, 4) AS storage_pb,
    round((storage_bytes - storage_bytes_prev) / '1000000000000000'::numeric, 4) AS storage_pb_delta,
        CASE
            WHEN storage_bytes_prev > 0::numeric THEN round((storage_bytes - storage_bytes_prev) / storage_bytes_prev * 100::numeric, 1)
            ELSE NULL::numeric
        END AS storage_pb_delta_pct,
    active_tenants,
    active_tenants - active_tenants_prev AS active_tenants_delta,
        CASE
            WHEN active_tenants_prev > 0 THEN round((active_tenants - active_tenants_prev)::numeric / active_tenants_prev::numeric * 100::numeric, 1)
            ELSE NULL::numeric
        END AS active_tenants_delta_pct,
    alarm_count,
    alarm_count - alarm_count_prev AS alarm_count_delta,
        CASE
            WHEN alarm_count_prev > 0 THEN round((alarm_count - alarm_count_prev)::numeric / alarm_count_prev::numeric * 100::numeric, 1)
            ELSE NULL::numeric
        END AS alarm_count_delta_pct,
    alarm_critical_count,
    alarm_high_count,
    alarm_low_count,
    alarm_compute_members_count,
    alarm_storage_members_count,
    alarm_network_members_count,
    alarm_virtual_machines_count,
    round(bandwidth_mbps / 1000.0, 2) AS bandwidth_gbps,
    round((bandwidth_mbps - bandwidth_mbps_prev) / 1000.0, 2) AS bandwidth_gbps_delta,
        CASE
            WHEN bandwidth_mbps_prev > 0::numeric THEN round((bandwidth_mbps - bandwidth_mbps_prev) / bandwidth_mbps_prev * 100::numeric, 1)
            ELSE NULL::numeric
        END AS bandwidth_gbps_delta_pct
   FROM ( SELECT ( SELECT count(*) AS count
                   FROM iaas_cloud_nodes
                  WHERE iaas_cloud_nodes.deleted_at IS NULL AND iaas_cloud_nodes.is_active = true AND iaas_cloud_nodes.is_alive = true AND iaas_cloud_nodes.is_in_maintenance = false) AS active_clouds,
            ( SELECT count(*) AS count
                   FROM iaas_cloud_nodes
                  WHERE iaas_cloud_nodes.created_at <= (now() - '30 days'::interval) AND (iaas_cloud_nodes.deleted_at IS NULL OR iaas_cloud_nodes.deleted_at > (now() - '30 days'::interval)) AND iaas_cloud_nodes.is_active = true AND iaas_cloud_nodes.is_alive = true AND iaas_cloud_nodes.is_in_maintenance = false) AS active_clouds_prev,
            ( SELECT COALESCE(sum(iaas_compute_members.total_cpu * iaas_compute_members.max_overbooking_ratio), 0::bigint) AS "coalesce"
                   FROM iaas_compute_members
                  WHERE iaas_compute_members.deleted_at IS NULL AND iaas_compute_members.is_alive = true AND iaas_compute_members.is_in_maintenance = false) AS compute_vcpus,
            ( SELECT COALESCE(sum(iaas_compute_members.total_cpu * iaas_compute_members.max_overbooking_ratio), 0::bigint) AS "coalesce"
                   FROM iaas_compute_members
                  WHERE iaas_compute_members.created_at <= (now() - '30 days'::interval) AND (iaas_compute_members.deleted_at IS NULL OR iaas_compute_members.deleted_at > (now() - '30 days'::interval)) AND iaas_compute_members.is_alive = true AND iaas_compute_members.is_in_maintenance = false) AS compute_vcpus_prev,
            ( SELECT COALESCE(sum(iaas_virtual_disk_images.size), 0::numeric) AS "coalesce"
                   FROM iaas_virtual_disk_images
                  WHERE iaas_virtual_disk_images.deleted_at IS NULL AND iaas_virtual_disk_images.is_draft = false) AS storage_bytes,
            ( SELECT COALESCE(sum(iaas_virtual_disk_images.size), 0::numeric) AS "coalesce"
                   FROM iaas_virtual_disk_images
                  WHERE iaas_virtual_disk_images.created_at <= (now() - '30 days'::interval) AND (iaas_virtual_disk_images.deleted_at IS NULL OR iaas_virtual_disk_images.deleted_at > (now() - '30 days'::interval)) AND iaas_virtual_disk_images.is_draft = false) AS storage_bytes_prev,
            ( SELECT count(*) AS count
                   FROM iaas_accounts
                  WHERE iaas_accounts.deleted_at IS NULL) AS active_tenants,
            ( SELECT count(*) AS count
                   FROM iaas_accounts
                  WHERE iaas_accounts.created_at <= (now() - '30 days'::interval) AND (iaas_accounts.deleted_at IS NULL OR iaas_accounts.deleted_at > (now() - '30 days'::interval))) AS active_tenants_prev,
            ( SELECT count(*) AS count
                   FROM iaas_health_checks
                  WHERE iaas_health_checks.deleted_at IS NULL AND (iaas_health_checks.check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text])) AND (iaas_health_checks.object_type = ANY (ARRAY['NextDeveloper\IAAS\ComputeMembers'::text, 'NextDeveloper\IAAS\StorageMembers'::text, 'NextDeveloper\IAAS\NetworkMembers'::text, 'NextDeveloper\IAAS\VirtualMachines'::text]))) AS alarm_count,
            ( SELECT count(*) AS count
                   FROM iaas_health_checks
                  WHERE iaas_health_checks.created_at <= (now() - '30 days'::interval) AND (iaas_health_checks.deleted_at IS NULL OR iaas_health_checks.deleted_at > (now() - '30 days'::interval)) AND (iaas_health_checks.check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text])) AND (iaas_health_checks.object_type = ANY (ARRAY['NextDeveloper\IAAS\ComputeMembers'::text, 'NextDeveloper\IAAS\StorageMembers'::text, 'NextDeveloper\IAAS\NetworkMembers'::text, 'NextDeveloper\IAAS\VirtualMachines'::text]))) AS alarm_count_prev,
            ( SELECT count(*) AS count
                   FROM iaas_health_checks
                  WHERE iaas_health_checks.deleted_at IS NULL AND (iaas_health_checks.check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text])) AND (iaas_health_checks.object_type = ANY (ARRAY['NextDeveloper\IAAS\ComputeMembers'::text, 'NextDeveloper\IAAS\StorageMembers'::text, 'NextDeveloper\IAAS\NetworkMembers'::text, 'NextDeveloper\IAAS\VirtualMachines'::text])) AND iaas_health_checks.severity = 'critical'::text) AS alarm_critical_count,
            ( SELECT count(*) AS count
                   FROM iaas_health_checks
                  WHERE iaas_health_checks.deleted_at IS NULL AND (iaas_health_checks.check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text])) AND (iaas_health_checks.object_type = ANY (ARRAY['NextDeveloper\IAAS\ComputeMembers'::text, 'NextDeveloper\IAAS\StorageMembers'::text, 'NextDeveloper\IAAS\NetworkMembers'::text, 'NextDeveloper\IAAS\VirtualMachines'::text])) AND iaas_health_checks.severity = 'high'::text) AS alarm_high_count,
            ( SELECT count(*) AS count
                   FROM iaas_health_checks
                  WHERE iaas_health_checks.deleted_at IS NULL AND (iaas_health_checks.check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text])) AND (iaas_health_checks.object_type = ANY (ARRAY['NextDeveloper\IAAS\ComputeMembers'::text, 'NextDeveloper\IAAS\StorageMembers'::text, 'NextDeveloper\IAAS\NetworkMembers'::text, 'NextDeveloper\IAAS\VirtualMachines'::text])) AND (iaas_health_checks.severity = ANY (ARRAY['medium'::text, 'low'::text, 'info'::text]))) AS alarm_low_count,
            ( SELECT count(*) AS count
                   FROM iaas_health_checks
                  WHERE iaas_health_checks.deleted_at IS NULL AND (iaas_health_checks.check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text])) AND iaas_health_checks.object_type = 'NextDeveloper\IAAS\ComputeMembers'::text) AS alarm_compute_members_count,
            ( SELECT count(*) AS count
                   FROM iaas_health_checks
                  WHERE iaas_health_checks.deleted_at IS NULL AND (iaas_health_checks.check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text])) AND iaas_health_checks.object_type = 'NextDeveloper\IAAS\StorageMembers'::text) AS alarm_storage_members_count,
            ( SELECT count(*) AS count
                   FROM iaas_health_checks
                  WHERE iaas_health_checks.deleted_at IS NULL AND (iaas_health_checks.check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text])) AND iaas_health_checks.object_type = 'NextDeveloper\IAAS\NetworkMembers'::text) AS alarm_network_members_count,
            ( SELECT count(*) AS count
                   FROM iaas_health_checks
                  WHERE iaas_health_checks.deleted_at IS NULL AND (iaas_health_checks.check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text])) AND iaas_health_checks.object_type = 'NextDeveloper\IAAS\VirtualMachines'::text) AS alarm_virtual_machines_count,
            ( SELECT COALESCE(sum(iaas_networks.bandwidth), 0::numeric) AS "coalesce"
                   FROM iaas_networks
                  WHERE iaas_networks.deleted_at IS NULL) AS bandwidth_mbps,
            ( SELECT COALESCE(sum(iaas_networks.bandwidth), 0::numeric) AS "coalesce"
                   FROM iaas_networks
                  WHERE iaas_networks.created_at <= (now() - '30 days'::interval) AND (iaas_networks.deleted_at IS NULL OR iaas_networks.deleted_at > (now() - '30 days'::interval))) AS bandwidth_mbps_prev) t;
