# STO Operations Runbook

## Production Environment Checklist

- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Set a generated `APP_KEY`.
- Set `APP_URL` to the public HTTPS URL.
- Set `TRUSTED_PROXIES` to the reverse proxy or load balancer IP list. Use `*` only when proxy headers are controlled by trusted infrastructure.
- Use MySQL with a dedicated database user.
- Use `SESSION_DRIVER=database`.
- Use `SESSION_ENCRYPT=true`.
- Use `SESSION_SECURE_COOKIE=true` behind HTTPS.
- Use `CACHE_STORE=database` or Redis in production.
- Use `QUEUE_CONNECTION=database` or Redis in production.
- Use `LOG_CHANNEL=stack` and `LOG_STACK=daily`.
- Keep `STO_HEALTH_EXPOSE_ENVIRONMENT=false` unless internal monitoring needs environment visibility.
- If seeders are executed in production, immediately change the default seeded passwords for `admin`, `operator1`, and `operator2`.

## Deployment Commands

Run these after deploying application code and environment changes:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run vendor:publish
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

The Blade views load DataTables, jQuery, SweetAlert2, Chart.js, Html5 QR Code, and Inter from `public/vendor`. Do not skip `npm run vendor:publish` unless those files are already present from the build artifact.

For troubleshooting after an incident, clear cached bootstrap files:

```bash
php artisan optimize:clear
```

## Queue Worker

Queued exports require an active queue worker.

Database queue example:

```bash
php artisan queue:work database --queue=default --sleep=3 --tries=3 --timeout=300
```

Run queue workers under a process supervisor such as Supervisor, systemd, Laragon service tooling, or another production process manager. Restart workers after deployments:

```bash
php artisan queue:restart
```

## Export Storage

The export disk is controlled by `STO_EXPORT_DISK`.

Scan result exports are queued. Direct browser export routes enqueue an `export_requests` row and require a queue worker to generate the final file.

Single server:

- `STO_EXPORT_DISK=local` is acceptable.
- Ensure `storage/app/private` or the configured local disk path is persistent.

Multi-server or load balanced:

- Use shared storage or S3-compatible object storage.
- Configure `STO_EXPORT_DISK=s3` only after credentials, bucket, and permissions are ready.
- All app servers and queue workers must read and write the same disk.

Export downloads are owner-scoped by user ID. Do not expose storage paths directly through the web server.

## Backup Policy

Minimum database backup:

- Full MySQL backup at least daily.
- Keep multiple restore points.
- Store backups outside the application server.
- Test restore procedures periodically.

Suggested backup command pattern:

```bash
mysqldump --single-transaction --routines --triggers --databases adasi_sto > adasi_sto_YYYYMMDD.sql
```

Do not store database passwords in shell history. Use a secured MySQL option file or secret manager.

## Restore Procedure

High-level restore steps:

1. Put the application in maintenance mode.
2. Stop queue workers.
3. Restore the database backup into the target MySQL database.
4. Verify migrations and application config.
5. Verify storage/export disk availability.
6. Run a smoke test for login, scanner setup, scan store, admin dashboard, and export status.
7. Start queue workers.
8. Disable maintenance mode.

Commands:

```bash
php artisan down
php artisan queue:restart
mysql adasi_sto < adasi_sto_YYYYMMDD.sql
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

## Export Retention

Recommended retention:

- Keep completed export files for 7 to 30 days depending on business need.
- Remove failed or expired export files with a scheduled cleanup command or maintenance job.
- Keep `export_requests` rows as audit metadata unless a formal retention policy requires pruning.

## Health Monitoring

Monitor:

- `GET /health`
- HTTP 5xx error rate.
- Queue length and failed jobs.
- Disk usage for logs and exports.
- Database connectivity and slow queries.

The health endpoint checks app status, database connection, storage access, and queue readiness. It must not expose secrets or stack traces.

## Error Tracking

External error tracking is recommended for production.

Options:

- Sentry
- Bugsnag
- Rollbar

Install and configure the chosen tool through environment variables and the provider's Laravel package. Do not hardcode DSNs or tokens in the repository.

## Recovery Notes

- Scanner data ownership is enforced at the application layer.
- `scan_result_logs` records scan create/update/delete audit.
- `activity_logs` records important master data, export, and STO activation activity.
- Hard-deleted scan rows should still have delete audit entries before deletion.
