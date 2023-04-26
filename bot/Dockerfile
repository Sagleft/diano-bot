FROM rsubr/php-ubuntu:focal
WORKDIR /app
RUN mkdir /app/cache
COPY . .

# install composer
RUN apt update
RUN apt-get install -y curl
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# install dependencies
RUN composer update
RUN bash install.sh
