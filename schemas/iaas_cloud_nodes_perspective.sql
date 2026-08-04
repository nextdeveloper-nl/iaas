-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_cloud_nodes_perspective AS
SELECT id,
    uuid,
    name,
    is_public,
    is_alive,
    is_in_maintenance,
    ( SELECT c_id.name
           FROM iaas_datacenters c_id
          WHERE c_id.id = icn.iaas_datacenter_id) AS datacenter_name,
    ( SELECT count(c_icp.id) AS count
           FROM iaas_compute_pools c_icp
          WHERE c_icp.iaas_cloud_node_id = icn.id) AS compute_pool_count,
    ( SELECT count(c_isp.id) AS count
           FROM iaas_storage_pools c_isp
          WHERE c_isp.iaas_cloud_node_id = icn.id) AS storage_pool_count,
    ( SELECT count(c_inp.id) AS count
           FROM iaas_network_pools c_inp
          WHERE c_inp.iaas_cloud_node_id = icn.id) AS network_pool_count,
    ( SELECT c_ia.name
           FROM iam_accounts c_ia
          WHERE c_ia.id = icn.iam_account_id) AS maintainer,
    ( SELECT n_ia.fullname
           FROM iam_users n_ia
          WHERE n_ia.id = icn.iam_user_id) AS responsible,
    iam_user_id,
    iam_account_id,
    created_at,
    updated_at,
    deleted_at
   FROM iaas_cloud_nodes icn;
