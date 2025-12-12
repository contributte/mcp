<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Mcp\DI\McpExtension;
use Contributte\Mcp\McpManager;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Mcp\Server\Configuration;
use Mcp\Server\Transport\InMemoryTransport;
use Nette\DI\Compiler;
use Tester\Assert;
use Tests\Toolkit\ServerInspector;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function (Compiler $compiler): void {
			$compiler->addExtension('mcp', new McpExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				mcp:
					servers:
						default:
							name: Main MCP Server
							version: 1.0.0
						secondary:
							name: Secondary MCP Server
							version: 2.0.0
			NEON
			));
		})->build();

	/** @var McpManager $mcpManager */
	$mcpManager = $container->getByType(McpManager::class);

	// Test default server
	$defaultFactory = $mcpManager->getServerFactory('default');
	$defaultServer = $defaultFactory->create();
	Assert::notNull($defaultServer);

	// Validate default server configuration
	$defaultConfig = ServerInspector::getConfiguration($defaultServer);
	Assert::type(Configuration::class, $defaultConfig);
	Assert::same('Main MCP Server', $defaultConfig->serverInfo->name);
	Assert::same('1.0.0', $defaultConfig->serverInfo->version);

	$transport = new InMemoryTransport();
	$result = $defaultServer->run($transport);
	Assert::null($result);

	// Test secondary server
	$secondaryFactory = $mcpManager->getServerFactory('secondary');
	$secondaryServer = $secondaryFactory->create();
	Assert::notNull($secondaryServer);

	// Validate secondary server configuration
	$secondaryConfig = ServerInspector::getConfiguration($secondaryServer);
	Assert::type(Configuration::class, $secondaryConfig);
	Assert::same('Secondary MCP Server', $secondaryConfig->serverInfo->name);
	Assert::same('2.0.0', $secondaryConfig->serverInfo->version);

	$result = $secondaryServer->run($transport);
	Assert::null($result);

	// Verify they are different instances
	Assert::notSame($defaultServer, $secondaryServer);
});
