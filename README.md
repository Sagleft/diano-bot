
![logo](https://github.com/Sagleft/diano-bot/raw/master/logo.png)

This bot allows you to periodically copy content from one messenger to another.

Supported Clients:

* [Telegram](https://telegram.org/);
* [Utopia](https://u.is/).

## Requirements

* PHP 7.1+
* php-composer
* cron
* mariadb 5.5+

## Installation

Extended instruction [available here](https://gist.github.com/Sagleft/1ec700b47e6e8b59d1599385d7139829) (while in Russian, later there will be a translation in English).

Using Centos 7 as an example. First, let's install everything you need:

```bash
sudo yum install -y unzip nano git composer
```

Next, you need to install PHP version 7.1 or higher, as well as MariaDB, [you can use this instruction](https://github.com/Sagleft/install-lamp/blob/master/centos7_lamp.sh).

Next, we clone the repository and install the necessary packages:

```bash
cd ~
git clone https://github.com/Sagleft/diano-bot
cd diano-bot
mkdir cache
cp example.env .env
composer update
```



Next, we will deal with the database. First, you need to create a user to grant privileges to:

```bash
mysql -u root
```

then in mysql:

```
CREATE USER 'dianouser'@'localhost' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON * . * TO 'dianouser'@'localhost';
FLUSH PRIVILEGES;
```

Create a database:

```
CREATE DATABASE `diano` CHARACTER SET utf8 COLLATE utf8_general_ci;
```

and import tables dump:

```
cd ~/diano-bot
mysql -u dianouser -b diano<diano.sql
```

Next, you need to create tasks for importing channels. An example of such files is shown below. The files should be located in the `cache` directory.

## Usage

This command processes all requests for content transportation:

```bash
php cron/execute_orders.php
```

To create a new ticket create a .json file in the `cache` folder.

For example, 'export_NationGeographic.json'.

Content example:

```
{
	"source": {
		"tag": "telegram",
		"channelid": "NationGeographic"
	},
	"destination": {
		"tag": "utopia",
		"channelid": "02FAD8A273A72241F2D5B725742B53B0"
	},
	"params": {
		"limit": 1
	}
}
```

where
* `tag`: technical parameter, messenger name;
* `channelid`: channel or group ID in the messenger;
* `source` section: where do we get the posts;
* `destination` section: where we will post posts;
* `params` section: other parameters;
* `limit`: number of posts for transportation;

## Channel Limit Problem

In Utopia, you can create a maximum of 10 channels. There is a way to solve this problem. You will need to create additional accounts on which you will create channels. Next, you add the bot account to these channels and give it moderator rights.

## other

This content transport request will be made the next time the script `cron/execute_orders.php` is called.

You will also need to enter data for authorization in the file `.env`


---

![image](https://github.com/Sagleft/Sagleft/raw/master/image.png)

### :globe_with_meridians: [Telegram канал](https://t.me/+VIvd8j6xvm9iMzhi)
