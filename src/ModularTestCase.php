<?php

namespace Damejidlo\ModularTestCase;

use Damejidlo\ModularTestCase\ContainerFactory\IContainerFactory;
use Damejidlo\ModularTestCase\ContainerFactory\NullContainerFactory;
use Damejidlo\ModularTestCase\Module\IModule;
use Damejidlo\ModularTestCase\Module\ITestAnnotation;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Tester\TestCase;



class ModularTestCase extends TestCase
{

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var LifeCycle
	 */
	private $lifeCycle;

	/**
	 * @var IContainerFactory
	 */
	private $containerFactory;



	public function __construct()
	{
		$this->lifeCycle = new LifeCycle();
		$this->containerFactory = new NullContainerFactory();

		AnnotationRegistry::registerLoader(function ($class) {
			return class_exists($class, TRUE);
		});

		register_shutdown_function(function () {
			$this->lifeCycle->onShutDown();
		});
	}



	/**
	 * @inheritdoc
	 */
	public function runTest($method, array $args = NULL)
	{
		try {
			$this->initializeModules($method);
			parent::runTest($method, $args);
			$this->lifeCycle->onSuccess();
		} catch (\Exception $e) {
			$this->lifeCycle->onException();
			throw $e;
		} finally {
			$this->lifeCycle->onFinally();
		}
	}



	/**
	 * @param string $type
	 * @return object
	 */
	protected function getService($type)
	{
		$container = $this->getContainer();
		$object = $container->getByType($type, FALSE);
		if (!$object) {
			$object = $container->createInstance($type);
		}

		return $object;
	}



	/**
	 * @return IContainerFactory
	 */
	public function getContainerFactory()
	{
		return $this->containerFactory;
	}



	/**
	 * @param IContainerFactory $containerFactory
	 */
	public function setContainerFactory($containerFactory)
	{
		$this->containerFactory = $containerFactory;
	}



	protected function setUp()
	{
		parent::setUp();

		$this->lifeCycle->onSetUp();
	}



	protected function tearDown()
	{
		$this->lifeCycle->onTearDown();

		parent::tearDown();
	}



	/**
	 * @return Container
	 */
	protected function getContainer()
	{
		if ($this->container === NULL) {
			$this->container = $this->containerFactory->createContainer();
		}

		return $this->container;
	}



	/**
	 * @param string $methodName
	 * @throws MissingServiceException
	 */
	private function initializeModules($methodName)
	{
		/** @var Reader $reader */
		$reader = $this->getContainer()->getByType(Reader::class);

		$method = new \ReflectionMethod($this, $methodName);
		$annotations = $reader->getMethodAnnotations($method);

		foreach ($annotations as $annotation) {
			if ($annotation instanceof ITestAnnotation) {
				$this->loadModule($annotation);
			}
		}
	}



	/**
	 * @param ITestAnnotation $annotation
	 */
	private function loadModule(ITestAnnotation $annotation)
	{
		$moduleType = $annotation->getModuleType();
		/** @var IModule $module */
		$module = $this->getContainer()->getByType($moduleType);
		$module->setParameters($annotation->getArguments());
		$module->listen($this->lifeCycle);
	}

}
