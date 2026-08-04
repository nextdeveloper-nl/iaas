-- PostgreSQL

CREATE TABLE iaas_compute_member_events (
    id                      bigint NOT NULL DEFAULT nextval('iaas_compute_member_events_id_seq'::regclass),
    uuid                    uuid DEFAULT gen_random_uuid(),
    source                  text,
    type                    text,
    event                   text,
    iaas_compute_member_id  bigint,
    is_executed             boolean DEFAULT false,
    created_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at              timestamp with time zone,
    iam_account_id          bigint,
    iam_user_id             bigint,
    is_flagged              boolean DEFAULT false,
    results                 json,
    CONSTRAINT iaas_compute_member_events_pkey PRIMARY KEY (id)
);
