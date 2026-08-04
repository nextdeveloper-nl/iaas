-- PostgreSQL

CREATE TABLE iaas_virtual_disk_images (
    id                        bigint NOT NULL DEFAULT nextval('iaas_virtual_disk_images_id_seq'::regclass),
    uuid                      uuid DEFAULT gen_random_uuid(),
    name                      text NOT NULL,
    size                      bigint DEFAULT 0,
    available_operations      json, -- [ro]
    current_operations        json, -- [ro]
    is_cdrom                  boolean DEFAULT false, -- [ro]
    hypervisor_uuid           text,
    hypervisor_data           json,
    iaas_storage_volume_id    bigint,
    iaas_virtual_machine_id   bigint,
    device_number             smallint DEFAULT 0,
    iam_account_id            bigint NOT NULL,
    iam_user_id               bigint NOT NULL,
    created_at                timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                timestamp with time zone,
    physical_utilisation      bigint DEFAULT 0, -- [ro]
    is_draft                  boolean NOT NULL DEFAULT true, -- [ro]
    iaas_repository_image_id  bigint, -- [ro]
    iaas_storage_pool_id      bigint,
    vbd_hypervisor_data       json,
    vbd_hypervisor_uuid       text,
    utilization               double precision DEFAULT 
CASE
    WHEN ((physical_utilisation IS NOT NULL) AND (size IS NOT NULL) AND (size <> 0)) THEN ((physical_utilisation / size) * 100)
    ELSE NULL::bigint
END,
    CONSTRAINT iaas_virtual_disk_images_pkey PRIMARY KEY (id)
);

CREATE INDEX idx_iaas_vdi_acct_vm_cdrom_draft ON public.iaas_virtual_disk_images USING btree (iam_account_id, iaas_virtual_machine_id, is_cdrom, is_draft);
