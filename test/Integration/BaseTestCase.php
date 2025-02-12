<?php

namespace CeusMedia\HydrogenModulesIntegrationTest;

use CeusMedia\Common\Net\HTTP\Reader as HttpReader;
use CeusMedia\Common\Net\HTTP\Response as HttpResponse;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
	protected string $baseUrl	= '';

	protected function get( $path ): HttpResponse
	{
		$request	= new HttpReader();
		return $request->get( $this->baseUrl.$path );
	}

	protected function setUp(): void
	{
		$this->baseUrl	= 'http://localhost/test/app/current/';
		parent::setUp(); // TODO: Change the autogenerated stub
	}
}