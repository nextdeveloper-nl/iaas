-- PostgreSQL

CREATE TABLE iaas_ip_addresses (
    id                            bigint NOT NULL DEFAULT nextval('iaas_ip_addresses_id_seq'::regclass),
    uuid                          uuid DEFAULT gen_random_uuid(),
    ip_addr                       cidr NOT NULL,
    is_reserved                   boolean DEFAULT false,
    iam_account_id                bigint,
    iam_user_id                   bigint,
    created_at                    timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                    timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                    timestamp with time zone,
    iaas_network_id               bigint NOT NULL,
    iaas_virtual_network_card_id  bigint,
    custom_mac_addr               macaddr,
    CONSTRAINT iaas_ip_addresses_pkey PRIMARY KEY (id)
);
