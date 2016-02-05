<?php

namespace Damejidlo\ModularTestCase;



class Posix
{

	/**
	 * @return int
	 */
	public function getPid()
	{
		return getmypid();
	}

}
