-- PostgreSQL

CREATE TABLE iaas_compute_member_metrics (
    id                      bigint NOT NULL DEFAULT nextval('iaas_compute_member_metrics_id_seq'::regclass),
    uuid                    uuid DEFAULT gen_random_uuid(),
    source                  text DEFAULT 'hypervisor'::text,
    iaas_compute_member_id  bigint NOT NULL,
    parameter               text,
    value                   numeric(16,8) DEFAULT 0,
    timestamp               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at              timestamp with time zone,
    CONSTRAINT iaas_compute_member_metrics_pkey PRIMARY KEY (id)
);
