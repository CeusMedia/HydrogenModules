<?php
namespace CeusMedia\HydrogenModulesTest\UI\TEA;


use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;
use CeusMedia\HydrogenModulesTest\BaseTestCase;
use CeusMedia\TemplateAbstraction\Factory;

class ModuleTest extends BaseTestCase
{
	public function testHookEnvInit(): void
	{
		$env	= $this->createEnvironment();

		/** @var Factory $tea */
		$tea		= $env->get( 'tea' );

		self::assertNotEmpty( $tea );

		$date		= date( 'Y-m-d H:i:s' );
		$adapter	= $tea->getTemplate( 'test.twig', ['date' => $date] );
		$content	= $adapter->render();

		self::assertIsString( $content );
		self::assertEquals( 'Hello Test '.$date, $content );
	}

	public function testOnViewRealizeTemplate(): void
	{
		/** @var WebEnvironment $env */
		$env	= $this->createEnvironment( NULL, TRUE );

		$date	= date( 'Y-m-d H:i:s' );
		$view	= new View( $env );
		$view->addData( 'date', $date );

		$content	= $view->loadTemplateFile( 'test.php' );
		self::assertIsString( $content );
		self::assertEquals( 'Hello Test '.$date, $content );
	}

	protected function setUp(): void
	{
		$templatePhp	= <<<'PHP'
<?php
/** @var string $date */
return 'Hello Test '.$date;
PHP;
		$templateTwig	= <<<'TWIG'
<!--Engine:Twig-->
Hello Test {{ date }}
TWIG;

		parent::setUp();
		$this->installModule( 'UI:TEA' );
		file_put_contents( $this->pathApp.'templates/test.twig', $templateTwig );
		file_put_contents( $this->pathApp.'templates/test.php', $templatePhp );
	}

	protected function tearDown(): void
	{
		unlink( $this->pathApp.'templates/test.twig' );
		unlink( $this->pathApp.'templates/test.php' );
		$this->uninstallModule( 'UI:TEA' );
		parent::tearDown();
	}
}