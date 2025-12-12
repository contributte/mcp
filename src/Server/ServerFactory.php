<?php declare(strict_types = 1);

namespace Contributte\Mcp\Server;

use Mcp\Server;
use Mcp\Server\Builder;

final class ServerFactory
{

	public function __construct(
		private Builder $builder
	)
	{
	}

	public function create(): Server
	{
		return $this->builder->build();
	}

}
