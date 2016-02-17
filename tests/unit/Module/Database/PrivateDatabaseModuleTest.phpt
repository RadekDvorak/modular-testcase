<?php
/**
 * @testCase
 */

namespace DamejidloTests\ModularTestCase\Unit\Module\Database;

require_once __DIR__ . '/../../../bootstrap.php';

use Damejidlo\ModularTestCase\LifeCycle;
use Damejidlo\ModularTestCase\Module\Database\DataLoader;
use Damejidlo\ModularTestCase\Module\Database\PrivateDatabaseModule;
use Damejidlo\ModularTestCase\ProcessIdProvider;
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
	 * @param int $initializationCount
	 */
	public function testDatabaseSetup($eventNames, $initializationCount)
	{
		$lifeCycle = new LifeCycle();

		$schemaManager = $this->mockSchemaManager();
		$connection = $this->mockConnection($schemaManager);
		$dataLoader = $this->mockDataLoader($initializationCount);
		$this->createPrivateDatabaseModule($connection, $lifeCycle, $dataLoader);

		$schemaManager->shouldReceive('dropAndCreateDatabase')->times($initializationCount);
		$connection->shouldReceive('exec')->with('/^USE /')->times($initializationCount);

		foreach ($eventNames as $name) {
			call_user_func([$lifeCycle, $name]);
		}

		Environment::$checkAssertions = FALSE;
	}



	public function getDatabaseSetUpEvents()
	{
		return [
			[['onInitialized'], 1],
			[['onSetUp'], 1],
			[['onInitialized', 'onSetUp'], 1],
			[['onInitialized', 'onSetUp', 'onTearDown', 'onSetUp', 'onTearDown', 'onSetUp', 'onTearDown'], 3],
		];
	}



	public function testDatabaseTearDown()
	{
		$lifeCycle = new LifeCycle();

		$schemaManager = $this->mockSchemaManager();
		$connection = $this->mockConnection($schemaManager);
		$dataLoader = $this->mockDataLoader();
		$this->createPrivateDatabaseModule($connection, $lifeCycle, $dataLoader);

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
	 * @param DataLoader $dataLoader
	 * @return PrivateDatabaseModule
	 */
	private function createPrivateDatabaseModule(Connection $connection, LifeCycle $lifeCycle, DataLoader $dataLoader)
	{
		$processIdProvider = $this->mockProcessIdProvider();
		$module = new PrivateDatabaseModule($processIdProvider, $connection, $dataLoader, self::SQL_FILES, self::DB_PREFIX);

		$module->listen($lifeCycle);
		$module->setParameters([]);

		return $module;
	}



	/**
	 * @return ProcessIdProvider|MockInterface
	 */
	private function mockProcessIdProvider()
	{
		/** @var ProcessIdProvider|MockInterface $posix */
		$posix = Mockery::mock(ProcessIdProvider::class);
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
	private function mockDataLoader($callCount = 1)
	{
		/** @var DataLoader|MockInterface $dataLoader */
		$dataLoader = Mockery::mock(DataLoader::class);
		$dataLoader->shouldReceive('loadFiles')->times($callCount);

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
