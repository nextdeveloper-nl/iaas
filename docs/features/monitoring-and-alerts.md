# Monitoring & Alerts

The platform continuously checks the health of your resources and tracks performance over time, so problems surface as alerts instead of being discovered when something goes down.

## Key Capabilities

- Health checks run against VMs, networks, storage, and other resources on a schedule
- Each health check reports a status, severity, and response time
- CPU usage anomalies on a VM are detected and raised as alerts automatically
- A real-time view of all currently active alarms across your resources
- Monitoring software (such as Prometheus-style instances) can be deployed and tracked per VM
- Performance metrics (CPU, network, storage) are recorded for historical reporting

## Health Checks

A health check is a periodic probe against a resource — checking whether it's reachable, responding correctly, and within expected limits. Each check records its status (for example, passing or failing), a severity level, how long the check took to respond, and any error details if it failed. Health checks are what drive automatic detection of an unresponsive VM or an unreachable service.

## Alerts and Active Alarms

When a health check fails, or a metric crosses a threshold — like sustained abnormal CPU usage on a VM — an alert is raised. CPU alerts specifically compare current usage against a rolling average to catch unusual spikes rather than relying on a single fixed threshold. All currently active alarms across your account surface in a single dashboard view, so you don't need to check each resource individually.

## Monitoring Instances

For deeper visibility, monitoring software can be deployed against a VM and registered with the platform, letting you track its configuration and status (active/inactive) alongside everything else.

## API Examples

**List active health checks**

```
GET /iaas/health-checks
```

**Create a health check**

```
POST /iaas/health-checks
```
```json
{
  "object_type": "VirtualMachine",
  "object_id": 123,
  "check_type": "tcp",
  "check_data": {
    "port": 5432
  }
}
```

**List currently active alarms**

```
GET /iaas/active-alarms-perspective
```

## Related Features

- [Virtual Machines](virtual-machines.md) — health checks and CPU alerts run against VMs
- [Networking](networking.md) — network-level statistics and health
- [Storage](storage.md) — storage volume health and performance stats
