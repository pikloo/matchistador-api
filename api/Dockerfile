# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION=8.0
ARG CADDY_VERSION=2

# "php" stage
FROM php:${PHP_VERSION}-fpm-alpine AS api_platform_php

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		fcgi \
		file \
		gettext \
		git \
		gnu-libiconv \
	;

# install gnu-libiconv and set LD_PRELOAD env to make iconv work fully on Alpine image.
# see https://github.com/docker-library/php/issues/240#issuecomment-763112749
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so

ARG APCU_VERSION=5.1.19
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		icu-dev \
		libzip-dev \
		postgresql-dev \
		zlib-dev \
	; \
	\
	docker-php-ext-configure zip; \
	docker-php-ext-install -j$(nproc) \
		intl \
		pdo_pgsql \
		zip \
	; \
	pecl install \
		apcu-${APCU_VERSION} \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
		apcu \
		opcache \
	; \
	\
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .api-phpexts-rundeps $runDeps; \
	\
	apk del .build-deps

###> recipes ###
###< recipes ###

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY docker/php/conf.d/api-platform.prod.ini $PHP_INI_DIR/conf.d/api-platform.ini

COPY docker/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

VOLUME /var/run/php

# https://FROM ubuntu:latest
# # MAINTAINER docker@ekito.fr

# #Install Cron
# RUN apt-get update
# RUN apt-get -y install cron

# # Add crontab file in the cron directory
# COPY crontab /etc/cron.d/matchistador-cron

# # Give execution rights on the cron job
# RUN chmod 0644 /etc/cron.d/matchistador-cron

# # Apply cron job
# RUN crontab /etc/cron.d/matchistador-cron

# # Create the log file to be able to run tail
# RUN touch /var/log/cron.log

# RUN crontab -l | { cat; echo "* * * * * echo 'Hello world' >> /var/log/cron.log 2>&1"; } | crontab -

# # Create the log file to be able to run tail
# RUN touch /var/log/cron.log


# # Run the command on container startup
# CMD cron && tail -f /var/log/cron.loggetcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/api

# build for production
ARG APP_ENV=prod

# prevent the reinstallation of vendors at every changes in the source code
COPY composer.json composer.lock symfony.lock ./
RUN set -eux; \
	composer install --prefer-dist --no-dev --no-scripts --no-progress; \
	composer clear-cache

# copy only specifically what we need
COPY .env ./
COPY bin bin/
COPY config config/
COPY migrations migrations/
COPY public public/
COPY src src/
COPY templates templates/

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync
VOLUME /srv/api/var

COPY docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENV SYMFONY_PHPUNIT_VERSION=9

# RUN printf '* * * * * cd /var/www && /usr/local/bin/php bin/console a:m:s >> /var/log/cron.log 2>&1\n#' >> /root/crontab

# # Add crontab file in the cron directory
# COPY crontab /etc/crontabs/matchistador-cron

# # Give execution rights on the cron job
# RUN chmod 0644 /etc/crontabs/matchistador-cron


# # Create the log file to be able to run tail
# RUN touch /var/log/cron.log
# # Apply cron job
# COPY crontab-test /etc/crontabs/matchistador-cron

# # # Give execution rights on the cron job
# RUN chmod 600 /etc/crontabs/matchistador-cron

# COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ENTRYPOINT ["/usr/bin/supervisord"]

ENTRYPOINT ["docker-entrypoint"]

#####cron
#Install Cron
# RUN apt-get update
# RUN apt-get -y install cron

# Add crontab file in the cron directory
# COPY crontab /etc/crontabs/matchistador-cron

# # # Give execution rights on the cron job
# # RUN chmod 0644 /etc/crontabs/matchistador-cron

# # Apply cron job
# RUN crontab /etc/crontabs/matchistador-cron



CMD ["php-fpm"]
# #cron
# ADD crontab.txt /crontab.txt
# ADD script.sh /script.sh
# RUN chmod 755 /script.sh
# RUN /usr/bin/crontab /crontab.txt



# CMD ["/usr/bin/supervisord"]

# "caddy" stage
# depends on the "php" stage above
FROM caddy:${CADDY_VERSION}-builder-alpine AS api_platform_caddy_builder

# install Mercure and Vulcain modules
RUN xcaddy build \
    --with github.com/dunglas/mercure \
    --with github.com/dunglas/mercure/caddy \
    --with github.com/dunglas/vulcain \
    --with github.com/dunglas/vulcain/caddy

FROM caddy:${CADDY_VERSION} AS api_platform_caddy

WORKDIR /srv/api

COPY --from=api_platform_caddy_builder /usr/bin/caddy /usr/bin/caddy
COPY --from=api_platform_php /srv/api/public public/
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile

# FROM postgres:13-alpine

# LABEL maintainer="PostGIS Project - https://postgis.net"

# ENV POSTGIS_VERSION 3.1.4
# ENV POSTGIS_SHA256 dfcbad0c6090c80bc59d3ea77d1adc4b3ade533a403761b4af6d9a44be1a6e48

# #Temporary fix:
# #   for PostGIS 2.* - building a special geos
# #   reason:  PostGIS 2.5.5 is not working with GEOS 3.9.*
# ENV POSTGIS2_GEOS_VERSION tags/3.8.2

# RUN set -ex \
#     \
#     && apk add --no-cache --virtual .fetch-deps \
#         ca-certificates \
#         openssl \
#         tar \
#     \
#     && wget -O postgis.tar.gz "https://github.com/postgis/postgis/archive/$POSTGIS_VERSION.tar.gz" \
#     && echo "$POSTGIS_SHA256 *postgis.tar.gz" | sha256sum -c - \
#     && mkdir -p /usr/src/postgis \
#     && tar \
#         --extract \
#         --file postgis.tar.gz \
#         --directory /usr/src/postgis \
#         --strip-components 1 \
#     && rm postgis.tar.gz \
#     \
#     && apk add --no-cache --virtual .build-deps \
#         autoconf \
#         automake \
#         file \
#         json-c-dev \
#         libtool \
#         libxml2-dev \
#         make \
#         perl \
#         clang-dev \
#         g++ \
#         gcc \
#         gdal-dev \
#         geos-dev \
#         llvm9-dev \
#         proj-dev \
#         protobuf-c-dev \
#     && cd /usr/src/postgis \
#     && ./autogen.sh \
# # configure options taken from:
# # https://anonscm.debian.org/cgit/pkg-grass/postgis.git/tree/debian/rules?h=jessie
#     && ./configure \
# #       --with-gui \
#     && make \
#     && make install \
#     && apk add --no-cache --virtual .postgis-rundeps \
#         json-c \
#         geos \
#         gdal \
#         proj \
#         libstdc++ \
#         protobuf-c \
#     && cd / \
#     && rm -rf /usr/src/postgis \
#     && apk del .fetch-deps .build-deps

# COPY ./initdb-postgis.sh /docker-entrypoint-initdb.d/postgis.sh
# COPY ./update-postgis.sh /usr/local/bin



# FROM ubuntu:latest
# # MAINTAINER docker@ekito.fr

# #Install Cron
# RUN apt-get update
# RUN apt-get -y install cron

# # Add crontab file in the cron directory
# COPY crontab /etc/cron.d/matchistador-cron

# # Give execution rights on the cron job
# RUN chmod 0644 /etc/cron.d/matchistador-cron

# # Apply cron job
# RUN crontab /etc/cron.d/matchistador-cron

# # Create the log file to be able to run tail
# RUN touch /var/log/cron.log




# # Run the command on container startup
# CMD cron && tail -f /var/log/cron.log


# FROM alpine:3.6

# # copy crontabs for root user
# COPY crontab /etc/crontabs/root

# # start crond with log level 8 in foreground, output to stderr
# CMD ["crond", "-f", "-d", "8"]
