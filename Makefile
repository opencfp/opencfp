.PHONY: composer coverage cs database infection integration it test unit

it: cs test

composer:
	composer install

coverage: composer
	vendor/bin/phpunit --configuration tests/Unit/phpunit.xml.dist --coverage-text

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
	vendor/bin/phpunit --configuration tests/Integration/phpunit.xml.dist

test: integration unit

unit: composer
	vendor/bin/phpunit --configuration tests/Unit/phpunit.xml.dist

test: composer database
	vendor/bin/phpunit
