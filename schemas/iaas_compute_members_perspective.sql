-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_compute_members_perspective AS
SELECT id,
    uuid,
    name,
    hostname,
    ip_addr,
    has_warning,
    has_error,
    ssh_username,
    ssh_password,
    ssh_port,
    total_socket,
    total_cpu,
    total_ram,
    used_cpu,
    used_ram,
    total_cpu * max_overbooking_ratio - used_cpu AS free_cpu,
    running_vm,
    halted_vm,
    total_vm,
    uptime,
    idle_time,
    benchmark_score,
    is_in_maintenance,
    is_alive,
    ( SELECT n_icp.name
           FROM iaas_compute_pools n_icp
          WHERE n_icp.id = cm.iaas_compute_pool_id) AS compute_pool_name,
    iaas_compute_pool_id,
    ( SELECT c_ia.name
           FROM iam_accounts c_ia
          WHERE c_ia.id = cm.iam_account_id) AS maintainer,
    ( SELECT n_ia.fullname
           FROM iam_users n_ia
          WHERE n_ia.id = cm.iam_user_id) AS responsible,
    ( SELECT json_agg(json_build_object('name', n_cs.name, 'value', n_cs.value, 'reason', n_cs.reason, 'object_state', n_cs.object_states)) AS json_agg
           FROM common_states n_cs
          WHERE n_cs.object_id = cm.id AND n_cs.object_type = 'NextDeveloper\IAAS\Database\Models\ComputeMembers'::text) AS states,
    tags,
    iam_account_id,
    iam_user_id,
    is_event_service_running,
    created_at,
    updated_at,
    deleted_at
   FROM iaas_compute_members cm;
