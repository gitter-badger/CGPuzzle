#!/bin/sh

DIR="$(cd "$(dirname "$0")" && pwd)"

docker rm -f cgpuzzle
docker run --restart=always --name=cgpuzzle -d -p 9595:80 \
    cgpuzzle
