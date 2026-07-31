-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_network_pools_perspective AS
SELECT id,
    uuid,
    name,
    resource_validator,
    is_active,
    vlan_start,
    vlan_end,
    vxlan_start,
    vxlan_end,
    provisioning_alg,
    price_pergb,
    ( SELECT n_cc.name
           FROM common_currencies n_cc
          WHERE n_cc.id = np.common_currency_id) AS currency,
    ( SELECT count(n_in.id) AS count
           FROM iaas_networks n_in
          WHERE n_in.iaas_network_pool_id = np.id) AS total_networks,
    ( SELECT n_id.name
           FROM iaas_datacenters n_id
          WHERE n_id.id = np.iaas_datacenter_id) AS datacenter,
    ( SELECT n_cn.name
           FROM iaas_cloud_nodes n_cn
          WHERE n_cn.id = np.iaas_cloud_node_id) AS cloud_node,
    ( SELECT n_ia.name
           FROM iam_accounts n_ia
          WHERE n_ia.id = np.iam_account_id) AS maintainer,
    ( SELECT n_ia.fullname
           FROM iam_users n_ia
          WHERE n_ia.id = np.iam_account_id) AS responsible,
    tags,
    iam_account_id,
    iam_user_id,
    created_at,
    updated_at,
    deleted_at
   FROM iaas_network_pools np;
