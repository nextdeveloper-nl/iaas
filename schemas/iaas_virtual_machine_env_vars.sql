-- PostgreSQL

CREATE TABLE iaas_virtual_machine_env_vars (
    id                       bigint NOT NULL DEFAULT nextval('iaas_virtual_machine_env_vars_id_seq'::regclass),
    uuid                     uuid DEFAULT gen_random_uuid(),
    iaas_virtual_machine_id  bigint NOT NULL,
    key                      text NOT NULL,
    value                    text,
    source_type              text NOT NULL DEFAULT 'direct'::text, -- direct: value field is used as-is. common_ai: value is resolved from common_ai.credentials at ISO generation time using source_id.
    source_id                bigint, -- [alias:common_ai.id] Used when source_type is not direct.
    is_secret                boolean NOT NULL DEFAULT false, -- When true, value is masked in API responses.
    description              text,
    iam_account_id           bigint NOT NULL,
    iam_user_id              bigint NOT NULL,
    created_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at               timestamp with time zone,
    CONSTRAINT iaas_virtual_machine_env_vars_pkey PRIMARY KEY (id)
);
