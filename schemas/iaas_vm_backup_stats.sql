-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_vm_backup_stats AS
SELECT vms_protected,
    vms_protected - vms_protected_prev AS vms_protected_delta,
        CASE
            WHEN vms_protected_prev > 0 THEN round((vms_protected - vms_protected_prev)::numeric / vms_protected_prev::numeric * 100::numeric, 1)
            ELSE NULL::numeric
        END AS vms_protected_delta_pct,
    rpo_breached_vms,
    sla_breached_jobs,
    jobs_disabled,
    jobs_failed_24h,
    jobs_failed_30d,
    avg_rpo_achieved_hours,
    avg_rpo_target_hours,
    storage_used_bytes,
    round(storage_used_bytes::numeric / '1000000000'::numeric, 2) AS storage_used_gb,
    round(storage_used_bytes::numeric / '1000000000000'::numeric, 4) AS storage_used_tb,
    protections_done_24h,
    protections_done_30d,
    protections_done_30d - protections_done_30d_prev AS protections_done_delta,
        CASE
            WHEN protections_done_30d_prev > 0 THEN round((protections_done_30d - protections_done_30d_prev)::numeric / protections_done_30d_prev::numeric * 100::numeric, 1)
            ELSE NULL::numeric
        END AS protections_done_delta_pct,
    jobs_with_replication
   FROM ( SELECT ( SELECT count(DISTINCT ibj.object_id) AS count
                   FROM iaas_backup_jobs ibj
                  WHERE ibj.deleted_at IS NULL AND ibj.is_enabled = true AND ibj.object_type = 'NextDeveloper\IAAS\Database\Models\VirtualMachines'::text AND (EXISTS ( SELECT 1
                           FROM common_task_schedulers cts
                          WHERE cts.object_id = ibj.id AND cts.object_type = 'NextDeveloper\IAAS\Database\Models\BackupJobs'::text AND cts.deleted_at IS NULL))) AS vms_protected,
            ( SELECT count(DISTINCT ibj.object_id) AS count
                   FROM iaas_backup_jobs ibj
                  WHERE ibj.created_at <= (now() - '30 days'::interval) AND (ibj.deleted_at IS NULL OR ibj.deleted_at > (now() - '30 days'::interval)) AND ibj.is_enabled = true AND ibj.object_type = 'NextDeveloper\IAAS\Database\Models\VirtualMachines'::text AND (EXISTS ( SELECT 1
                           FROM common_task_schedulers cts
                          WHERE cts.object_id = ibj.id AND cts.object_type = 'NextDeveloper\IAAS\Database\Models\BackupJobs'::text AND cts.created_at <= (now() - '30 days'::interval) AND (cts.deleted_at IS NULL OR cts.deleted_at > (now() - '30 days'::interval))))) AS vms_protected_prev,
            ( SELECT count(DISTINCT ibj.object_id) AS count
                   FROM iaas_backup_jobs ibj
                     JOIN ( SELECT iaas_virtual_machine_backups.iaas_virtual_machine_id,
                            max(iaas_virtual_machine_backups.backup_ends) AS last_ok_ends
                           FROM iaas_virtual_machine_backups
                          WHERE iaas_virtual_machine_backups.deleted_at IS NULL AND (iaas_virtual_machine_backups.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) AND iaas_virtual_machine_backups.backup_ends IS NOT NULL
                          GROUP BY iaas_virtual_machine_backups.iaas_virtual_machine_id) lb ON lb.iaas_virtual_machine_id = ibj.object_id
                  WHERE ibj.deleted_at IS NULL AND ibj.is_enabled = true AND ibj.expected_rpo_hours IS NOT NULL AND ibj.object_type = 'NextDeveloper\IAAS\Database\Models\VirtualMachines'::text AND (now() - lb.last_ok_ends) > (ibj.expected_rpo_hours * '01:00:00'::interval)) AS rpo_breached_vms,
            ( SELECT count(*) AS count
                   FROM iaas_backup_jobs ibj
                  WHERE ibj.deleted_at IS NULL AND ibj.is_enabled = true AND ibj.object_type = 'NextDeveloper\IAAS\Database\Models\VirtualMachines'::text AND (( SELECT count(*) AS count
                           FROM iaas_virtual_machine_backups f
                          WHERE f.iaas_backup_job_id = ibj.id AND f.deleted_at IS NULL AND (f.status = ANY (ARRAY['failed'::text, 'error'::text])) AND f.backup_starts > COALESCE(( SELECT max(s.backup_starts) AS max
                                   FROM iaas_virtual_machine_backups s
                                  WHERE s.iaas_backup_job_id = ibj.id AND s.deleted_at IS NULL AND (s.status <> ALL (ARRAY['failed'::text, 'error'::text, 'in-progress'::text, 'running'::text]))), '1970-01-01 00:00:00+00'::timestamp with time zone))) >= ibj.max_allowed_failures) AS sla_breached_jobs,
            ( SELECT count(*) AS count
                   FROM iaas_backup_jobs
                  WHERE iaas_backup_jobs.deleted_at IS NULL AND iaas_backup_jobs.is_enabled = false AND iaas_backup_jobs.object_type = 'NextDeveloper\IAAS\Database\Models\VirtualMachines'::text) AS jobs_disabled,
            ( SELECT count(*) AS count
                   FROM iaas_virtual_machine_backups
                  WHERE iaas_virtual_machine_backups.deleted_at IS NULL AND (iaas_virtual_machine_backups.status = ANY (ARRAY['failed'::text, 'error'::text])) AND iaas_virtual_machine_backups.backup_ends >= (now() - '24:00:00'::interval)) AS jobs_failed_24h,
            ( SELECT count(*) AS count
                   FROM iaas_virtual_machine_backups
                  WHERE iaas_virtual_machine_backups.deleted_at IS NULL AND (iaas_virtual_machine_backups.status = ANY (ARRAY['failed'::text, 'error'::text])) AND iaas_virtual_machine_backups.backup_ends >= (now() - '30 days'::interval)) AS jobs_failed_30d,
            ( SELECT round(avg(EXTRACT(epoch FROM now() - latest_per_vm.last_backup_end) / 3600::numeric), 1) AS round
                   FROM ( SELECT max(iaas_virtual_machine_backups.backup_ends) AS last_backup_end
                           FROM iaas_virtual_machine_backups
                          WHERE iaas_virtual_machine_backups.deleted_at IS NULL AND (iaas_virtual_machine_backups.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) AND iaas_virtual_machine_backups.backup_ends IS NOT NULL
                          GROUP BY iaas_virtual_machine_backups.iaas_virtual_machine_id) latest_per_vm) AS avg_rpo_achieved_hours,
            ( SELECT round(avg(ibj.expected_rpo_hours)::numeric, 1) AS round
                   FROM iaas_backup_jobs ibj
                  WHERE ibj.deleted_at IS NULL AND ibj.is_enabled = true AND ibj.expected_rpo_hours IS NOT NULL AND ibj.object_type = 'NextDeveloper\IAAS\Database\Models\VirtualMachines'::text) AS avg_rpo_target_hours,
            ( SELECT COALESCE(sum(iri.size), 0::bigint) AS "coalesce"
                   FROM iaas_virtual_machine_backups ivmb
                     JOIN iaas_repository_images iri ON iri.id = ivmb.iaas_repository_image_id
                  WHERE ivmb.deleted_at IS NULL AND iri.deleted_at IS NULL) AS storage_used_bytes,
            ( SELECT count(*) AS count
                   FROM iaas_virtual_machine_backups
                  WHERE iaas_virtual_machine_backups.deleted_at IS NULL AND (iaas_virtual_machine_backups.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) AND iaas_virtual_machine_backups.backup_ends >= (now() - '24:00:00'::interval)) AS protections_done_24h,
            ( SELECT count(*) AS count
                   FROM iaas_virtual_machine_backups
                  WHERE iaas_virtual_machine_backups.deleted_at IS NULL AND (iaas_virtual_machine_backups.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) AND iaas_virtual_machine_backups.backup_ends >= (now() - '30 days'::interval)) AS protections_done_30d,
            ( SELECT count(*) AS count
                   FROM iaas_virtual_machine_backups
                  WHERE iaas_virtual_machine_backups.deleted_at IS NULL AND (iaas_virtual_machine_backups.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) AND iaas_virtual_machine_backups.backup_ends >= (now() - '60 days'::interval) AND iaas_virtual_machine_backups.backup_ends < (now() - '30 days'::interval)) AS protections_done_30d_prev,
            ( SELECT count(DISTINCT iaas_backup_job_replications.iaas_backup_job_id) AS count
                   FROM iaas_backup_job_replications
                  WHERE iaas_backup_job_replications.deleted_at IS NULL AND iaas_backup_job_replications.is_enabled = true) AS jobs_with_replication) t;
