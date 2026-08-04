-- PostgreSQL

CREATE TABLE iaas_compute_member_tasks (
    id                       bigint NOT NULL DEFAULT nextval('iaas_compute_member_tasks_id_seq'::regclass),
    uuid                     uuid DEFAULT gen_random_uuid(),
    hypervisor_uuid          uuid,
    name                     text,
    description              text,
    error                    text,
    progress                 integer,
    status                   text,
    hypervisor_data          json,
    iaas_virtual_machine_id  bigint,
    iaas_compute_member_id   bigint,
    created_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT iaas_compute_member_tasks_pkey PRIMARY KEY (id)
);
