-- PostgreSQL
-- Hourly aggregated CPU statistics

CREATE TABLE iaas_virtual_machine_cpu_hourly_stats (
    id                       bigint NOT NULL DEFAULT nextval('iaas_virtual_machine_cpu_hourly_stats_id_seq'::regclass),
    iaas_virtual_machine_id  bigint NOT NULL,
    hour_bucket              timestamp with time zone NOT NULL,
    avg_cpu                  numeric(5,2),
    max_cpu                  numeric(5,2),
    min_cpu                  numeric(5,2),
    stddev_cpu               numeric(5,2),
    data_points              integer,
    created_at               timestamp with time zone DEFAULT now(),
    updated_at               timestamp with time zone DEFAULT now(),
    deleted_at               timestamp with time zone,
    CONSTRAINT iaas_virtual_machine_cpu_hourly_stats_pkey PRIMARY KEY (id)
);
