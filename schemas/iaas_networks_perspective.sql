-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_networks_perspective AS
SELECT id,
    uuid,
    name,
    bandwidth,
    is_dmz,
    is_public,
    price_perip,
    price_pergb,
    speed_limit,
    ( SELECT n_inp.name
           FROM iaas_network_pools n_inp
          WHERE n_inp.id = net.iaas_network_pool_id) AS network_pool_name,
    ( SELECT n_icn.name
           FROM iaas_cloud_nodes n_icn
          WHERE n_icn.id = net.iaas_cloud_node_id) AS cloud_pool_name,
    iam_account_id,
    iam_user_id,
    created_at,
    updated_at,
    deleted_at
   FROM iaas_networks net
  WHERE deleted_at IS NULL;
