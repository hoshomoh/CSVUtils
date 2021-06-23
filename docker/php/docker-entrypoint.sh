#!/usr/bin/env bash
set -e

echo $1

function install_dependencies()
{
    echo "--- Installing dependencies ---"
    cd "$APP_DIR"
    composer install
}

if [ "$1" = "develop" ]; then
    install_dependencies
elif [ "$1" = "test" ]; then
    install_dependencies
    composer test
elif [ -z "$1" ]; then
    install_dependencies
else
    exec "$@"
fi
