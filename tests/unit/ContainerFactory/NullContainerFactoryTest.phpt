<?php
/**
 * @testCase
 */

namespace DamejidloTests\ModularTestCase\Unit\ContainerFactory;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\ModularTestCase\ContainerFactory\NullContainerFactory;
use Tester\TestCase;



class NullContainerFactoryTest extends TestCase
{

	/**
	 * @throws \Damejidlo\ModularTestCase\Exception\UnsupportedException
	 */
	public function testCreateContainer()
	{
		$factory = new NullContainerFactory();
		$factory->createContainer();
	}

}



(new NullContainerFactoryTest())->run();
