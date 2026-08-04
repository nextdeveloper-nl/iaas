-- PostgreSQL
-- Stores CPU usage alerts and anomalies detected by monitoring system

CREATE TABLE iaas_virtual_machine_cpu_alerts (
    id                       bigint NOT NULL DEFAULT nextval('iaas_virtual_machine_cpu_alerts_id_seq'::regclass),
    iaas_virtual_machine_id  bigint NOT NULL,
    alert_time               timestamp with time zone DEFAULT now(),
    current_cpu              numeric(5,2),
    sma_9                    numeric(5,2),
    deviation                numeric(5,2),
    severity                 text NOT NULL,
    alert_reason             text,
    check_duration_ms        integer,
    created_at               timestamp with time zone DEFAULT now(),
    updated_at               timestamp with time zone DEFAULT now(),
    deleted_at               timestamp with time zone,
    CONSTRAINT iaas_virtual_machine_cpu_alerts_severity_check CHECK ((severity = ANY (ARRAY['CRITICAL'::text, 'HIGH'::text, 'MEDIUM'::text, 'NORMAL'::text]))),
    CONSTRAINT iaas_virtual_machine_cpu_alerts_pkey PRIMARY KEY (id)
);
