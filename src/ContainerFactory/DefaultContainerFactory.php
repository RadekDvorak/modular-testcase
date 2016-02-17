<?php

namespace Damejidlo\ModularTestCase\ContainerFactory;

use Nette\Configurator;
use Nette\DI\Container;



class DefaultContainerFactory implements IContainerFactory
{

	/**
	 * @var string
	 */
	private $temporaryDirectory;

	/**
	 * @var array Map file => section
	 */
	private $configurationFiles;

	/**
	 * @var array
	 */
	private $parameters;



	/**
	 * @param string $temporaryDirectory
	 * @param array $configurationFiles
	 * @param array $parameters
	 */
	public function __construct($temporaryDirectory, array $configurationFiles, array $parameters = [])
	{
		$this->temporaryDirectory = $temporaryDirectory;
		$this->configurationFiles = $configurationFiles;
		$this->parameters = $parameters;
	}



	/**
	 * @return Container
	 */
	public function createContainer()
	{
		$config = new Configurator();
		$config->setTempDirectory($this->temporaryDirectory);
		$config->addParameters($this->parameters);

		foreach ($this->configurationFiles as $file => $section) {
			$config->addConfig($file, $section);
		}

		return $config->createContainer();
	}

}
