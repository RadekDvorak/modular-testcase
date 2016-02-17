<?php

namespace Damejidlo\ModularTestCase;

use Nette\Object;



/**
 * @method onInitialized()
 * @method onSetUp()
 * @method onSuccess()
 * @method onException()
 * @method onFinally()
 * @method onTearDown()
 * @method onShutDown()
 */
class LifeCycle extends Object
{

	/**
	 * @var \Closure[]
	 */
	public $onInitialized = [];

	/**
	 * @var \Closure[]
	 */
	public $onSetUp = [];

	/**
	 * @var \Closure[]
	 */
	public $onSuccess = [];

	/**
	 * @var \Closure[]
	 */
	public $onException = [];

	/**
	 * @var \Closure[]
	 */
	public $onFinally = [];

	/**
	 * @var \Closure[]
	 */
	public $onTearDown = [];

	/**
	 * @var \Closure[]
	 */
	public $onShutDown = [];

}
