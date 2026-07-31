-- PostgreSQL

CREATE TABLE iaas_ansible_roles (
    id                      bigint NOT NULL DEFAULT nextval('iaas_ansible_roles_id_seq'::regclass),
    uuid                    uuid DEFAULT gen_random_uuid(),
    name                    text,
    version                 smallint,
    release_number          smallint DEFAULT 1,
    config                  json NOT NULL,
    hash                    text NOT NULL,
    min_ansible_version     text,
    prerequisites           text,
    is_active               boolean DEFAULT true,
    is_procedure            boolean DEFAULT false,
    iaas_ansible_server_id  bigint NOT NULL,
    iam_account_id          bigint NOT NULL,
    iam_user_id             bigint NOT NULL,
    created_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at              timestamp with time zone,
    description             text,
    CONSTRAINT iaas_ansible_roles_pkey PRIMARY KEY (id)
);
