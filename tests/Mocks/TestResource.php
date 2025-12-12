<?php declare(strict_types = 1);

namespace Tests\Mocks;

final class TestResource
{

	public function get(string $id): string
	{
		return 'resource-' . $id;
	}

}
