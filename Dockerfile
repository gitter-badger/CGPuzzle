FROM php:5.6-apache

MAINTAINER Benoit Chauvet "benoit.chauvet@gmail.com"

RUN apt-get update && apt-get install -y php-http && pecl install pecl_http && apt-get clean

COPY www /var/www/html
