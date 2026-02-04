# Cloudlog — Portainer Stack

Deploy Cloudlog as a Portainer stack with optional Traefik HTTPS.

## Prerequisites

- **Portainer** (Community or Business) running on a Docker host
- **Docker Compose v2** (bundled with modern Docker)
- *Optional:* **Traefik** reverse proxy for automatic HTTPS

## Quick Start

### Option A: Stack from Git Repository

1. In Portainer, go to **Stacks → Add stack → Repository**
2. Enter the repository URL and branch
3. Set **Compose path** to `portainer/docker-compose.yml`
4. Under **Environment variables**, add variables from `.env.example`
   (at minimum, change `MYSQL_ROOT_PASSWORD` and `MYSQL_PASSWORD`)
5. Click **Deploy the stack**

### Option B: Stack from Web Editor

1. In Portainer, go to **Stacks → Add stack → Web editor**
2. Paste the contents of `portainer/docker-compose.yml`
3. Add environment variables in the **Environment variables** section
4. Click **Deploy the stack**

> **Note:** Option B requires that the Docker images are pre-built and pushed to
> a registry, since the web editor method does not support `build:` directives.
> Use Option A for automatic builds from the Git repository.

## Environment Variables

| Variable | Default | Description |
|---|---|---|
| `MYSQL_ROOT_PASSWORD` | `rootpassword` | MariaDB root password |
| `MYSQL_DATABASE` | `cloudlog` | Database name |
| `MYSQL_USER` | `cloudlog` | Database user |
| `MYSQL_PASSWORD` | `cloudlogpassword` | Database user password |
| `MYSQL_HOST` | `db` | Database hostname (use the service name) |
| `MYSQL_PORT` | `3306` | Database port |
| `BASE_LOCATOR` | `IO91WM` | Maidenhead grid locator for your station |
| `WEBSITE_URL` | `http://localhost` | Full URL where Cloudlog is accessible |
| `CLOUDLOG_HTTP_PORT` | `80` | Host port for direct HTTP access |
| `CLOUDLOG_TRAEFIK_ENABLE` | `false` | Set to `true` to enable Traefik labels |
| `CLOUDLOG_DOMAIN` | `cloudlog.local` | Domain for Traefik Host rule |

**Important:** Change `MYSQL_ROOT_PASSWORD` and `MYSQL_PASSWORD` before deploying
to production.

## Default Login

After first deploy, log in with:

- **Username:** `m0abc`
- **Password:** `demo`

Change these immediately after first login.

## Using with Traefik (HTTPS)

1. Create the shared Traefik network if it doesn't exist:
   ```bash
   docker network create traefik
   ```
2. In `docker-compose.yml`, uncomment the lines marked `[TRAEFIK]`:
   - The `traefik` network definition (two lines under `networks:`)
   - The `- traefik` entry in the cloudlog service's `networks:` list
3. Set these environment variables:
   ```
   CLOUDLOG_TRAEFIK_ENABLE=true
   CLOUDLOG_DOMAIN=cloudlog.yourdomain.com
   WEBSITE_URL=https://cloudlog.yourdomain.com
   ```
4. Traefik will automatically provision a Let's Encrypt certificate via the
   `letsencrypt` certresolver (this must be configured in your Traefik static
   config)
5. HTTP requests are automatically redirected to HTTPS

## Using without Traefik (Direct Port)

By default, Cloudlog binds to port 80 on the host. Change the port with:

```
CLOUDLOG_HTTP_PORT=8080
```

Then access Cloudlog at `http://your-server:8080`.

## Persistent Data

Named volumes preserve data across container restarts and stack updates:

| Volume | Path in Container | Contents |
|---|---|---|
| `db_data` | `/var/lib/mysql` | MariaDB data files |
| `cloudlog_uploads` | `/var/www/html/uploads` | User-uploaded files |
| `cloudlog_backup` | `/var/www/html/backup` | ADIF backup exports |
| `cloudlog_logs` | `/var/www/html/application/logs` | Application logs |
| `cloudlog_eqsl` | `/var/www/html/images/eqsl_card_images` | eQSL card images |
| `cloudlog_qslcard` | `/var/www/html/assets/qslcard` | QSL card assets |
| `cloudlog_json` | `/var/www/html/assets/json` | JSON data files |

## Updating Cloudlog

To update to a new version:

1. In Portainer, select the stack
2. Click **Editor** → update the Git reference (branch or tag) if needed
3. Check **Re-pull image and redeploy**
4. Click **Update the stack**

The database migrations run automatically on page load (handled by Cloudlog's
`OptionsLib`).

## Backup and Restore

### Database Backup

```bash
docker exec <db-container> mariadb-dump -u cloudlog -p cloudlog > backup.sql
```

### Database Restore

```bash
docker exec -i <db-container> mariadb -u cloudlog -p cloudlog < backup.sql
```

### Volume Backup

Use `docker run --rm -v <volume>:/data -v $(pwd):/backup alpine tar czf /backup/<volume>.tar.gz /data` for each named volume.

## Troubleshooting

**Container starts but Cloudlog shows a database error:**
Check that `MYSQL_HOST` is set to `db` (the service name) and that the database
container is healthy. View logs with Portainer's container log viewer.

**"Install" page appears instead of login:**
The entrypoint removes the `/install` directory automatically. If you see it,
the entrypoint may not have run — verify the container uses the correct image.

**Permission denied errors in logs:**
The entrypoint sets ownership on all writable directories. If you mounted
additional host paths, ensure they are writable by UID 33 (`www-data`).

**Traefik not routing to Cloudlog:**
Verify that `CLOUDLOG_TRAEFIK_ENABLE=true` is set, the `traefik` Docker network
exists, and your DNS record points to the Traefik host.

## Architecture

```
┌─────────────────────────────────────────────┐
│ Docker Host                                 │
│                                             │
│  ┌──────────┐      ┌──────────┐             │
│  │ cloudlog │─────→│    db    │             │
│  │ (Apache) │      │(MariaDB) │             │
│  └────┬─────┘      └──────────┘             │
│       │ :80                                 │
│       │                                     │
│  ┌────┴─────────────────┐                   │
│  │ Traefik (optional)   │                   │
│  │ :443 ←→ cloudlog:80  │                   │
│  └──────────────────────┘                   │
└─────────────────────────────────────────────┘
```
