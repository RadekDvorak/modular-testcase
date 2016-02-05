<?php

namespace Damejidlo\ModularTestCase\Module\Dumper;

use Damejidlo\ModularTestCase\LifeCycle;
use Damejidlo\ModularTestCase\Module\IModule;



class EchoModule implements IModule
{

	/**
	 * @inheritdoc
	 */
	public function listen(LifeCycle $lifeCycle)
	{
		$lifeCycle->onSetUp[] = function () {
			echo 'setUp, ';
		};
		$lifeCycle->onSuccess[] = function () {
			echo 'success, ';
		};

		$lifeCycle->onException[] = function () {
			echo 'exception, ';
		};

		$lifeCycle->onFinally[] = function () {
			echo 'finally, ';
		};

		$lifeCycle->onTearDown[] = function () {
			echo 'tearDown, ';
		};
		$lifeCycle->onShutDown[] = function () {
			echo 'shutdown.';
		};
	}



	/**
	 * @inheritdoc
	 */
	public function setParameters(array $args)
	{
		echo implode('|', $args), ', ';
	}

}
