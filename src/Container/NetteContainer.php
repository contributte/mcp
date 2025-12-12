<?php declare(strict_types = 1);

namespace Contributte\Mcp\Container;

use Nette\DI\Container;
use Psr\Container\ContainerInterface;

final class NetteContainer implements ContainerInterface
{

	public function __construct(
		private Container $container,
	)
	{
	}

	public function get(string $id): object
	{
		return $this->container->getByType($id); // @phpstan-ignore-line
	}

	public function has(string $id): bool
	{
		return $this->container->getByType($id, false) !== null; // @phpstan-ignore-line
	}

}
