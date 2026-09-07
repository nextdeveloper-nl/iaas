-- PostgreSQL

CREATE SEQUENCE iaas_docker_containers_id_seq;

CREATE TABLE iaas_docker_containers (
    id                       bigint NOT NULL DEFAULT nextval('iaas_docker_containers_id_seq'::regclass),
    uuid                     uuid DEFAULT gen_random_uuid(),
    name                     text NOT NULL,
    iaas_virtual_machine_id  bigint NOT NULL,
    image                    text NOT NULL,
    status                   text NOT NULL DEFAULT 'creating'::text,
    container_id             text,
    command                  json,
    env_vars                 json,
    ports                    json,
    restart_policy           text,
    cpu_limit                numeric,
    memory_limit_mb          bigint,
    last_agent_sync_at       timestamp with time zone,
    agent_error              text,
    iam_account_id           bigint NOT NULL,
    iam_user_id              bigint NOT NULL,
    created_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at               timestamp with time zone,
    CONSTRAINT iaas_docker_containers_virtual_machine_fkey FOREIGN KEY (iaas_virtual_machine_id) REFERENCES iaas_virtual_machines(id),
    CONSTRAINT iaas_docker_containers_pkey PRIMARY KEY (id)
);

ALTER SEQUENCE iaas_docker_containers_id_seq OWNED BY iaas_docker_containers.id;

-- Not present in the iaas_gateways.sql template this was modeled on, added
-- deliberately: iaas_virtual_machine_id is queried on every "containers for
-- this VM" lookup (VirtualMachines::dockerContainers()) and Postgres does not
-- auto-index foreign key columns; container_id is looked up directly by
-- HandleVmAgentEventJob::resolveDockerContainer() on every async command
-- result; uuid is the primary external lookup key (findByRef/getByRef,
-- doAction) and should be enforced unique at the DB level, not just assumed
-- collision-free from gen_random_uuid().
CREATE INDEX iaas_docker_containers_virtual_machine_id_idx ON iaas_docker_containers (iaas_virtual_machine_id);
CREATE INDEX iaas_docker_containers_container_id_idx ON iaas_docker_containers (container_id);
CREATE UNIQUE INDEX iaas_docker_containers_uuid_idx ON iaas_docker_containers (uuid);
