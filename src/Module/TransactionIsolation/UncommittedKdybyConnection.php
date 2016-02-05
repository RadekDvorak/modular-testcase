<?php

namespace Damejidlo\ModularTestCase\Module\TransactionIsolation;

use Damejidlo\ModularTestCase\Exception\LogicException;
use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\ConnectionException;
use Kdyby\Doctrine\Connection;



class UncommittedKdybyConnection extends Connection implements IWrappedConnection
{

	/**
	 * @var int
	 */
	private $oldIsolationLevel;

	/**
	 * @var bool
	 */
	private $isWrapped = FALSE;



	/**
	 * @inheritdoc
	 * @throws ConnectionException
	 */
	public function commit()
	{
		if ($this->isWrapped && parent::getTransactionNestingLevel() === 1) {
			throw ConnectionException::noActiveTransaction();
		}
		parent::commit();
	}



	/**
	 * @inheritdoc
	 * @throws ConnectionException
	 */
	public function rollBack()
	{
		if ($this->isWrapped && parent::getTransactionNestingLevel() === 1) {
			throw ConnectionException::noActiveTransaction();
		}
		parent::rollBack();
	}



	/**
	 * @inheritdoc
	 */
	public function getTransactionNestingLevel()
	{
		$transactionNestingLevel = parent::getTransactionNestingLevel();

		if ($this->isWrapped) {
			$transactionNestingLevel--;
		}

		return $transactionNestingLevel;
	}



	/**
	 * @inheritdoc
	 */
	public function setTransactionIsolation($level)
	{
		if ($this->isWrapped) {
			throw new LogicException('Isolation level may not be changed in isolated sessions.');
		}
	}



	/**
	 * @inheritdoc
	 */
	public function setAutoCommit($autoCommit)
	{
		if ($this->isWrapped) {
			throw new LogicException('Auto commit may not be changed in isolated sessions.');
		}

		return parent::setAutoCommit($autoCommit);
	}



	public function wrap()
	{
		if ($this->isWrapped) {
			throw new LogicException('The connection has been already wrapped');
		}

		$this->isWrapped = TRUE;
		$this->oldIsolationLevel = parent::getTransactionIsolation();
		parent::setTransactionIsolation(DBALConnection::TRANSACTION_SERIALIZABLE);
		parent::beginTransaction();
	}



	public function unwrap()
	{
		parent::rollBack();
		parent::setTransactionIsolation($this->oldIsolationLevel);
		$this->isWrapped = FALSE;
	}

}
