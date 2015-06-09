#!/usr/bin/env bash
function e {
    echo "####################################"
    echo $1
    echo "####################################"
}

sudo locale-gen UTF-8

cd /vagrant/
sudo composer self-update
composer install
chmod 755 test.sh