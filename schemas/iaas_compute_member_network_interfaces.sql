-- PostgreSQL

CREATE TABLE iaas_compute_member_network_interfaces (
    id                      bigint NOT NULL DEFAULT nextval('iaas_compute_member_network_interfaces_id_seq'::regclass),
    uuid                    uuid DEFAULT gen_random_uuid(),
    device                  text,
    mac_addr                macaddr NOT NULL,
    vlan                    integer,
    mtu                     integer,
    is_management           boolean DEFAULT false,
    is_default              boolean DEFAULT false,
    is_connected            boolean DEFAULT false,
    hypervisor_data         json,
    iaas_compute_member_id  bigint NOT NULL,
    iam_account_id          bigint NOT NULL,
    iam_user_id             bigint NOT NULL,
    created_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at              timestamp with time zone,
    hypervisor_uuid         text,
    is_bridge               boolean NOT NULL DEFAULT true,
    network_uuid            text,
    network_name            text,
    vlan_data               json,
    CONSTRAINT iaas_compute_member_network_interfaces_pkey PRIMARY KEY (id)
);
