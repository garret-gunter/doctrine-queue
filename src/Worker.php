<?php

namespace GarretGunter\DoctrineQueue;

use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Factory as QueueManager;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Worker as IlluminateWorker;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Str;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

/**
 * Class Worker
 * @package GarretGunter\DoctrineQueue
 */
class Worker extends IlluminateWorker
{
	use AssertsValidEntityManager;

	/**
	 * Worker constructor.
	 *
	 * @param QueueManager     $manager
	 * @param Dispatcher       $events
	 * @param ExceptionHandler $exceptions
	 * @param callable         $isDownForMaintenance
	 * @param EntityManager    $entityManager
	 */
	public function __construct(
		QueueManager $manager,
		Dispatcher $events,
		ExceptionHandler $exceptions,
		callable $isDownForMaintenance,
		EntityManager $entityManager
	)
	{
		parent::__construct($manager, $events, $exceptions, $isDownForMaintenance);

		$this->entityManager = $entityManager;
	}

	/**
	 * Wrap parent::runJob to make sure we have a good EM.
	 *
	 * Most exception handling is done in the parent method, so we consider any new
	 * exceptions to be a result of our setup.
	 *
	 * @param Job           $job
	 * @param string        $connectionName
	 * @param WorkerOptions $options
	 */
	protected function runJob($job, $connectionName, WorkerOptions $options)
	{
		$this->prepareEntityManager();
		parent::runJob($job, $connectionName, $options);
	}

	/**
	 * Prepare the entity manager for the next job.
	 */
	protected function prepareEntityManager(): void
	{
		try {
			$this->assertEntityManagerOpen();
			$this->assertEntityManagerClear();
			$this->assertGoodDatabaseConnection();
		} catch (EntityManagerClosedException $e) {
			$this->exceptions->report($e);
			$this->stop(1);
		} catch (Exception $e) {
			$this->exceptions->report(new QueueSetupException(
				'Error in queue setup while running a job',
				0,
				$e
			));
			$this->stop(1);
		} catch (Throwable $e) {
			$this->exceptions->report(
				new QueueSetupException('Error in queue setup while running a job', 0, new FatalThrowableError($e))
			);
			$this->stop(1);
		}
	}

	/**
	 * Stop the worker if we have lost connection to a database.
	 *
	 * @param Throwable $e
	 *
	 * @return void
	 */
	protected function stopWorkerIfLostConnection($e)
	{
		parent::stopWorkerIfLostConnection($e);

		// Stop if entity manager is closed.
		if (!$this->entityManager->isOpen()) {
			$this->shouldQuit = true;
		}
	}

	/**
	 * Determine if the given exception was caused by a lost connection.
	 *
	 * @param \Throwable $e
	 *
	 * @return bool
	 */
	protected function causedByLostConnection(Throwable $e)
	{
		if (parent::causedByLostConnection($e)) {
			return true;
		}

		$message = $e->getMessage();

		return Str::contains($message, [
			'The EntityManager is closed.',
		]);
	}
}
