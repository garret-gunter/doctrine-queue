<?php

namespace GarretGunter\DoctrineQueueTest;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Exception;
use GarretGunter\DoctrineQueue\Worker;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Factory as QueueManager;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Queue\WorkerOptions;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \GarretGunter\DoctrineQueue\Worker
 */
class WorkerTest extends TestCase
{
	use MockeryPHPUnitIntegration;

	/**
	 * @covers \GarretGunter\DoctrineQueue\Worker
	 */
	public function testChecksEntityManagerState(): void
	{
		$connectionName = 'foo';
		$queueName = 'baz';

		// Mock job
		$job = Mockery::mock(Job::class);
		$job->shouldReceive('timeoutAt')->once()->andReturnNull();
		$job->shouldReceive('maxTries')->once()->andReturnNull();
		$job->shouldReceive('isDeleted')->once()->andReturnFalse();
		$job->shouldReceive('fire')->once();

		// Mock queue manager and a queue
		$queue = Mockery::mock(Queue::class);
		$queue->shouldReceive('pop')->with($queueName)->zeroOrMoreTimes()->andReturn($job, null);

		$manager = Mockery::mock(QueueManager::class);
		$manager->expects('connection')->with($connectionName)->once()->andReturn($queue);

		// Mock dependencies
		[$events, $exceptions] = $this->mockDependencies();

		$isDownForMaintenance = static function () {
			return false;
		};

		$workerOptions = Mockery::mock(WorkerOptions::class);
		$workerOptions->sleep = 0;
		$workerOptions->maxTries = 0;

		// Set up expectations
		$connection = Mockery::mock(Connection::class);
		$connection->expects('ping')->once()->andReturnTrue();

		$entityManager = Mockery::mock(EntityManagerInterface::class);
		$entityManager->expects('isOpen')->once()->andReturnTrue();
		$entityManager->expects('clear')->once();
		$entityManager->expects('getConnection')->once()->andReturn($connection);

		// Run job
		$worker = new Worker($manager, $events, $exceptions, $isDownForMaintenance, $entityManager);
		$worker->runNextJob($connectionName, $queueName, $workerOptions);
		$this->assertFalse($worker->shouldQuit);
	}

	/**
	 * @covers ::stopWorkerIfLostConnection
	 */
	public function testStopsIfClosedOnQueuePop(): void
	{
		$connectionName = 'foo';
		$queueName = 'baz';
		$exception = new Exception();

		// Mock queue manager and a queue
		$queue = Mockery::mock(Queue::class);
		$queue->expects('pop')->once()->with($queueName)->andThrow($exception);
		$manager = Mockery::mock(QueueManager::class);
		$manager->expects('connection')->with($connectionName)->once()->andReturn($queue);

		// Mock dependencies
		[$events, $exceptions] = $this->mockDependencies();
		$exceptions->expects('report')->once()->with($exception);

		$isDownForMaintenance = static function () {
			return false;
		};

		$workerOptions = Mockery::mock(WorkerOptions::class);
		$workerOptions->sleep = 0;
		$workerOptions->maxTries = 0;

		// Manager is closed.
		$entityManager = Mockery::mock(EntityManagerInterface::class);
		$entityManager->expects('isOpen')->once()->andReturnFalse();

		// Run job
		$worker = new Worker($manager, $events, $exceptions, $isDownForMaintenance, $entityManager);
		$worker->runNextJob($connectionName, $queueName, $workerOptions);
		$this->assertTrue($worker->shouldQuit);
	}

	/**
	 * @covers ::causedByLostConnection
	 */
	public function testClosedEntityManagerIsLostConnection(): void
	{
		$connectionName = 'foo';
		$queueName = 'baz';
		$exception = ORMException::entityManagerClosed();

		// Mock queue manager and a queue
		$queue = Mockery::mock(Queue::class);
		$queue->expects('pop')->once()->with($queueName)->andThrow($exception);
		$manager = Mockery::mock(QueueManager::class);
		$manager->expects('connection')->with($connectionName)->once()->andReturn($queue);

		// Manager is closed.
		$entityManager = Mockery::mock(EntityManagerInterface::class);
		$entityManager->expects('isOpen')->once()->andReturnTrue();

		// Mock dependencies
		[$events, $exceptions] = $this->mockDependencies();
		$exceptions->expects('report')->once()->with($exception);

		$isDownForMaintenance = static function () {
			return false;
		};

		$workerOptions = Mockery::mock(WorkerOptions::class);
		$workerOptions->sleep = 0;
		$workerOptions->maxTries = 0;

		// Run job
		$worker = new Worker($manager, $events, $exceptions, $isDownForMaintenance, $entityManager);
		$worker->runNextJob($connectionName, $queueName, $workerOptions);
		$this->assertTrue($worker->shouldQuit);
	}

	/**
	 * @return array
	 */
	protected function mockDependencies(): array
	{
		// Mock event dispatcher and exception handler
		$events = Mockery::mock(Dispatcher::class);
		$events->shouldReceive('dispatch')->zeroOrMoreTimes()->withAnyArgs();
		$exceptions = Mockery::mock(ExceptionHandler::class);

		return [
			$events,
			$exceptions,
		];
	}
}