-- PostgreSQL

CREATE TABLE iaas_ssh_public_key_virtual_machines (
    id                       bigint NOT NULL DEFAULT nextval('iaas_ssh_public_key_virtual_machines_id_seq'::regclass),
    uuid                     uuid NOT NULL DEFAULT gen_random_uuid(),
    iam_ssh_public_key_id    bigint NOT NULL,
    iaas_virtual_machine_id  bigint NOT NULL,
    deployed_at              timestamp with time zone,
    created_at               timestamp with time zone NOT NULL DEFAULT now(),
    updated_at               timestamp with time zone NOT NULL DEFAULT now(),
    deleted_at               timestamp with time zone,
    CONSTRAINT iaas_ssh_public_key_virtual_machines_pkey PRIMARY KEY (id)
);
