-- PostgreSQL

CREATE TABLE iaas_backup_jobs (
    id                               bigint NOT NULL DEFAULT nextval('iaas_backup_jobs_id_seq'::regclass),
    uuid                             uuid DEFAULT gen_random_uuid(),
    name                             text NOT NULL,
    type                             text NOT NULL DEFAULT 'full'::text,
    iaas_repository_id               bigint,
    iaas_backup_retention_policy_id  bigint,
    object_type                      text NOT NULL,
    object_id                        bigint NOT NULL,
    iam_account_id                   bigint NOT NULL,
    iam_user_id                      bigint NOT NULL,
    created_at                       timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                       timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                       timestamp with time zone,
    email_notification_recipients    text[],
    expected_rpo_hours               double precision,
    expected_rto_hours               double precision,
    is_enabled                       boolean,
    sla_target_pct                   double precision DEFAULT 90,
    notification_webhook             text,
    max_allowed_failures             integer,
    CONSTRAINT iaas_backup_jobs_pkey PRIMARY KEY (id)
);
