FROM node:16.8.0

COPY  . /build
WORKDIR /build/public

RUN yarn && yarn run build

FROM golang:1.18.3

COPY --from=0 /build  /build
WORKDIR /build/node-client

RUN go mod tidy && go build -ldflags="-s -w" client.go


FROM hyperf/hyperf:8.0-alpine-v3.12-swoole
LABEL version="1.0" license="MIT" app.name="slime-status"

# --build-arg timezone=Asia/Hong_Kong
ARG timezone
ENV TIMEZONE=${timezone:-"Asia/Hong_Kong"} \
    APP_ENV=prod \
    SCAN_CACHEABLE=(true)

# update
RUN set -ex \
    # show php version and extensions
    && php -v \
    && php -m \
    && php --ri swoole \
    #  ---------- some config ----------
    && cd /etc/php8 \
    # - config PHP
    && { \
        echo "upload_max_filesize=128M"; \
        echo "post_max_size=128M"; \
        echo "memory_limit=1G"; \
        echo "date.timezone=${TIMEZONE}"; \
    } | tee conf.d/99_overrides.ini \
    # - config timezone
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    # ---------- clear works ----------
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man \
    && echo -e "\033[42;37m Build Completed :).\033[0m\n"


COPY --from=1 /build /www
WORKDIR /www

RUN composer install --ignore-platform-reqs --no-dev -o

ENTRYPOINT ["php", "/www/bin/hyperf.php", "start"]