<?php declare(strict_types = 1);

namespace Contributte\Mcp\Http;

use Mcp\Server\Transport\TransportInterface;

interface TransportFactoryInterface
{

	/**
	 * @return TransportInterface<mixed>
	 */
	public function create(mixed ...$args): TransportInterface;

}
