-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_cloud_node_daily_stats AS
SELECT date_trunc('day'::text, stat_hour) AS stat_date,
    iaas_cloud_node_id,
    round(avg(vm_count)) AS avg_vm_count,
    max(vm_count) AS max_vm_count,
    round(avg(total_vcpus)) AS avg_vcpus,
    max(total_vcpus) AS max_vcpus,
    round(avg(total_ram_gb)) AS avg_ram_gb,
    max(total_ram_gb) AS max_ram_gb
   FROM iaas_cloud_node_hourly_stats
  GROUP BY (date_trunc('day'::text, stat_hour)), iaas_cloud_node_id;
