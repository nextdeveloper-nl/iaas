-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_vm_backup_heatmap_stats AS
SELECT ibj.id AS iaas_backup_job_id,
    ibj.name AS job_name,
    ibj.type AS job_type,
    ibj.iam_account_id,
    ibj.is_enabled,
    ibj.expected_rpo_hours,
    ivm.name AS virtual_machine_name,
    ivm.hostname,
    d.backup_date,
    CURRENT_DATE - d.backup_date AS day_offset,
    to_char(d.backup_date::timestamp with time zone, 'Dy'::text) AS day_of_week,
        CASE
            WHEN count(*) FILTER (WHERE ivmb.status = ANY (ARRAY['failed'::text, 'error'::text])) > 0 THEN 'failed'::text
            WHEN count(*) FILTER (WHERE ivmb.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) = 0 AND ibj.expected_rpo_hours IS NOT NULL AND ibj.expected_rpo_hours <= 24::double precision AND d.backup_date < CURRENT_DATE THEN 'rpo_breach'::text
            WHEN count(*) FILTER (WHERE (ivmb.status <> ALL (ARRAY['ok'::text, 'completed'::text, 'done'::text, 'failed'::text, 'error'::text, 'in-progress'::text, 'running'::text])) AND ivmb.status IS NOT NULL) > 0 THEN 'warning'::text
            WHEN count(*) FILTER (WHERE ivmb.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) > 0 THEN 'success'::text
            ELSE 'no_job'::text
        END AS day_status,
    count(*) FILTER (WHERE ivmb.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) = 0 AND ibj.expected_rpo_hours IS NOT NULL AND ibj.expected_rpo_hours <= 24::double precision AND d.backup_date < CURRENT_DATE AS is_rpo_breach,
    count(ivmb.id) AS total_runs,
    count(*) FILTER (WHERE ivmb.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) AS success_runs,
    count(*) FILTER (WHERE ivmb.status = ANY (ARRAY['failed'::text, 'error'::text])) AS failed_runs,
    COALESCE(sum(iri.size) FILTER (WHERE ivmb.id IS NOT NULL), 0::bigint) AS day_size_bytes,
    round(avg(EXTRACT(epoch FROM ivmb.backup_ends - ivmb.backup_starts)) FILTER (WHERE ivmb.backup_ends IS NOT NULL))::integer AS avg_duration_secs
   FROM iaas_backup_jobs ibj
     JOIN iaas_virtual_machines ivm ON ivm.id = ibj.object_id AND ivm.deleted_at IS NULL
     CROSS JOIN ( SELECT generate_series(CURRENT_DATE - '27 days'::interval, CURRENT_DATE::timestamp without time zone, '1 day'::interval)::date AS backup_date) d
     LEFT JOIN iaas_virtual_machine_backups ivmb ON ivmb.iaas_backup_job_id = ibj.id AND ivmb.deleted_at IS NULL AND ivmb.backup_starts::date = d.backup_date
     LEFT JOIN iaas_repository_images iri ON iri.id = ivmb.iaas_repository_image_id AND iri.deleted_at IS NULL
  WHERE ibj.deleted_at IS NULL AND ibj.object_type = 'NextDeveloper\IAAS\Database\Models\VirtualMachines'::text
  GROUP BY ibj.id, ibj.name, ibj.type, ibj.iam_account_id, ibj.is_enabled, ibj.expected_rpo_hours, ivm.name, ivm.hostname, d.backup_date
  ORDER BY ibj.id, d.backup_date;
