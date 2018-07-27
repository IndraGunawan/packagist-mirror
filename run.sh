#!/bin/bash

PWD=$(dirname "$(readlink -f "$0")")
PHP=$(which php7.2)

if [[ $EUID -ne 0 ]]; then
    echo -e '\nThis script must be run as root\n'
    exit 1
fi

function do_mirroring() {
    find . -type d -exec chmod 0777 {} \;
    find . -type f -exec chmod 0775 {} \;
    chown -R www-data:www-data ./*
    $PHP -d memory_limit=2G $PWD/bin/console app:metadata:dump
}

if [ ! -d "$PWD/vendor" ]; then
    COMPOSER_MEMORY_LIMIT=1GB composer update
    find . -type d -exec chmod 0777 {} \;
    find . -type f -exec chmod 0775 {} \;
    chown -R www-data:www-data ./*
    do_mirroring
else
    do_mirroring
fi

## creating crons job
if [ ! -f "/etc/cron.d/packagist" ]; then
    echo -e "#!/bin/bash" > /etc/cron.d/packagist
    echo -e "0 * * * * $PWD/bin/console app:metadata:dump >/dev/null 2>&1" > /etc/cron.d/packagist
    chmod +x /etc/cron.d/packagist
fi
