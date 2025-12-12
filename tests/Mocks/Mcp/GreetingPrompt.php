<?php declare(strict_types = 1);

namespace Tests\Mocks\Mcp;

use Mcp\Capability\Attribute\McpPrompt;

final class GreetingPrompt
{

	#[McpPrompt(name: 'greeting_simple', description: 'A simple greeting prompt')]
	public function simple(string $name): string
	{
		return sprintf('Hello, %s!', $name);
	}

	#[McpPrompt(name: 'greeting_formal', description: 'A formal greeting prompt')]
	public function formal(string $name, string $title): string
	{
		return sprintf('Good day, %s %s.', $title, $name);
	}

}
