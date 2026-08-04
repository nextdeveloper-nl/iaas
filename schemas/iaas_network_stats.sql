-- PostgreSQL

CREATE TABLE iaas_network_stats (
    id                bigint NOT NULL DEFAULT nextval('iaas_network_stats_id_seq'::regclass),
    uuid              uuid DEFAULT gen_random_uuid(),
    iaas_network_id   bigint NOT NULL,
    total_tx          integer NOT NULL DEFAULT 0,
    total_rx          integer NOT NULL DEFAULT 0,
    total_ip_address  integer NOT NULL DEFAULT 0,
    created_at        timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at        timestamp with time zone,
    CONSTRAINT iaas_network_stats_pkey PRIMARY KEY (id)
);
