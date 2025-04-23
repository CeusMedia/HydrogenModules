<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenModulesUnitTest\Server\Measurement\InfluxDB;

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenModulesUnitTest\BaseTestCase;
use Logic_Measurement_Influx;

class ModuleTest extends BaseTestCase
{
#	public static string $host		= 'localhost';
#	public static ?int $port		= 8086;
#	public static string $token		= 'sx7sj1Njp63OgK4SAUt2a0yqY0bgYlVsuEREv8UTFElnQMr9YAZESGVHY6VoBxuLNjZ0fmaM5eqI0zh9Bmp_oQ==';
#	public static string $org		= 'Test';
#	public static string $bucket	= 'test';
	public static string $host		= '23.88.115.198';
	public static ?int $port		= 8086;
	public static string $token		= 'Dy6IOPENvcnSiOEYHiFG-tVmiCzHKsB6Rsqe774o1HbcnkUFI3GmhwvlL6g4vkYyqCELe8HqW83y10thOiGDpw==';
	public static string $org		= 'Ceus Media';
	public static string $bucket	= 'test';

	protected Environment $env;

	public function testInfluxDB()
	{
		$logic	= new Logic_Measurement_Influx( $this->env );
		$min = 25;
		$max = 95;
		$randomFloat = mt_rand($min * 100, $max * 100) / 100;

		$result	= $logic->write( 'mem', ['host' => 'host1'], ['used_percent' => $randomFloat] );
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