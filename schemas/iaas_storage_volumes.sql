-- PostgreSQL

CREATE TABLE iaas_storage_volumes (
    id                      bigint NOT NULL DEFAULT nextval('iaas_storage_volumes_id_seq'::regclass),
    uuid                    uuid DEFAULT gen_random_uuid(),
    hypervisor_uuid         uuid,
    name                    text NOT NULL,
    disk_physical_type      text,
    connection_parameters   json,
    total_hdd               bigint NOT NULL DEFAULT 0,
    used_hdd                bigint NOT NULL DEFAULT 0,
    free_hdd                bigint DEFAULT (total_hdd - used_hdd),
    virtual_allocation      bigint DEFAULT 0,
    is_storage              boolean NOT NULL DEFAULT true,
    is_repo                 boolean NOT NULL DEFAULT false,
    is_cdrom                boolean NOT NULL DEFAULT false,
    hypervisor_data         json,
    iaas_storage_pool_id    bigint,
    iaas_storage_member_id  bigint,
    is_alive                boolean DEFAULT true,
    tags                    text[] NOT NULL DEFAULT '{}'::text[],
    iam_account_id          bigint NOT NULL,
    iam_user_id             bigint NOT NULL,
    created_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at              timestamp with time zone,
    CONSTRAINT iaas_storage_volumes_pkey PRIMARY KEY (id)
);
