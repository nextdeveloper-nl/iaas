-- PostgreSQL

CREATE TABLE iaas_storage_pools (
    id                  bigint NOT NULL DEFAULT nextval('iaas_storage_pools_id_seq'::regclass),
    uuid                uuid DEFAULT gen_random_uuid(),
    name                text NOT NULL,
    price_pergb         numeric(13,8) NOT NULL DEFAULT 0, -- [ui:money]
    is_active           boolean NOT NULL DEFAULT false,
    iaas_cloud_node_id  bigint NOT NULL,
    iam_account_id      bigint NOT NULL,
    iam_user_id         bigint NOT NULL,
    tags                text[] NOT NULL DEFAULT '{}'::text[],
    common_currency_id  bigint,
    created_at          timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at          timestamp with time zone,
    storage_pool_type   text, -- [ro]
    iaas_datacenter_id  bigint,
    price_pergb_month   numeric(4,4),
    CONSTRAINT iaas_storage_pools_pkey PRIMARY KEY (id)
);
