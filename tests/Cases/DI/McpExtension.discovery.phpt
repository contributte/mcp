<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use _PHPStan_5adafcbb8\React\Cache\CacheInterface;
use Contributte\Mcp\DI\McpExtension;
use Contributte\Mcp\McpManager;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Mcp\Server\Configuration;
use Nette\DI\Compiler;
use Tester\Assert;
use Tests\Toolkit\ServerInspector;
use Tests\Toolkit\Tests;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../Mocks/Mcp/CalculatorTool.php';
require_once __DIR__ . '/../../Mocks/Mcp/FileResource.php';
require_once __DIR__ . '/../../Mocks/Mcp/GreetingPrompt.php';

function discoveryTempDir(string $name): string
{
	return Tests::TEMP_PATH . '/' . $name . '-' . getmypid() . '-' . bin2hex(random_bytes(4));
}

// Test discovery disabled (explicitly disabled, default is enabled)
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withTempDir(discoveryTempDir('mcp-discovery-disabled'))
		->withCompiler(static function (Compiler $compiler): void {
			$compiler->addExtension('mcp', new McpExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				mcp:
					servers:
						default:
							name: Test Server
							version: 1.0.0
							discovery:
								enabled: false
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

	// No tools/resources/prompts should be discovered when disabled
	Assert::false(ServerInspector::hasTools($server));
	Assert::false(ServerInspector::hasResources($server));
	Assert::false(ServerInspector::hasPrompts($server));
});

// Test discovery enabled with custom scanDirs - discovers tools, resources, and prompts
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withTempDir(discoveryTempDir('mcp-discovery-enabled'))
		->withCompiler(static function (Compiler $compiler): void {
			$compiler->addExtension('mcp', new McpExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				mcp:
					servers:
						default:
							name: Test Server
							version: 1.0.0
							discovery:
								enabled: true
								basePath: %testsDir%
								scanDirs:
									- Mocks/Mcp
								excludeDirs:
									- vendor
			NEON
			));
			$compiler->addConfig([
				'parameters' => [
					'testsDir' => __DIR__ . '/../../',
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

	// Verify tools are discovered (3 tools from CalculatorTool)
	Assert::true(ServerInspector::hasTools($server));
	$tools = ServerInspector::getTools($server);
	Assert::count(3, $tools);
	Assert::true(isset($tools['calculator_add']));
	Assert::true(isset($tools['calculator_subtract']));
	Assert::true(isset($tools['calculator_multiply']));

	// Verify resources are discovered (2 resources from FileResource)
	Assert::true(ServerInspector::hasResources($server));
	$resources = ServerInspector::getResources($server);
	Assert::count(2, $resources);
	Assert::true(isset($resources['app://config.json']));
	Assert::true(isset($resources['file://readme.md']));

	// Verify prompts are discovered (2 prompts from GreetingPrompt)
	Assert::true(ServerInspector::hasPrompts($server));
	$prompts = ServerInspector::getPrompts($server);
	Assert::count(2, $prompts);
	Assert::true(isset($prompts['greeting_simple']));
	Assert::true(isset($prompts['greeting_formal']));
});

// Test discovery with cache service (if PSR-16 is available)
Toolkit::test(function (): void {
	if (!interface_exists(CacheInterface::class)) {
		return;
	}

	$container = ContainerBuilder::of()
		->withTempDir(discoveryTempDir('mcp-discovery-cache'))
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
							discovery:
								enabled: true
								cache: @testCache
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
