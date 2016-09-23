#!/bin/sh

DIR="$(cd "$(dirname "$0")" && pwd)"

docker rm -f cgpuzzle
docker run --name=cgpuzzle -d -p 9595:80 \
    -v $DIR/www:/var/www/html \
    cgpuzzle
