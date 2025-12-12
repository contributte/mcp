<?php declare(strict_types = 1);

namespace Tests\Mocks\Mcp;

use Mcp\Capability\Attribute\McpResource;

final class FileResource
{

	#[McpResource(uri: 'app://config.json', name: 'ConfigFile', description: 'Application configuration file')]
	public function getConfig(): string
	{
		return '{"app": "test"}';
	}

	#[McpResource(uri: 'file://readme.md', name: 'Readme', description: 'Application readme file')]
	public function getReadme(): string
	{
		return '# Test Application';
	}

}
