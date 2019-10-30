<?php

namespace GarretGunter\DoctrineQueueTest;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use GarretGunter\DoctrineQueue\DoctrineQueueMiddleware;
use GarretGunter\DoctrineQueue\EntityManagerClosedException;
use Illuminate\Contracts\Queue\Job;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \GarretGunter\DoctrineQueue\DoctrineQueueMiddleware
 */
class DoctrineQueueMiddlewareTest extends TestCase
{

	use MockeryPHPUnitIntegration;

	/**
	 * @covers \GarretGunter\DoctrineQueue\DoctrineQueueMiddleware
	 */
	public function testHandle(): void
	{
		$job = \Mockery::mock(Job::class);
		$nextMock = \Mockery::mock(\stdClass::class);
		$nextMock->expects('called')->with($job)->once();
		$next = static function ($job) use ($nextMock) {
			$nextMock->called($job);
		};

		$connection = \Mockery::mock(Connection::class);
		$connection->expects('ping')->once()->andReturnTrue();

		$entityManager = \Mockery::mock(EntityManagerInterface::class);
		$entityManager->expects('isOpen')->once()->andReturnTrue();
		$entityManager->expects('clear')->once();
		$entityManager->expects('getConnection')->once()->andReturn($connection);

		$middleware = new DoctrineQueueMiddleware($entityManager);
		$middleware->handle($job, $next);
	}

	/**
	 * @covers \GarretGunter\DoctrineQueue\DoctrineQueueMiddleware
	 */
	public function testFailsWhenEntityManagerIsClosed(): void
	{
		$job = \Mockery::mock(Job::class);
		$nextMock = \Mockery::mock(\stdClass::class);
		$nextMock->expects('called')->never();
		$next = static function ($job) use ($nextMock) {
			$nextMock->called($job);
		};

		$entityManager = \Mockery::mock(EntityManagerInterface::class);
		$entityManager->expects('isOpen')->once()->andReturnFalse();
		$entityManager->expects('clear')->never();
		$entityManager->expects('getConnection')->never();

		$this->expectException(EntityManagerClosedException::class);

		$middleware = new DoctrineQueueMiddleware($entityManager);
		$middleware->handle($job, $next);
	}

	/**
	 * @covers \GarretGunter\DoctrineQueue\DoctrineQueueMiddleware
	 */
	public function testReconnectsConnection(): void
	{
		$job = \Mockery::mock(Job::class);
		$nextMock = \Mockery::mock(\stdClass::class);
		$nextMock->expects('called')->with($job)->once();
		$next = static function ($job) use ($nextMock) {
			$nextMock->called($job);
		};

		$connection = \Mockery::mock(Connection::class);
		$connection->expects('ping')->once()->andReturnFalse();
		$connection->expects('close')->once();
		$connection->expects('connect')->once();

		$entityManager = \Mockery::mock(EntityManagerInterface::class);
		$entityManager->expects('isOpen')->once()->andReturnTrue();
		$entityManager->expects('clear')->once();
		$entityManager->expects('getConnection')->once()->andReturn($connection);

		$middleware = new DoctrineQueueMiddleware($entityManager);
		$middleware->handle($job, $next);
	}
}
