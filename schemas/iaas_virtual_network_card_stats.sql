-- PostgreSQL

CREATE TABLE iaas_virtual_network_card_stats (
    id                            bigint NOT NULL DEFAULT nextval('iaas_virtual_network_card_stats_id_seq'::regclass),
    uuid                          uuid DEFAULT gen_random_uuid(),
    used_tx                       integer NOT NULL DEFAULT 0,
    used_rx                       integer NOT NULL DEFAULT 0,
    created_at                    timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                    timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                    timestamp with time zone,
    iaas_virtual_network_card_id  bigint NOT NULL,
    CONSTRAINT iaas_virtual_network_card_stats_pkey PRIMARY KEY (id)
);
