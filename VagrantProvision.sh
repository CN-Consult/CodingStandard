#!/usr/bin/env bash

# Install apache and php
apt-get update
apt-get install -y  php7.2-common php7.2-cli php7.2-xsl php7.2-mbstring php7.2-zip php-xdebug

# Install composer
cd /usr/local/bin
EXPECTED_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig)
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")

if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]
then
    >&2 echo 'ERROR: Invalid installer signature'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php --quiet --filename=composer
RESULT=$?
rm composer-setup.php
chmod a+x composer
cd


# Update composer as user vagrant so that the file-rights are ok and composer doesn't complain
cd /vagrant
su -c "composer install" vagrant
