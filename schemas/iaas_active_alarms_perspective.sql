-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_active_alarms_perspective AS
SELECT id,
    uuid,
    object_type,
    object_id,
    check_type,
    check_status,
    severity,
    error_message,
    error_code,
    response_time_ms,
    checked_at,
    next_check_at,
    check_data,
    metadata,
    created_at,
    updated_at
   FROM iaas_health_checks hc
  WHERE deleted_at IS NULL AND (check_status = ANY (ARRAY['warning'::text, 'critical'::text, 'failed'::text])) AND (object_type = ANY (ARRAY['NextDeveloper\IAAS\ComputeMembers'::text, 'NextDeveloper\IAAS\StorageMembers'::text, 'NextDeveloper\IAAS\NetworkMembers'::text, 'NextDeveloper\IAAS\VirtualMachines'::text]))
  ORDER BY (
        CASE severity
            WHEN 'critical'::text THEN 1
            WHEN 'high'::text THEN 2
            WHEN 'medium'::text THEN 3
            WHEN 'low'::text THEN 4
            ELSE 5
        END), checked_at DESC;
