<?php
/**
 * @outputMatch foo|bar, initialized, setUp, foo, tearDown, exception, finally, shutdown.
 */

namespace DamejidloTests\ModularTestCase\Integration\DumperModule;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\ModularTestCase\ContainerFactory\DefaultContainerFactory;
use Damejidlo\ModularTestCase\ModularTestCase;
use Damejidlo\ModularTestCase\Module\Dumper\Echoes;
use Nette\Configurator;
use Tester\Assert;



class ModularTestCaseRunFailTest extends ModularTestCase
{

	/**
	 * @Echoes()
	 */
	public function testLifeCycle()
	{
		echo 'foo, ';
		throw new \RuntimeException('boo');
	}

}



Assert::exception(function () {
	$test = new ModularTestCaseRunFailTest();
	$containerFactory = new DefaultContainerFactory(
		TEMP_DIR,
		[
			__DIR__ . '/config/config.neon' => Configurator::AUTO,
			__DIR__ . '/config/echo.neon' => NULL,
		]);
	$test->setContainerFactory($containerFactory);
	$test->run();
}, \RuntimeException::class);
