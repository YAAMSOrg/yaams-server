#!/usr/bin/env sh
# Production container entrypoint.
#
# Runs the runtime-only preparation that needs the real environment (.env) -
# so it happens on container start, not at image build time - then hands off to
# the passed command (supervisord running nginx + php-fpm by default; the
# worker/scheduler services pass their own artisan command).
set -e

# The compose stack mounts a named volume at /app/storage so uploads and logs
# persist across image upgrades. On first boot that volume is empty and hides
# the skeleton baked into the image, so recreate the required directory tree
# (idempotent) and fix ownership before Laravel touches it.
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

# Only root can hand the tree to www-data. Under rootless Podman/OpenShift the
# container runs as an arbitrary unprivileged UID that already owns (or shares
# the GID-0 group of) these paths, and a chown would fail with EPERM - so skip
# it there. The chmod is best-effort for the same reason.
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage
fi
chmod -R 775 storage 2>/dev/null || true

# Without an APP_KEY every request fails with a 500. If .env leaves it empty,
# generate one on first boot and persist it in the storage volume, so it
# survives restarts/upgrades and all services share the same key. noclobber
# makes the write atomic, so concurrently starting services cannot race.
if [ -z "$APP_KEY" ]; then
    (set -C; php -r 'echo "base64:".base64_encode(random_bytes(32));' > storage/app/.app_key) 2>/dev/null || true
    chmod 600 storage/app/.app_key
    APP_KEY="$(cat storage/app/.app_key)"
    export APP_KEY
fi

# Public symlink for user-uploaded storage (idempotent).
php artisan storage:link --quiet || true

# Cache config/routes/views for speed. Rebuilt every start so a changed .env is
# always picked up. `optimize` bundles config:cache + route:cache + view:cache.
php artisan optimize

exec "$@"
