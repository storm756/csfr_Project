# VPS Deployment Guide — CSFR/CCFR on Hostinger KVM 2

## VPS Setup (pick Ubuntu 22.04 or 24.04 on Hostinger)

### 1. SSH into the VPS
```bash
ssh root@<VPS_IP>
```

### 2. Install Docker and Docker Compose
```bash
apt update && apt upgrade -y
apt install -y ca-certificates curl gnupg
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo $VERSION_CODENAME) stable" > /etc/apt/sources.list.d/docker.list
apt update
apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
```

### 3. Install Nginx (reverse proxy + SSL)
```bash
apt install -y nginx certbot python3-certbot-nginx
```

### 4. Clone the repo
```bash
cd /opt
git clone https://github.com/storm756/csfr_Project.git csfr-lms
cd csfr-lms
```

### 5. Create the .env file
Copy from local — it has DB passwords, Moodle secrets, etc. NEVER commit this.
```bash
nano .env
# Paste the contents of D:\csfr-lms\.env from your local machine
```

### 6. Update docker-compose.yml
Change the Moodle wwwroot and port mappings if needed. The web container should listen on 127.0.0.1:8000 (Nginx will proxy to it).

### 7. Start the containers
```bash
docker compose up -d
```

### 8. Set up the domain + SSL
Point your domain's A record to the VPS IP in your domain registrar's DNS settings.

```bash
# Create Nginx config
cat > /etc/nginx/sites-available/csfr <<'EOF'
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;

    # Landing page (static)
    location / {
        root /opt/csfr-lms/landing;
        index index.html;
        try_files $uri $uri/ =404;
    }

    # Moodle LMS (reverse proxy to Docker)
    location /local/ {
        proxy_pass http://127.0.0.1:8000/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        client_max_body_size 256M;
    }
}
EOF

ln -s /etc/nginx/sites-available/csfr /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# Get SSL certificate
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

**Alternative (simpler):** Serve Moodle at the root domain instead of `/local/`:
```nginx
server {
    listen 80;
    server_name lms.yourdomain.com;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        client_max_body_size 256M;
    }
}
```
Then use `lms.yourdomain.com` for Moodle and `yourdomain.com` for the landing page.

### 9. Update Moodle config
```bash
docker exec csfr-lms-web-1 sed -i "s|http://localhost:8000|https://lms.yourdomain.com|" /var/www/html/config.php
# Add SSL proxy support
docker exec csfr-lms-web-1 sed -i '/wwwroot/a $CFG->sslproxy = true;' /var/www/html/config.php
```

### 10. Update landing page links
Replace all `http://localhost:8000` URLs in `landing/index.html` with `https://lms.yourdomain.com`.

### 11. Import local data (optional — for migrating the demo)
Export the local MySQL database:
```bash
# On your local machine
docker exec csfr-lms-db-1 mysqldump -u root -p<password> moodle > moodle_backup.sql
# SCP to VPS
scp moodle_backup.sql root@<VPS_IP>:/opt/csfr-lms/
```
Import on VPS:
```bash
docker exec -i csfr-lms-db-1 mysql -u root -p<password> moodle < /opt/csfr-lms/moodle_backup.sql
```
Also copy moodledata if needed:
```bash
# Local
docker cp csfr-lms-web-1:/var/www/moodledata ./moodledata_backup
scp -r moodledata_backup root@<VPS_IP>:/opt/csfr-lms/
# VPS
docker cp /opt/csfr-lms/moodledata_backup/. csfr-lms-web-1:/var/www/moodledata/
docker exec csfr-lms-web-1 chown -R www-data:www-data /var/www/moodledata
```

## Recommended subdomain layout
- `yourdomain.com` → landing page (static HTML from `landing/`)
- `lms.yourdomain.com` → Moodle LMS (Docker)

## Security checklist
- [ ] Change default Moodle admin password
- [ ] Disable Moodle debug mode (`$CFG->debug = 0;`)
- [ ] Set up firewall: `ufw allow 22,80,443/tcp && ufw enable`
- [ ] Set up automatic security updates: `apt install unattended-upgrades`
- [ ] Never commit .env to git

## Notes for the agent
- The docker-compose.yml, Dockerfile, and all Moodle config are in `D:\csfr-lms` (locally) / `/opt/csfr-lms` (VPS)
- The .env file has DB credentials — read it locally, never print values
- The landing page is pure static HTML/CSS/JS in `landing/`
- Moodle data lives in Docker volumes — back them up before any destructive operation
- The current Cloudflare tunnel setup (for the demo) is documented in CHANGELOG.md
