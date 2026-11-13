<?php
namespace CeusMedia\HydrogenModulesUnitTest\Resource\SMB;

use CeusMedia\HydrogenModulesUnitTest\BaseTestCase;

class ModuleTest extends BaseTestCase
{
	public static string $host			= '';
	public static string $username		= '';
	public static string $password		= '';
	public static string $workgroup	= '';
	public static string $share		= '';

	protected \Resource_SMB $resource;

	public function testIndex(): void
	{
		$path	= '';
		$map	= $this->resource->index( $path );

		self::assertIsArray( $map );
	}

	protected function setUp(): void
	{
		parent::setUp();
		if( '' === self::$host )
			$this->markTestSkipped( 'No SMB configuration provided' );

		$this->installModule( 'Resource:SMB' );
		$env		= $this->createEnvironment();
		$this->resource	= new \Resource_SMB( $env );
		$this->resource->connect(
			self::$host,
			self::$username,
			self::$workgroup,
			self::$password,
			self::$share
		);
	}
}