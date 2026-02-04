# Cloudlog Ansible Deployment

Ansible playbook that deploys [Cloudlog](https://www.cloudlog.co.uk) on a fresh Ubuntu server using a standard LAMP stack (Apache, MariaDB, PHP 8.2). Works with any cloud provider (AWS EC2/Lightsail, Azure, DigitalOcean, Hetzner) or bare-metal Ubuntu host.

## What Gets Installed

| Component | Details |
|-----------|---------|
| **Apache 2** | With `mod_rewrite` and `mod_ssl` enabled, custom vhost, clean URLs |
| **MariaDB** | Database and user created, anonymous users and test DB removed |
| **PHP 8.2** | Extensions: mysql, gd, mbstring, zip, xml, curl |
| **Cloudlog** | Cloned from GitHub, config files templated, migrations auto-triggered |
| **UFW Firewall** | SSH + HTTP + HTTPS allowed, all other inbound denied (optional) |
| **Let's Encrypt SSL** | Via Certbot with auto-renewal (optional) |

## Prerequisites

### On Your Local Machine (Control Node)

1. **Ansible 2.14+** installed:

   ```bash
   # Ubuntu/Debian
   sudo apt install ansible

   # macOS
   brew install ansible

   # pip (any OS)
   pip install ansible
   ```

2. **Required Ansible collections** (the playbook uses `community.mysql`, `community.general`):

   ```bash
   ansible-galaxy collection install community.mysql community.general
   ```

3. **SSH access** to the target server with a user that has `sudo` privileges.

### On the Target Server

- **Ubuntu 22.04 or 24.04** (fresh install recommended)
- SSH server running
- The user specified in the inventory must be able to run `sudo` without a password, or you must provide `ansible_become_password`

#### Cloud-Specific Notes

| Provider | Default SSH User | Typical Instance |
|----------|-----------------|------------------|
| AWS Lightsail | `ubuntu` | $3.50/mo (512 MB) or $5/mo (1 GB) |
| AWS EC2 | `ubuntu` | t3.micro (free tier) or t3.small |
| Azure | `azureuser` | B1s (1 vCPU, 1 GB) |
| DigitalOcean | `root` | Basic Droplet ($6/mo, 1 GB) |

Make sure your security group / network security group allows inbound traffic on ports **22** (SSH), **80** (HTTP), and **443** (HTTPS).

## Quick Start

All commands below are run from the `ansible/` directory.

### 1. Create Your Inventory

```bash
cp inventory.yml.example inventory.yml
```

Edit `inventory.yml` with your server's IP address, SSH user, and key path:

```yaml
all:
  hosts:
    cloudlog-server:
      ansible_host: 203.0.113.10          # <-- your server IP
      ansible_user: ubuntu                 # <-- ubuntu, azureuser, or root
      ansible_ssh_private_key_file: ~/.ssh/my-key.pem
```

### 2. Verify Connectivity

```bash
ansible -i inventory.yml all -m ping
```

You should see a green `pong` response. If it fails, check that:
- The IP address is correct
- Your SSH key works (`ssh -i ~/.ssh/my-key.pem ubuntu@203.0.113.10`)
- Port 22 is open in your cloud security group

### 3. Run the Playbook

At minimum you must provide the database passwords and your domain:

```bash
ansible-playbook -i inventory.yml playbook.yml \
  -e cloudlog_db_password='YourSecurePass123' \
  -e mariadb_root_password='YourRootPass456' \
  -e cloudlog_domain='log.yourcall.com'
```

If deploying with just an IP address (no domain name):

```bash
ansible-playbook -i inventory.yml playbook.yml \
  -e cloudlog_db_password='YourSecurePass123' \
  -e mariadb_root_password='YourRootPass456' \
  -e cloudlog_domain='203.0.113.10' \
  -e cloudlog_base_url='http://203.0.113.10'
```

### 4. First Login

1. Open `http://your-server` in a browser
2. Log in with the default credentials: **m0abc** / **demo**
3. Go to **Admin** > **User Accounts** and create your own administrator account
4. Delete the default `m0abc` account

## Configuration Reference

All variables live in `group_vars/all.yml`. You can edit the file directly, override values with `-e` flags on the command line, or use Ansible Vault for secrets.

### Application Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `cloudlog_repo` | `https://github.com/magicbug/Cloudlog.git` | Git repository to clone |
| `cloudlog_branch` | `master` | Git branch or tag to deploy |
| `cloudlog_install_dir` | `/var/www/cloudlog` | Installation path on the server |
| `cloudlog_domain` | `cloudlog.example.com` | Server FQDN or IP for Apache vhost |
| `cloudlog_base_url` | `http://{{ cloudlog_domain }}` | Full base URL for Cloudlog config |
| `cloudlog_directory` | `/` | Web path (use `/` for document root) |
| `cloudlog_default_locator` | `IO91WM` | Default Maidenhead gridsquare |

### Database Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `cloudlog_db_name` | `cloudlog` | MariaDB database name |
| `cloudlog_db_user` | `cloudlog` | MariaDB user for the app |
| `cloudlog_db_password` | *(must be set)* | MariaDB user password |
| `mariadb_root_password` | *(must be set)* | MariaDB root password |

### PHP Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `php_version` | `8.2` | PHP version to install |
| `php_upload_max_filesize` | `30M` | Max upload file size (ADIF imports) |
| `php_post_max_size` | `35M` | Max POST body size |
| `php_memory_limit` | `64M` | PHP memory limit |
| `php_max_execution_time` | `300` | Max script execution (seconds) |
| `php_max_input_time` | `300` | Max input parsing time (seconds) |

### Optional Features

| Variable | Default | Description |
|----------|---------|-------------|
| `cloudlog_enable_ufw` | `true` | Configure UFW firewall |
| `cloudlog_enable_ssl` | `false` | Install Certbot and obtain Let's Encrypt cert |
| `cloudlog_ssl_email` | `admin@example.com` | Email for Let's Encrypt registration |

## Enabling HTTPS with Let's Encrypt

**Requirements:** Your server must be reachable at the domain name specified in `cloudlog_domain` (DNS A record must point to the server IP). Let's Encrypt cannot issue certificates for bare IP addresses.

```bash
ansible-playbook -i inventory.yml playbook.yml \
  -e cloudlog_db_password='YourSecurePass123' \
  -e mariadb_root_password='YourRootPass456' \
  -e cloudlog_domain='log.yourcall.com' \
  -e cloudlog_base_url='https://log.yourcall.com' \
  -e cloudlog_enable_ssl=true \
  -e cloudlog_ssl_email='you@example.com'
```

Certbot's auto-renewal timer is enabled automatically. Certificates renew before expiry without manual intervention.

## Using Ansible Vault for Secrets

Instead of passing passwords on the command line (which may be visible in shell history), you can encrypt them with Ansible Vault.

### 1. Create an Encrypted Vars File

```bash
ansible-vault create group_vars/vault.yml
```

Enter a vault password when prompted, then add:

```yaml
cloudlog_db_password: "YourSecurePass123"
mariadb_root_password: "YourRootPass456"
```

### 2. Run with Vault

```bash
ansible-playbook -i inventory.yml playbook.yml --ask-vault-pass
```

Or store the vault password in a file (excluded from git):

```bash
echo 'my-vault-password' > .vault_pass
chmod 600 .vault_pass
echo '.vault_pass' >> ../.gitignore

ansible-playbook -i inventory.yml playbook.yml --vault-password-file .vault_pass
```

## Deploying a Specific Version

To deploy a tagged release instead of the latest `master`:

```bash
ansible-playbook -i inventory.yml playbook.yml \
  -e cloudlog_branch='2.8.7' \
  -e cloudlog_db_password='...' \
  -e mariadb_root_password='...'
```

## Updating an Existing Installation

The playbook is idempotent. Running it again on a server that already has Cloudlog will:

- Pull the latest code from the configured branch (without overwriting local config files)
- Apply any new PHP/Apache configuration changes
- Skip database and user creation (already exist)

To update Cloudlog to the latest version:

```bash
ansible-playbook -i inventory.yml playbook.yml \
  -e cloudlog_db_password='...' \
  -e mariadb_root_password='...'
```

Database migrations run automatically on the next page load after the code is updated (handled by Cloudlog's `OptionsLib`).

> **Note:** If you have made manual edits to files inside `cloudlog_install_dir` on the server, the `git` task will not overwrite them (`force: false`). If you need a clean re-deploy, SSH into the server and run `git checkout .` inside the install directory first.

## Deploying Multiple Servers

Add additional hosts to your inventory:

```yaml
all:
  hosts:
    production:
      ansible_host: 203.0.113.10
      ansible_user: ubuntu
      ansible_ssh_private_key_file: ~/.ssh/prod-key.pem
    staging:
      ansible_host: 203.0.113.20
      ansible_user: ubuntu
      ansible_ssh_private_key_file: ~/.ssh/staging-key.pem
```

Use `--limit` to target a specific server:

```bash
# Deploy to production only
ansible-playbook -i inventory.yml playbook.yml --limit production -e ...

# Deploy to staging only
ansible-playbook -i inventory.yml playbook.yml --limit staging -e ...
```

You can also use host-specific variables by creating files in `host_vars/`:

```
ansible/
  host_vars/
    production.yml    # cloudlog_domain: log.mycall.com
    staging.yml       # cloudlog_domain: staging.mycall.com
```

## File Structure

```
ansible/
├── README.md                          # This file
├── inventory.yml.example              # Inventory template (copy to inventory.yml)
├── playbook.yml                       # Main playbook
├── group_vars/
│   └── all.yml                        # Default variables
└── templates/
    ├── cloudlog-apache.conf.j2        # Apache virtual host
    ├── cloudlog-htaccess.j2           # mod_rewrite rules for clean URLs
    ├── config.php.j2                  # Cloudlog application config
    ├── database.php.j2                # Cloudlog database config
    └── php-cloudlog.ini.j2            # PHP tuning overrides
```

## Troubleshooting

### Playbook fails at "Validate required variables"

You forgot to pass the database passwords. Add `-e cloudlog_db_password=... -e mariadb_root_password=...` to the command.

### Playbook fails at "Verify Ubuntu"

The target server is not running Ubuntu. This playbook uses `apt` and Ubuntu-specific package names. Adapting it for RHEL/CentOS would require replacing the package installation tasks.

### "pong" works but playbook fails with permission errors

The SSH user cannot run `sudo` without a password. Either:
- Configure passwordless sudo on the server: `echo 'ubuntu ALL=(ALL) NOPASSWD:ALL' | sudo tee /etc/sudoers.d/ubuntu`
- Or add `--ask-become-pass` to the `ansible-playbook` command

### Cloudlog shows a blank page after deployment

Check Apache error logs on the server:

```bash
sudo tail -50 /var/log/apache2/cloudlog-error.log
```

Common causes:
- Missing PHP extension (the playbook installs all required ones, but check if `php_version` matches what's available in your Ubuntu release)
- File permission issues — re-run the playbook to reset permissions

### Can't connect to the database

Verify MariaDB is running and the credentials are correct:

```bash
sudo systemctl status mariadb
mysql -u cloudlog -p -D cloudlog -e "SELECT 1;"
```

### SSL certificate fails to issue

- Verify the DNS A record for your domain points to the server IP: `dig +short your-domain.com`
- Ensure port 80 is open (Let's Encrypt uses HTTP-01 challenge)
- Check Certbot logs: `sudo cat /var/log/letsencrypt/letsencrypt.log`

### Re-running the playbook after a failed SSL attempt

Certbot uses a `creates` guard — it won't retry if the certificate directory already exists. To force a retry:

```bash
# On the server
sudo rm -rf /etc/letsencrypt/live/your-domain.com
sudo rm -rf /etc/letsencrypt/renewal/your-domain.com.conf

# Then re-run the playbook
```
