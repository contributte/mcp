<?php declare(strict_types = 1);

namespace Contributte\Mcp\DI;

use Contributte\Mcp\Console\McpCommand;
use Contributte\Mcp\Container\NetteContainer;
use Contributte\Mcp\DI\Helpers\BuilderMan;
use Contributte\Mcp\Exception\LogicalException;
use Contributte\Mcp\Http\StdioTransportFactory;
use Contributte\Mcp\Http\StreamableTransportFactory;
use Contributte\Mcp\McpManager;
use Contributte\Mcp\Server\ServerFactory;
use GuzzleHttp\Psr7\HttpFactory;
use Mcp\Server\Builder;
use Mcp\Server\Session\FileSessionStore;
use Mcp\Server\Session\InMemorySessionStore;
use Mcp\Server\Session\Psr16StoreSession;
use Nette\Application\IPresenterFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use stdClass;

/**
 * @property-read stdClass $config
 */
class McpExtension extends CompilerExtension
{

	public const string SERVER_TAG = 'contributte.mcp.server';
	public const string SERVER_FACTORY_TAG = 'contributte.mcp.server_factory';
	public const string TRANSPORT_FACTORY_TAG = 'contributte.mcp.transport_factory';

	public function getConfigSchema(): Schema
	{
		$parameters = $this->getContainerBuilder()->parameters;

		$expectService = Expect::anyOf(
			Expect::string()->required()->assert(static fn ($input) => str_starts_with($input, '@') || class_exists($input) || interface_exists($input)),
			Expect::type(Statement::class)->required(),
		);

		return Expect::structure([
			'servers' => Expect::arrayOf(
				Expect::structure([
					'name' => Expect::string()->default('MCP'),
					'version' => Expect::string()->default('1.0.0'),
					'discovery' => Expect::structure([
						'enabled' => Expect::bool(true),
						'basePath' => Expect::string()->default($parameters['appDir'] ?? getcwd()),
						'scanDirs' => Expect::arrayOf(Expect::string())->default(['.']),
						'excludeDirs' => Expect::arrayOf(Expect::string())->default([]),
						'cache' => Expect::anyOf($expectService, null)->default(null),
					])->required(),
					'session' => Expect::structure([
						'type' => Expect::anyOf('file', 'inmemory', 'psr16')->default(isset($parameters['tempDir']) ? 'file' : 'inmemory'),
						'path' => Expect::string()->nullable(),
						'ttl' => Expect::int()->default(3600),
						'prefix' => Expect::string()->default('mcp-'),
						'cache' => Expect::anyOf($expectService, null)->default(null),
					])->required(),
					'container' => Expect::anyOf($expectService, null)->default(null),
				]),
				Expect::string()->required()
			),
		]);
	}

	public function loadConfiguration(): void
	{
		$config = $this->config;
		$builder = $this->getContainerBuilder();

		foreach ($config->servers as $serverName => $serverConfig) {
			$this->loadServerConfiguration($serverName, $serverConfig);
		}

		$loggerDef = $builder->addDefinition($this->prefix('logger'))
			->setFactory(NullLogger::class)
			->setType(LoggerInterface::class)
			->setAutowired(false);

		$responseFactoryDef = $builder->addDefinition($this->prefix('http.responseFactory'))
			->setFactory(HttpFactory::class)
			->setAutowired(false);

		$streamFactoryDef = $builder->addDefinition($this->prefix('http.streamFactory'))
			->setFactory(HttpFactory::class)
			->setAutowired(false);

		$builder->addDefinition($this->prefix('transport.streamable.transportFactory'))
			->setFactory(StreamableTransportFactory::class, [
				$responseFactoryDef,
				$streamFactoryDef,
				$loggerDef,
			])
			->addTag(self::TRANSPORT_FACTORY_TAG, 'streamable');

		$builder->addDefinition($this->prefix('transport.stdio.transportFactory'))
			->setFactory(StdioTransportFactory::class, [
				$loggerDef,
			])
			->addTag(self::TRANSPORT_FACTORY_TAG, 'stdio');

		$mcpManagerDef = $builder->addDefinition($this->prefix('mcpManager'))
			->setFactory(McpManager::class, [
				BuilderMan::of($this)->getServiceDefinitionsByTag(self::SERVER_FACTORY_TAG),
				BuilderMan::of($this)->getServiceDefinitionsByTag(self::TRANSPORT_FACTORY_TAG),
			]);

		$builder->addDefinition($this->prefix('console.mcpCommand'))
			->setFactory(McpCommand::class, [$mcpManagerDef]);
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		$presenterFactory = $builder->getByType(IPresenterFactory::class);
		if ($presenterFactory !== null) {
			$presenterFactoryDef = $builder->getDefinition($presenterFactory);
			assert($presenterFactoryDef instanceof ServiceDefinition);
			$presenterFactoryDef->addSetup('setMapping', [
				[
					'ContributteMcp' => 'Contributte\Mcp\Presenter\*Presenter',
				],
			]);
		}
	}

	private function loadServerConfiguration(string $serverName, stdClass $serverConfig): void
	{
		$builder = $this->getContainerBuilder();

		// Server
		$builderDef = $builder->addDefinition($this->prefix('server.' . $serverName . '.builder'))
			->setFactory(Builder::class)
			->addTag(self::SERVER_TAG, $serverName)
			->setAutowired(false);

		// Server:ServerInfo
		$builderDef->addSetup('setServerInfo', [
			$serverConfig->name,
			$serverConfig->version,
		]);

		// Server:Container
		if ($serverConfig->container === null) {
			$builderDef->addSetup('setContainer', [new Statement(NetteContainer::class, ['@container'])]);
		} else {
			$builderDef->addSetup('setContainer', [$serverConfig->container]);
		}

		// Server:Discovery
		if ($serverConfig->discovery->enabled) {
			$cacheService = $serverConfig->discovery->cache !== null
				? BuilderMan::of($this)->resolveService($serverConfig->discovery->cache)
				: null;

			$builderDef->addSetup('setDiscovery', [
				$serverConfig->discovery->basePath,
				$serverConfig->discovery->scanDirs,
				$serverConfig->discovery->excludeDirs,
				$cacheService,
			]);
		}

		// Server:Session
		switch ($serverConfig->session->type) {
			case 'file':
				$path = $serverConfig->session->path ?? (isset($builder->parameters['tempDir']) ? $builder->parameters['tempDir'] . '/mcp' : null);
				if ($path === null) {
					throw new LogicalException(
						sprintf('Session path must be configured for file sessions (server "%s"). Either set session.path or ensure %%tempDir%% is available.', $serverName)
					);
				}

				$sessionStore = new Statement(FileSessionStore::class, [$path, $serverConfig->session->ttl]);
				break;
			case 'inmemory':
				$sessionStore = new Statement(InMemorySessionStore::class, [$serverConfig->session->ttl]);
				break;
			case 'psr16':
				if ($serverConfig->session->cache === null) {
					throw new LogicalException(
						sprintf('Session cache service must be configured for psr16 sessions (server "%s"). Set session.cache to a PSR-16 cache service.', $serverName)
					);
				}

				$cacheService = BuilderMan::of($this)->resolveService($serverConfig->session->cache);
				$sessionStore = new Statement(Psr16StoreSession::class, [$cacheService, $serverConfig->session->prefix, $serverConfig->session->ttl]);
				break;
			default:
				$sessionStore = null;
		}

		if ($sessionStore !== null) {
			$builderDef->addSetup('setSession', [$sessionStore]);
		}

		// ServerFactory
		$builder->addDefinition($this->prefix('server.' . $serverName . '.factory'))
			->setFactory(ServerFactory::class, [$builderDef])
			->addTag(self::SERVER_FACTORY_TAG, $serverName)
			->setAutowired($serverName === 'default');
	}

}
