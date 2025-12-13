<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Mcp\DI\McpExtension;
use Contributte\Mcp\Registry\TraceableRegistry;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Mcp\Capability\Registry;
use Mcp\Capability\RegistryInterface;
use Nette\DI\Compiler;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test: Registry and TraceableRegistry are always registered
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function (Compiler $compiler): void {
			$compiler->addExtension('mcp', new McpExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				mcp:
					servers:
						default:
							name: Test MCP
							version: 1.0.0
			NEON
			));
		})->build();

	Assert::true($container->hasService('mcp.server.default.registry'));
	Assert::true($container->hasService('mcp.server.default.traceableRegistry'));
});

// Test: debug.panel enabled - TraceableRegistry wraps inner Registry
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function (Compiler $compiler): void {
			$compiler->addExtension('mcp', new McpExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				mcp:
					debug:
						panel: true
					servers:
						default:
							name: Test MCP
							version: 1.0.0
			NEON
			));
		})->build();

	// TraceableRegistry should be registered
	Assert::true($container->hasService('mcp.server.default.registry'));
	Assert::true($container->hasService('mcp.server.default.traceableRegistry'));

	/** @var TraceableRegistry $traceableRegistry */
	$traceableRegistry = $container->getService('mcp.server.default.traceableRegistry');
	Assert::type(TraceableRegistry::class, $traceableRegistry);
	Assert::type(RegistryInterface::class, $traceableRegistry);

	/** @var Registry $innerRegistry */
	$innerRegistry = $container->getService('mcp.server.default.registry');
	Assert::type(Registry::class, $innerRegistry);

	// TraceableRegistry should wrap the inner Registry
	Assert::same($innerRegistry, $traceableRegistry->getRegistry());
});

// Test: debug.panel enabled with multiple servers - each has its own TraceableRegistry
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function (Compiler $compiler): void {
			$compiler->addExtension('mcp', new McpExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				mcp:
					debug:
						panel: true
					servers:
						default:
							name: Default Server
							version: 1.0.0
						api:
							name: API Server
							version: 2.0.0
			NEON
			));
		})->build();

	// Both servers should have TraceableRegistry
	Assert::true($container->hasService('mcp.server.default.traceableRegistry'));
	Assert::true($container->hasService('mcp.server.api.traceableRegistry'));

	/** @var TraceableRegistry $defaultRegistry */
	$defaultRegistry = $container->getService('mcp.server.default.traceableRegistry');

	/** @var TraceableRegistry $apiRegistry */
	$apiRegistry = $container->getService('mcp.server.api.traceableRegistry');

	// Each server has its own TraceableRegistry instance
	Assert::type(TraceableRegistry::class, $defaultRegistry);
	Assert::type(TraceableRegistry::class, $apiRegistry);
	Assert::notSame($defaultRegistry, $apiRegistry);

	// Each TraceableRegistry has its own inner Registry
	Assert::notSame($defaultRegistry->getRegistry(), $apiRegistry->getRegistry());
});
