# Staging image for PHP-SF Template
#
# Built per PR by the staging-deploy workflow.
# Bakes in app code + compiled assets; entrypoint handles cache warmup at runtime.

FROM ghcr.io/ondottr/php-sf-ci:latest

WORKDIR /app

COPY . .

RUN cp config/constants.example.php config/constants.php \
    && sed -i "s/const SERVER_IP = '127.0.0.1'/const SERVER_IP = '157.173.126.27'/" config/constants.php \
    && cp .env.example .env \
    && sh docker/ci-init.sh \
    && composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts \
    && npm ci \
    && npm run build

RUN chmod +x docker/staging-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker/staging-entrypoint.sh"]
