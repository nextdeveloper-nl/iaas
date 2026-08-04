-- PostgreSQL

CREATE TABLE iaas_network_members_interfaces (
    id                      bigint NOT NULL DEFAULT nextval('iaas_network_members_interfaces_id_seq'::regclass),
    uuid                    uuid DEFAULT gen_random_uuid(),
    name                    text,
    ip_addr                 cidr,
    configuration           text,
    iaas_network_member_id  bigint NOT NULL,
    created_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at              timestamp with time zone,
    is_up                   boolean DEFAULT false,
    iaas_network_id         bigint,
    is_shutdown             boolean NOT NULL DEFAULT true,
    iam_account_id          bigint,
    iam_user_id             bigint,
    CONSTRAINT iaas_network_members_interfaces_pkey PRIMARY KEY (id)
);
