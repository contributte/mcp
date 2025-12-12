<?php declare(strict_types = 1);

namespace Tests\Mocks;

use Psr\SimpleCache\CacheInterface;

final class TestCache implements CacheInterface
{

	/** @var array<string, mixed> */
	private array $data = [];

	public function get(string $key, mixed $default = null): mixed
	{
		return $this->data[$key] ?? $default;
	}

	public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
	{
		$this->data[$key] = $value;

		return true;
	}

	public function delete(string $key): bool
	{
		unset($this->data[$key]);

		return true;
	}

	public function clear(): bool
	{
		$this->data = [];

		return true;
	}

	/**
	 * @param iterable<string> $keys
	 * @return iterable<string, mixed>
	 */
	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		$result = [];
		foreach ($keys as $key) {
			$result[$key] = $this->get($key, $default);
		}

		return $result;
	}

	/**
	 * @param iterable<string, mixed> $values
	 */
	public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
	{
		foreach ($values as $key => $value) {
			$this->set($key, $value, $ttl);
		}

		return true;
	}

	/**
	 * @param iterable<string> $keys
	 */
	public function deleteMultiple(iterable $keys): bool
	{
		foreach ($keys as $key) {
			$this->delete($key);
		}

		return true;
	}

	public function has(string $key): bool
	{
		return isset($this->data[$key]);
	}

}
