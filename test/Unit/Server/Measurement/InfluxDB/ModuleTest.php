<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenModulesUnitTest\Server\Measurement\InfluxDB;

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenModulesUnitTest\BaseTestCase;
use Logic_Measurement_Influx;

class ModuleTest extends BaseTestCase
{
	public static string $host		= '';
	public static ?int $port		= 8086;
	public static string $token		= '';
	public static string $org		= '';
	public static string $bucket	= '';

	protected Environment $env;

	public function testInfluxDB()
	{
		$logic	= new Logic_Measurement_Influx( $this->env );
		$result	= $logic->write( 'mem', ['host' => 'host1'], ['used_percent' => 100] );
		self::assertTrue( $result );
	}

	protected function setUp(): void
	{
		parent::setUp();
		if( '' === self::$host )
			$this->markTestSkipped( 'No InfluxDB configuration provided' );

		$this->installModule( 'Server:Measurement:InfluxDB', [
			'host'		=> self::$host,
			'port'		=> self::$port,
			'org'		=> self::$org,
			'token'		=> self::$token,
			'bucket'	=> self::$bucket,
		] );

		$this->env	= $this->createEnvironment();

	}
}