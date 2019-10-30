<?php

namespace GarretGunter\DoctrineQueue;


use Doctrine\ORM\EntityManagerInterface as EntityManager;

/**
 * Trait AssertsValidEntityManager
 * @package GarretGunter\DoctrineQueue
 */
trait AssertsValidEntityManager
{
	/**
	 * @var EntityManager
	 */
	protected $entityManager;

	/**
	 * @throws EntityManagerClosedException
	 */
	protected function assertEntityManagerOpen(): void
	{
		if ($this->entityManager->isOpen()) {
			return;
		}

		throw new EntityManagerClosedException('Entity manager closed while running a job.');
	}

	/**
	 * To clear the em before doing any work.
	 */
	protected function assertEntityManagerClear(): void
	{
		$this->entityManager->clear();
	}

	/**
	 * Some database systems close the connection after a period of time, in MySQL this is system variable
	 * `wait_timeout`. Given the daemon is meant to run indefinitely we need to make sure we have an open
	 * connection before working any job. Otherwise we would see `MySQL has gone away` type errors.
	 */
	protected function assertGoodDatabaseConnection(): void
	{
		$connection = $this->entityManager->getConnection();

		if ($connection->ping() === false) {
			$connection->close();
			$connection->connect();
		}
	}
}