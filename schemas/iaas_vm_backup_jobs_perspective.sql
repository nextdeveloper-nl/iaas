-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_vm_backup_jobs_perspective AS
SELECT ibj.id,
    ibj.name AS job_name,
    ibj.type AS job_type,
    ibj.iam_account_id,
    ibj.object_id AS iaas_virtual_machine_id,
    ibj.is_enabled,
    ibj.expected_rpo_hours,
    ibj.expected_rto_hours,
    ibj.max_allowed_failures,
    ibj.sla_target_pct,
    ibj.notification_webhook,
    ibj.email_notification_recipients,
    ivm.name AS virtual_machine_name,
    ivm.hostname,
    ibrp.name AS retention_policy_name,
    ibrp.keep_for_days,
    ibrp.keep_last_n_backups,
    (EXISTS ( SELECT 1
           FROM common_task_schedulers cts
          WHERE cts.object_id = ibj.id AND cts.object_type = 'NextDeveloper\IAAS\Database\Models\BackupJobs'::text AND cts.deleted_at IS NULL)) AS is_scheduled,
    lr.backup_starts AS last_run_at,
    lr.backup_ends AS last_run_ended_at,
    lr.status AS last_run_status,
    lr.progress AS last_run_progress,
    EXTRACT(epoch FROM lr.backup_ends - lr.backup_starts)::integer AS last_run_duration_secs,
    iri.size AS last_run_size_bytes,
    cf.consecutive_failures,
        CASE
            WHEN ibj.expected_rpo_hours IS NULL THEN NULL::boolean
            WHEN lrok.last_ok_ends IS NULL THEN true
            ELSE (now() - lrok.last_ok_ends) > (ibj.expected_rpo_hours * '01:00:00'::interval)
        END AS rpo_breached,
    round(EXTRACT(epoch FROM now() - lrok.last_ok_ends) / 3600::numeric)::integer AS rpo_achieved_hours,
    cf.consecutive_failures >= ibj.max_allowed_failures AS sla_breached,
        CASE
            WHEN lr.id IS NULL THEN 'idle'::text
            WHEN lr.status = ANY (ARRAY['in-progress'::text, 'running'::text]) THEN 'running'::text
            WHEN lr.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text]) THEN 'ok'::text
            WHEN cf.consecutive_failures >= ibj.max_allowed_failures THEN 'critical'::text
            WHEN cf.consecutive_failures > 0 THEN 'warning'::text
            ELSE 'warning'::text
        END AS status_indicator,
    rep.replication_count,
    rep.replication_ok_count,
    rep.replication_failed_count,
    rep.last_replication_at,
        CASE
            WHEN rep.replication_count = 0 THEN 'none'::text
            WHEN rep.replication_failed_count = rep.replication_count THEN 'failed'::text
            WHEN rep.replication_failed_count > 0 THEN 'partial'::text
            ELSE 'ok'::text
        END AS replication_status_indicator,
    ibj.created_at,
    ibj.updated_at
   FROM iaas_backup_jobs ibj
     JOIN iaas_virtual_machines ivm ON ivm.id = ibj.object_id AND ivm.deleted_at IS NULL
     LEFT JOIN iaas_backup_retention_policies ibrp ON ibrp.id = ibj.iaas_backup_retention_policy_id AND ibrp.deleted_at IS NULL
     LEFT JOIN LATERAL ( SELECT iaas_virtual_machine_backups.id,
            iaas_virtual_machine_backups.uuid,
            iaas_virtual_machine_backups.name,
            iaas_virtual_machine_backups.description,
            iaas_virtual_machine_backups.path,
            iaas_virtual_machine_backups.filename,
            iaas_virtual_machine_backups.username,
            iaas_virtual_machine_backups.password,
            iaas_virtual_machine_backups.size,
            iaas_virtual_machine_backups.ram,
            iaas_virtual_machine_backups.cpu,
            iaas_virtual_machine_backups.hash,
            iaas_virtual_machine_backups.backup_type,
            iaas_virtual_machine_backups.iaas_virtual_machine_id,
            iaas_virtual_machine_backups.iam_account_id,
            iaas_virtual_machine_backups.iam_user_id,
            iaas_virtual_machine_backups.created_at,
            iaas_virtual_machine_backups.updated_at,
            iaas_virtual_machine_backups.deleted_at,
            iaas_virtual_machine_backups.status,
            iaas_virtual_machine_backups.backup_starts,
            iaas_virtual_machine_backups.backup_ends,
            iaas_virtual_machine_backups.iaas_repository_image_id,
            iaas_virtual_machine_backups.iaas_backup_job_id,
            iaas_virtual_machine_backups.data,
            iaas_virtual_machine_backups.progress
           FROM iaas_virtual_machine_backups
          WHERE iaas_virtual_machine_backups.iaas_backup_job_id = ibj.id AND iaas_virtual_machine_backups.deleted_at IS NULL
          ORDER BY iaas_virtual_machine_backups.backup_starts DESC
         LIMIT 1) lr ON true
     LEFT JOIN LATERAL ( SELECT max(iaas_virtual_machine_backups.backup_ends) AS last_ok_ends
           FROM iaas_virtual_machine_backups
          WHERE iaas_virtual_machine_backups.iaas_backup_job_id = ibj.id AND iaas_virtual_machine_backups.deleted_at IS NULL AND (iaas_virtual_machine_backups.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) AND iaas_virtual_machine_backups.backup_ends IS NOT NULL) lrok ON true
     LEFT JOIN iaas_repository_images iri ON iri.id = lr.iaas_repository_image_id AND iri.deleted_at IS NULL
     LEFT JOIN LATERAL ( SELECT count(*) AS consecutive_failures
           FROM iaas_virtual_machine_backups f
          WHERE f.iaas_backup_job_id = ibj.id AND f.deleted_at IS NULL AND (f.status = ANY (ARRAY['failed'::text, 'error'::text])) AND f.backup_starts > COALESCE(( SELECT max(s.backup_starts) AS max
                   FROM iaas_virtual_machine_backups s
                  WHERE s.iaas_backup_job_id = ibj.id AND s.deleted_at IS NULL AND (s.status <> ALL (ARRAY['failed'::text, 'error'::text, 'in-progress'::text, 'running'::text]))), '1970-01-01 00:00:00+00'::timestamp with time zone)) cf ON true
     LEFT JOIN LATERAL ( SELECT count(*) AS replication_count,
            count(*) FILTER (WHERE iaas_backup_job_replications.last_replication_status = ANY (ARRAY['ok'::text, 'completed'::text])) AS replication_ok_count,
            count(*) FILTER (WHERE iaas_backup_job_replications.last_replication_status = ANY (ARRAY['failed'::text, 'error'::text])) AS replication_failed_count,
            max(iaas_backup_job_replications.last_replicated_at) AS last_replication_at
           FROM iaas_backup_job_replications
          WHERE iaas_backup_job_replications.iaas_backup_job_id = ibj.id AND iaas_backup_job_replications.deleted_at IS NULL AND iaas_backup_job_replications.is_enabled = true) rep ON true
  WHERE ibj.deleted_at IS NULL AND ibj.object_type = 'NextDeveloper\IAAS\Database\Models\VirtualMachines'::text;
