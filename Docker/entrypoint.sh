#!/usr/bin/env sh
# Production container entrypoint.
#
# Runs the runtime-only preparation that needs the real environment (.env) -
# so it happens on container start, not at image build time - then hands off to
# the passed command (FrankenPHP by default; the worker/scheduler services pass
# their own artisan command).
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
chown -R www-data:www-data storage
chmod -R 775 storage

# Public symlink for user-uploaded storage (idempotent).
php artisan storage:link --quiet || true

# Cache config/routes/views for speed. Rebuilt every start so a changed .env is
# always picked up. `optimize` bundles config:cache + route:cache + view:cache.
php artisan optimize

exec "$@"
