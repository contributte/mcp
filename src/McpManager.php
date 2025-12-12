<?php declare(strict_types = 1);

namespace Contributte\Mcp;

use Contributte\Mcp\Exception\LogicalException;
use Contributte\Mcp\Http\TransportFactoryInterface;
use Contributte\Mcp\Server\ServerFactory;

final class McpManager
{

	/**
	 * @param array<string, ServerFactory> $serverFactories
	 * @param array<string, TransportFactoryInterface> $transportFactories
	 */
	public function __construct(
		private array $serverFactories,
		private array $transportFactories,
	)
	{
	}

	public function getTransportFactory(string $name): TransportFactoryInterface
	{
		return $this->transportFactories[$name] ?? throw new LogicalException(sprintf('Missing transport "%s" factory', $name));
	}

	public function getServerFactory(string $name): ServerFactory
	{
		return $this->serverFactories[$name] ?? throw new LogicalException(sprintf('Missing server "%s" factory', $name));
	}

}
