#!/usr/bin/env bash
#
# Deploy this app on the live server.
#
# Run from the site root:  bash deploy.sh
#
# The compiled front end is not in git (public/build is ignored), so a deploy
# that only pulls PHP leaves the server running last release's JavaScript
# against this release's endpoints. That mismatch is silent -- the page renders,
# the broken part just does nothing -- so building is not optional here, and the
# script refuses to finish if the build did not actually land.

set -euo pipefail

BRANCH="${1:-master}"

say() { printf '\n\033[1;36m==> %s\033[0m\n' "$1"; }
die() { printf '\n\033[1;31mFAILED: %s\033[0m\n' "$1" >&2; exit 1; }

[ -f artisan ] || die "No artisan here. Run this from the site root."

command -v php >/dev/null || die "php not found on PATH."
command -v composer >/dev/null || die "composer not found on PATH."
command -v npm >/dev/null || die "npm not found. Install Node on the server, or build locally and upload public/build."

# Whatever happens after this point, the site does not stay dark.
trap 'php artisan up >/dev/null 2>&1 || true' EXIT

say "Putting the site into maintenance mode"
php artisan down --retry=15 || true

say "Pulling $BRANCH"
git pull origin "$BRANCH"

say "Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

say "Building the front end"
npm ci
npm run build

# The exact failure this script exists to prevent: a stale or missing build.
MANIFEST="public/build/manifest.json"
[ -f "$MANIFEST" ] || die "$MANIFEST was not produced -- the front end did not build."
find "$MANIFEST" -mmin -10 | grep -q . || die "$MANIFEST is older than 10 minutes -- the build did not rerun."

say "Running migrations"
php artisan migrate --force

say "Refreshing caches"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Only creates the symlink when it is missing; harmless to repeat.
[ -L public/storage ] || php artisan storage:link

say "Bringing the site back up"
php artisan up
trap - EXIT

say "Deployed $(git rev-parse --short HEAD) on $BRANCH"
