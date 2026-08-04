-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_vm_daily_stats AS
SELECT date_trunc('day'::text, stat_hour) AS stat_date,
    iaas_virtual_machine_id,
    round(avg(cpu)) AS avg_vcpus,
    max(cpu) AS max_vcpus,
    round(avg(ram)) AS avg_ram_gb,
    max(ram) AS max_ram_gb
   FROM iaas_vm_hourly_stats
  GROUP BY (date_trunc('day'::text, stat_hour)), iaas_virtual_machine_id;
