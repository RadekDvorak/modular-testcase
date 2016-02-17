<?php
/**
 * @testCase
 */

namespace DamejidloTests\ModularTestCase\Unit\Module\TransactionIsolation;

require_once __DIR__ . '/../../../bootstrap.php';

use Damejidlo\ModularTestCase\LifeCycle;
use Damejidlo\ModularTestCase\Module\TransactionIsolation\PDOConnection;
use Damejidlo\ModularTestCase\Module\TransactionIsolation\TransactionIsolationModule;
use Damejidlo\ModularTestCase\Module\TransactionIsolation\UncommittedKdybyConnection;
use Kdyby\Doctrine\Connection as KdybyConnection;
use Kdyby\Doctrine\EntityManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;



class TransactionIsolationModuleTest extends TestCase
{

	/**
	 * @dataProvider getTransactionWrapperStartEvents
	 * @param string[] $events
	 */
	public function testSingleTransactionWrapperStart(array $events)
	{
		$lifeCycle = new LifeCycle();

		$wrappedConnection = $this->mockWrappedConnection();
		$kdybyConnection = $this->mockKdybyConnection($wrappedConnection);
		$entityManager = $this->mockEntityManager($kdybyConnection);
		$this->createModule($entityManager, $lifeCycle);

		$kdybyConnection->shouldReceive('wrap')->once();
		$wrappedConnection->shouldReceive('wrap')->once();

		foreach ($events as $eventName) {
			call_user_func([$lifeCycle, $eventName]);
		}

		Environment::$checkAssertions = FALSE;
	}



	public function getTransactionWrapperStartEvents()
	{
		return [
			[['onInitialized']],
			[['onSetUp']],
			[['onInitialized', 'onSetUp']],
		];
	}



	/**
	 * @dataProvider getTransactionWrapperShutdownEvents
	 * @param string[] $events
	 */
	public function testSingleTransactionWrapperShutdown(array $events)
	{
		$lifeCycle = new LifeCycle();

		$wrappedConnection = $this->mockWrappedConnection();
		$kdybyConnection = $this->mockKdybyConnection($wrappedConnection);
		$entityManager = $this->mockEntityManager($kdybyConnection);
		$this->createModule($entityManager, $lifeCycle);

		$kdybyConnection->shouldReceive('unwrap')->once();
		$wrappedConnection->shouldReceive('unwrap')->once();
		$entityManager->shouldReceive('clear');

		foreach ($events as $eventName) {
			call_user_func([$lifeCycle, $eventName]);
		}

		Environment::$checkAssertions = FALSE;
	}



	public function getTransactionWrapperShutdownEvents()
	{
		return [
			[['onTearDown']],
			[['onShutDown']],
			[['onTearDown', 'onShutDown']],
		];
	}



	/**
	 * @return PDOConnection|MockInterface
	 */
	private function mockWrappedConnection()
	{
		/** @var PDOConnection|MockInterface $connection */
		$connection = Mockery::mock(PDOConnection::class);

		return $connection;
	}



	/**
	 * @param PDOConnection $wrappedConnection
	 * @return UncommittedKdybyConnection|MockInterface
	 */
	private function mockKdybyConnection(PDOConnection $wrappedConnection)
	{
		/** @var UncommittedKdybyConnection|MockInterface $connection */
		$connection = Mockery::mock(UncommittedKdybyConnection::class);

		$connection->shouldReceive('getWrappedConnection')->andReturn($wrappedConnection);

		return $connection;
	}



	/**
	 * @param EntityManager $entityManager
	 * @param LifeCycle $lifeCycle
	 * @return TransactionIsolationModule
	 */
	private function createModule(EntityManager $entityManager, LifeCycle $lifeCycle)
	{
		$module = new TransactionIsolationModule($entityManager);

		$module->listen($lifeCycle);
		$module->setParameters([TRUE]);

		return $module;
	}



	/**
	 * @param KdybyConnection $kdybyConnection
	 * @return EntityManager|MockInterface
	 */
	private function mockEntityManager(KdybyConnection $kdybyConnection)
	{
		/** @var EntityManager|MockInterface $entityManager */
		$entityManager = Mockery::mock(EntityManager::class);

		$entityManager->shouldReceive('getConnection')->andReturn($kdybyConnection);

		return $entityManager;
	}

}



(new TransactionIsolationModuleTest())->run();
