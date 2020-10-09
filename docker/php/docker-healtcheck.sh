#!/bin/sh

set -e 

#healtcheck script must return a zero or one status code according to the docker documentation
#A status code of 1 mean an error append and the container is unhealthy

if cgi-fcgi -bind -connect php:9000;then
    exit 0
fi
exit 1

