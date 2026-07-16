#!/bin/sh
set -eu

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

# The source tree is bind-mounted during development; keep PHP dependencies in
# a named Docker volume and reconcile them with composer.lock at startup.
composer install --no-interaction --prefer-dist

exec "$@"
