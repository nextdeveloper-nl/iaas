-- PostgreSQL

CREATE TABLE iaas_compute_pools (
    id                   bigint NOT NULL DEFAULT nextval('iaas_compute_pools_id_seq'::regclass),
    uuid                 uuid DEFAULT gen_random_uuid(),
    name                 text NOT NULL,
    resource_validator   text, -- Sunucuda kurulmak istenen CPU, RAM ve Harddisk değerlerinin doğruluğunu kontrol eden class.
    pool_data            json,
    virtualization       text NOT NULL DEFAULT 'xenserver-8.2'::text,
    provisioning_alg     text,
    is_active            boolean NOT NULL DEFAULT false,
    is_alive             boolean NOT NULL DEFAULT false,
    is_public            boolean NOT NULL DEFAULT false,
    iaas_datacenter_id   bigint,
    iaas_cloud_node_id   bigint,
    iam_account_id       bigint NOT NULL,
    iam_user_id          bigint NOT NULL,
    tags                 text[] NOT NULL DEFAULT '{}'::text[],
    price_pergb          numeric(10,10) DEFAULT 0,
    common_currency_id   bigint,
    created_at           timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at           timestamp with time zone,
    pool_type            text DEFAULT 'one'::text,
    total_cpu            bigint, -- [label:"Total amount of CPU in pool"][ro]
    total_ram            bigint, -- [label:"Total amount of ram in pool"][ro]
    price_pergb_month    numeric(4,1),
    disk_ram_ratio       numeric(4,2) DEFAULT 1.0,
    code_name            text DEFAULT 'LH'::text,
    is_default           boolean DEFAULT false,
    is_iso27001_enabled  boolean DEFAULT true,
    CONSTRAINT iaas_compute_pools_pkey PRIMARY KEY (id)
);
