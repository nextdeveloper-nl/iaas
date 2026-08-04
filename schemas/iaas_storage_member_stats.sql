-- PostgreSQL

CREATE TABLE iaas_storage_member_stats (
    id                      bigint NOT NULL DEFAULT nextval('iaas_storage_member_stats_id_seq'::regclass),
    uuid                    uuid DEFAULT gen_random_uuid(),
    iaas_storage_member_id  bigint NOT NULL,
    used_disk               integer NOT NULL DEFAULT 0,
    created_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at              timestamp with time zone,
    CONSTRAINT iaas_storage_member_stats_pkey PRIMARY KEY (id)
);
