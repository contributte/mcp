<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Mcp\DI\McpExtension;
use Contributte\Mcp\McpManager;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Mcp\Server\Configuration;
use Nette\DI\Compiler;
use Tester\Assert;
use Tests\Toolkit\ServerInspector;

require_once __DIR__ . '/../../bootstrap.php';

// Test default NetteContainer (out-of-the-box, no configuration needed)
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function (Compiler $compiler): void {
			$compiler->addExtension('mcp', new McpExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				mcp:
					servers:
						default:
							name: Test Server
							version: 1.0.0
			NEON
			));
		})->build();

	/** @var McpManager $mcpManager */
	$mcpManager = $container->getByType(McpManager::class);
	$server = $mcpManager->getServerFactory('default')->create();
	Assert::notNull($server);

	$config = ServerInspector::getConfiguration($server);
	Assert::type(Configuration::class, $config);
	Assert::same('Test Server', $config->serverInfo->name);
	Assert::same('1.0.0', $config->serverInfo->version);
});

// Test explicit NetteContainer configuration (backward compatibility)
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function (Compiler $compiler): void {
			$compiler->addExtension('mcp', new McpExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				services:
					mcpContainer: Contributte\Mcp\Container\NetteContainer(@container)

				mcp:
					servers:
						default:
							name: Test Server
							version: 1.0.0
							container: @mcpContainer
			NEON
			));
		})->build();

	/** @var McpManager $mcpManager */
	$mcpManager = $container->getByType(McpManager::class);
	$server = $mcpManager->getServerFactory('default')->create();
	Assert::notNull($server);

	$config = ServerInspector::getConfiguration($server);
	Assert::type(Configuration::class, $config);
	Assert::same('Test Server', $config->serverInfo->name);
});

// Test custom container service
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function (Compiler $compiler): void {
			$compiler->addExtension('mcp', new McpExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				services:
					customContainer: Tests\Mocks\TestContainer

				mcp:
					servers:
						default:
							name: Test Server
							version: 1.0.0
							container: @customContainer
			NEON
			));
		})->build();

	/** @var McpManager $mcpManager */
	$mcpManager = $container->getByType(McpManager::class);
	$server = $mcpManager->getServerFactory('default')->create();
	Assert::notNull($server);

	$config = ServerInspector::getConfiguration($server);
	Assert::type(Configuration::class, $config);
	Assert::same('Test Server', $config->serverInfo->name);
});
