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

# build helper script
FROM golang:1.18.3-buster as builder
COPY ./helper /app/helper
WORKDIR /app/helper
RUN go build -o /app/bot

#FROM gcr.io/distroless/base
#WORKDIR /app
#COPY --from=builder /app /app
WORKDIR /app
CMD ["./bot"]
