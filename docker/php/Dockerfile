FROM php:8.2-fpm

# Instalacja niezbędnych narzędzi i bibliotek dla PostgreSQL oraz diagnostyki sieci
RUN apt-get update && apt-get install -y \
    libpq-dev \
    iputils-ping \         
    net-tools \            
    dnsutils \            
    procps \            
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Instalacja Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
