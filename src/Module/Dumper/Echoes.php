<?php

namespace Damejidlo\ModularTestCase\Module\Dumper;

use Damejidlo\ModularTestCase\Module\ITestAnnotation;


/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Echoes implements ITestAnnotation
{

	/**
	 * @inheritdoc
	 */
	public function getModuleType()
	{
		return EchoModule::class;
	}



	/**
	 * @inheritdoc
	 */
	public function getArguments()
	{
		return ["foo", "bar"];
	}

}
