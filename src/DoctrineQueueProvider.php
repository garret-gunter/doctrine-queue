<?php

namespace GarretGunter\DoctrineQueue;

use GarretGunter\DoctrineQueue\Console\WorkCommand;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use const DIRECTORY_SEPARATOR;

/**
 * Class DoctrineQueueProvider
 * @package GarretGunter\DoctrineQueue
 * @codeCoverageIgnore
 */
class DoctrineQueueProvider extends ServiceProvider implements DeferrableProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/doctrine-queue.php', 'doctrine-queue');

		$this->registerWorkCommand();
		$this->registerWorker();
	}

	/**
	 * Handle boot event
	 */
	public function boot(): void
	{
		if (!$this->isLumen() && $this->app->runningInConsole()) {
			$this->publishes([
				__DIR__ . '/../config/doctrine-queue.php' => $this->configPath('doctrine-queue.php'),
			], ['doctrine-queue', 'config']);
		}

		if ($this->app->runningInConsole()) {
			$this->commands(
				[
					WorkCommand::class,
				]
			);
		}
	}

	/**
	 * @return void
	 */
	protected function registerWorker(): void
	{
		$this->app->bind(Worker::class, static function ($app) {
			$isDownForMaintenance = function () {
				return $this->app->isDownForMaintenance();
			};

			return new Worker(
				$app['queue'],
				$app['events'],
				$app[ExceptionHandler::class],
				$isDownForMaintenance,
				$app['em']
			);
		});

		$this->app->alias(Worker::class, 'doctrine-queue.worker');
	}

	/**
	 * @return void
	 */
	protected function registerWorkCommand(): void
	{
		$this->app->singleton(WorkCommand::class, static function ($app) {
			return new WorkCommand(
				$app['doctrine-queue.worker'],
				$app['cache.store'],
				$app['config']->get('doctrine-queue')
			);
		});

		$this->app->alias(WorkCommand::class, 'command.doctrine-queue.work');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'command.doctrine-queue.work',
			'doctrine-queue.worker',
			WorkCommand::class,
			Worker::class,
		];
	}

	/**
	 * @return bool
	 */
	protected function isLumen(): bool
	{
		return Str::contains($this->app->version(), 'Lumen');
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	private function configPath($path = ''): string
	{
		return $this->app->make('path.config') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
	}
}
