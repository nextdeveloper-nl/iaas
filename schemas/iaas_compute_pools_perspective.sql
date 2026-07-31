-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_compute_pools_perspective AS
SELECT id,
    uuid,
    name,
    virtualization,
    resource_validator,
    is_active,
    price_pergb,
    ( SELECT n_cc.name
           FROM common_currencies n_cc
          WHERE n_cc.id = cp.common_currency_id) AS currency,
    ( SELECT sum(n_icm.total_ram) AS sum
           FROM iaas_compute_members n_icm
          WHERE n_icm.iaas_compute_pool_id = cp.id) AS total_ram_in_pool,
    ( SELECT sum(n_icm.total_cpu) AS sum
           FROM iaas_compute_members n_icm
          WHERE n_icm.iaas_compute_pool_id = cp.id) AS total_cpu_in_pool,
    ( SELECT sum(n_icm.used_ram) AS sum
           FROM iaas_compute_members n_icm
          WHERE n_icm.iaas_compute_pool_id = cp.id) AS used_ram_in_pool,
    ( SELECT sum(n_icm.used_cpu) AS sum
           FROM iaas_compute_members n_icm
          WHERE n_icm.iaas_compute_pool_id = cp.id) AS used_cpu_in_pool,
    ( SELECT sum(n_icm.total_vm) AS sum
           FROM iaas_compute_members n_icm
          WHERE n_icm.iaas_compute_pool_id = cp.id) AS total_vm_in_pool,
    ( SELECT sum(n_icm.running_vm) AS sum
           FROM iaas_compute_members n_icm
          WHERE n_icm.iaas_compute_pool_id = cp.id) AS running_ram_in_pool,
    ( SELECT sum(n_icm.halted_vm) AS sum
           FROM iaas_compute_members n_icm
          WHERE n_icm.iaas_compute_pool_id = cp.id) AS halted_ram_in_pool,
    ( SELECT c_ia.name
           FROM iam_accounts c_ia
          WHERE c_ia.id = cp.iam_account_id) AS maintainer,
    ( SELECT n_ia.fullname
           FROM iam_users n_ia
          WHERE n_ia.id = cp.iam_user_id) AS responsible,
    tags,
    pool_type,
    iam_account_id,
    iam_user_id,
    created_at,
    updated_at,
    deleted_at
   FROM iaas_compute_pools cp;
