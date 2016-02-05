<?php

namespace Damejidlo\ModularTestCase\Module;



interface ITestAnnotation
{

	/**
	 * @return string
	 */
	public function getModuleType();



	/**
	 * @return array
	 */
	public function getArguments();

}
