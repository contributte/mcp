<?php declare(strict_types = 1);

namespace Tests\Toolkit;

use Mcp\Capability\RegistryInterface;
use Mcp\Schema\Prompt;
use Mcp\Schema\Resource;
use Mcp\Schema\Tool;
use Mcp\Server;
use Mcp\Server\Configuration;
use Mcp\Server\Handler\Request\InitializeHandler;
use Mcp\Server\Handler\Request\ListPromptsHandler;
use Mcp\Server\Handler\Request\ListResourcesHandler;
use Mcp\Server\Handler\Request\ListToolsHandler;
use Mcp\Server\Handler\Request\RequestHandlerInterface;
use Mcp\Server\Protocol;
use ReflectionProperty;

final class ServerInspector
{

	public static function getConfiguration(Server $server): ?Configuration
	{
		$protocol = self::getProtocol($server);
		$handler = self::getInitializeHandler($protocol);

		return $handler?->configuration;
	}

	/**
	 * @return array<string, Tool>
	 */
	public static function getTools(Server $server): array
	{
		$registry = self::getRegistry($server, ListToolsHandler::class);

		return $registry?->getTools()->references ?? [];
	}

	public static function hasTools(Server $server): bool
	{
		return count(self::getTools($server)) > 0;
	}

	/**
	 * @return array<string, Resource>
	 */
	public static function getResources(Server $server): array
	{
		$registry = self::getRegistry($server, ListResourcesHandler::class);

		return $registry?->getResources()->references ?? [];
	}

	public static function hasResources(Server $server): bool
	{
		return count(self::getResources($server)) > 0;
	}

	/**
	 * @return array<string, Prompt>
	 */
	public static function getPrompts(Server $server): array
	{
		$registry = self::getRegistry($server, ListPromptsHandler::class);

		return $registry?->getPrompts()->references ?? [];
	}

	public static function hasPrompts(Server $server): bool
	{
		return count(self::getPrompts($server)) > 0;
	}

	public static function getProtocol(Server $server): Protocol
	{
		$prop = new ReflectionProperty($server, 'protocol');
		$prop->setAccessible(true);

		return $prop->getValue($server);
	}

	public static function getInitializeHandler(Protocol $protocol): ?InitializeHandler
	{
		return self::getHandler($protocol, InitializeHandler::class);
	}

	/**
	 * @template T of RequestHandlerInterface
	 * @param class-string<T> $handlerClass
	 * @return T|null
	 */
	public static function getHandler(Protocol $protocol, string $handlerClass): ?RequestHandlerInterface
	{
		$prop = new ReflectionProperty($protocol, 'requestHandlers');
		$prop->setAccessible(true);
		$handlers = $prop->getValue($protocol);

		foreach ($handlers as $handler) {
			if ($handler instanceof $handlerClass) {
				return $handler;
			}
		}

		return null;
	}

	/**
	 * @param class-string<RequestHandlerInterface> $handlerClass
	 */
	private static function getRegistry(Server $server, string $handlerClass): ?RegistryInterface
	{
		$protocol = self::getProtocol($server);
		$handler = self::getHandler($protocol, $handlerClass);

		if ($handler === null) {
			return null;
		}

		$prop = new ReflectionProperty($handler, 'registry');
		$prop->setAccessible(true);

		return $prop->getValue($handler);
	}

}
