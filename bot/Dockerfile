FROM rsubr/php-ubuntu:focal
WORKDIR /app
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

# build helper script
RUN wget https://go.dev/dl/go1.18.3.linux-amd64.tar.gz
RUN rm -rf /usr/local/go && tar -C /usr/local -xzf go1.18.3.linux-amd64.tar.gz
RUN export GOROOT=/usr/local/go \
    export GOPATH=$GOROOT/bin \
    export PATH=$PATH:$GOPATH/bin \
    export PATH=$PATH:$GOPATH
RUN cd /app/helper
RUN go build -o /app/bot
RUN cd /app

CMD ["./bot"]
