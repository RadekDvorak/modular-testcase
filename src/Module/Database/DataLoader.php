<?php

namespace Damejidlo\ModularTestCase\Module\Database;

use Kdyby\Doctrine\Connection;
use Kdyby\Doctrine\Helpers;



class DataLoader
{

	/**
	 * @param Connection $connection
	 * @param string[] $files
	 */
	public function loadFiles(Connection $connection, array $files)
	{
		foreach ($files as $file) {
			Helpers::loadFromFile($connection, $file);
		}
	}

}
