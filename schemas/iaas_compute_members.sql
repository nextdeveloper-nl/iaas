-- PostgreSQL

CREATE TABLE iaas_compute_members (
    id                             bigint NOT NULL DEFAULT nextval('iaas_compute_members_id_seq'::regclass),
    uuid                           uuid DEFAULT gen_random_uuid(),
    name                           text NOT NULL,
    hostname                       text,
    ip_addr                        inet,
    local_ip_addr                  inet,
    management_data                json, -- [ro]
    features                       json, -- [ro]
    is_behind_firewall             boolean DEFAULT false,
    hypervisor_uuid                uuid, -- [ro]
    hypervisor_data                json, -- [ro]
    total_socket                   integer NOT NULL DEFAULT 2, -- [ro]
    total_cpu                      integer NOT NULL DEFAULT 16, -- [ro]
    total_ram                      integer NOT NULL DEFAULT 128, -- [ro]
    used_cpu                       integer NOT NULL DEFAULT 0, -- [ro]
    used_ram                       integer NOT NULL DEFAULT 0, -- [ro]
    total_vm                       integer NOT NULL DEFAULT 0, -- [ro]
    max_overbooking_ratio          smallint DEFAULT 15,
    cpu_info                       json, -- [ro]
    uptime                         timestamp with time zone, -- [ro]
    idle_time                      timestamp with time zone, -- [ro]
    benchmark_score                integer DEFAULT 0, -- [ro]
    is_in_maintenance              boolean DEFAULT false,
    is_alive                       boolean NOT NULL DEFAULT false,
    iaas_compute_pool_id           bigint,
    iam_account_id                 bigint NOT NULL,
    iam_user_id                    bigint NOT NULL,
    tags                           text[] NOT NULL DEFAULT '{}'::text[],
    created_at                     timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                     timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                     timestamp with time zone,
    is_management_agent_available  boolean DEFAULT false, -- [ro]
    ssh_username                   text,
    ssh_password                   text,
    ssh_port                       integer DEFAULT 22,
    hypervisor_model               text, -- [ro]
    has_warning                    boolean DEFAULT false, -- [ro]
    has_error                      boolean DEFAULT false, -- [ro]
    running_vm                     integer NOT NULL DEFAULT 0, -- [ro]
    halted_vm                      integer NOT NULL DEFAULT 0, -- [ro]
    free_ram                       integer DEFAULT (total_ram - used_ram), -- [ro]
    events_token                   text,
    is_event_service_running       boolean DEFAULT false,
    agent_api_key                  text,
    agent_latest_ping              timestamp with time zone,
    available_operations           json,
    CONSTRAINT iaas_compute_members_pkey PRIMARY KEY (id)
);
