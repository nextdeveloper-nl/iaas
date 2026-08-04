-- PostgreSQL

CREATE TABLE iaas_ansible_system_playbooks (
    id                      bigint NOT NULL DEFAULT nextval('iaas_ansible_system_playbooks_id_seq'::regclass),
    uuid                    uuid DEFAULT gen_random_uuid(),
    slug                    text NOT NULL,
    name                    text NOT NULL,
    description             text,
    package                 text NOT NULL,
    path                    text NOT NULL,
    playbook_filename       text NOT NULL,
    is_public               boolean DEFAULT false,
    is_procedure            boolean DEFAULT false,
    iam_user_id             bigint NOT NULL,
    iam_account_id          bigint NOT NULL,
    iaas_ansible_server_id  bigint NOT NULL,
    created_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at              timestamp with time zone,
    CONSTRAINT iaas_ansible_system_playbooks_pkey PRIMARY KEY (id)
);
