<?php declare(strict_types = 1);

namespace Tests\Cases\Console;

use Contributte\Mcp\DI\McpExtension;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Nette\DI\Compiler;
use Symfony\Component\Console\Command\Command;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

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
						secondary:
							name: Secondary Server
							version: 2.0.0
			NEON
			));
		})->build();

	Assert::count(1, $container->findByType(Command::class));
});
