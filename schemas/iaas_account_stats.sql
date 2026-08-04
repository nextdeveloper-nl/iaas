-- PostgreSQL

CREATE TABLE iaas_account_stats (
    id              bigint NOT NULL DEFAULT nextval('iaas_accounts_stats_id_seq'::regclass),
    uuid            uuid DEFAULT gen_random_uuid(),
    usage_report    json NOT NULL, -- [ro]
    iam_account_id  bigint NOT NULL,
    created_at      timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at      timestamp with time zone,
    CONSTRAINT iaas_account_stats_pkey PRIMARY KEY (id)
);
