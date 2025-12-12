<?php declare(strict_types = 1);

namespace Contributte\Mcp\DI\Helpers;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\Statement;

final class BuilderMan
{

	private CompilerExtension $extension;

	private function __construct(CompilerExtension $extension)
	{
		$this->extension = $extension;
	}

	public static function of(CompilerExtension $extension): self
	{
		return new self($extension);
	}

	/**
	 * @return array<string, Definition>
	 */
	public function getServiceDefinitionsByTag(string $tag): array
	{
		$builder = $this->extension->getContainerBuilder();
		$definitions = [];

		foreach ($builder->findByTag($tag) as $serviceName => $tagValue) {
			$definitions[(string) $tagValue] = $builder->getDefinition($serviceName);
		}

		return $definitions;
	}

	/**
	 * Resolves a service reference from various formats (string with @, Statement, class name)
	 */
	public function resolveService(string|Statement $service): Reference|Statement
	{
		if ($service instanceof Statement) {
			return $service;
		}

		if (str_starts_with($service, '@')) {
			return new Reference(substr($service, 1));
		}

		return new Statement($service);
	}

}
