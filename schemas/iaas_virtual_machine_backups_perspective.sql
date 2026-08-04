-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_virtual_machine_backups_perspective AS
SELECT ivmb.id,
    ivmb.uuid,
    ivmb.name,
    ivmb.description,
    ivmb.path,
    ivmb.filename,
    ivmb.cpu,
    ivmb.ram,
    ivmb.backup_type,
    ivmb.iaas_virtual_machine_id,
    ivmb.iam_user_id,
    ivmb.iam_account_id,
    ivmb.status,
    ivmb.backup_starts,
    ivmb.backup_ends,
    ivmb.iaas_repository_image_id,
    iri.iaas_repository_id,
    ivmb.iaas_backup_job_id,
    ivmb.progress,
    iri.hash,
    iri.is_latest,
    iri.size,
    iri.os,
    iri.distro,
    iri.cpu_type,
    iri.supported_virtualizations,
    ibj.type AS backup_job_type,
    ibrp.name AS retention_policy_name,
    ibrp.keep_for_days,
    ibrp.keep_last_n_backups,
    ivm.hostname,
    ivm.name AS virtual_machine_name,
    ivmb.created_at,
    ivmb.updated_at,
    ivmb.deleted_at
   FROM iaas_virtual_machine_backups ivmb
     JOIN iaas_backup_jobs ibj ON ibj.id = ivmb.iaas_backup_job_id
     JOIN iaas_backup_retention_policies ibrp ON ibj.iaas_backup_retention_policy_id = ibrp.id
     JOIN iaas_virtual_machines ivm ON ivm.id = ivmb.iaas_virtual_machine_id
     JOIN iaas_repository_images iri ON iri.id = ivmb.iaas_repository_image_id;
