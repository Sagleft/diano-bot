cp example.env .env
composer update
mkdir cache
chmod 777 cron/execute_orders.php
chmod 777 cache
mkdir logs
cd logs
touch access.log error.log
