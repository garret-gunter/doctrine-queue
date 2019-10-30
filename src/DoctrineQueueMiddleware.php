<?php

namespace GarretGunter\DoctrineQueue;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Queue\Job;
use Throwable;

/**
 * Class DoctrineQueueMiddleware
 *
 * WARNING: This is experimental and not proven to function properly.
 *
 * @package GarretGunter\DoctrineQueue
 */
class DoctrineQueueMiddleware
{
	use AssertsValidEntityManager;

	/**
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * Process the queued job.
	 *
	 * @param Job      $job
	 * @param callable $next
	 *
	 * @return void
	 * @throws EntityManagerClosedException
	 * @throws QueueSetupException
	 */
	public function handle($job, $next): void
	{
		// fixme: this might not work since the worker will continue failing any job that needs the entity manager.
		$this->prepareEntityManager();
		$next($job);
	}

	/**
	 * Prepare the entity manager to handle the job.
	 * @throws QueueSetupException
	 * @throws EntityManagerClosedException
	 */
	protected function prepareEntityManager(): void
	{
		try {
			$this->assertEntityManagerOpen();
			$this->assertEntityManagerClear();
			$this->assertGoodDatabaseConnection();
		} catch (EntityManagerClosedException $e) {
			throw $e;
		} catch (Throwable $e) {
			throw new QueueSetupException('Error in queue setup while running a job', 0, $e);
		}
	}
}
