#!/bin/sh
set -eux
# docker-entrypoint.sh: Set things up so the app will run in the Docker container

if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	cat config/docker.yml.dist | envsubst >config/$CFP_ENV.yml

	echo "==> Installing dependencies..."
	if [ "$CFP_ENV" != 'production' ]; then
		composer install --prefer-dist --no-progress --no-suggest --no-interaction
	fi

	echo "==> Waiting for db to be ready..."
	ATTEMPS_LEFT_TO_REACH_DB=300
	until [ $ATTEMPS_LEFT_TO_REACH_DB -eq 0 ] || bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; do
		sleep 1
		ATTEMPS_LEFT_TO_REACH_DB=$((ATTEMPS_LEFT_TO_REACH_DB - 1))
		echo "Still waiting for the db to be ready... Or maybe db is not reachable. $ATTEMPS_LEFT_TO_REACH_DB attemps left"
	done

	if [ $ATTEMPS_LEFT_TO_REACH_DB -eq 0 ]; then
		echo "The db is not up or not reachable"
		exit 1
	fi
	echo "The db is now up and reachable"

	# Clearing cache for the environment set in the docker-compose.yml
	echo "==> Clearing caches..."
	bin/console --env=$CFP_ENV cache:clear

	# Running the migrations only if we have php file in the migrations folder
	if ls -A migrations/*.php >/dev/null 2>&1; then
		echo "==> Running migrations..."
		bin/console doctrine:migrations:migrate --env=$CFP_ENV --no-interaction
		# If the previous command return a non zero status code we leave the initialization processus with a status code error of 1.
	fi

	echo "==> Setting the user of the cache to avoir permission issue"
	# Since we only make this change in the container, this will not raise us a permission issue in our local machine
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX cache log web/uploads || chmod -R 755 cache log web/uploads
	echo "==> Everything is ready to use !"
fi

exec docker-php-entrypoint "$@"
