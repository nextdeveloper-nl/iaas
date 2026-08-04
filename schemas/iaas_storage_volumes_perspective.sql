-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_storage_volumes_perspective AS
SELECT id,
    uuid,
    name,
    disk_physical_type,
    total_hdd,
    free_hdd,
    virtual_allocation,
    is_storage,
    is_repo,
    is_cdrom,
    ( SELECT n_isp.name
           FROM iaas_storage_pools n_isp
          WHERE n_isp.id = isv.iaas_storage_pool_id) AS storage_pool,
    ( SELECT n_isp.id
           FROM iaas_storage_pools n_isp
          WHERE n_isp.id = isv.iaas_storage_pool_id) AS iaas_storage_pool_id,
    ( SELECT n_ism.name
           FROM iaas_storage_members n_ism
          WHERE n_ism.id = isv.iaas_storage_member_id) AS storage_member,
    ( SELECT n_ism.id
           FROM iaas_storage_members n_ism
          WHERE n_ism.id = isv.iaas_storage_member_id) AS iaas_storage_member_id,
    ( SELECT c_ia.name
           FROM iam_accounts c_ia
          WHERE c_ia.id = isv.iam_account_id) AS maintainer,
    ( SELECT n_ia.fullname
           FROM iam_users n_ia
          WHERE n_ia.id = isv.iam_account_id) AS responsible,
    iam_account_id,
    iam_user_id,
    created_at,
    updated_at,
    deleted_at
   FROM iaas_storage_volumes isv;
