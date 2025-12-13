<?php declare(strict_types = 1);

namespace Contributte\Mcp\Utils;

final class McpUtils
{

	public static function isDebug(mixed $value): bool
	{
		return in_array($value, ['1', 'true', 1, true, 'on'], true);
	}

}
