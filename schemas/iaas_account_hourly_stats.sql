-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_account_hourly_stats AS
SELECT h.stat_hour,
    vm.iam_account_id,
    count(DISTINCT h.iaas_virtual_machine_id) AS vm_count,
    sum(h.cpu) AS total_vcpus,
    sum(h.ram) AS total_ram_gb
   FROM iaas_vm_hourly_stats h
     JOIN iaas_virtual_machines vm ON vm.id = h.iaas_virtual_machine_id AND vm.deleted_at IS NULL
  GROUP BY h.stat_hour, vm.iam_account_id;
