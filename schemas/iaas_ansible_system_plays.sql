-- PostgreSQL

CREATE TABLE iaas_ansible_system_plays (
    id                               bigint NOT NULL DEFAULT nextval('iaas_ansible_system_plays_id_seq'::regclass),
    uuid                             uuid DEFAULT gen_random_uuid(),
    name                             text NOT NULL,
    iaas_ansible_system_playbook_id  bigint NOT NULL,
    hosts                            text,
    roles                            json NOT NULL,
    config                           json NOT NULL,
    become                           boolean DEFAULT false,
    gather_facts                     boolean DEFAULT false,
    iam_account_id                   bigint NOT NULL,
    iam_user_id                      bigint NOT NULL,
    created_at                       timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                       timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                       timestamp with time zone,
    CONSTRAINT iaas_ansible_system_plays_pkey PRIMARY KEY (id)
);
