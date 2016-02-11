<?php

namespace Damejidlo\ModularTestCase\Module\Database;

use Damejidlo\ModularTestCase\LifeCycle;
use Damejidlo\ModularTestCase\Lock\ILock;
use Damejidlo\ModularTestCase\Module\IModule;
use Kdyby\Doctrine\Connection;



class SharedDatabaseModule implements IModule
{

	/**
	 * @var Connection
	 */
	private $connection;

	/**
	 * @var string[]
	 */
	private $tableSqlFiles;

	/**
	 * @var string
	 */
	private $namePrefix;

	/**
	 * @var ILock
	 */
	private $lock;

	/**
	 * @var DataLoader
	 */
	private $dataLoader;



	/**
	 * @param Connection $connection
	 * @param ILock $lock
	 * @param DataLoader $dataLoader
	 * @param string[] $tableSqlFiles
	 * @param string $namePrefix
	 */
	public function __construct(Connection $connection, ILock $lock, DataLoader $dataLoader, array $tableSqlFiles, $namePrefix)
	{
		$this->connection = $connection;
		$this->lock = $lock;
		$this->dataLoader = $dataLoader;
		$this->tableSqlFiles = $tableSqlFiles;
		$this->namePrefix = $namePrefix;
	}



	/**
	 * @inheritdoc
	 */
	public function listen(LifeCycle $lifeCycle)
	{
		$lifeCycle->onInitialized[] = function () {
			$this->createDatabase();
		};
	}



	/**
	 * @inheritdoc
	 */
	public function setParameters(array $args)
	{
	}



	private function createDatabase()
	{
		$databaseName = $this->namePrefix . '_shared';
		$schemaManager = $this->connection->getSchemaManager();

		$this->lock->acquire(__FILE__);

		$isDatabaseCreated = in_array($databaseName, $schemaManager->listDatabases(), TRUE);

		if ($isDatabaseCreated) {
			$this->lock->release(__FILE__);
			$this->connection->exec("USE `$databaseName`");
		} else {
			try {
				$schemaManager->createDatabase($databaseName);
				$this->connection->exec("USE `$databaseName`");
				$this->dataLoader->loadFiles($this->connection, $this->tableSqlFiles);
			} catch (\Exception $e) {
				$schemaManager->dropDatabase($databaseName);
				throw $e;
			} finally {
				$this->lock->release(__FILE__);
			}
		}
	}

}
