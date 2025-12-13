<?php declare(strict_types = 1);

namespace Contributte\Mcp\Registry;

use Mcp\Capability\Discovery\DiscoveryState;
use Mcp\Capability\Registry\PromptReference;
use Mcp\Capability\Registry\ResourceReference;
use Mcp\Capability\Registry\ResourceTemplateReference;
use Mcp\Capability\Registry\ToolReference;
use Mcp\Capability\RegistryInterface;
use Mcp\Schema\Page;
use Mcp\Schema\Prompt;
use Mcp\Schema\Resource;
use Mcp\Schema\ResourceTemplate;
use Mcp\Schema\Tool;

final class TraceableRegistry implements RegistryInterface
{

	/** @var list<array{method: string, args: array<mixed>, time: float, result: mixed}> */
	private array $calls = [];

	public function __construct(
		private readonly RegistryInterface $registry,
	)
	{
	}

	public function getRegistry(): RegistryInterface
	{
		return $this->registry;
	}

	/**
	 * @return list<array{method: string, args: array<mixed>, time: float, result: mixed}>
	 */
	public function getCalls(): array
	{
		return $this->calls;
	}

	public function registerTool(Tool $tool, callable|array|string $handler, bool $isManual = false): void
	{
		$this->registry->registerTool($tool, $handler, $isManual);
	}

	public function registerResource(Resource $resource, callable|array|string $handler, bool $isManual = false): void
	{
		$this->registry->registerResource($resource, $handler, $isManual);
	}

	/**
	 * @param array<string, class-string|object> $completionProviders
	 */
	public function registerResourceTemplate(
		ResourceTemplate $template,
		callable|array|string $handler,
		array $completionProviders = [],
		bool $isManual = false,
	): void
	{
		$this->registry->registerResourceTemplate($template, $handler, $completionProviders, $isManual);
	}

	/**
	 * @param array<string, class-string|object> $completionProviders
	 */
	public function registerPrompt(
		Prompt $prompt,
		callable|array|string $handler,
		array $completionProviders = [],
		bool $isManual = false,
	): void
	{
		$this->registry->registerPrompt($prompt, $handler, $completionProviders, $isManual);
	}

	public function clear(): void
	{
		$this->registry->clear();
	}

	public function getDiscoveryState(): DiscoveryState
	{
		return $this->registry->getDiscoveryState();
	}

	public function setDiscoveryState(DiscoveryState $state): void
	{
		$this->registry->setDiscoveryState($state);
	}

	public function hasTools(): bool
	{
		return $this->registry->hasTools();
	}

	public function getTools(?int $limit = null, ?string $cursor = null): Page
	{
		return $this->trace(__FUNCTION__, [$limit, $cursor], fn () => $this->registry->getTools($limit, $cursor));
	}

	public function getTool(string $name): ToolReference
	{
		return $this->trace(__FUNCTION__, [$name], fn () => $this->registry->getTool($name));
	}

	public function hasResources(): bool
	{
		return $this->registry->hasResources();
	}

	public function getResources(?int $limit = null, ?string $cursor = null): Page
	{
		return $this->trace(__FUNCTION__, [$limit, $cursor], fn () => $this->registry->getResources($limit, $cursor));
	}

	public function getResource(string $uri, bool $includeTemplates = true): ResourceReference|ResourceTemplateReference
	{
		return $this->trace(__FUNCTION__, [$uri, $includeTemplates], fn () => $this->registry->getResource($uri, $includeTemplates));
	}

	public function hasResourceTemplates(): bool
	{
		return $this->registry->hasResourceTemplates();
	}

	public function getResourceTemplates(?int $limit = null, ?string $cursor = null): Page
	{
		return $this->trace(__FUNCTION__, [$limit, $cursor], fn () => $this->registry->getResourceTemplates($limit, $cursor));
	}

	public function getResourceTemplate(string $uriTemplate): ResourceTemplateReference
	{
		return $this->trace(__FUNCTION__, [$uriTemplate], fn () => $this->registry->getResourceTemplate($uriTemplate));
	}

	public function hasPrompts(): bool
	{
		return $this->registry->hasPrompts();
	}

	public function getPrompts(?int $limit = null, ?string $cursor = null): Page
	{
		return $this->trace(__FUNCTION__, [$limit, $cursor], fn () => $this->registry->getPrompts($limit, $cursor));
	}

	public function getPrompt(string $name): PromptReference
	{
		return $this->trace(__FUNCTION__, [$name], fn () => $this->registry->getPrompt($name));
	}

	/**
	 * @template T
	 * @param array<mixed> $args
	 * @param callable(): T $callback
	 * @return T
	 */
	private function trace(string $method, array $args, callable $callback): mixed
	{
		$start = microtime(true);
		$result = $callback();
		$time = microtime(true) - $start;

		$this->calls[] = [
			'method' => $method,
			'args' => $args,
			'time' => $time,
			'result' => $result,
		];

		return $result;
	}

}
