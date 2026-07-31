-- PostgreSQL
-- [label:Health check records for all IAAS infrastructure components including VMs, compute members, storage, and network resources]

CREATE TABLE iaas_health_checks (
    id                bigint NOT NULL DEFAULT nextval('iaas_health_checks_id_seq'::regclass), -- [label:Primary key identifier]
    uuid              uuid NOT NULL DEFAULT gen_random_uuid(), -- [label:Unique UUID for external references]
    object_type       text NOT NULL, -- [label:Type of object being checked (e.g., VirtualMachines, ComputeMembers, StorageMembers, NetworkMembers)]
    object_id         bigint NOT NULL, -- [label:ID of the object being checked]
    check_type        text NOT NULL DEFAULT 'general'::text, -- [label:Type of health check: general, service, connectivity, resource, backup, security, performance, etc.]
    check_status      text NOT NULL, -- [label:Status of the check: healthy, warning, critical, unknown, pending, failed]
    severity          text DEFAULT 'info'::text, -- [label:Severity level: info, low, medium, high, critical]
    checked_at        timestamp with time zone NOT NULL DEFAULT now(), -- [label:Timestamp when the health check was performed]
    next_check_at     timestamp with time zone, -- [label:Scheduled timestamp for the next health check]
    response_time_ms  integer, -- [label:Response time of the health check in milliseconds]
    error_message     text, -- [label:Detailed error message if check failed]
    error_code        text, -- [label:Error code for categorization and filtering]
    check_data        json, -- [label:JSONB field containing detailed check results, metrics, and raw data]
    metadata          json, -- [label:JSONB field for additional contextual information]
    created_at        timestamp with time zone NOT NULL DEFAULT now(), -- [label:Record creation timestamp]
    updated_at        timestamp with time zone NOT NULL DEFAULT now(), -- [label:Record last update timestamp]
    deleted_at        timestamp with time zone, -- [label:Soft delete timestamp]
    CONSTRAINT iaas_health_checks_pkey PRIMARY KEY (id)
);
