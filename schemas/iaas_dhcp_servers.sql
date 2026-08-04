-- PostgreSQL

CREATE TABLE iaas_dhcp_servers (
    id                       bigint NOT NULL DEFAULT nextval('iaas_dhcp_servers_id_seq'::regclass),
    uuid                     uuid DEFAULT gen_random_uuid(),
    name                     text NOT NULL,
    iaas_virtual_machine_id  bigint,
    dhcp_data                json,
    ssh_username             text,
    ssh_password             text,
    ip_addr                  cidr NOT NULL,
    api_token                text,
    api_url                  text,
    iam_account_id           bigint NOT NULL,
    iam_user_id              bigint NOT NULL,
    created_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at               timestamp with time zone,
    server_type              text DEFAULT 'isc'::text,
    CONSTRAINT iaas_dhcp_servers_pkey PRIMARY KEY (id)
);
