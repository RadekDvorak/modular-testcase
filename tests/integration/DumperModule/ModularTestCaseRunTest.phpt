<?php
/**
 * @outputMatch foo|bar, setUp, foo, tearDown, success, finally, shutdown.
 */

namespace DamejidloTests\ModularTestCase\Integration\DumperModule;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\ModularTestCase\ContainerFactory\DefaultContainerFactory;
use Damejidlo\ModularTestCase\ModularTestCase;
use Damejidlo\ModularTestCase\Module\Dumper\Echoes;
use Nette\Configurator;



class ModularTestCaseRunTest extends ModularTestCase
{

	/**
	 * @Echoes()
	 */
	public function testLifeCycle()
	{
		echo 'foo, ';
	}

}



$test = new ModularTestCaseRunTest();
$containerFactory = new DefaultContainerFactory(
	TEMP_DIR,
	[
		__DIR__ . '/config/config.neon' => Configurator::AUTO,
		__DIR__ . '/config/echo.neon' => NULL,
	]);
$test->setContainerFactory($containerFactory);
$test->run();
