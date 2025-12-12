<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Mcp\DI\McpExtension;
use Contributte\Mcp\McpManager;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Mcp\Server\Configuration;
use Nette\DI\Compiler;
use Psr\SimpleCache\CacheInterface;
use Tester\Assert;
use Tests\Toolkit\ServerInspector;

require_once __DIR__ . '/../../bootstrap.php';

// Test file-based sessions (default)
Toolkit::test(function (): void {
	$tempDir = sys_get_temp_dir() . '/mcp-test-sessions';
	$container = ContainerBuilder::of()
		->withCompiler(static function (Compiler $compiler) use ($tempDir): void {
			$compiler->addExtension('mcp', new McpExtension());
			$compiler->addConfig([
				'mcp' => [
					'servers' => [
						'default' => [
							'name' => 'Test Server',
							'version' => '1.0.0',
							'session' => [
								'type' => 'file',
								'path' => $tempDir,
								'ttl' => 3600,
							],
						],
					],
				],
			]);
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

// Test in-memory sessions
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
							session:
								type: inmemory
								ttl: 7200
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

// Test PSR-16 cache sessions (if PSR-16 is available)
Toolkit::test(function (): void {
	if (!interface_exists(CacheInterface::class)) {
		return;
	}

	$container = ContainerBuilder::of()
		->withCompiler(static function (Compiler $compiler): void {
			$compiler->addExtension('mcp', new McpExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				services:
					testCache: Tests\Mocks\TestCache

				mcp:
					servers:
						default:
							name: Test Server
							version: 1.0.0
							session:
								type: psr16
								cache: @testCache
								ttl: 1800
								prefix: custom-
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
