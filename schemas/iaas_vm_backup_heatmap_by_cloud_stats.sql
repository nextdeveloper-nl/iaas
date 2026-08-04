-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_vm_backup_heatmap_by_cloud_stats AS
SELECT icn.id AS iaas_cloud_node_id,
    icn.name AS cloud_node_name,
    idc.id AS iaas_datacenter_id,
    idc.name AS datacenter_name,
    d.backup_date,
    CURRENT_DATE - d.backup_date AS day_offset,
    to_char(d.backup_date::timestamp with time zone, 'Dy'::text) AS day_of_week,
    count(DISTINCT ibj.id) AS distinct_jobs,
    count(DISTINCT ibj.id) FILTER (WHERE ibj.expected_rpo_hours IS NOT NULL AND ibj.expected_rpo_hours <= 24::double precision AND d.backup_date < CURRENT_DATE AND NOT (EXISTS ( SELECT 1
           FROM iaas_virtual_machine_backups x
          WHERE x.iaas_backup_job_id = ibj.id AND x.deleted_at IS NULL AND (x.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) AND x.backup_starts::date = d.backup_date))) AS rpo_breach_count,
        CASE
            WHEN count(*) FILTER (WHERE ivmb.status = ANY (ARRAY['failed'::text, 'error'::text])) > 0 THEN 'failed'::text
            WHEN count(DISTINCT ibj.id) FILTER (WHERE ibj.expected_rpo_hours IS NOT NULL AND ibj.expected_rpo_hours <= 24::double precision AND d.backup_date < CURRENT_DATE AND NOT (EXISTS ( SELECT 1
               FROM iaas_virtual_machine_backups x
              WHERE x.iaas_backup_job_id = ibj.id AND x.deleted_at IS NULL AND (x.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) AND x.backup_starts::date = d.backup_date))) > 0 THEN 'rpo_breach'::text
            WHEN count(*) FILTER (WHERE (ivmb.status <> ALL (ARRAY['ok'::text, 'completed'::text, 'done'::text, 'failed'::text, 'error'::text, 'in-progress'::text, 'running'::text])) AND ivmb.status IS NOT NULL) > 0 THEN 'warning'::text
            WHEN count(*) FILTER (WHERE ivmb.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) > 0 THEN 'success'::text
            ELSE 'no_job'::text
        END AS day_status,
    count(ivmb.id) AS total_runs,
    count(*) FILTER (WHERE ivmb.status = ANY (ARRAY['ok'::text, 'completed'::text, 'done'::text])) AS success_runs,
    count(*) FILTER (WHERE ivmb.status = ANY (ARRAY['failed'::text, 'error'::text])) AS failed_runs,
    COALESCE(sum(iri.size) FILTER (WHERE ivmb.id IS NOT NULL), 0::bigint) AS day_size_bytes,
    round(avg(EXTRACT(epoch FROM ivmb.backup_ends - ivmb.backup_starts)) FILTER (WHERE ivmb.backup_ends IS NOT NULL))::integer AS avg_duration_secs
   FROM iaas_cloud_nodes icn
     LEFT JOIN iaas_datacenters idc ON idc.id = icn.iaas_datacenter_id AND idc.deleted_at IS NULL
     CROSS JOIN ( SELECT generate_series(CURRENT_DATE - '27 days'::interval, CURRENT_DATE::timestamp without time zone, '1 day'::interval)::date AS backup_date) d
     JOIN iaas_virtual_machines ivm ON ivm.iaas_cloud_node_id = icn.id AND ivm.deleted_at IS NULL
     JOIN iaas_backup_jobs ibj ON ibj.object_id = ivm.id AND ibj.object_type = 'NextDeveloper\IAAS\Database\Models\VirtualMachines'::text AND ibj.deleted_at IS NULL AND COALESCE(ibj.is_enabled, true) = true
     LEFT JOIN iaas_virtual_machine_backups ivmb ON ivmb.iaas_backup_job_id = ibj.id AND ivmb.deleted_at IS NULL AND ivmb.backup_starts::date = d.backup_date
     LEFT JOIN iaas_repository_images iri ON iri.id = ivmb.iaas_repository_image_id AND iri.deleted_at IS NULL
  WHERE icn.deleted_at IS NULL
  GROUP BY icn.id, icn.name, idc.id, idc.name, d.backup_date
  ORDER BY idc.name, icn.name, d.backup_date;
