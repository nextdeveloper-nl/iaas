-- PostgreSQL

CREATE TABLE iaas_virtual_machine_cpu_metrics_agg (
    id                       bigint NOT NULL DEFAULT nextval('iaas_virtual_machine_cpu_metrics_agg_id_seq'::regclass),
    iaas_virtual_machine_id  bigint NOT NULL,
    timestamp                timestamp with time zone NOT NULL,
    avg_cpu                  numeric(10,4) NOT NULL,
    sma9                     numeric(10,4),
    stddev9                  numeric(10,4),
    ema9                     numeric(10,4),
    created_at               timestamp with time zone DEFAULT now(),
    updated_at               timestamp with time zone DEFAULT now(),
    CONSTRAINT iaas_virtual_machine_cpu_metrics_agg_pkey PRIMARY KEY (id)
);
