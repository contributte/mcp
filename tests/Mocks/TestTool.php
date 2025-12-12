<?php declare(strict_types = 1);

namespace Tests\Mocks;

final class TestTool
{

	public function add(int $a, int $b): int
	{
		return $a + $b;
	}

}
