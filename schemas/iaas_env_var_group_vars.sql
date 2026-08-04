-- PostgreSQL

CREATE TABLE iaas_env_var_group_vars (
    id                     bigint NOT NULL DEFAULT nextval('iaas_env_var_group_vars_id_seq'::regclass),
    uuid                   uuid DEFAULT gen_random_uuid(),
    iaas_env_var_group_id  bigint NOT NULL,
    key                    text NOT NULL,
    value                  text,
    is_secret              boolean NOT NULL DEFAULT false, -- When true, value is masked in API responses.
    description            text,
    iam_account_id         bigint NOT NULL,
    iam_user_id            bigint NOT NULL,
    created_at             timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at             timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at             timestamp with time zone,
    CONSTRAINT iaas_env_var_group_vars_pkey PRIMARY KEY (id)
);
