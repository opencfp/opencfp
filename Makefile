.PHONY: asset auto-review cache coverage cs database infection integration it stan test test-env unit

it: cs stan test

asset:
	yarn install
	yarn run production

auto-review: vendor
	SYMFONY_DEPRECATIONS_HELPER=disabled vendor/bin/phpunit --testsuite auto-review

cache: vendor
	bin/console cache:clear --env=testing

coverage: vendor
	if [ $(type) = "html" ]; then SYMFONY_DEPRECATIONS_HELPER=disabled vendor/bin/phpunit --testsuite unit --coverage-html coverage; else vendor/bin/phpunit --testsuite unit --coverage-text; fi;

cs: vendor
	vendor/bin/php-cs-fixer fix --verbose --diff

database: test-env vendor
	bin/console doctrine:database:drop --env=testing --force
	bin/console doctrine:database:create --env=testing
	bin/console doctrine:migrations:migrate --env=testing -n

doctrine:
	bin/console doctrine:schema:update --env=testing --force
	bin/console doctrine:schema:validate --env=testing
	bin/console doctrine:mapping:info --env=testing

infection: vendor database
	php -d zend_extension=xdebug.so vendor/bin/infection

integration: test-env vendor database cache
	SYMFONY_DEPRECATIONS_HELPER=disabled CFP_ENV=testing vendor/bin/phpunit --testsuite integration

stan: vendor
	vendor/bin/phpstan analyse

test: auto-review doctrine integration unit

test-env:
	if [ ! -f "config/testing.yml" ]; then cp config/testing.yml.dist config/testing.yml; fi

unit: vendor
	SYMFONY_DEPRECATIONS_HELPER=disabled CFP_ENV=testing vendor/bin/phpunit --testsuite unit

vendor: composer.json composer.lock
	composer validate
	composer install
