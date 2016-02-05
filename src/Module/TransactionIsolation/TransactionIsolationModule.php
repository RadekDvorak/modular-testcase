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
		$lifeCycle->onSetUp[] = function () {
			$this->startTransactionWrapper();
		};

		$lifeCycle->onSuccess[] = function () {
			$this->stopTransactionWrapper();
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
		/** @var IWrappedConnection|Connection $connection */
		$connection = $this->entityManager->getConnection();
		$this->validateConnection($connection);
		$connection->wrap();

		if ($this->isImplicitFlushPrevented) {
			$wrappedConnection = $this->getWrappedPDOConnection();
			$wrappedConnection->wrap();
		}
	}



	private function stopTransactionWrapper()
	{
		if ($this->isImplicitFlushPrevented) {
			$wrappedConnection = $this->getWrappedPDOConnection();
			$wrappedConnection->unwrap();
		}

		/** @var IWrappedConnection|Connection $connection */
		$connection = $this->entityManager->getConnection();
		$connection->unwrap();

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
