#!/bin/sh

DIR="$(cd "$(dirname "$0")" && pwd)"
PORT=81

./build.sh
docker rm -f cgpuzzle-prod
docker run --restart=always --name=cgpuzzle-prod -d -p $PORT:80 \
    cgpuzzle
