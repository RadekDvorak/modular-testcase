<?php

namespace Damejidlo\ModularTestCase\Lock;

use Damejidlo\ModularTestCase\Exception\RuntimeException;



class FileLock implements ILock
{

	/**
	 * @var resource[]
	 */
	private $handles = [];



	/**
	 * @inheritdoc
	 */
	public function acquire($file)
	{
		$handle = $this->getHandle($file);
		$isLocked = flock($handle, LOCK_EX);

		if (!$isLocked) {
			fclose($handle);
			throw new RuntimeException('Failed to acquire lock');
		}
	}



	/**
	 * @inheritdoc
	 */
	public function release($file)
	{
		$handle = $this->getHandle($file);
		flock($handle, LOCK_UN);
		fclose($handle);
	}



	/**
	 * @param string $file
	 * @return resource
	 * @throws RuntimeException
	 */
	private function getHandle($file)
	{
		if (!isset($this->handles[$file]) || !is_resource($this->handles[$file])) {
			$this->handles[$file] = fopen($file, 'r');

			if ($this->handles[$file] === FALSE) {
				$message = sprintf('Failed to open file "%s"', $file);
				throw new RuntimeException($message);
			}
		}

		return $this->handles[$file];
	}

}
