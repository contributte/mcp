<?php declare(strict_types = 1);

namespace Tests\Mocks\Mcp;

use Mcp\Capability\Attribute\McpTool;

final class CalculatorTool
{

	#[McpTool(name: 'calculator_add', description: 'Add two numbers')]
	public function add(int $a, int $b): int
	{
		return $a + $b;
	}

	#[McpTool(name: 'calculator_subtract', description: 'Subtract two numbers')]
	public function subtract(int $a, int $b): int
	{
		return $a - $b;
	}

	#[McpTool(name: 'calculator_multiply', description: 'Multiply two numbers')]
	public function multiply(int $a, int $b): int
	{
		return $a * $b;
	}

}
