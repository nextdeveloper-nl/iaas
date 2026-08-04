-- PostgreSQL

CREATE TABLE iaas_virtual_machines (
    id                           bigint NOT NULL DEFAULT nextval('iaas_virtual_machines_id_seq'::regclass),
    uuid                         uuid DEFAULT gen_random_uuid(),
    name                         text NOT NULL,
    username                     text, -- [ro]
    password                     text,
    hostname                     text, -- [ro]
    description                  text,
    os                           text, -- [ro]
    distro                       text, -- [ro]
    version                      text, -- [ro]
    domain_type                  text, -- [ro]
    status                       text DEFAULT 'draft'::text, -- [ro]
    cpu                          smallint NOT NULL, -- [ro]
    ram                          integer NOT NULL, -- [ui:slider]
    is_winrm_enabled             boolean DEFAULT false, -- [ro]
    available_operations         json, -- [ro]
    current_operations           json, -- [ro]
    blocked_operations           json, -- [ro]
    console_data                 json, -- [ro]
    is_snapshot                  boolean DEFAULT false, -- [ro]
    is_lost                      boolean DEFAULT false, -- [ro]
    is_locked                    boolean DEFAULT false, -- [ro]
    last_metadata_request        timestamp with time zone, -- [ro]
    features                     json, -- [ro]
    hypervisor_uuid              uuid, -- [ro]
    hypervisor_data              json, -- [ro]
    iaas_cloud_node_id           bigint NOT NULL, -- [ro]
    iaas_compute_member_id       bigint, -- [ro]
    iam_account_id               bigint NOT NULL,
    iam_user_id                  bigint NOT NULL,
    tags                         text[] NOT NULL DEFAULT '{}'::text[],
    created_at                   timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                   timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                   timestamp with time zone,
    iaas_repository_image_id     bigint,
    template_id                  bigint, -- [ro][alias:iaas_virtual_machine_id]
    is_draft                     boolean DEFAULT true, -- [ro]
    common_domain_id             bigint,
    lock_password                text, -- [ro]
    is_template                  boolean DEFAULT false, -- [ro]
    iaas_compute_pool_id         bigint DEFAULT 0,
    auto_backup_interval         text DEFAULT 'none'::text,
    auto_backup_time             text DEFAULT '12+4'::text,
    snapshot_of_virtual_machine  bigint, -- [alias:iaas_virtual_machine_id]
    backup_repository_id         bigint, -- [alias:iaas_repository_id]
    post_boot_script             text,
    tokens                       json DEFAULT '[]'::json,
    agent_api_key                text,
    agent_latest_ping            timestamp with time zone,
    is_pending_update            boolean NOT NULL DEFAULT false,
    CONSTRAINT iaas_virtual_machines_pkey PRIMARY KEY (id)
);

CREATE INDEX idx_iaas_vms_acct_created ON public.iaas_virtual_machines USING btree (iam_account_id, created_at);
