<?php

namespace Damejidlo\ModularTestCase\Module\TransactionIsolation;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use PDOException;



class MySqlDriver extends Driver
{

	/**
	 * {@inheritdoc}
	 */
	public function connect(array $params, $username = NULL, $password = NULL, array $driverOptions = [])
	{
		try {
			// create our special driver
			$conn = new PDOConnection(
				$this->constructPdoDsn($params),
				$username,
				$password,
				$driverOptions
			);
		} catch (PDOException $e) {
			throw DBALException::driverException($this, $e);
		}

		return $conn;
	}

}
