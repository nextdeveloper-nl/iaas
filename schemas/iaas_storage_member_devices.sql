-- PostgreSQL

CREATE TABLE iaas_storage_member_devices (
    id                      bigint NOT NULL DEFAULT nextval('iaas_storage_member_devices_id_seq'::regclass),
    uuid                    uuid DEFAULT gen_random_uuid(),
    name                    text NOT NULL,
    device_identification   text NOT NULL,
    is_healthy              boolean DEFAULT true,
    health_information      json,
    device_type             text,
    iaas_storage_member_id  bigint NOT NULL,
    iam_account_id          bigint,
    iam_user_id             bigint,
    created_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at              timestamp with time zone,
    CONSTRAINT iaas_storage_member_devices_pkey PRIMARY KEY (id)
);
