.PHONY: asset composer coverage cs database infection integration it test unit

it: cs test

asset:
	yarn install
	yarn run production

composer:
	composer install

coverage: composer
	vendor/bin/phpunit --testsuite unit --coverage-text

cs: composer
	vendor/bin/php-cs-fixer fix --verbose --diff

database: composer
	mysql -uroot -e "DROP DATABASE IF EXISTS cfp_test"
	mysql -uroot -e "CREATE DATABASE cfp_test"
	if [ ! -f "phinx.yml" ]; then cp phinx.yml.dist phinx.yml; fi
	vendor/bin/phinx migrate -e testing
	mysqldump -uroot cfp_test > tests/dump.sql

infection: composer database
	vendor/bin/infection

integration: composer database
	if [ ! -f "config/testing.yml" ]; then cp config/testing.yml.dist config/testing.yml; fi
	vendor/bin/phpunit --testsuite integration

test: integration unit

unit: composer
	vendor/bin/phpunit --testsuite unit
