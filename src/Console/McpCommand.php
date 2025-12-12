<?php declare(strict_types = 1);

namespace Contributte\Mcp\Console;

use Contributte\Mcp\McpManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
	name: 'mcp:server',
	description: 'Run MCP server'
)]
final class McpCommand extends Command
{

	public function __construct(
		private readonly McpManager $mcpManager
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->addOption(
			'server',
			's',
			InputOption::VALUE_REQUIRED,
			'MCP server',
			'default'
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$serverName = $input->getOption('server');
		assert(is_string($serverName));

		// Create server
		$server = $this->mcpManager->getServerFactory($serverName)->create();

		// Create stdio transport
		$transport = $this->mcpManager->getTransportFactory('stdio')->create();

		// Run server
		$server->run($transport);

		return Command::SUCCESS;
	}

}
