FROM php:5.6-apache

MAINTAINER Benoit Chauvet "benoit.chauvet@gmail.com"

COPY www /var/www/html

RUN mkdir /var/www/cache && chmod a+w /var/www/cache
RUN mkdir /var/www/users && chmod a+w /var/www/users
