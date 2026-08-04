-- PostgreSQL

CREATE TABLE iaas_gateways (
    id                       bigint NOT NULL DEFAULT nextval('iaas_gateways_id_seq'::regclass),
    uuid                     uuid DEFAULT gen_random_uuid(),
    name                     text NOT NULL,
    iaas_virtual_machine_id  bigint NOT NULL,
    gateway_data             json,
    is_public                boolean DEFAULT false,
    ssh_username             text,
    ssh_password             text,
    ip_addr                  cidr,
    api_token                text,
    api_url                  text,
    gateway_type             text DEFAULT 'pfsense'::text,
    iam_account_id           bigint NOT NULL,
    iam_user_id              bigint NOT NULL,
    created_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at               timestamp with time zone,
    hostname                 text,
    domain_type              text,
    common_domain_id         integer,
    CONSTRAINT iaas_gateways_common_domain_id_fkey FOREIGN KEY (common_domain_id) REFERENCES common_domains(id),
    CONSTRAINT iaas_gateways_pkey PRIMARY KEY (id)
);
