.PHONY: cs

cs:
	vendor/bin/php-cs-fixer fix --verbose --diff
