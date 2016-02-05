<?php
/**
 * @testCase
 */

namespace DamejidloTests\ModularTestCase\Unit\Module\Database;

require_once __DIR__ . '/../../../bootstrap.php';

use Damejidlo\ModularTestCase\Module\Database\DataLoader;
use Kdyby\Doctrine\Connection;
use Mockery;
use Mockery\MockInterface;
use Tester\Environment;
use Tester\TestCase;



class DataLoaderTest extends TestCase
{

	/**
	 * @internal
	 */
	const SQL_FILES = [
		'foo.sql',
		'bar/baz.sql',
	];



	public function testLoadFiles()
	{
		$dataLoader = new DataLoader();
		$connection = $this->mockConnection();

		$kdybyLoader = Mockery::mock('alias:Kdyby\Doctrine\Helpers');
		$kdybyLoader->shouldReceive('loadFromFile')->twice()->with($connection, Mockery::type('string'));

		$dataLoader->loadFiles($connection, self::SQL_FILES);

		Environment::$checkAssertions = FALSE;
	}



	protected function tearDown()
	{
		parent::tearDown();
		Mockery::close();
	}



	/**
	 * @return Connection|MockInterface
	 */
	private function mockConnection()
	{
		return Mockery::mock(Connection::class);
	}

}



(new DataLoaderTest())->run();
