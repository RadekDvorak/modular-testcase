<?php

namespace Damejidlo\ModularTestCase;



class ProcessIdProvider
{

	/**
	 * @return int
	 */
	public function getPid()
	{
		return getmypid();
	}

}
