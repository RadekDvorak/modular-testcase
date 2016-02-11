<?php

namespace Damejidlo\ModularTestCase\Module\Database;

use Damejidlo\ModularTestCase\LifeCycle;
use Damejidlo\ModularTestCase\Module\IModule;
use Damejidlo\ModularTestCase\Posix;
use Kdyby\Doctrine\Connection;



class PrivateDatabaseModule implements IModule
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
	 * @var Posix
	 */
	private $posix;

	/**
	 * @var string
	 */
	private $databaseName = '';

	/**
	 * @var DataLoader
	 */
	private $dataLoader;

	/**
	 * @var bool
	 */
	private $isInitialized = FALSE;



	/**
	 * @param Posix $posix
	 * @param Connection $connection
	 * @param DataLoader $dataLoader
	 * @param string[] $tableSqlFiles
	 * @param string $namePrefix
	 */
	public function __construct(Posix $posix, Connection $connection, DataLoader $dataLoader, array $tableSqlFiles, $namePrefix)
	{
		$this->posix = $posix;
		$this->connection = $connection;
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

		$lifeCycle->onSetUp[] = function () {
			$this->createDatabase();
		};

		$lifeCycle->onShutDown[] = function () {
			$this->tearDownDatabase();
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
		if ($this->isInitialized) {
			return;
		}

		$databaseName = $this->databaseName = sprintf('%s_%d', $this->namePrefix, $this->posix->getPid());
		$schemaManager = $this->connection->getSchemaManager();

		$schemaManager->dropAndCreateDatabase($databaseName);
		$this->connection->exec("USE `$databaseName`");
		$this->dataLoader->loadFiles($this->connection, $this->tableSqlFiles);
		$this->isInitialized = TRUE;
	}



	private function tearDownDatabase()
	{
		if ($this->databaseName !== '') {
			$schemaManager = $this->connection->getSchemaManager();
			$schemaManager->dropDatabase($this->databaseName);
		}
		$this->isInitialized = FALSE;
	}

}
