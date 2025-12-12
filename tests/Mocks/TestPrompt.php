<?php declare(strict_types = 1);

namespace Tests\Mocks;

final class TestPrompt
{

	public function generate(string $input): string
	{
		return 'prompt-' . $input;
	}

}
