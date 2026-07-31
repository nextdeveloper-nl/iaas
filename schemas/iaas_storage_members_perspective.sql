-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_storage_members_perspective AS
SELECT id,
    uuid,
    name,
    hostname,
    ip_addr,
    local_ip_addr,
    is_healthy,
    has_warning,
    has_error,
    total_disk,
    used_disk,
    uptime,
    is_maintenance,
    is_alive,
    ( SELECT n_isp.name
           FROM iaas_storage_pools n_isp
          WHERE n_isp.id = ism.iaas_storage_pool_id) AS storage_pool,
    ( SELECT n_isp.id
           FROM iaas_storage_pools n_isp
          WHERE n_isp.id = ism.iaas_storage_pool_id) AS iaas_storage_pool_id,
    ( SELECT c_ia.name
           FROM iam_accounts c_ia
          WHERE c_ia.id = ism.iam_account_id) AS maintainer,
    ( SELECT n_ia.fullname
           FROM iam_users n_ia
          WHERE n_ia.id = ism.iam_account_id) AS responsible,
    iam_account_id,
    iam_user_id,
    created_at,
    updated_at,
    deleted_at
   FROM iaas_storage_members ism;
