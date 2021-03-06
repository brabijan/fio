<?php

namespace h4kuna\Fio\Nette\DI;

use h4kuna\Fio\Utils\FioException,
	Nette\DI\CompilerExtension,
	Nette\Utils;

class FioExtension extends CompilerExtension
{

	public $defaults = array(
		'account' => NULL,
		'token' => NULL,
		'temp' => '%tempDir%/fio',
		'transactionClass' => '\h4kuna\Fio\Response\Read\Transaction'
	);

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if (!$config['token']) {
			throw new FioException('Token is required');
		}

		if (!$config['account']) {
			throw new FioException('Account is required');
		}

		Utils\FileSystem::createDir($config['temp']);

		// XMLFile
		$builder->addDefinition($this->prefix('xmlFile'))
				->setClass('h4kuna\Fio\Request\Pay\XMLFile')
				->setArguments(array($config['temp']));

		// PaymentFactory
		$builder->addDefinition($this->prefix('paymentFactory'))
				->setClass('h4kuna\Fio\Request\Pay\PaymentFactory')
				->setArguments(array($config['account']));

		// Queue
		$builder->addDefinition($this->prefix('queue'))
				->setClass('h4kuna\Fio\Request\Queue')
				->setArguments(array($config['temp']));

		// Context
		$builder->addDefinition($this->prefix('context'))
				->setClass('h4kuna\Fio\Utils\Context')
				->setArguments(array($config['token']));

		// StatementFactory
		$builder->addDefinition($this->prefix('statementFactory'))
				->setClass('h4kuna\Fio\Response\Read\JsonStatementFactory')
				->setArguments(array($config['transactionClass']));

		// FioPay
		$builder->addDefinition($this->prefix('fioPay'))
				->setClass('h4kuna\Fio\FioPay');

		// FioRead
		$builder->addDefinition($this->prefix('fioRead'))
				->setClass('h4kuna\Fio\FioRead')
				->setArguments(array($this->prefix('@context')));
	}

}
