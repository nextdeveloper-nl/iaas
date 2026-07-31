-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_repositories_perspective AS
SELECT id,
    uuid,
    name,
    description,
    ip_addr,
    local_ip_addr,
    is_active,
    is_vm_repo,
    is_iso_repo,
    is_docker_registry,
    ( SELECT u_ia.name
           FROM iam_accounts u_ia
          WHERE u_ia.id = ir.iam_account_id) AS repository_maintainer,
    ( SELECT count(u_iri.id) AS count
           FROM iaas_repository_images u_iri
          WHERE u_iri.is_iso = true AND u_iri.iaas_repository_id = ir.id) AS iso_image_count,
    ( SELECT count(u_iri.id) AS count
           FROM iaas_repository_images u_iri
          WHERE u_iri.is_virtual_machine_image = true AND u_iri.iaas_repository_id = ir.id) AS vm_image_count,
    iam_user_id,
    iam_account_id,
    ( SELECT json_agg(json_build_object('name', n_cs.name, 'value', n_cs.value, 'reason', n_cs.reason)) AS json_agg
           FROM common_states n_cs
          WHERE n_cs.object_id = ir.id AND n_cs.object_type = 'NextDeveloper\IAAS\Database\Models\Repositories'::text) AS states,
    is_backup_repository,
    price_pergb,
    common_currency_id,
    created_at,
    updated_at,
    deleted_at
   FROM iaas_repositories ir;
