<?php declare(strict_types = 1);

namespace Contributte\Mcp\Router;

use Nette\Application\Routers\Route;

final class McpRouter extends Route
{

	public function __construct(string $path, string $server)
	{
		parent::__construct($path, [
			'presenter' => 'ContributteMcp:Mcp',
			'action' => 'default',
			'server' => $server,
		]);
	}

}
