<?php

namespace Damejidlo\ModularTestCase\Lock;

use Damejidlo\ModularTestCase\Exception\RuntimeException;



interface ILock
{

	/**
	 * @param string $file
	 * @throws RuntimeException
	 */
	public function acquire($file);



	/**
	 * @param string $file
	 */
	public function release($file);

}
