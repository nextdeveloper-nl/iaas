-- PostgreSQL

CREATE TABLE iaas_backup_job_replications (
    id                               bigint NOT NULL DEFAULT nextval('iaas_backup_job_replications_id_seq'::regclass),
    uuid                             uuid DEFAULT gen_random_uuid(),
    iaas_backup_job_id               bigint NOT NULL,
    iaas_repository_id               bigint NOT NULL,
    replication_type                 text NOT NULL DEFAULT 'async'::text, -- sync | async
    iaas_backup_retention_policy_id  bigint,
    priority                         integer NOT NULL DEFAULT 10,
    is_enabled                       boolean NOT NULL DEFAULT true,
    encrypt_in_transit               boolean NOT NULL DEFAULT true,
    bandwidth_limit_mbps             integer,
    last_replicated_at               timestamp with time zone, -- [ro]
    last_replication_status          text, -- [ro]
    last_replication_size_bytes      bigint, -- [ro]
    last_replication_duration_secs   integer, -- [ro]
    iam_account_id                   bigint NOT NULL,
    iam_user_id                      bigint NOT NULL,
    created_at                       timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                       timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                       timestamp with time zone,
    CONSTRAINT iaas_backup_job_replications_pkey PRIMARY KEY (id)
);
