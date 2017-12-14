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
	cp phinx.yml.dist phinx.yml
	vendor/bin/phinx migrate -e testing

infection: composer database
	vendor/bin/infection

integration: composer database
	vendor/bin/phpunit --testsuite integration

test: integration unit

unit: composer
	vendor/bin/phpunit --testsuite unit
