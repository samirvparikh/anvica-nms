# Anvica Customized NMS - PHP/MySQL Starter

Features included:
- Device master with location/category
- Ping uptime/downtime monitoring
- SNMP polling for CPU, RAM, disk and interface traffic
- Bandwidth history
- Alerts table and dashboard
- Email alert support
- WhatsApp/SMS webhook placeholder
- SLA availability report
- Cron polling script

## Install
1. Create MySQL database: `anvica_nms`
2. Import `database.sql`
3. Edit `config.php`
4. Run: `php cron/poller.php`
5. Add cron:
   `*/5 * * * * /usr/bin/php /path/to/anvica_nms/cron/poller.php`

Default login:
- Username: admin
- Password: admin123

Change password after login.
