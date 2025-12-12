<?php declare(strict_types = 1);

namespace Contributte\Mcp\Http;

use Nette\Application\Response;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Psr\Http\Message\ResponseInterface;

final class Psr7Response implements Response
{

	public function __construct(
		private readonly ResponseInterface $psr7Response,
	)
	{
	}

	public function send(IRequest $httpRequest, IResponse $httpResponse): void
	{
		// Set status code
		$httpResponse->setCode($this->psr7Response->getStatusCode());

		// Set headers (PSR-7 headers are multi-value arrays)
		foreach ($this->psr7Response->getHeaders() as $name => $values) {
			$first = true;
			foreach ($values as $value) {
				if ($first) {
					$httpResponse->setHeader($name, $value);
					$first = false;
				} else {
					$httpResponse->addHeader($name, $value);
				}
			}
		}

		// Output body
		echo $this->psr7Response->getBody();
	}

}
