[![Latest Stable Version](https://poser.pugx.org/ublaboo/predis-client-nette-extension/v/stable)](https://packagist.org/packages/ublaboo/predis-client-nette-extension)
[![License](https://poser.pugx.org/ublaboo/predis-client-nette-extension/license)](https://packagist.org/packages/ublaboo/predis-client-nette-extension)
[![Total Downloads](https://poser.pugx.org/ublaboo/predis-client-nette-extension/downloads)](https://packagist.org/packages/ublaboo/predis-client-nette-extension)
[![Gitter](https://img.shields.io/gitter/room/nwjs/nw.js.svg)](https://gitter.im/ublaboo/help)

# ublaboo/predis-client-nette-extension

Nette DIC extension for [predis/predis](https://github.com/nrk/predis) client

## Installation

Download extension using composer

```
composer require ublaboo/predis-client-nette-extension
```

Register extension in your config.neon file:

```yaml 
extensions:
    predisClient: Ublaboo\PredisClientNetteExtension\DI\PredisClientNetteExtension
```

## Configuration

Configure extension in your `config.neon` file:

```yaml
predisClient:
    uri: 'tcp://127.0.0.1:32768'
    options:
        prefix: 'fooPrefix:'
        # other options
    sessions: true # Whether to register redis session handler or not
    sessionsTtl: null # Seconds or null (null = ini_get('session.gc_maxlifetime'))
```

## Usage

```php
<?php

declare(strict_types=1);

use Predis\Client;

class Foo
{

	/**
	 * @var Client
	 */
	public $redisClient;


	public function __construct(Client $redisClient)
	{
		$this->redisClient = $redisClient;
	}


	public function save(string $key, string $value): void
	{
		$this->redisClient->set($key, $value);
	}
	
	
	public function retrive(string $key): ?string
	{
		return $this->redisClient->get($key);
	}

}
```
