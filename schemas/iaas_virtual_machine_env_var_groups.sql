-- PostgreSQL

CREATE TABLE iaas_virtual_machine_env_var_groups (
    id                       bigint NOT NULL DEFAULT nextval('iaas_virtual_machine_env_var_groups_id_seq'::regclass),
    uuid                     uuid DEFAULT gen_random_uuid(),
    iaas_virtual_machine_id  bigint NOT NULL,
    iaas_env_var_group_id    bigint NOT NULL,
    priority                 smallint NOT NULL DEFAULT 0, -- Groups with higher priority number override vars from lower priority groups.
    iam_account_id           bigint NOT NULL,
    iam_user_id              bigint NOT NULL,
    created_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at               timestamp with time zone,
    CONSTRAINT iaas_virtual_machine_env_var_groups_pkey PRIMARY KEY (id)
);
