-- PostgreSQL

CREATE TABLE iaas_network_members (
    id                    bigint NOT NULL DEFAULT nextval('iaas_network_members_id_seq'::regclass),
    uuid                  uuid DEFAULT gen_random_uuid(),
    name                  text,
    ip_addr               inet NOT NULL,
    ssh_username          text,
    ssh_password          text,
    iaas_network_pool_id  bigint NOT NULL,
    tags                  text[] NOT NULL DEFAULT '{}'::text[],
    created_at            timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at            timestamp with time zone,
    iam_account_id        bigint,
    iam_user_id           bigint,
    ssh_port              integer DEFAULT 22,
    local_ip_addr         inet,
    is_behind_firewall    boolean DEFAULT false,
    switch_type           text,
    is_root_switch        boolean DEFAULT false,
    CONSTRAINT iaas_network_members_pkey PRIMARY KEY (id)
);
