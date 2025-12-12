<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Mcp\DI\McpExtension;
use Contributte\Mcp\Http\TransportFactoryInterface;
use Contributte\Mcp\McpManager;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Mcp\Server\Configuration;
use Nette\DI\Compiler;
use Tester\Assert;
use Tests\Mocks\TestTransportFactory;
use Tests\Toolkit\ServerInspector;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function (Compiler $compiler): void {
			$compiler->addExtension('mcp', new McpExtension());

			// Register custom transport factory with tag
			$builder = $compiler->getContainerBuilder();
			$builder->addDefinition('customTransportFactory')
				->setFactory(TestTransportFactory::class)
				->addTag(McpExtension::TRANSPORT_FACTORY_TAG, 'custom');

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

	// Test default transport factories
	$stdioFactory = $mcpManager->getTransportFactory('stdio');
	Assert::type(TransportFactoryInterface::class, $stdioFactory);

	$streamableFactory = $mcpManager->getTransportFactory('streamable');
	Assert::type(TransportFactoryInterface::class, $streamableFactory);

	// Test custom transport factory
	$customFactory = $mcpManager->getTransportFactory('custom');
	Assert::type(TransportFactoryInterface::class, $customFactory);
	Assert::type(TestTransportFactory::class, $customFactory);

	// Validate server configuration
	$server = $mcpManager->getServerFactory('default')->create();
	$config = ServerInspector::getConfiguration($server);
	Assert::type(Configuration::class, $config);
	Assert::same('Test Server', $config->serverInfo->name);
	Assert::same('1.0.0', $config->serverInfo->version);
});
