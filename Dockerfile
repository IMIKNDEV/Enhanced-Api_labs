FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    curl \
    git \
    && docker-php-ext-install pdo_mysql bcmath zip

RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN groupadd --force -g 1000 sail && \
    useradd -ms /bin/bash --no-user-group -g 1000 -u 1337 sail

WORKDIR /var/www/html

EXPOSE 80

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
