-- PostgreSQL

CREATE TABLE iaas_compute_member_storage_volumes (
    id                      bigint NOT NULL DEFAULT nextval('iaas_compute_member_storage_volumes_id_seq'::regclass),
    uuid                    uuid DEFAULT gen_random_uuid(),
    hypervisor_uuid         text,
    hypervisor_data         json,
    created_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at              timestamp with time zone,
    name                    text,
    description             text,
    block_device_data       json,
    iam_account_id          bigint,
    iam_user_id             bigint,
    iaas_storage_volume_id  bigint,
    iaas_storage_member_id  integer,
    iaas_storage_pool_id    bigint,
    iaas_compute_member_id  bigint,
    is_local_storage        boolean DEFAULT false,
    CONSTRAINT iaas_compute_member_storage_volumes_pkey PRIMARY KEY (id)
);
