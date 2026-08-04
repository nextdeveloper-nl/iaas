-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_compute_member_storage_volumes_perspective AS
SELECT cm.id,
    cm.uuid,
    cm.name,
    cm.description,
    ( SELECT n_isv.name
           FROM iaas_storage_volumes n_isv
          WHERE n_isv.id = cm.iaas_storage_volume_id) AS volume_name,
    ( SELECT n_isv.id
           FROM iaas_storage_volumes n_isv
          WHERE n_isv.id = cm.iaas_storage_volume_id) AS iaas_storage_volume_id,
    ( SELECT n_isp.name
           FROM iaas_storage_pools n_isp
          WHERE n_isp.id = cm.iaas_storage_pool_id) AS storage_pool_name,
    ( SELECT n_isp.id
           FROM iaas_storage_pools n_isp
          WHERE n_isp.id = cm.iaas_storage_pool_id) AS iaas_storage_pool_id,
    ( SELECT n_ism.name
           FROM iaas_storage_members n_ism
          WHERE n_ism.id = cm.iaas_storage_member_id) AS storage_member_name,
    ( SELECT n_ism.id
           FROM iaas_storage_members n_ism
          WHERE n_ism.id = cm.iaas_storage_member_id) AS iaas_storage_member_id,
    ( SELECT n_icm.name
           FROM iaas_compute_members n_icm
          WHERE n_icm.id = cm.iaas_compute_member_id) AS compute_member_name,
    cm.iaas_compute_member_id,
    ( SELECT c_ia.name
           FROM iam_accounts c_ia
          WHERE c_ia.id = cm.iam_account_id) AS maintainer,
    ( SELECT n_ia.fullname
           FROM iam_users n_ia
          WHERE n_ia.id = cm.iam_user_id) AS responsible,
    cm.iam_account_id,
    cm.iam_user_id,
    isv.used_hdd,
    isv.free_hdd,
    isv.disk_physical_type,
    isv.is_storage,
    isv.is_alive,
    isv.is_cdrom,
    isv.total_hdd,
    isv.virtual_allocation,
    isv.created_at,
    isv.updated_at,
    isv.deleted_at
   FROM iaas_compute_member_storage_volumes cm
     LEFT JOIN iaas_storage_volumes isv ON cm.iaas_storage_volume_id = isv.id;
