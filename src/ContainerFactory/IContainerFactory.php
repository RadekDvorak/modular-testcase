<?php

namespace Damejidlo\ModularTestCase\ContainerFactory;

use Nette\DI\Container;



interface IContainerFactory
{

	/**
	 * @return Container
	 */
	public function createContainer();

}
