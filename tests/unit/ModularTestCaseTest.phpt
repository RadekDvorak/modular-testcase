<?php
/**
 * @testCase
 */

namespace DamejidloTests\ModularTestCase\Unit;

require_once __DIR__ . '/../bootstrap.php';

use Damejidlo\ModularTestCase\ContainerFactory\IContainerFactory;
use Damejidlo\ModularTestCase\ModularTestCase;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;



class ModularTestCaseTest extends TestCase
{

	public function testContainerFactoryProperty()
	{
		$testCase = new ModularTestCase();

		$original = $testCase->getContainerFactory();
		Assert::type(IContainerFactory::class, $original);

		/** @var IContainerFactory|MockInterface $containerFactory */
		$containerFactory = Mockery::mock(IContainerFactory::class);
		$testCase->setContainerFactory($containerFactory);
		$actual = $testCase->getContainerFactory();

		Assert::same($containerFactory, $actual);
	}

}



(new ModularTestCaseTest())->run();
