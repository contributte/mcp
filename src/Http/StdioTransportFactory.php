<?php declare(strict_types = 1);

namespace Contributte\Mcp\Http;

use Mcp\Server\Transport\StdioTransport;
use Mcp\Server\Transport\TransportInterface;
use Psr\Log\LoggerInterface;
use const STDIN;
use const STDOUT;

final class StdioTransportFactory implements TransportFactoryInterface
{

	public function __construct(
		private readonly LoggerInterface $logger,
	)
	{
	}

	/**
	 * @return TransportInterface<mixed>
	 */
	public function create(mixed ...$args): TransportInterface
	{
		return new StdioTransport(
			STDIN,
			STDOUT,
			$this->logger,
		);
	}

}
