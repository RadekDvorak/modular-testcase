<?php

namespace Damejidlo\ModularTestCase\ContainerFactory;

use Damejidlo\ModularTestCase\Exception\UnsupportedException;



class NullContainerFactory implements IContainerFactory
{

	/**
	 * @inheritdoc
	 */
	public function createContainer()
	{
		throw new UnsupportedException('Set container factory in ModularTestCase::setContainerFactory().');
	}

}
