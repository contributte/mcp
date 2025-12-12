<?php declare(strict_types = 1);

namespace Contributte\Mcp\DI\Helpers;

use Contributte\Mcp\Exception\LogicalException;
use Nette\DI\Definitions\Statement;

final class SmartStatement
{

	public static function from(mixed $service): Statement
	{
		if (is_string($service)) {
			return new Statement($service);
		} elseif ($service instanceof Statement) {
			return $service;
		} else {
			throw new LogicalException('Unsupported type of service');
		}
	}

}
