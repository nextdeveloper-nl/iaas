-- PostgreSQL

CREATE TABLE iaas_virtual_network_cards (
    id                       bigint NOT NULL DEFAULT nextval('iaas_virtual_network_cards_id_seq'::regclass),
    uuid                     uuid DEFAULT gen_random_uuid(),
    name                     text NOT NULL,
    mac_addr                 macaddr,
    bandwidth_limit          bigint DEFAULT 0,
    hypervisor_uuid          text,
    hypervisor_data          json,
    iaas_network_id          bigint NOT NULL,
    iaas_virtual_machine_id  bigint,
    device_number            smallint DEFAULT 0,
    iam_account_id           bigint NOT NULL,
    iam_user_id              bigint NOT NULL,
    created_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at               timestamp with time zone,
    is_draft                 boolean NOT NULL DEFAULT true,
    status                   text DEFAULT 'draft'::text,
    CONSTRAINT iaas_virtual_network_cards_pkey PRIMARY KEY (id)
);
