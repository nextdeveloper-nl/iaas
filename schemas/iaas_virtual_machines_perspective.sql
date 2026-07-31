-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_virtual_machines_perspective AS
WITH vm_disks AS (
         SELECT iaas_virtual_disk_images.iaas_virtual_machine_id,
            count(*) AS disk_count,
            sum(iaas_virtual_disk_images.size) / 1024::numeric / 1024::numeric / 1024::numeric AS total_disk_size
           FROM iaas_virtual_disk_images
          GROUP BY iaas_virtual_disk_images.iaas_virtual_machine_id
        ), vm_network_cards AS (
         SELECT iaas_virtual_network_cards.iaas_virtual_machine_id,
            count(*) AS network_card_count
           FROM iaas_virtual_network_cards
          GROUP BY iaas_virtual_network_cards.iaas_virtual_machine_id
        ), vm_states AS (
         SELECT common_states.object_id,
            count(*) FILTER (WHERE common_states.object_states = 'warn'::object_states) AS has_warnings,
            count(*) FILTER (WHERE common_states.object_states = 'error'::object_states) AS has_errors,
            json_agg(json_build_object('name', common_states.name, 'value', common_states.value, 'reason', common_states.reason)) AS states
           FROM common_states
          WHERE common_states.object_type = 'NextDeveloper\\IAAS\\Database\\Models\\VirtualMachines'::text
          GROUP BY common_states.object_id
        ), vm_primary_network AS (
         SELECT DISTINCT ON (vnc_1.iaas_virtual_machine_id) vnc_1.iaas_virtual_machine_id,
            net.name AS network,
            ip.ip_addr
           FROM iaas_virtual_network_cards vnc_1
             LEFT JOIN iaas_networks net ON net.id = vnc_1.iaas_network_id
             LEFT JOIN iaas_ip_addresses ip ON ip.iaas_virtual_network_card_id = vnc_1.id
          ORDER BY vnc_1.iaas_virtual_machine_id, vnc_1.device_number
        )
 SELECT vm.id,
    vm.uuid,
    vm.name,
    vm.description,
    vm.hostname,
    vm.username,
    vm.os,
    vm.distro,
    vm.version,
    vm.domain_type,
    vm.status,
    vm.cpu,
    vm.ram,
    vm.last_metadata_request,
    vm.iaas_cloud_node_id,
    cn.name AS cloud_node,
    cn.uuid AS cloud_node_uuid,
    vm.common_domain_id,
    cd.name AS domain,
    cd.uuid AS domain_uuid,
    COALESCE(vd.disk_count, 0::bigint) AS disk_count,
    COALESCE(vnc.network_card_count, 0::bigint) AS network_card_count,
    COALESCE(vs.has_warnings, 0::bigint) AS has_warnings,
    COALESCE(vs.has_errors, 0::bigint) AS has_errors,
    COALESCE(vd.disk_count, 0::bigint) AS number_of_disks,
    COALESCE(vd.total_disk_size::integer, 0) AS total_disk_size,
    vpn.network,
    vpn.ip_addr,
    vs.states,
    cp.pool_type,
        CASE
            WHEN cp.pool_type = 'star'::text THEN true
            ELSE false
        END AS is_snapshot_available,
    vm.iaas_compute_member_id,
    icm.name AS compute_member_name,
    icm.uuid AS compute_member_uuid,
    vm.tags,
    vm.is_template,
    vm.is_draft,
    vm.is_lost,
    vm.is_locked,
    vm.is_snapshot,
    vm.auto_backup_interval,
    vm.auto_backup_time,
    vm.post_boot_script,
    ia.name AS maintainer,
    ia.uuid AS account_uuid,
    iu.fullname AS responsible,
    iu.uuid AS user_uuid,
    iaa.id AS iaas_account_id,
    vm.iaas_compute_pool_id,
    cp.uuid AS compute_pool_uuid,
    vm.snapshot_of_virtual_machine,
    vm.agent_latest_ping,
    vm.is_pending_update,
    vm.iam_account_id,
    vm.iam_user_id,
    vm.created_at,
    vm.updated_at,
    vm.deleted_at
   FROM iaas_virtual_machines vm
     LEFT JOIN iaas_cloud_nodes cn ON cn.id = vm.iaas_cloud_node_id
     LEFT JOIN common_domains cd ON cd.id = vm.common_domain_id
     LEFT JOIN vm_disks vd ON vd.iaas_virtual_machine_id = vm.id
     LEFT JOIN vm_network_cards vnc ON vnc.iaas_virtual_machine_id = vm.id
     LEFT JOIN vm_states vs ON vs.object_id = vm.id
     LEFT JOIN vm_primary_network vpn ON vpn.iaas_virtual_machine_id = vm.id
     LEFT JOIN iaas_compute_pools cp ON cp.id = vm.iaas_compute_pool_id
     LEFT JOIN iam_accounts ia ON ia.id = vm.iam_account_id
     LEFT JOIN iam_users iu ON iu.id = vm.iam_user_id
     LEFT JOIN iaas_compute_members icm ON icm.id = vm.iaas_compute_member_id
     LEFT JOIN iaas_repositories ir ON vm.backup_repository_id = ir.id
     LEFT JOIN iaas_accounts iaa ON vm.iam_account_id = iaa.iam_account_id
  WHERE vm.name !~~ 'Control domain on host%'::text
  ORDER BY vm.id DESC;
