-- PostgreSQL

CREATE TABLE iaas_virtual_machine_stats (
    id                       bigint NOT NULL DEFAULT nextval('iaas_virtual_machine_stats_id_seq'::regclass),
    uuid                     uuid DEFAULT gen_random_uuid(),
    iaas_virtual_machine_id  bigint NOT NULL,
    cpu                      integer NOT NULL,
    ram                      integer NOT NULL,
    created_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at               timestamp with time zone,
    status                   text,
    uptime_seconds           bigint,
    cpu_percent              numeric(5,2) DEFAULT NULL::numeric,
    memory_percent           numeric(5,2) DEFAULT NULL::numeric,
    agent_version            character varying(20) DEFAULT NULL::character varying,
    source                   character varying(20) NOT NULL DEFAULT 'hypervisor'::character varying,
    CONSTRAINT iaas_virtual_machine_stats_pkey PRIMARY KEY (id)
);
