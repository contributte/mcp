<?php declare(strict_types = 1);

namespace Tests\Mocks;

use Contributte\Mcp\Http\TransportFactoryInterface;
use Mcp\Server\Transport\InMemoryTransport;
use Mcp\Server\Transport\TransportInterface;

final class TestTransportFactory implements TransportFactoryInterface
{

	/**
	 * @return TransportInterface<mixed>
	 */
	public function create(mixed ...$args): TransportInterface
	{
		return new InMemoryTransport();
	}

}
