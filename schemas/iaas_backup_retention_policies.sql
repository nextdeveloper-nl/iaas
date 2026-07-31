-- PostgreSQL

CREATE TABLE iaas_backup_retention_policies (
    id                   bigint NOT NULL DEFAULT nextval('iaas_backup_retention_policies_id_seq'::regclass),
    uuid                 uuid DEFAULT gen_random_uuid(),
    name                 text NOT NULL,
    description          text,
    keep_for_days        integer DEFAULT 15,
    keep_last_n_backups  integer DEFAULT 7,
    is_public            boolean DEFAULT false,
    iam_account_id       bigint NOT NULL,
    iam_user_id          bigint NOT NULL,
    created_at           timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at           timestamp with time zone,
    CONSTRAINT iaas_backup_retention_policies_pkey PRIMARY KEY (id)
);
