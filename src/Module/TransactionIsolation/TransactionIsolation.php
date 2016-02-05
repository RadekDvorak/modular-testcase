<?php

namespace Damejidlo\ModularTestCase\Module\TransactionIsolation;

use Damejidlo\ModularTestCase\Module\ITestAnnotation;
use Doctrine\Common\Annotations\Annotation\Target;



/**
 * @Annotation
 * @Target({"METHOD"})
 */
class TransactionIsolation implements ITestAnnotation
{

	/**
	 * @var bool
	 */
	public $preventImplicitFlush = TRUE;



	/**
	 * @inheritdoc
	 */
	public function getModuleType()
	{
		return TransactionIsolationModule::class;
	}



	/**
	 * @inheritdoc
	 */
	public function getArguments()
	{
		return [$this->preventImplicitFlush];
	}

}
