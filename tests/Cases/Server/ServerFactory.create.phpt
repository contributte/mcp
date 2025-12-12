<?php declare(strict_types = 1);

namespace Tests\Cases\Server;

use Contributte\Mcp\Server\ServerFactory;
use Contributte\Tester\Toolkit;
use Mcp\Server;
use Mcp\Server\Builder;
use Mcp\Server\Transport\InMemoryTransport;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$builder = new Builder();
	$builder->setServerInfo('Test Server', '1.0.0');
	$factory = new ServerFactory($builder);

	// Test server creation
	$server = $factory->create();
	Assert::type(Server::class, $server);
	Assert::notNull($server);

	// Verify server can run
	$transport = new InMemoryTransport();
	$result = $server->run($transport);
	Assert::null($result);
});
