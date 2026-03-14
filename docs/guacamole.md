# Guacamole on Ubuntu 24.04 — Setup Notes & Bugfixes

**Date:** March 14, 2026  
**Domain:** kvm.plusclouds.com  
**Stack:** Docker Compose · Nginx · Let's Encrypt · PostgreSQL 15

---

## Architecture Overview

```
Customer Browser (HTTPS)
        │
        ▼
Nginx reverse proxy (port 443, TLS)
        │
        ▼
Guacamole / Tomcat (127.0.0.1:8080)
        │
   ┌────┴────┐
   ▼         ▼
guacd     PostgreSQL
(4822)     (5432)
   │
   ▼
Ubuntu Desktop VMs (VNC)
```

---

## Final Working docker-compose.yml

```yaml
services:
  guacd:
    image: guacamole/guacd
    restart: unless-stopped
    networks:
      - guac-net

  postgres:
    image: postgres:15
    restart: unless-stopped
    environment:
      POSTGRES_DB: guacamole_db
      POSTGRES_USER: guacamole_user
      POSTGRES_PASSWORD: "your-password-here"
    volumes:
      - ./initdb:/docker-entrypoint-initdb.d
      - pg_data:/var/lib/postgresql/data
    networks:
      - guac-net

  guacamole:
    image: guacamole/guacamole
    restart: unless-stopped
    environment:
      GUACD_HOSTNAME: guacd
      GUACD_PORT: 4822
      EXTENSIONS: "jdbc-postgresql"
    volumes:
      - ./guacamole-config:/etc/guacamole
    ports:
      - "127.0.0.1:8080:8080"
    networks:
      - guac-net
    depends_on:
      - guacd
      - postgres

networks:
  guac-net:

volumes:
  pg_data:
```

> **Note:** The `version:` field at the top of compose files is obsolete in modern Docker and should be removed to avoid warnings.

---

## Final Working guacamole.properties

Located at `./guacamole-config/guacamole.properties`:

```properties
guacd-hostname: guacd
guacd-port: 4822

postgresql-hostname: postgres
postgresql-port: 5432
postgresql-database: guacamole_db
postgresql-username: guacamole_user
postgresql-password: your-password-here
```

> **Why this file?** In newer Guacamole image versions, the `POSTGRES_*` environment variables are not reliably passed to the JDBC driver. Using `guacamole.properties` mounted directly bypasses this issue entirely.

---

## Final Working Nginx Config

Located at `/etc/nginx/sites-available/guacamole`:

```nginx
server {
    listen 80;
    server_name kvm.plusclouds.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name kvm.plusclouds.com;

    ssl_certificate     /etc/letsencrypt/live/kvm.plusclouds.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/kvm.plusclouds.com/privkey.pem;
    include             /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam         /etc/letsencrypt/ssl-dhparams.pem;

    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options SAMEORIGIN always;
    add_header X-Content-Type-Options nosniff always;

    proxy_buffer_size       128k;
    proxy_buffers           4 256k;
    proxy_busy_buffers_size 256k;

    location / {
        proxy_pass http://127.0.0.1:8080/guacamole/;
        proxy_http_version 1.1;

        proxy_set_header Host              $host;
        proxy_set_header X-Real-IP         $remote_addr;
        proxy_set_header X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        proxy_set_header Upgrade    $http_upgrade;
        proxy_set_header Connection "upgrade";

        proxy_buffering    off;
        proxy_read_timeout 3600s;
        proxy_send_timeout 3600s;
        proxy_connect_timeout 10s;

        proxy_redirect http://127.0.0.1:8080/guacamole/ https://kvm.plusclouds.com/;
        proxy_redirect http://kvm.plusclouds.com/guacamole/ https://kvm.plusclouds.com/;
    }

    # Dedicated block for WebSocket tunnel
    location /websocket-tunnel {
        proxy_pass http://127.0.0.1:8080/guacamole/websocket-tunnel;
        proxy_http_version 1.1;

        proxy_set_header Host              $host;
        proxy_set_header X-Real-IP         $remote_addr;
        proxy_set_header X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        proxy_set_header Upgrade    $http_upgrade;
        proxy_set_header Connection "upgrade";

        proxy_buffering    off;
        proxy_read_timeout 3600s;
        proxy_send_timeout 3600s;
    }
}
```

---

## Installation Steps

### 1. Install Docker

```bash
# Remove conflicting packages
for pkg in docker.io docker-doc docker-compose docker-compose-v2 podman-docker containerd runc; do
  sudo apt remove $pkg
done

# Add Docker's official repo
sudo apt update
sudo apt install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] \
  https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Allow running without sudo
sudo usermod -aG docker $USER
newgrp docker
```

### 2. Generate Guacamole Database Schema

```bash
mkdir -p ./initdb
docker run --rm guacamole/guacamole \
  /opt/guacamole/bin/initdb.sh --postgresql > ./initdb/initdb.sql

# Verify it has content
wc -l ./initdb/initdb.sql
```

### 3. Create guacamole.properties

```bash
mkdir -p ./guacamole-config
cat > ./guacamole-config/guacamole.properties << EOF
guacd-hostname: guacd
guacd-port: 4822

postgresql-hostname: postgres
postgresql-port: 5432
postgresql-database: guacamole_db
postgresql-username: guacamole_user
postgresql-password: your-password-here
EOF
```

### 4. Start the Stack

```bash
docker compose up -d
sleep 15
docker compose logs --tail 30
```

### 5. Install Nginx + Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx

# Place HTTP-only config first, then run certbot
sudo nginx -t && sudo systemctl reload nginx

sudo certbot --nginx \
  -d kvm.plusclouds.com \
  --non-interactive \
  --agree-tos \
  -m admin@plusclouds.com \
  --redirect
```

---

## Bugs Encountered & Fixes

### Bug 1 — Nginx fails with "options-ssl-nginx.conf not found"

**Error:**
```
open() "/etc/letsencrypt/options-ssl-nginx.conf" failed (2: No such file or directory)
```

**Cause:** The full SSL Nginx config was deployed before Certbot had run, so the Let's Encrypt config files didn't exist yet.

**Fix:** Deploy a plain HTTP-only Nginx config first, run Certbot to obtain the certificate, then Certbot automatically upgrades the config to HTTPS.

---

### Bug 2 — Guacamole tunnel ID leaking into URL (e.g. `/wR684aDwgKAm`)

**Cause:** Guacamole's WebSocket tunnel endpoint `/websocket-tunnel` was going through the generic `location /` block, causing WebSocket upgrade headers to not apply correctly. Guacamole fell back to HTTP long-polling and exposed the raw tunnel ID in the URL.

**Fix:** Add a dedicated `location /websocket-tunnel` block in Nginx with explicit WebSocket headers.

---

### Bug 3 — SCRAM authentication error with empty password

**Error:**
```
PSQLException: The server requested SCRAM-based authentication, but the password is an empty string.
```

**Cause:** PostgreSQL 15 uses SCRAM-SHA-256 authentication by default and rejects empty passwords. The `POSTGRES_PASSWORD` environment variable was either not set or not being passed correctly to the Guacamole container.

**Fix:** Set a strong password in both `postgres` and `guacamole` service blocks. If the volume was previously initialized with an empty password, it must be wiped completely:

```bash
docker compose down -v --remove-orphans
docker volume rm $(docker volume ls -q | grep pg)
docker compose up -d
```

---

### Bug 4 — POSTGRES_* env vars not reliably passed to JDBC driver

**Cause:** In newer `guacamole/guacamole` image versions, the `POSTGRES_*` environment variables are not consistently forwarded to the internal JDBC connection, even when correctly set in `docker-compose.yml`.

**Fix:** Remove Postgres credentials from environment variables entirely and use a mounted `guacamole.properties` file instead:

```yaml
volumes:
  - ./guacamole-config:/etc/guacamole
```

With credentials defined in `./guacamole-config/guacamole.properties` using the `postgresql-*` prefix.

---

### Bug 5 — guacadmin login rejected despite correct password hash

**Cause:** The `guacamole-auth-ban` extension (bundled in the image) blocks an IP after repeated failed login attempts. Multiple failed attempts during debugging triggered the ban.

**Diagnosis:**
```bash
docker compose logs guacamole -f
# Look for: InMemoryAuthenticationFailureTracker
```

**Fix:** Wait for the ban to expire (default 5 minutes), or clear it from the database:

```bash
docker compose exec postgres psql -U guacamole_user -d guacamole_db -c \
  "DELETE FROM guacamole_auth_ban;"
```

---

### Bug 6 — EXTENSIONS env var not activating PostgreSQL auth

**Cause:** The correct extension name for the JDBC PostgreSQL driver is `jdbc-postgresql`, not `postgresql`.

**Fix:**
```yaml
environment:
  EXTENSIONS: "jdbc-postgresql"
```

---

## Default Credentials

| Field    | Value       |
|----------|-------------|
| Username | `guacadmin` |
| Password | `guacadmin` |

> **Change this immediately after first login.** Settings → Preferences → Change Password.

---

## Post-Install Checklist

- [ ] Change `guacadmin` default password
- [ ] Add VNC connection (Settings → Connections → New Connection)
    - Protocol: `VNC`
    - Hostname: IP of Ubuntu desktop VM
    - Port: `5900`
- [ ] Create per-customer user accounts (Settings → Users)
- [ ] Assign connections to users so customers only see their own desktop
- [ ] Disable or rename the `guacadmin` account once your own admin account is set up
- [ ] Set up monitoring: `docker compose logs guacamole -f`
- [ ] Test SSL renewal: `sudo certbot renew --dry-run`

---

## Useful Diagnostic Commands

```bash
# View all container logs
docker compose logs --tail 50 -f

# Check all containers are running
docker compose ps

# Check what env vars Guacamole sees
docker compose exec guacamole env | grep -i postgres

# Test DB connection and list tables
docker compose exec postgres psql -U guacamole_user -d guacamole_db -c "\dt"

# Check guacadmin user exists in DB
docker compose exec postgres psql -U guacamole_user -d guacamole_db -c "
SELECT e.name, u.password_hash
FROM guacamole_user u
JOIN guacamole_entity e ON u.entity_id = e.entity_id;"

# Clear auth ban (if login is blocked)
docker compose exec postgres psql -U guacamole_user -d guacamole_db -c "
DELETE FROM guacamole_auth_ban;"

# Test Nginx config
sudo nginx -t

# Test SSL renewal
sudo certbot renew --dry-run

# Restart only Guacamole (without touching Postgres)
docker compose up -d --force-recreate guacamole
```

---

## Resource Guidelines

| Concurrent Sessions | CPU       | RAM    |
|--------------------|-----------|--------|
| Up to 10           | 2 vCPU    | 2 GB   |
| Up to 25           | 2 vCPU    | 4 GB   |
| Up to 50           | 4 vCPU    | 8 GB   |
| 50+                | 8+ vCPU   | 16 GB+ |

The bottleneck is always `guacd` — approximately 25–50 MB RAM and 0.1 CPU core per active session depending on desktop activity level. Bandwidth is also significant: budget ~4 Mbps upstream per active normal desktop session.
