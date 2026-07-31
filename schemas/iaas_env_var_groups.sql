-- PostgreSQL

CREATE TABLE iaas_env_var_groups (
    id              bigint NOT NULL DEFAULT nextval('iaas_env_var_groups_id_seq'::regclass),
    uuid            uuid DEFAULT gen_random_uuid(),
    name            text NOT NULL,
    description     text,
    iam_account_id  bigint NOT NULL,
    iam_user_id     bigint NOT NULL,
    created_at      timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at      timestamp with time zone,
    CONSTRAINT iaas_env_var_groups_pkey PRIMARY KEY (id)
);
