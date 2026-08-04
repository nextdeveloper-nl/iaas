-- PostgreSQL

CREATE TABLE iaas_accounts (
    id                  bigint NOT NULL DEFAULT nextval('iaas_accounts_id_seq'::regclass),
    uuid                uuid DEFAULT gen_random_uuid(),
    limits              json DEFAULT '{}'::json, -- [ro]
    iam_account_id      bigint NOT NULL,
    created_at          timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at          timestamp with time zone,
    is_service_enabled  boolean DEFAULT false,
    is_suspended        boolean DEFAULT false,
    is_prepaid          boolean DEFAULT false, -- Indicates whether the account is prepaid. If true, the account must have sufficient prepaid balance before provisioning resources.
    CONSTRAINT iaas_accounts_pkey PRIMARY KEY (id)
);
