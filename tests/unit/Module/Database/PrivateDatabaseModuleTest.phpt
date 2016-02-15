<?php
/**
 * @testCase
 */

namespace DamejidloTests\ModularTestCase\Unit\Module\Database;

require_once __DIR__ . '/../../../bootstrap.php';

use Damejidlo\ModularTestCase\LifeCycle;
use Damejidlo\ModularTestCase\Module\Database\DataLoader;
use Damejidlo\ModularTestCase\Module\Database\PrivateDatabaseModule;
use Damejidlo\ModularTestCase\Posix;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Kdyby\Doctrine\Connection;
use Mockery;
use Mockery\MockInterface;
use Tester\Environment;
use Tester\TestCase;



class PrivateDatabaseModuleTest extends TestCase
{

	/**
	 * @internal
	 */
	const MY_PID = 123;

	/**
	 * @internal
	 */
	const SQL_FILES = [
		'foo.sql',
		'bar/baz.sql',
	];

	/**
	 * @internal
	 */
	const DB_PREFIX = 'foo_prefix';



	/**
	 * @dataProvider getDatabaseSetUpEvents
	 * @param string[] $eventNames
	 */
	public function testDatabaseSetup($eventNames)
	{
		$lifeCycle = new LifeCycle();

		$schemaManager = $this->mockSchemaManager();
		$connection = $this->mockConnection($schemaManager);
		$this->createPrivateDatabaseModule($connection, $lifeCycle);

		$schemaManager->shouldReceive('dropAndCreateDatabase')->once();
		$connection->shouldReceive('exec')->with('/^USE /')->once();

		foreach ($eventNames as $name) {
			call_user_func([$lifeCycle, $name]);
		}

		Environment::$checkAssertions = FALSE;
	}



	public function getDatabaseSetUpEvents()
	{
		return [
			[['onInitialized']],
			[['onSetUp']],
			[['onInitialized', 'onSetUp']],
		];
	}



	public function testDatabaseTearDown()
	{
		$lifeCycle = new LifeCycle();

		$schemaManager = $this->mockSchemaManager();
		$connection = $this->mockConnection($schemaManager);
		$this->createPrivateDatabaseModule($connection, $lifeCycle);

		$schemaManager->shouldReceive('dropAndCreateDatabase');
		$connection->shouldReceive('exec');
		$schemaManager->shouldReceive('dropDatabase')->once();

		$lifeCycle->onSetUp();
		$lifeCycle->onShutDown();

		Environment::$checkAssertions = FALSE;
	}



	protected function tearDown()
	{
		parent::tearDown();
		Mockery::close();
	}



	/**
	 * @param Connection $connection
	 * @param LifeCycle $lifeCycle
	 * @return PrivateDatabaseModule
	 */
	private function createPrivateDatabaseModule(Connection $connection, LifeCycle $lifeCycle)
	{
		$posix = $this->mockPosix();
		$dataLoader = $this->mockDataLoader();
		$module = new PrivateDatabaseModule($posix, $connection, $dataLoader, self::SQL_FILES, self::DB_PREFIX);

		$module->listen($lifeCycle);
		$module->setParameters([]);

		return $module;
	}



	/**
	 * @return Posix|MockInterface
	 */
	private function mockPosix()
	{
		/** @var Posix|MockInterface $posix */
		$posix = Mockery::mock(Posix::class);
		$posix->shouldReceive('getPid')->andReturn(self::MY_PID);

		return $posix;
	}



	/**
	 * @param AbstractSchemaManager $schemaManager
	 * @return Connection|MockInterface
	 */
	private function mockConnection(AbstractSchemaManager $schemaManager)
	{
		/** @var Connection|MockInterface $connection */
		$connection = Mockery::mock(Connection::class);
		$connection->shouldReceive('getSchemaManager')->andReturn($schemaManager);

		return $connection;
	}



	/**
	 * @return DataLoader|MockInterface
	 */
	private function mockDataLoader()
	{
		/** @var DataLoader|MockInterface $dataLoader */
		$dataLoader = Mockery::mock(DataLoader::class);
		$dataLoader->shouldReceive('loadFiles')->once();

		return $dataLoader;
	}



	/**
	 * @return AbstractSchemaManager|MockInterface
	 */
	private function mockSchemaManager()
	{
		/** @var AbstractSchemaManager|MockInterface $schemaManager */
		$schemaManager = Mockery::mock(AbstractSchemaManager::class);

		return $schemaManager;
	}

}



(new PrivateDatabaseModuleTest())->run();
