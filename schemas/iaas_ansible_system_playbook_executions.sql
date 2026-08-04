-- PostgreSQL

CREATE TABLE iaas_ansible_system_playbook_executions (
    id                               bigint NOT NULL DEFAULT nextval('iaas_ansible_system_playbook_executions_id_seq'::regclass),
    uuid                             uuid DEFAULT gen_random_uuid(),
    iaas_ansible_system_plays_id     bigint NOT NULL,
    last_execution_time              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    package                          text NOT NULL,
    config                           json NOT NULL,
    execution_total_time             integer DEFAULT 0,
    last_output                      text,
    result_ok                        smallint DEFAULT 0,
    result_unreachable               smallint DEFAULT 0,
    result_failed                    smallint DEFAULT 0,
    result_skipped                   smallint DEFAULT 0,
    result_rescued                   smallint DEFAULT 0,
    result_ignored                   smallint DEFAULT 0,
    iam_account_id                   bigint NOT NULL,
    iam_user_id                      bigint NOT NULL,
    created_at                       timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                       timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                       timestamp with time zone,
    iaas_ansible_system_playbook_id  bigint,
    CONSTRAINT iaas_ansible_system_playbook_executions_pkey PRIMARY KEY (id)
);
