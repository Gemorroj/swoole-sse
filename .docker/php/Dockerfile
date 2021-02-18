FROM phpswoole/swoole:4.6-php8.0

# update
RUN set -xe \
    && apt-get update \
    && apt-get upgrade -y \

    # Postgresql
    && apt-get install libpq-dev -y \
    && docker-php-ext-install pdo_pgsql \

    && cd /tmp \
    && apt-get install git -y \
    && git clone https://github.com/swoole/ext-postgresql.git --depth=1 \
    && cd ext-postgresql \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && cd /var/www \

    && apt-get clean \
    && rm -rf /tmp/*


WORKDIR /var/www
