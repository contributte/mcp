<?php declare(strict_types = 1);

namespace Contributte\Mcp\Presenter;

use Contributte\Mcp\Http\GuzzleBridge;
use Contributte\Mcp\McpManager;
use Contributte\Mcp\Utils\McpUtils;
use Nette\Application\IPresenter;
use Nette\Application\Request as AppRequest;
use Nette\Application\Response;
use Nette\Application\Responses\TextResponse;
use Nette\Http\IRequest;
use Psr\Http\Message\ResponseInterface;

class McpPresenter implements IPresenter
{

	public function __construct(
		protected IRequest $httpRequest,
		protected McpManager $mcpManager,
	)
	{
	}

	public function run(AppRequest $appRequest): Response
	{
		// Get server name from route parameters
		$serverName = $appRequest->getParameter('server');
		$serverName = is_string($serverName) ? $serverName : 'default';

		// Convert Nette request to PSR-7 request
		$serverRequest = GuzzleBridge::fromNette($this->httpRequest);

		// Create server and transport
		$server = $this->mcpManager->getServerFactory($serverName)->create();

		if (McpUtils::isDebug($appRequest->getParameter('debug'))) {
			return new TextResponse('MCP debug mode, inspect Tracy.');
		}

		$transport = $this->mcpManager->getTransportFactory('streamable')->create($serverRequest);

		// Run server
		$psr7Response = $server->run($transport);
		assert($psr7Response instanceof ResponseInterface);

		return GuzzleBridge::toNette($psr7Response);
	}

}
