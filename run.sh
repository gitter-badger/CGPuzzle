#!/bin/sh

DIR="$(cd "$(dirname "$0")" && pwd)"
PORT=80

./build.sh
docker rm -f cgpuzzle-prod
docker run --restart=always --name=cgpuzzle-prod -d -p $PORT:80 \
    -v $DIR/users:/var/www/users \
    -v $DIR/cache:/var/www/cache \
    cgpuzzle
