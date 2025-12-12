<?php declare(strict_types = 1);

namespace Tests\Cases\Server;

use Contributte\Tester\Toolkit;
use Mcp\Server;
use Mcp\Server\Configuration;
use Tester\Assert;
use Tests\Toolkit\ServerInspector;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$server = Server::builder()
		->setServerInfo('Test Server', '1.0.0')
		->build();

	$configuration = ServerInspector::getConfiguration($server);

	Assert::type(Configuration::class, $configuration);
	Assert::same('Test Server', $configuration->serverInfo->name);
	Assert::same('1.0.0', $configuration->serverInfo->version);
});

Toolkit::test(function (): void {
	$server = Server::builder()
		->setServerInfo('Test Server', '1.0.0')
		->build();

	// Server without tools/resources/prompts should return empty arrays
	$tools = ServerInspector::getTools($server);
	$resources = ServerInspector::getResources($server);
	$prompts = ServerInspector::getPrompts($server);

	Assert::type('array', $tools);
	Assert::type('array', $resources);
	Assert::type('array', $prompts);
	Assert::count(0, $tools);
	Assert::count(0, $resources);
	Assert::count(0, $prompts);
});
