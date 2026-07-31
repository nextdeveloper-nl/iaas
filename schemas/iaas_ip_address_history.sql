-- PostgreSQL

CREATE TABLE iaas_ip_address_history (
    id                    bigint NOT NULL DEFAULT nextval('iaas_ip_address_history_id_seq'::regclass),
    uuid                  uuid DEFAULT gen_random_uuid(),
    body                  text,
    hash                  text,
    is_create             boolean DEFAULT true,
    is_update             boolean DEFAULT false,
    created_at            timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at            timestamp with time zone,
    iam_account_id        bigint,
    iam_user_id           bigint,
    iaas_ip_addresses_id  bigint,
    CONSTRAINT iaas_ip_address_history_pkey PRIMARY KEY (id)
);
