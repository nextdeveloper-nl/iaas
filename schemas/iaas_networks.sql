-- PostgreSQL

CREATE TABLE iaas_networks (
    id                    bigint NOT NULL DEFAULT nextval('iaas_networks_id_seq'::regclass),
    uuid                  uuid DEFAULT gen_random_uuid(),
    name                  text NOT NULL,
    vlan                  integer NOT NULL, -- [ro]
    vxlan                 text NOT NULL, -- [ro]
    bandwidth             bigint, -- [ro]
    is_public             boolean DEFAULT false,
    is_vpn                boolean DEFAULT false, -- [ro]
    is_management         boolean DEFAULT false, -- [ro]
    ip_addr               cidr,
    ip_addr_range_start   cidr,
    ip_addr_range_end     cidr,
    dns_nameservers       text[],
    mtu                   integer DEFAULT 1500, -- [ro]
    common_domain_id      bigint,
    iaas_dhcp_server_id   bigint,
    iaas_gateway_id       bigint,
    iam_account_id        bigint,
    iam_user_id           bigint,
    created_at            timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at            timestamp with time zone,
    is_dmz                boolean DEFAULT true, -- [ro]
    price_pergb           numeric(13,8) DEFAULT 0, -- [ro]
    price_perip           numeric(13,8) DEFAULT 0, -- [ro]
    speed_limit           integer DEFAULT 0,
    iaas_network_pool_id  bigint NOT NULL,
    iaas_cloud_node_id    bigint NOT NULL,
    cidr                  cidr,
    common_currency_id    bigint,
    iaas_datacenter_id    bigint,
    gateway_ip_addr       text,
    CONSTRAINT iaas_networks_pkey PRIMARY KEY (id)
);
