.PHONY: composer cs database it test

it: cs test

composer:
	composer install

cs: composer
	vendor/bin/php-cs-fixer fix --verbose --diff

database: composer
	mysql -uroot -e "DROP DATABASE IF EXISTS cfp_test"
	mysql -uroot -e "CREATE DATABASE cfp_test"
	cp phinx.yml.dist phinx.yml
	vendor/bin/phinx migrate -e testing

test: composer database
	vendor/bin/phpunit

