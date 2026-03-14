FROM ubuntu:24.04

LABEL maintainer="Taylor Otwell"

ARG WWWGROUP
ARG WWWUSER
ARG NODE_VERSION=24
ARG MYSQL_CLIENT="mysql-client"

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS artisan octane:start --server=swoole --host=0.0.0.0 --port=8000 --watch"
ENV SUPERVISOR_PHP_USER="sail"

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=1 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=20000 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=192 \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE=10

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom \
 && echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom \
 && echo "Acquire::BrokenProxy true;" >> /etc/apt/apt.conf.d/99custom

RUN apt-get update && apt-get upgrade -y \
 && mkdir -p /etc/apt/keyrings \
 && apt-get install -y \
    gnupg gosu curl ca-certificates zip unzip git supervisor \
    libcap2-bin libpng-dev \
 && curl -sS https://keyserver.ubuntu.com/pks/lookup?op=get\&search=0xb8dc7e53946656efbce4c1dd71daeaab4ad4cab6 \
    | gpg --dearmor -o /etc/apt/keyrings/ppa_ondrej_php.gpg \
 && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" \
    > /etc/apt/sources.list.d/ppa_ondrej_php.list \
 && apt-get update \
 && apt-get install -y \
    php8.4-cli \
    php8.4-dev \
    php8.4-opcache \
    php8.4-mysql \
    php8.4-redis \
    php8.4-swoole \
    php8.4-curl \
    php8.4-mbstring \
    php8.4-xml \
    php8.4-zip \
    php8.4-bcmath \
    php8.4-intl \
    php8.4-readline \
    php8.4-gd \
    php8.4-pcov \
    php8.4-xdebug \
 && curl -sLS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/bin/ --filename=composer \
 && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
    | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
 && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" \
    > /etc/apt/sources.list.d/nodesource.list \
 && apt-get update \
 && apt-get install -y nodejs \
 && apt-get install -y $MYSQL_CLIENT \
 && apt-get autoremove -y \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.4

RUN userdel -r ubuntu || true
RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

RUN git config --global --add safe.directory /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY . .

RUN php artisan package:discover

COPY docker/local/start-container /usr/local/bin/start-container
COPY docker/local/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/local/php.ini /etc/php/8.4/cli/conf.d/99-sail.ini

RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

ENTRYPOINT ["start-container"]
