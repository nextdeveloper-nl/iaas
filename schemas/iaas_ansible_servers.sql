-- PostgreSQL

CREATE TABLE iaas_ansible_servers (
    id                       bigint NOT NULL DEFAULT nextval('iaas_ansible_servers_id_seq'::regclass),
    uuid                     uuid DEFAULT gen_random_uuid(),
    name                     text NOT NULL,
    is_external_machine      boolean DEFAULT false,
    iaas_virtual_machine_id  bigint,
    ssh_username             text,
    ssh_password             text,
    ssh_port                 smallint DEFAULT 22,
    ip_v4                    inet,
    ip_v6                    inet,
    ansible_version          smallint,
    roles_path               text,
    system_playbooks_path    text,
    execution_path           text,
    is_active                boolean DEFAULT false,
    is_public                boolean DEFAULT false,
    price_persecond          numeric(1,8) DEFAULT 0,
    common_currency_id       bigint,
    iam_user_id              bigint NOT NULL,
    iam_account_id           bigint NOT NULL,
    created_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at               timestamp with time zone,
    CONSTRAINT iaas_ansible_servers_pkey PRIMARY KEY (id)
);
