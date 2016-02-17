<?php

namespace Damejidlo\ModularTestCase\Module;

use Damejidlo\ModularTestCase\LifeCycle;



interface IModule
{

	/**
	 * @param LifeCycle $lifeCycle
	 */
	public function listen(LifeCycle $lifeCycle);



	/**
	 * @param array $args
	 */
	public function setParameters(array $args);

}
