-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_storage_pools_perspective AS
SELECT sp.id,
    sp.uuid,
    sp.name,
    sp.price_pergb,
    sp.is_active,
    cc.name AS currency,
    dc.name AS datacenter,
    cn.name AS cloud_node,
    sp.tags,
    ia.name AS maintainer,
    iu.fullname AS responsible,
    sp.iam_account_id,
    sp.iam_user_id,
    COALESCE(v.total_hdd, 0::numeric) AS total_hdd,
    COALESCE(v.used_hdd, 0::numeric) AS used_hdd,
    COALESCE(v.free_hdd, 0::numeric) AS free_hdd,
    COALESCE(v.virtual_allocation, 0::numeric) AS virtual_allocation,
    sp.created_at,
    sp.updated_at,
    sp.deleted_at
   FROM iaas_storage_pools sp
     LEFT JOIN ( SELECT iaas_storage_volumes.iaas_storage_pool_id,
            sum(iaas_storage_volumes.total_hdd) AS total_hdd,
            sum(iaas_storage_volumes.used_hdd) AS used_hdd,
            sum(iaas_storage_volumes.free_hdd) AS free_hdd,
            sum(iaas_storage_volumes.virtual_allocation) AS virtual_allocation
           FROM iaas_storage_volumes
          GROUP BY iaas_storage_volumes.iaas_storage_pool_id) v ON v.iaas_storage_pool_id = sp.id
     LEFT JOIN common_currencies cc ON cc.id = sp.common_currency_id
     LEFT JOIN iaas_datacenters dc ON dc.id = sp.iaas_datacenter_id
     LEFT JOIN iaas_cloud_nodes cn ON cn.id = sp.iaas_cloud_node_id
     LEFT JOIN iam_accounts ia ON ia.id = sp.iam_account_id
     LEFT JOIN iam_users iu ON iu.id = sp.iam_user_id;
