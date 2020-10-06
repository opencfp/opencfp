#!/bin/sh

# docker-entrypoint.sh: Set things up so the app will run in the Docker container

if [ "${1#-}" != "$1" ];then
    set -- php-fpm "$@"
fi
if [ "$1" = 'php-fpm' ] || [  "$1" = 'php' ] || [ "$1" = 'bin/console' ];then
    # Doing stuff for avoid .env file
    echo $APP_ENV

    cat config/docker.yml.dist | envsubst > config/$CFP_ENV.yml 

    if [ $? != '0' ]; then
        exit 1;
    fi


    if [ $? != '0' ]; then
        exit 1;
    fi

    if [ "$CFP_ENV" != 'production' ]; then
        echo "==> Installing dependencies..."
        if command -v composer &>/dev/null; then
            composer install --prefer-dist --no-progress --no-suggest --no-interaction
            if [ $? != '0' ]; then
                exit 1;
            fi
        elif [ -f "composer.phar" ]; then
            php composer.phar install --prefer-dist --no-progress --no-suggest --no-interaction
            if [ $? != '0' ]; then
                exit 1;
            fi
        else
            echo "ERROR: Composer path unknown. Please install composer or download composer.phar"
            exit 1
        fi
    fi

    echo "==> Waiting for db to be ready..."
    ATTEMPS_LEFT_TO_REACH_DB=300
    until [ $ATTEMPS_LEFT_TO_REACH_DB -eq 0 ] || bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
        sleep 1
        ATTEMPS_LEFT_TO_REACH_DB=$((ATTEMPS_LEFT_TO_REACH_DB-1))
        echo "Still waiting for the db to be ready... Or maybe db is not reachable. $ATTEMPS_LEFT_TO_REACH_DB attemps left"
    done

    if [ $ATTEMPS_LEFT_TO_REACH_DB -eq 0 ]; then
        echo "The db is not up or not reachable"
        exit 1
    fi 
    echo "The db is now up and reachable"

    echo "==> Clearing caches..."
    bin/console --env=$CFP_ENV cache:clear
    
    if [ $? != '0' ]; then
        exit 1;
    fi

    if ls -A migrations/*.php > /dev/null 2>&1 ; then
        echo "==> Running migrations..."
        bin/console doctrine:migrations:migrate --env=$CFP_ENV --no-interaction

        if [ $? != '0' ]; then
            exit 1;
        fi
    fi

    if [ "$CFP_ENV" != 'production' ]; then
		if [ ! -z $ADMIN_NAME ] && [ ! -z $ADMIN_PASSWORD ] && [ ! -z $ADMIN_EMAIL ] && [ ! -z $ADMIN_LAST_NAME ]; then
            echo "==> Adding superUser..."
            bin/console user:create --first_name="$ADMIN_NAME" --last_name="$ADMIN_LAST_NAME" --email="$ADMIN_EMAIL" --password="$ADMIN_PASSWORD" --admin
		fi
	fi

    echo "==> Installing yarn recompilation"
    yarn install
    if [ $? != '0' ]; then
        exit 1;
    fi
    echo "==> Compiling frontend assets files"
    yarn run production
    if [ $? != '0' ]; then
        exit 1;
    fi
    
    echo "==> Everything is ready to use !"
fi

exec docker-php-entrypoint "$@"





