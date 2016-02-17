<?php

namespace Damejidlo\ModularTestCase\Module\Database;

use Damejidlo\ModularTestCase\LifeCycle;
use Damejidlo\ModularTestCase\Module\IModule;
use Damejidlo\ModularTestCase\ProcessIdProvider;
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
	private $schemaFiles;

	/**
	 * @var string
	 */
	private $namePrefix;

	/**
	 * @var ProcessIdProvider
	 */
	private $processIdProvider;

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
	 * @param ProcessIdProvider $processIdProvider
	 * @param Connection $connection
	 * @param DataLoader $dataLoader
	 * @param string[] $schemaFiles
	 * @param string $namePrefix
	 */
	public function __construct(
		ProcessIdProvider $processIdProvider,
		Connection $connection,
		DataLoader $dataLoader,
		array $schemaFiles,
		$namePrefix
	) {
		$this->processIdProvider = $processIdProvider;
		$this->connection = $connection;
		$this->dataLoader = $dataLoader;
		$this->schemaFiles = $schemaFiles;
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

		$lifeCycle->onTearDown[] = function () {
			$this->isInitialized = FALSE;
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

		$databaseName = $this->databaseName = sprintf('%s_%d', $this->namePrefix, $this->processIdProvider->getPid());
		$schemaManager = $this->connection->getSchemaManager();

		$schemaManager->dropAndCreateDatabase($databaseName);
		$this->connection->exec("USE `$databaseName`");
		$this->dataLoader->loadFiles($this->connection, $this->schemaFiles);
		$this->isInitialized = TRUE;
	}



	private function tearDownDatabase()
	{
		if ($this->databaseName !== '') {
			$schemaManager = $this->connection->getSchemaManager();
			$schemaManager->dropDatabase($this->databaseName);
		}
	}

}
