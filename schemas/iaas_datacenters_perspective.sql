-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_datacenters_perspective AS
SELECT id,
    uuid,
    name,
    slug,
    description,
    is_public,
    is_active,
    maintenance_mode AS is_in_maintenance,
    geo_latitude,
    geo_longitude,
    tier_level,
    total_capacity,
    guaranteed_uptime,
    is_carrier_neutral,
    power_source,
    ups,
    cooling,
    ( SELECT u_cc.name
           FROM common_cities u_cc
          WHERE u_cc.id = id.common_city_id) AS city_name,
    ( SELECT u_ccountries.name
           FROM common_countries u_ccountries
          WHERE u_ccountries.id = id.common_country_id) AS country_name,
    ( SELECT count(u_icn.id) AS count
           FROM iaas_cloud_nodes u_icn
          WHERE u_icn.iaas_datacenter_id = id.id) AS cloud_nodes_count,
    ( SELECT count(u_icp.id) AS count
           FROM iaas_compute_pools u_icp
          WHERE u_icp.iaas_datacenter_id = id.id) AS compute_pools_count,
    ( SELECT count(u_isp.id) AS count
           FROM iaas_storage_pools u_isp
          WHERE u_isp.iaas_datacenter_id = id.id) AS storage_pools_count,
    ( SELECT count(u_inp.id) AS count
           FROM iaas_network_pools u_inp
          WHERE u_inp.iaas_datacenter_id = id.id) AS network_pools_count,
    tags,
    ( SELECT u_ia.name
           FROM iam_accounts u_ia
          WHERE u_ia.id = id.iam_account_id) AS datacenter_maintainer,
    ( SELECT c_ia.name
           FROM iam_accounts c_ia
          WHERE c_ia.id = id.iam_account_id) AS maintainer,
    ( SELECT n_ia.fullname
           FROM iam_users n_ia
          WHERE n_ia.id = id.iam_user_id) AS responsible,
    iam_user_id,
    iam_account_id,
    created_at,
    updated_at,
    deleted_at
   FROM iaas_datacenters id;
