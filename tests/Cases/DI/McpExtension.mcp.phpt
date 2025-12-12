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
							name: Contributte MCP
							version: 1.2.3
			NEON
			));
		})->build();

	/** @var McpManager $mcpManager */
	$mcpManager = $container->getByType(McpManager::class);

	$mcpServerFactory = $mcpManager->getServerFactory('default');
	$mcpServer = $mcpServerFactory->create();

	// Validate configuration via ServerInspector
	$configuration = ServerInspector::getConfiguration($mcpServer);
	Assert::type(Configuration::class, $configuration);
	Assert::same('Contributte MCP', $configuration->serverInfo->name);
	Assert::same('1.2.3', $configuration->serverInfo->version);

	$transport = new InMemoryTransport();
	$result = $mcpServer->run($transport);
	Assert::null($result);
});
