-- PostgreSQL

CREATE TABLE iaas_virtual_machine_metrics (
    id                       bigint NOT NULL DEFAULT nextval('iaas_virtual_machine_metrics_id_seq'::regclass),
    uuid                     uuid DEFAULT gen_random_uuid(),
    iaas_virtual_machine_id  bigint NOT NULL,
    parameter                text,
    value                    double precision DEFAULT 0,
    timestamp                timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at               timestamp with time zone,
    source                   text DEFAULT 'hypervisor'::text,
    CONSTRAINT iaas_virtual_machine_metrics_pkey PRIMARY KEY (id)
);
