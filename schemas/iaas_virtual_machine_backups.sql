-- PostgreSQL

CREATE TABLE iaas_virtual_machine_backups (
    id                        bigint NOT NULL DEFAULT nextval('iaas_virtual_machine_backups_id_seq'::regclass),
    uuid                      uuid DEFAULT gen_random_uuid(),
    name                      text NOT NULL,
    description               text,
    path                      text, -- [ro]
    filename                  text, -- [ro]
    username                  text, -- [ro]
    password                  text, -- [ro]
    size                      integer, -- [ro]
    ram                       integer NOT NULL DEFAULT 1, -- [ro]
    cpu                       integer NOT NULL DEFAULT 2, -- [ro]
    hash                      text, -- [ro]
    backup_type               text NOT NULL DEFAULT 'full-backup'::text,
    iaas_virtual_machine_id   bigint NOT NULL,
    iam_account_id            bigint NOT NULL,
    iam_user_id               bigint NOT NULL,
    created_at                timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at                timestamp with time zone,
    status                    text NOT NULL DEFAULT 'pending'::text,
    backup_starts             timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    backup_ends               timestamp with time zone,
    iaas_repository_image_id  bigint,
    iaas_backup_job_id        bigint,
    data                      json,
    progress                  integer,
    CONSTRAINT iaas_virtual_machine_backups_pkey PRIMARY KEY (id)
);

CREATE INDEX idx_iaas_vm_backups_acct_created ON public.iaas_virtual_machine_backups USING btree (iam_account_id, created_at);
