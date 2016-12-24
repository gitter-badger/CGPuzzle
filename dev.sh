#!/bin/sh

DIR="$(cd "$(dirname "$0")" && pwd)"
PORT=81

./build.sh
docker rm -f cgpuzzle-dev
docker run --name=cgpuzzle-dev -d -p $PORT:80 \
    -v $DIR/www:/var/www/html \
    cgpuzzle
