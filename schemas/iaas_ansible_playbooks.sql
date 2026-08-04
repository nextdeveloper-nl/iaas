-- PostgreSQL

CREATE TABLE iaas_ansible_playbooks (
    id                      bigint NOT NULL DEFAULT nextval('iaas_ansible_playbooks_id_seq'::regclass),
    uuid                    uuid DEFAULT gen_random_uuid(),
    name                    text NOT NULL,
    description             text, -- [ui:markdown]
    is_public               boolean DEFAULT false,
    is_procedure            boolean DEFAULT false,
    iam_user_id             bigint NOT NULL,
    iam_account_id          bigint NOT NULL,
    iaas_ansible_server_id  bigint NOT NULL,
    created_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at              timestamp with time zone,
    CONSTRAINT iaas_ansible_playbooks_pkey PRIMARY KEY (id)
);
