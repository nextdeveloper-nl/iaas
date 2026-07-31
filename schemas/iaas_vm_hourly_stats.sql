-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_vm_hourly_stats AS
SELECT h.stat_hour,
    s.iaas_virtual_machine_id,
    s.cpu,
    s.ram,
    s.created_at AS valid_from,
    COALESCE(( SELECT min(s2.created_at) AS min
           FROM iaas_virtual_machine_stats s2
          WHERE s2.iaas_virtual_machine_id = s.iaas_virtual_machine_id AND s2.created_at > s.created_at AND s2.deleted_at IS NULL), now()) AS valid_to
   FROM iaas_virtual_machine_stats s
     CROSS JOIN LATERAL ( SELECT h_1.stat_hour
           FROM generate_series(date_trunc('hour'::text, s.created_at), date_trunc('hour'::text, COALESCE(( SELECT min(s2.created_at) AS min
                   FROM iaas_virtual_machine_stats s2
                  WHERE s2.iaas_virtual_machine_id = s.iaas_virtual_machine_id AND s2.created_at > s.created_at AND s2.deleted_at IS NULL), now()) - '00:00:01'::interval), '01:00:00'::interval) h_1(stat_hour)) h
  WHERE s.deleted_at IS NULL;
