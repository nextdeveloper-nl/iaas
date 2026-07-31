-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_repository_images_perspective AS
SELECT ri.id,
    ri.uuid,
    ri.name,
    concat(ri.distro, ' ', ri.version, ' ', ri.extra, ' ', ri.cpu_type) AS image_name,
    ri.description,
    ri.post_boot_script,
    ri.os,
    ri.distro,
    ri.version,
    ri.cpu_type,
    ri.extra,
    ri.release_version,
    ri.is_latest,
    ri.supported_virtualizations,
    ri.is_iso,
    ri.is_public,
    ri.is_virtual_machine_image,
    ri.is_docker_image,
    ri.cpu,
    ri.ram,
    ri.size,
    ri.iaas_virtual_machine_id,
    ri.iaas_repository_id,
    ir.name AS repository_name,
    ir.is_backup_repository,
    ri.iam_account_id,
    ri.iam_user_id,
    ri.has_plusclouds_service,
        CASE
            WHEN ivmb.name IS NULL THEN false
            ELSE true
        END AS is_backup,
    ri.created_at,
    ri.updated_at,
    ri.deleted_at
   FROM iaas_repository_images ri
     JOIN iaas_repositories ir ON ri.iaas_repository_id = ir.id
     LEFT JOIN iaas_virtual_machine_backups ivmb ON ri.iaas_virtual_machine_id = ivmb.iaas_virtual_machine_id
  WHERE ri.is_active = true;
