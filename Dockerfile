FROM php:8.4-cli-bookworm
RUN docker-php-ext-install pdo_mysql
WORKDIR /app
CMD ["php", "-S", "0.0.0.0:3000", "-t", "public"]
