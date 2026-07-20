FROM php:8.3-cli

# System deps + PHP extensions CodeIgniter 4 needs (intl, pgsql)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        libpq-dev \
        unzip \
    && docker-php-ext-install intl pgsql pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . /app

# Writable dirs must be owned by the runtime user
# RUN chmod -R 0777 /app/writable
RUN mkdir -p /app/writable/cache /app/writable/logs /app/writable/session /app/writable/uploads \
    && chmod -R 0777 /app/writable

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
