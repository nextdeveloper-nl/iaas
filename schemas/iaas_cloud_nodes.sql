-- PostgreSQL

CREATE TABLE iaas_cloud_nodes (
    id                    bigint NOT NULL DEFAULT nextval('iaas_cloud_nodes_id_seq'::regclass),
    uuid                  uuid DEFAULT gen_random_uuid(),
    name                  text NOT NULL,
    slug                  text NOT NULL,
    is_active             boolean NOT NULL DEFAULT false,
    is_public             boolean NOT NULL DEFAULT false,
    is_edge               boolean NOT NULL DEFAULT false,
    is_alive              boolean NOT NULL DEFAULT false,
    is_in_maintenance     boolean NOT NULL DEFAULT false,
    position              integer,
    iaas_datacenter_id    bigint NOT NULL,
    iam_account_id        bigint NOT NULL,
    iam_user_id           bigint NOT NULL,
    tags                  text[] NOT NULL DEFAULT '{}'::text[],
    created_at            timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at            timestamp with time zone,
    default_backup_path   text,
    backup_repository_id  bigint, -- [alias:iaas_repository_id]
    CONSTRAINT iaas_cloud_nodes_pkey PRIMARY KEY (id)
);
