-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_virtual_machines_management_perspective AS
WITH vm_primary_network AS (
         SELECT DISTINCT ON (vnc.iaas_virtual_machine_id) vnc.iaas_virtual_machine_id,
            net.name AS network,
            ip.ip_addr
           FROM iaas_virtual_network_cards vnc
             LEFT JOIN iaas_networks net ON net.id = vnc.iaas_network_id
             LEFT JOIN iaas_ip_addresses ip ON ip.iaas_virtual_network_card_id = vnc.id
          ORDER BY vnc.iaas_virtual_machine_id, vnc.device_number
        )
 SELECT ivm.id,
    ivm.uuid,
    ivm.name,
    ivm.hypervisor_uuid,
    ivm.hypervisor_data ->> 'name-label'::text AS hypervisor_name_label,
    vpn.ip_addr,
    ivdi.name AS disk_name,
    ivdi.hypervisor_uuid AS disk_hypervisor_uuid,
    cmsv.name AS storage_volume_name,
    cmsv.hypervisor_uuid AS storage_volume_hypervisor_uuid,
    icm.name AS compute_member_name,
    ivm.iam_account_id,
    ivm.iam_user_id,
    ivm.iaas_compute_member_id,
    ivm.iaas_compute_pool_id,
    ivm.iaas_cloud_node_id
   FROM iaas_virtual_machines ivm
     LEFT JOIN iaas_virtual_network_cards ivnc ON ivm.id = ivnc.iaas_virtual_machine_id
     LEFT JOIN iaas_ip_addresses iia ON ivnc.id = iia.iaas_virtual_network_card_id
     LEFT JOIN vm_primary_network vpn ON vpn.iaas_virtual_machine_id = ivm.id
     LEFT JOIN iaas_compute_members icm ON icm.id = ivm.iaas_compute_member_id
     LEFT JOIN iaas_virtual_disk_images ivdi ON ivdi.iaas_virtual_machine_id = ivm.id
     LEFT JOIN iaas_compute_member_storage_volumes cmsv ON cmsv.iaas_storage_volume_id = ivdi.iaas_storage_volume_id AND ivm.iaas_compute_member_id = cmsv.iaas_compute_member_id
  WHERE ivm.deleted_at IS NULL;
