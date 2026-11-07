-- PostgreSQL
-- TRIGGERS (introspected live from leo_v4 via pg_trigger/pg_proc.)

-- Seeds iaas_accounts when a new iam_accounts row is created, mirroring the
-- equivalent pattern used by the Accounting/CRM/Communication modules.
create or replace function create_iaas_account()
    returns trigger
    language plpgsql
as
$function$
BEGIN
    insert into iaas_accounts (iam_account_id)
    values (new.id)
    ON CONFLICT DO NOTHING;

    return new;
end;
$function$;

create trigger trigger_create_iaas_account
    after insert
    on iam_accounts
    for each row
execute function create_iaas_account();

-- Snapshots pre-update resource usage of a non-draft VM into
-- iaas_virtual_machine_stats before the row is overwritten.
create or replace function update_iaas_vm_resources()
    returns trigger
    language plpgsql
as
$function$
BEGIN
    IF old.is_draft != true THEN
        insert into iaas_virtual_machine_stats (
              iaas_virtual_machine_id, cpu, ram, status
        )
        values (old.id, old.cpu, old.ram, old.status)
        ON CONFLICT DO NOTHING;
    END IF;

    return new;
end;
$function$;

create trigger trigger_update_iaas_virtual_machines
    before update
    on iaas_virtual_machines
    for each row
execute function update_iaas_vm_resources();

-- Snapshots pre-update size/utilisation of a non-draft VDI into
-- iaas_virtual_disk_image_stats before the row is overwritten.
create or replace function update_iaas_vdi_resources()
    returns trigger
    language plpgsql
as
$function$
BEGIN
    IF old.is_draft != true THEN
        insert into iaas_virtual_disk_image_stats (
              iaas_virtual_disk_image_id, size, physical_utilisation
        )
        values (old.id, old.size, old.physical_utilisation)
        ON CONFLICT DO NOTHING;
    END IF;

    return new;
end;
$function$;

create trigger trigger_update_iaas_virtual_disk_images
    before update
    on iaas_virtual_disk_images
    for each row
execute function update_iaas_vdi_resources();
