<?php

namespace Damejidlo\ModularTestCase\Module\Database;

use Damejidlo\ModularTestCase\Module\ITestAnnotation;
use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Annotations\Annotation\Required;



/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Database implements ITestAnnotation
{

	const MODE_PRIVATE = 'PRIVATE';
	const MODE_SHARED = 'SHARED';

	/**
	 * @internal
	 */
	const MODULES = [
		self::MODE_SHARED => SharedDatabaseModule::class,
		self::MODE_PRIVATE => PrivateDatabaseModule::class,
	];

	/**
	 * @Enum({"PRIVATE", "SHARED"})
	 * @Required()
	 */
	public $mode;



	/**
	 * @inheritdoc
	 */
	public function getModuleType()
	{
		return self::MODULES[$this->mode];
	}



	/**
	 * @inheritdoc
	 */
	public function getArguments()
	{
		return [];
	}

}
