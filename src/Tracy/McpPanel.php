<?php declare(strict_types = 1);

namespace Contributte\Mcp\Tracy;

use Contributte\Mcp\Registry\TraceableRegistry;
use Tracy\Debugger;
use Tracy\IBarPanel;

final class McpPanel implements IBarPanel
{

	public function __construct(
		private readonly TraceableRegistry $registry,
		private readonly string $serverName,
	)
	{
	}

	public static function initialize(
		TraceableRegistry $registry,
		string $serverName,
	): void
	{
		Debugger::getBar()->addPanel(new self($registry, $serverName), 'contributte.mcp.' . $serverName);
	}

	public function getTab(): string
	{
		// Variables used in template
		// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$registry = $this->registry;
		// phpcs:enable

		ob_start();
		require __DIR__ . '/templates/tab.phtml';

		return (string) ob_get_clean();
	}

	public function getPanel(): string
	{
		// Variables used in template
		// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$registry = $this->registry;
		$serverName = $this->serverName;
		$tools = $this->registry->getTools();
		$resources = $this->registry->getResources();
		$resourceTemplates = $this->registry->getResourceTemplates();
		$prompts = $this->registry->getPrompts();
		// phpcs:enable

		ob_start();
		require __DIR__ . '/templates/panel.phtml';

		return (string) ob_get_clean();
	}

}
