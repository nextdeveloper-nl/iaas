-- PostgreSQL

CREATE TABLE iaas_network_pools (
    id                  bigint NOT NULL DEFAULT nextval('iaas_network_pools_id_seq'::regclass),
    uuid                uuid DEFAULT gen_random_uuid(),
    name                text NOT NULL,
    vlan_start          integer NOT NULL DEFAULT 10,
    vlan_end            integer NOT NULL DEFAULT 4090,
    vxlan_start         integer NOT NULL DEFAULT 10,
    vxlan_end           integer NOT NULL DEFAULT 65530,
    is_vlan_available   boolean NOT NULL DEFAULT true,
    is_vxlan_available  boolean NOT NULL DEFAULT false,
    is_active           boolean NOT NULL DEFAULT false,
    iaas_datacenter_id  bigint,
    iaas_cloud_node_id  bigint,
    iam_account_id      bigint NOT NULL,
    iam_user_id         bigint NOT NULL,
    provisioning_alg    text,
    resource_validator  text,
    tags                text[] NOT NULL DEFAULT '{}'::text[],
    price_pergb         numeric(1,8) DEFAULT 0,
    common_currency_id  bigint,
    created_at          timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at          timestamp with time zone,
    CONSTRAINT iaas_network_pools_pkey PRIMARY KEY (id)
);
