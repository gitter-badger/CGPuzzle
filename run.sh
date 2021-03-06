#!/bin/sh

DIR="$(cd "$(dirname "$0")" && pwd)"
PORT=80

./build.sh
mkdir $DIR/users
mkdir $DIR/cache
chmod a+w $DIR/users
chmod a+w $DIR/cache

docker rm -f cgpuzzle-prod
docker run --restart=always --name=cgpuzzle-prod -d -p $PORT:80 \
    -v $DIR/users:/var/www/users \
    -v $DIR/cache:/var/www/cache \
    cgpuzzle

chmod a+w $DIR/users
chmod a+w $DIR/cache
