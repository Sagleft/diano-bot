
![logo](https://github.com/Sagleft/diano-bot/raw/master/logo.png)

This bot allows you to periodically copy content from one messenger to another.

Supported Clients:

* [Telegram](https://telegram.org/);
* [Utopia](https://u.is/).

## Requirements

* PHP 7.1+
* php-composer
* cron

## Installation

```bash
cd ~
git clone https://github.com/Sagleft/diano-bot
cd diano-bot
mkdir cache
cp example.env .env
composer update
```

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
