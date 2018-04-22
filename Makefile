.PHONY: asset cache coverage cs database infection integration it stan test test-env unit

it: cs test

asset:
	yarn install
	yarn run production

cache: vendor
	bin/console cache:clear --env=development
	bin/console cache:clear --env=testing

coverage: vendor
	if [ $(type) = "html" ]; then vendor/bin/phpunit --testsuite unit --coverage-html coverage; else vendor/bin/phpunit --testsuite unit --coverage-text; fi;

cs: vendor
	vendor/bin/php-cs-fixer fix --verbose --diff

database: test-env vendor
	mysql -uroot -e "DROP DATABASE IF EXISTS cfp_test"
	mysql -uroot -e "CREATE DATABASE cfp_test"
	CFP_ENV=testing vendor/bin/phinx migrate --environment testing
	mysqldump -uroot cfp_test > tests/dump.sql

infection: vendor database
	vendor/bin/infection --test-framework-options="--printer PHPUnit\\\TextUI\\\ResultPrinter"

integration: test-env vendor database cache
	vendor/bin/phpunit --testsuite integration

stan: vendor
	vendor/bin/phpstan analyse --level=2 src tests

test: integration unit

test-env:
	if [ ! -f "config/testing.yml" ]; then cp config/testing.yml.dist config/testing.yml; fi

unit: vendor
	vendor/bin/phpunit --testsuite unit

vendor: composer.json composer.lock
	composer install
