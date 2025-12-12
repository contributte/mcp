<?php declare(strict_types = 1);

namespace Contributte\Mcp\Http;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Utils;
use Nette\Application\Response;
use Nette\Http\IRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GuzzleBridge
{

	/**
	 * Convert Nette HTTP request to PSR-7 ServerRequest
	 */
	public static function fromNette(IRequest $netteRequest): ServerRequestInterface
	{
		$method = $netteRequest->getMethod();
		$uri = (string) $netteRequest->getUrl();

		$headers = [];
		foreach ($netteRequest->getHeaders() as $name => $value) {
			$headers[$name] = is_array($value) ? $value : [$value];
		}

		$body = $netteRequest->getRawBody();

		$serverRequest = new ServerRequest(
			$method,
			$uri,
			$headers,
			$body !== null ? Utils::streamFor($body) : null,
			'1.1',
			$_SERVER, // @phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
		);

		// Add query parameters
		/** @var array<string, mixed> $queryParams */
		$queryParams = $netteRequest->getQuery();
		if ($queryParams !== []) {
			$serverRequest = $serverRequest->withQueryParams($queryParams);
		}

		// Add parsed body for POST/PUT/PATCH
		if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
			/** @var array<string, mixed> $parsedBody */
			$parsedBody = $netteRequest->getPost();
			if ($parsedBody !== []) {
				$serverRequest = $serverRequest->withParsedBody($parsedBody);
			}
		}

		// Add cookies
		$cookies = $netteRequest->getCookies();
		if ($cookies !== []) {
			$serverRequest = $serverRequest->withCookieParams($cookies);
		}

		// Add uploaded files (not supported)
		// $files = $netteRequest->getFiles();

		return $serverRequest;
	}

	/**
	 * Create Nette Application Response from PSR-7 response
	 */
	public static function toNette(ResponseInterface $psr7Response): Response
	{
		return new Psr7Response($psr7Response);
	}

}
