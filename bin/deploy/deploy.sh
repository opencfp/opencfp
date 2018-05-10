#!/usr/bin/env bash

export SYMFONY_ENV=prod

rm -rf /tmp/deployment/symfony.prberghoff.de
git clone ../New/.git /tmp/deployment/symfony.prberghoff.de
cp ./deployment.ini /tmp/deployment/symfony.prberghoff.de/

php /usr/local/bin/deployment.phar /tmp/deployment/symfony.prberghoff.de/deployment.ini