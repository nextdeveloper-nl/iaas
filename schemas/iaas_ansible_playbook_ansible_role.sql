-- PostgreSQL

CREATE TABLE iaas_ansible_playbook_ansible_role (
    id                        bigint NOT NULL DEFAULT nextval('iaas_ansible_playbook_ansible_role_id_seq'::regclass),
    uuid                      uuid DEFAULT gen_random_uuid(),
    position                  smallint DEFAULT 0,
    config                    json NOT NULL,
    iaas_ansible_server_id    bigint NOT NULL,
    iaas_ansible_playbook_id  bigint NOT NULL,
    iam_account_id            bigint NOT NULL,
    iam_user_id               bigint NOT NULL,
    created_at                timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                timestamp with time zone,
    CONSTRAINT iaas_ansible_playbook_ansible_role_pkey PRIMARY KEY (id)
);
