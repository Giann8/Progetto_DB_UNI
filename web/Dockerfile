# Dockerfile per PHP con supporto PostgreSQL e Apache
FROM php:8.2-apache

# Installa dipendenze di sistema e estensioni PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Abilita mod_rewrite per Apache
RUN a2enmod rewrite

# Copia i file del progetto
COPY . /var/www/html/

# Imposta permessi corretti
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configurazione Apache (opzionale)
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Esponi porta 80
EXPOSE 80

# Comando di avvio (Apache in foreground)
CMD ["apache2-foreground"]
