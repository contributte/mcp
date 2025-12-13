<?php declare(strict_types = 1);

namespace Tests\Cases\Tracy;

use Contributte\Mcp\Registry\TraceableRegistry;
use Contributte\Tester\Toolkit;
use Mcp\Capability\Registry;
use Mcp\Capability\RegistryInterface;
use Mcp\Schema\Page;
use Mcp\Schema\Tool;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test: TraceableRegistry wraps inner registry
Toolkit::test(function (): void {
	$innerRegistry = new Registry();
	$traceableRegistry = new TraceableRegistry($innerRegistry);

	Assert::type(RegistryInterface::class, $traceableRegistry);
	Assert::same($innerRegistry, $traceableRegistry->getRegistry());
});

// Test: TraceableRegistry tracks getTools calls
Toolkit::test(function (): void {
	$innerRegistry = new Registry();
	$traceableRegistry = new TraceableRegistry($innerRegistry);

	Assert::same([], $traceableRegistry->getCalls());

	$result = $traceableRegistry->getTools();

	$calls = $traceableRegistry->getCalls();
	Assert::count(1, $calls);
	Assert::same('getTools', $calls[0]['method']);
	Assert::same([null, null], $calls[0]['args']);
	Assert::type('float', $calls[0]['time']);
	Assert::type(Page::class, $calls[0]['result']);
	Assert::same($result, $calls[0]['result']);
});

// Test: TraceableRegistry tracks getResources calls with limit
Toolkit::test(function (): void {
	$innerRegistry = new Registry();
	$traceableRegistry = new TraceableRegistry($innerRegistry);

	$traceableRegistry->getResources(10);

	$calls = $traceableRegistry->getCalls();
	Assert::count(1, $calls);
	Assert::same('getResources', $calls[0]['method']);
	Assert::same([10, null], $calls[0]['args']);
});

// Test: TraceableRegistry tracks getPrompts calls
Toolkit::test(function (): void {
	$innerRegistry = new Registry();
	$traceableRegistry = new TraceableRegistry($innerRegistry);

	$traceableRegistry->getPrompts();

	$calls = $traceableRegistry->getCalls();
	Assert::count(1, $calls);
	Assert::same('getPrompts', $calls[0]['method']);
});

// Test: TraceableRegistry delegates registerTool to inner registry
Toolkit::test(function (): void {
	$innerRegistry = new Registry();
	$traceableRegistry = new TraceableRegistry($innerRegistry);

	$tool = new Tool(
		name: 'test-tool',
		inputSchema: ['type' => 'object', 'properties' => [], 'required' => []],
		description: 'A test tool',
		annotations: null,
	);
	$traceableRegistry->registerTool($tool, fn () => 'result');

	Assert::true($traceableRegistry->hasTools());
	Assert::true($innerRegistry->hasTools());
});

// Test: TraceableRegistry accumulates multiple calls
Toolkit::test(function (): void {
	$innerRegistry = new Registry();
	$traceableRegistry = new TraceableRegistry($innerRegistry);

	$traceableRegistry->getTools();
	$traceableRegistry->getResources();
	$traceableRegistry->getPrompts();

	$calls = $traceableRegistry->getCalls();
	Assert::count(3, $calls);
	Assert::same('getTools', $calls[0]['method']);
	Assert::same('getResources', $calls[1]['method']);
	Assert::same('getPrompts', $calls[2]['method']);
});
