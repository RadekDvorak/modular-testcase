<?php

namespace Damejidlo\ModularTestCase\Module\TransactionIsolation;

use Damejidlo\ModularTestCase\Exception\LogicException;
use Damejidlo\ModularTestCase\LifeCycle;
use Damejidlo\ModularTestCase\Module\IModule;
use Doctrine\DBAL\Driver;
use Kdyby\Doctrine\Connection;
use Kdyby\Doctrine\EntityManager;



class TransactionIsolationModule implements IModule
{

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 * @var bool
	 */
	private $isImplicitFlushPrevented;

	/**
	 * @var bool
	 */
	private $isInitialized = FALSE;



	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}



	/**
	 * @inheritdoc
	 */
	public function listen(LifeCycle $lifeCycle)
	{
		$lifeCycle->onInitialized[] = function () {
			$this->startTransactionWrapper();
		};
		$lifeCycle->onSetUp[] = function () {
			$this->startTransactionWrapper();
		};

		$lifeCycle->onTearDown[] = function () {
			$this->stopTransactionWrapper();
			$this->entityManager->clear();
		};

		$lifeCycle->onShutDown[] = function () {
			$this->stopTransactionWrapper();
			$this->entityManager->clear();
		};
	}



	/**
	 * @inheritdoc
	 */
	public function setParameters(array $args)
	{
		list($preventImplicitFlush,) = $args;
		$this->isImplicitFlushPrevented = $preventImplicitFlush;
	}



	private function startTransactionWrapper()
	{
		if ($this->isInitialized) {
			return;
		}

		/** @var IWrappedConnection|Connection $connection */
		$connection = $this->entityManager->getConnection();
		$this->validateConnection($connection);
		$connection->wrap();

		if ($this->isImplicitFlushPrevented) {
			$wrappedConnection = $this->getWrappedPDOConnection();
			$wrappedConnection->wrap();
		}

		$this->isInitialized = TRUE;
	}



	private function stopTransactionWrapper()
	{
		if (!$this->isInitialized) {
			return;
		}

		if ($this->isImplicitFlushPrevented) {
			$wrappedConnection = $this->getWrappedPDOConnection();
			$wrappedConnection->unwrap();
		}

		/** @var IWrappedConnection|Connection $connection */
		$connection = $this->entityManager->getConnection();
		$connection->unwrap();

		$this->isInitialized = FALSE;
	}



	/**
	 * @param Connection $connection
	 */
	private function validateConnection(Connection $connection)
	{
		if (!$connection instanceof IWrappedConnection) {
			$message = sprintf('Set doctrine connection "wrapperClass" to %s.', UncommittedKdybyConnection::class);
			throw new LogicException($message);
		}
	}



	/**
	 * @return IWrappedConnection
	 */
	private function getWrappedPDOConnection()
	{
		$wrappedConnection = $this->entityManager->getConnection()->getWrappedConnection();
		if (!$wrappedConnection instanceof IWrappedConnection) {
			$message = sprintf('Change "preventImplicitFlush" to FALSE or set doctrine connection "driverClass" to %s.', MySqlDriver::class);
			throw new LogicException($message);
		}

		return $wrappedConnection;
	}

}
