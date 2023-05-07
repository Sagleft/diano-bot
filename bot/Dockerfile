# build helper script
FROM golang:1.18.3-buster as builder
WORKDIR /app
RUN mkdir /app/helper
COPY helper /app/
RUN go build -o /app/bot

FROM rsubr/php-ubuntu:focal
WORKDIR /app
COPY --from=builder /app/bot /app/bot
RUN mkdir /app/cache
COPY . .

# install composer
RUN apt-get update
RUN apt-get -y install curl wget
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# install dependencies
RUN composer update
RUN bash install.sh

CMD ["./bot"]
