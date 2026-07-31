-- PostgreSQL

CREATE TABLE iaas_network_pool_stats (
    id                    bigint NOT NULL DEFAULT nextval('iaas_network_pool_stats_id_seq'::regclass),
    uuid                  uuid DEFAULT gen_random_uuid(),
    iaas_network_pool_id  bigint NOT NULL,
    used_vlan             integer NOT NULL DEFAULT 0,
    used_vxlan            integer NOT NULL DEFAULT 0,
    created_at            timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at            timestamp with time zone,
    CONSTRAINT iaas_network_pool_stats_pkey PRIMARY KEY (id)
);
