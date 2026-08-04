-- PostgreSQL

CREATE TABLE iaas_virtual_disk_image_stats (
    id                          bigint NOT NULL DEFAULT nextval('iaas_virtual_disk_image_stats_id_seq'::regclass),
    uuid                        uuid DEFAULT gen_random_uuid(),
    iaas_virtual_disk_image_id  bigint NOT NULL,
    size                        bigint NOT NULL,
    physical_utilisation        bigint NOT NULL,
    created_at                  timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                  timestamp with time zone,
    CONSTRAINT iaas_virtual_disk_image_stats_pkey PRIMARY KEY (id)
);
