-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_account_current_stats AS
SELECT vm.iam_account_id,
    count(*) AS vm_count,
    sum(s.cpu) AS total_vcpus,
    sum(s.ram) AS total_ram_gb
   FROM iaas_virtual_machine_stats s
     JOIN iaas_virtual_machines vm ON vm.id = s.iaas_virtual_machine_id AND vm.deleted_at IS NULL
  WHERE s.deleted_at IS NULL AND s.created_at = (( SELECT max(s2.created_at) AS max
           FROM iaas_virtual_machine_stats s2
          WHERE s2.iaas_virtual_machine_id = s.iaas_virtual_machine_id AND s2.deleted_at IS NULL))
  GROUP BY vm.iam_account_id
  ORDER BY (sum(s.cpu)) DESC;
