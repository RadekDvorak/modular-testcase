<?php

namespace Damejidlo\ModularTestCase\Module\TransactionIsolation;

use Damejidlo\ModularTestCase\Exception\LogicException;
use PDO;



class PDOConnection extends PDO implements IWrappedConnection
{

	/**
	 * @internal
	 */
	const IMPLICIT_COMMIT_PHRASES = [
		'ALTER',
		'CREATE',
		'DROP',
		'INSTALL',
		'RENAME',
		'TRUNCATE',
		'UNINSTALL',
		'GRANT',
		'REVOKE',
		'SET[\s]+PASSWORD',
		'LOCK',
		'UNLOCK',
		'ANALYZE',
		'CHECK',
		'LOAD',
		'OPTIMIZE',
		'RESET',
		'REPAIR',
		'START',
		'STOP',
		'CHANGE'
	];

	private $isWrapped = FALSE;



	/**
	 * @inheritdoc
	 */
	public function prepare($prepareString, $options = NULL)
	{
		$this->validateQuery($prepareString);

		return parent::prepare($prepareString);
	}



	/**
	 * @inheritdoc
	 */
	public function query($statement)
	{
		$this->validateQuery($statement);
		$args = func_get_args();

		return parent::query(...$args);
	}



	/**
	 * @inheritdoc
	 */
	public function exec($statement)
	{
		$this->validateQuery($statement);

		return parent::exec($statement);
	}



	public function wrap()
	{
		$this->isWrapped = TRUE;
	}



	public function unwrap()
	{
		$this->isWrapped = FALSE;
	}



	/**
	 * @param string $sql
	 */
	private function validateQuery($sql)
	{
		if (!$this->isWrapped) {
			return;
		}

		$pattern = sprintf('~^[\s]*(%s)~i', implode('|', self::IMPLICIT_COMMIT_PHRASES));
		if (preg_match($pattern, $sql) > 0) {
			$message = sprintf('Query would cause implicit commit: "%s".', $sql);
			throw new LogicException($message);
		}
	}

}
