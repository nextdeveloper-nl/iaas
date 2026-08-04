-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW iaas_virtual_network_cards_perspective AS
SELECT id,
    uuid,
    name,
    mac_addr,
    bandwidth_limit,
    iaas_network_id,
    ( SELECT c_in.name
           FROM iaas_networks c_in
          WHERE c_in.id = vnc.iaas_network_id) AS network,
    iaas_virtual_machine_id,
    ( SELECT c_ivm.name
           FROM iaas_virtual_machines c_ivm
          WHERE c_ivm.id = vnc.iaas_virtual_machine_id) AS virtual_machine,
    ( SELECT json_agg(json_build_object('ip_addr', c_ip.ip_addr)) AS json_agg
           FROM iaas_ip_addresses c_ip
          WHERE c_ip.iaas_virtual_network_card_id = vnc.id) AS ip_addr,
    device_number,
    iam_account_id,
    iam_user_id,
    is_draft,
    status,
    created_at,
    updated_at,
    deleted_at
   FROM iaas_virtual_network_cards vnc;
