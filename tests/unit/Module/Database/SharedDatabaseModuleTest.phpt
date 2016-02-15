<?php
/**
 * @testCase
 */

namespace DamejidloTests\ModularTestCase\Unit\Module\Database;

require_once __DIR__ . '/../../../bootstrap.php';

use Damejidlo\ModularTestCase\Exception\RuntimeException;
use Damejidlo\ModularTestCase\LifeCycle;
use Damejidlo\ModularTestCase\Lock\ILock;
use Damejidlo\ModularTestCase\Module\Database\DataLoader;
use Damejidlo\ModularTestCase\Module\Database\SharedDatabaseModule;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Kdyby\Doctrine\Connection;
use Mockery;
use Mockery\MockInterface;
use Tester\Environment;
use Tester\TestCase;



class SharedDatabaseModuleTest extends TestCase
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



	public function testDatabaseAlreadyExists()
	{
		$lifeCycle = new LifeCycle();

		$schemaManager = $this->mockSchemaManager([self::DB_PREFIX . '_shared']);
		$connection = $this->mockConnection($schemaManager, TRUE);
		$lock = $this->mockLock();
		$dataLoader = $this->mockDataLoader();
		$this->createSharedDatabaseModule($connection, $lock, $dataLoader, $lifeCycle);

		$lifeCycle->onInitialized();

		Environment::$checkAssertions = FALSE;
	}



	public function testDatabaseCreation()
	{
		$lifeCycle = new LifeCycle();

		$schemaManager = $this->mockSchemaManager([]);
		$connection = $this->mockConnection($schemaManager, TRUE);
		$lock = $this->mockLock();
		$dataLoader = $this->mockDataLoader(TRUE);
		$this->createSharedDatabaseModule($connection, $lock, $dataLoader, $lifeCycle);

		$schemaManager->shouldReceive('createDatabase')->once();

		$lifeCycle->onInitialized();

		Environment::$checkAssertions = FALSE;
	}



	/**
	 * @throws \RuntimeException
	 */
	public function testDatabaseCreationFailure()
	{
		$lifeCycle = new LifeCycle();

		$schemaManager = $this->mockSchemaManager(['mysql', 'information_schema']);
		$connection = $this->mockConnection($schemaManager);
		$lock = $this->mockLock();
		$dataLoader = $this->mockDataLoader();
		$this->createSharedDatabaseModule($connection, $lock, $dataLoader, $lifeCycle);

		$schemaManager->shouldReceive('createDatabase')->once()->andThrow(new \RuntimeException('No No No'));
		$schemaManager->shouldReceive('dropDatabase');

		$lifeCycle->onInitialized();

		Environment::$checkAssertions = FALSE;
	}



	/**
	 * @throws \Damejidlo\ModularTestCase\Exception\RuntimeException
	 */
	public function testLockingFailure()
	{
		$lifeCycle = new LifeCycle();

		$schemaManager = $this->mockSchemaManager(['mysql', 'information_schema']);
		$connection = $this->mockConnection($schemaManager);
		$lock = $this->mockLock(TRUE);
		$dataLoader = $this->mockDataLoader();
		$this->createSharedDatabaseModule($connection, $lock, $dataLoader, $lifeCycle);

		$lifeCycle->onInitialized();

		Environment::$checkAssertions = FALSE;
	}



	protected function tearDown()
	{
		parent::tearDown();
		Mockery::close();
	}



	/**
	 * @param Connection $connection
	 * @param ILock $lock
	 * @param LifeCycle $lifeCycle
	 * @return SharedDatabaseModule
	 */
	private function createSharedDatabaseModule(Connection $connection, ILock $lock, DataLoader $dataLoader, LifeCycle $lifeCycle)
	{
		$module = new SharedDatabaseModule($connection, $lock, $dataLoader, self::SQL_FILES, self::DB_PREFIX);

		$module->listen($lifeCycle);
		$module->setParameters([]);

		return $module;
	}



	/**
	 * @param bool $isExceptionThrown
	 * @return ILock|MockInterface
	 */
	private function mockLock($isExceptionThrown = FALSE)
	{
		/** @var ILock|MockInterface $lock */
		$lock = Mockery::mock(ILock::class);

		$acquiringExpectation = $lock->shouldReceive('acquire')->once();

		if ($isExceptionThrown) {
			$acquiringExpectation->andThrow(new RuntimeException);
		} else {
			$lock->shouldReceive('release')->once();
		}

		return $lock;
	}



	/**
	 * @param AbstractSchemaManager $schemaManager
	 * @param bool $isDatabaseUseExpected
	 * @return Connection|MockInterface
	 */
	private function mockConnection(AbstractSchemaManager $schemaManager, $isDatabaseUseExpected = FALSE)
	{
		/** @var Connection|MockInterface $connection */
		$connection = Mockery::mock(Connection::class);
		$connection->shouldReceive('getSchemaManager')->andReturn($schemaManager);

		if ($isDatabaseUseExpected) {
			$connection->shouldReceive('exec')->with('/^USE /')->once();
		}

		return $connection;
	}



	/**
	 * @param bool $isLoadingExpected
	 * @return DataLoader|MockInterface
	 */
	private function mockDataLoader($isLoadingExpected = FALSE)
	{
		/** @var DataLoader|MockInterface $dataLoader */
		$dataLoader = Mockery::mock(DataLoader::class);

		if ($isLoadingExpected) {
			$dataLoader->shouldReceive('loadFiles')->once();
		}

		return $dataLoader;
	}



	/**
	 * @param string[] $databaseList
	 * @return AbstractSchemaManager|MockInterface
	 */
	private function mockSchemaManager(array $databaseList)
	{
		/** @var AbstractSchemaManager|MockInterface $schemaManager */
		$schemaManager = Mockery::mock(AbstractSchemaManager::class);

		$schemaManager->shouldReceive('listDatabases')->andReturn($databaseList);

		return $schemaManager;
	}

}



(new SharedDatabaseModuleTest())->run();
