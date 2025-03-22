<?php
namespace CeusMedia\HydrogenModulesUnitTest\Resource\Jobs;

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenModulesUnitTest\BaseTestCase;

class AbstractJobTest extends BaseTestCase
{
	protected Environment $env;

	public function testGetPeriod_success_returnsPeriodString(): void
	{
		$job	= new TestJob( $this->env );
		$job->noteJob( 'TestJob', 'getPeriod' );
		$this->assertEquals( 'P1Y', $job->forwardGetPeriod( '1Y' ) );
		$this->assertEquals( 'P2M', $job->forwardGetPeriod( '2M' ) );
		$this->assertEquals( 'P3D', $job->forwardGetPeriod( '3D' ) );
		$this->assertEquals( 'PT4H', $job->forwardGetPeriod( '4h' ) );
		$this->assertEquals( 'PT10M', $job->forwardGetPeriod( '10m' ) );
		$this->assertEquals( 'PT20S', $job->forwardGetPeriod( '20s' ) );
	}

	public function testGetPeriod_success_returnsNull(): void
	{
		$job	= new TestJob( $this->env );
		$this->assertNull( $job->forwardGetPeriod( '' ) );
		$this->assertNull( $job->forwardGetPeriod( '2' ) );
		$this->assertNull( $job->forwardGetPeriod( 'M' ) );
		$this->assertNull( $job->forwardGetPeriod( '-1' ) );
		$this->assertNull( $job->forwardGetPeriod( 'x' ) );
		$this->assertNull( $job->forwardGetPeriod( 'YM' ) );
	}

	protected function setUp(): void
	{
		parent::setUp();
//		$this->installModule( 'Resource:Jobs' );
		$this->env		= $this->createEnvironment();
	}
}

class TestJob extends \Job_Abstract
{
	public function forwardGetPeriod( string $value ): ?string
	{
		return $this->getPeriod( $value );
	}
}