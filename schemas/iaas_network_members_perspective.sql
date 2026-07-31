-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_network_members_perspective AS
SELECT id,
    uuid,
    ip_addr,
    ( SELECT n_inp.name
           FROM iaas_network_pools n_inp
          WHERE n_inp.id = inm.iaas_network_pool_id) AS network_pool_name,
    iaas_network_pool_id,
    ( SELECT c_ia.name
           FROM iam_accounts c_ia
          WHERE c_ia.id = inm.iam_account_id) AS maintainer,
    ( SELECT n_ia.fullname
           FROM iam_users n_ia
          WHERE n_ia.id = inm.iam_account_id) AS responsible,
    tags,
    iam_user_id,
    iam_account_id,
    created_at,
    updated_at,
    deleted_at
   FROM iaas_network_members inm;
