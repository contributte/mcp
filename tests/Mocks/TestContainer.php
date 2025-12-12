<?php declare(strict_types = 1);

namespace Tests\Mocks;

use Psr\Container\ContainerInterface;

final class TestContainer implements ContainerInterface
{

	/** @var array<string, object> */
	private array $services = [];

	public function set(string $id, object $service): void
	{
		$this->services[$id] = $service;
	}

	public function get(string $id): object
	{
		if (!isset($this->services[$id])) {
			throw new \RuntimeException(sprintf('Service "%s" not found', $id));
		}

		return $this->services[$id];
	}

	public function has(string $id): bool
	{
		return isset($this->services[$id]);
	}

}
