-- PostgreSQL

CREATE TABLE iaas_licences (
    id               bigint NOT NULL DEFAULT nextval('iaas_licences_id_seq'::regclass),
    uuid             uuid DEFAULT gen_random_uuid(),
    object_type      text NOT NULL,
    object_id        bigint NOT NULL,
    subscription_id  bigint NOT NULL, -- [alias:marketplace_subscription_id]
    iam_account_id   bigint,
    iam_user_id      bigint,
    created_at       timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at       timestamp with time zone,
    CONSTRAINT iaas_licences_pkey PRIMARY KEY (id)
);
