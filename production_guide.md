# SamvadHub Production Deployment Guide

This guide details the steps and best practices required to transition SamvadHub from a local Docker development container to a robust, scalable, and secure production environment.

---

## 1. Setup Production SMTP Credentials

To ensure reliable delivery of account verification codes and password reset links, you must replace the default PHP development mailer settings with a dedicated transactional email delivery service (e.g., **SendGrid**, **Mailgun**, or **Amazon SES**).

### Configuration Steps:
1. Register for an account on your chosen SMTP provider.
2. Verify your sending domain using DNS records (SPF, DKIM, DMARC) to maximize email deliverability and avoid spam folders.
3. Generate an API SMTP credential (username and secret password).
4. Update the production `.env` configuration file:

```ini
# Production Mail Settings
SMTP_HOST=smtp.sendgrid.net           # Or smtp.mailgun.org, email-smtp.us-east-1.amazonaws.com
SMTP_PORT=587                         # Secure TLS port (use 465 for SSL)
SMTP_USER=apikey                      # Usually 'apikey' for SendGrid, or full username
SMTP_PASS=your_secure_smtp_secret_key
SMTP_FROM_EMAIL=noreply@samvadhub.com
SMTP_FROM_NAME="SamvadHub Team"
```

> [!IMPORTANT]
> Ensure SMTP debug logs are disabled in production to protect sensitive token parameters in log outputs. The setting is already configured in `src/Helpers/MailerHelper.php`.

---

## 2. Managed Database Hosting

In production, avoid running MySQL inside a Docker container using a local volume. A managed database service offers high availability, automated backups, replication, and automatic security patching.

### Recommended Providers:
* **Google Cloud SQL** (MySQL 8.0+)
* **Amazon RDS** (MySQL 8.0+)

### Setup Instructions:
1. **Provision Instance**: Create a MySQL 8.0 database instance on Cloud SQL or AWS RDS.
2. **Configure Networking & Access Control**:
   - Secure the instance by restricting access. Do not allow public access (`0.0.0.0/0`).
   - Use Google Cloud SQL Auth Proxy or AWS Security Groups to allow connection only from your Web/App server IP addresses.
3. **Database Initialization**:
   - Run the initial schema migration using the database migration script:
     ```bash
     mysql -h <managed_db_host> -u <db_user> -p <db_name> < SamvadHub_migration_v2.sql
     ```
4. **Update Environment Configurations**:
   Update your production `.env` file with the managed database details:
   ```ini
   DB_HOST=10.0.0.5                   # Internal IP of your managed database instance
   DB_USER=samvadhub_prod_user
   DB_PASS=strong_db_password_here
   DB_NAME=samvadhub
   ```

> [!TIP]
> Ensure automated daily backups are enabled with a retention period of at least 7 days to protect against accidental data loss.

---

## 3. HTTPS / SSL Configuration

SamvadHub enforces a secure cookie policy (`Secure; HttpOnly; SameSite=Strict`) to prevent Session Fixation, Cross-Site Scripting (XSS), and Cross-Site Request Forgery (CSRF). **These features require HTTPS to function properly.**

### Option A: Let's Encrypt (Apache / Nginx)
Let's Encrypt provides free, automated SSL certificates.

1. **Install Certbot** on your Linux machine:
   ```bash
   sudo apt update
   sudo apt install certbot python3-certbot-apache  # For Apache
   # OR
   sudo apt install certbot python3-certbot-nginx   # For Nginx
   ```
2. **Generate Certificate**:
   ```bash
   sudo certbot --apache -d samvadhub.com -d www.samvadhub.com
   ```
3. **Automate Renewal**:
   Ensure the systemd timer or cron job is active:
   ```bash
   sudo systemctl status certbot.timer
   ```

### Option B: Cloudflare SSL/TLS
Cloudflare acts as a reverse proxy, caching and defending your site against DDoS attacks while providing SSL.

1. Point your domain DNS nameservers to Cloudflare.
2. Under **SSL/TLS** -> **Overview**, set encryption mode to **Full** or **Full (Strict)**.
3. To enforce HTTPS traffic, toggle on **Always Use HTTPS** in Cloudflare.
4. **Install Origin CA Certificate** on your origin Apache/Nginx web server to secure connections between Cloudflare and your server.

> [!WARNING]
> If HTTPS is not properly configured, session cookies will not be sent by modern browsers, resulting in users being unable to log in or maintain authenticated sessions.

---

## 4. Production Security Hardening

Ensure the following PHP configuration variables are optimized in your production environment (`php.ini`):

* `display_errors = Off` (Prevent exposing detailed system error paths)
* `log_errors = On` (Log errors to a secure private file)
* `session.cookie_secure = On` (Transmit cookies over secure connections only)
* `session.cookie_httponly = On` (Prevent Javascript access to cookies)
* `session.cookie_samesite = "Strict"` (Mitigate CSRF attacks)

Verify that `.env` is omitted from Git via `.gitignore`, and that its permissions are set to `0600` on the host to prevent unauthorized local reading.
