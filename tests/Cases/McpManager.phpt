<?php declare(strict_types = 1);

namespace Tests\Cases;

use Contributte\Mcp\Exception\LogicalException;
use Contributte\Mcp\Http\TransportFactoryInterface;
use Contributte\Mcp\McpManager;
use Contributte\Mcp\Server\ServerFactory;
use Contributte\Tester\Toolkit;
use Mcp\Server\Builder;
use Tester\Assert;
use Tests\Mocks\TestTransportFactory;

require_once __DIR__ . '/../bootstrap.php';

Toolkit::test(function (): void {
	$customFactory = new TestTransportFactory();

	$mcpManager = new McpManager(
		[],
		[
			'custom' => $customFactory,
		]
	);

	// Test retrieving custom transport factory
	$factory = $mcpManager->getTransportFactory('custom');
	Assert::same($customFactory, $factory);
	Assert::type(TransportFactoryInterface::class, $factory);
	Assert::type(TestTransportFactory::class, $factory);

	// Test error handling for missing transport factory
	Assert::exception(
		static fn () => $mcpManager->getTransportFactory('nonexistent'),
		LogicalException::class,
		'Missing transport "nonexistent" factory'
	);
});

Toolkit::test(function (): void {
	$builder = new Builder();
	$builder->setServerInfo('Test Server', '1.0.0');
	$serverFactory = new ServerFactory($builder);

	$mcpManager = new McpManager(
		['default' => $serverFactory],
		[]
	);

	// Test retrieving existing server factory
	$factory = $mcpManager->getServerFactory('default');
	Assert::same($serverFactory, $factory);
	Assert::type(ServerFactory::class, $factory);

	// Test error handling for missing server factory
	Assert::exception(
		static fn () => $mcpManager->getServerFactory('nonexistent'),
		LogicalException::class,
		'Missing server "nonexistent" factory'
	);
});
