<?php

/**
 * @copyright   Copyright (c) 2017 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\PredisClientNetteExtension\DI;

use Nette\DI\CompilerExtension;
use Nette\Http\Session;
use Predis\Client;
use Predis\Session\Handler;

class PredisClientNetteExtension extends CompilerExtension
{

	private $defaults = [
		/**
		 * Or UNIX socket: 'unix:/path/to/redis.sock' or other options
		 * @see https://github.com/nrk/predis#connecting-to-redis
		 */
		'uri' => 'tcp://127.0.0.1:6379',
		'options' => [],
		'sessions' => false,
		'sessionsTtl' => null,
	];

	/**
	 * @var array
	 */
	protected $config;


	/**
	 * @return void
	 */
	public function loadConfiguration()
	{
		$this->config = $this->validateConfig($this->defaults, $this->config);
	}


	/**
	 * @return void
	 */
	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$client = $builder->addDefinition($this->prefix('client'))
			->setClass(Client::class)
			->setArguments([$this->config['uri'], $this->config['options']]);

		if ($this->config['sessions']) {
			$sessionHandler = $builder->addDefinition($this->prefix('sessionHandler'))
				->setClass(Handler::class)
				->setArguments([$client, ['gc_maxlifetime' => $this->config['sessionsTtl']]]);

			$builder->getDefinition($builder->getByType(Session::class))
				->addSetup('setHandler', [$sessionHandler]);
		}
	}
}
