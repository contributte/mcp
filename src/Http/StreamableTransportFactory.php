<?php declare(strict_types = 1);

namespace Contributte\Mcp\Http;

use Contributte\Mcp\Exception\LogicalException;
use Mcp\Server\Transport\StreamableHttpTransport;
use Mcp\Server\Transport\TransportInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

final class StreamableTransportFactory implements TransportFactoryInterface
{

	public function __construct(
		private readonly ResponseFactoryInterface $responseFactory,
		private readonly StreamFactoryInterface $streamFactory,
		private readonly LoggerInterface $logger,
	)
	{
	}

	/**
	 * @return TransportInterface<mixed>
	 */
	public function create(mixed ...$args): TransportInterface
	{
		$serverRequest = $args[0] ?? null;

		if (!($serverRequest instanceof ServerRequestInterface)) {
			throw new LogicalException('Invalid server request parameter');
		}

		return new StreamableHttpTransport(
			$serverRequest,
			$this->responseFactory,
			$this->streamFactory,
			[],
			$this->logger,
		);
	}

}
