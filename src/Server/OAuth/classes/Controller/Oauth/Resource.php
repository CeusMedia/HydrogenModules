<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 *	@todo			finish implementation
 *	@todo			code doc
 */
class Controller_Oauth_Resource extends Controller
{
	protected static array $resources	= [];

	public static function registerResource( Environment $env, string $path, string $class, string $method, ?string $scope = NULL ): void
	{
		self::$resources[]	= (object) [
			'env'		=> $env,
			'path'		=> $path,
			'class'		=> $class,
			'method'	=> $method,
			'scope'		=> $scope,
		];
	}

	public function __construct( WebEnvironment $env, bool $setupView = TRUE )
	{
		parent::__construct( $env, !$setupView );
	}

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL, $arg5 = NULL ): void
	{
		$path	= '/'.implode( func_get_args() );
		$get	= $this->env->getRequest()->getAllFromSource( 'GET', TRUE );
		remark( "Path: ".$path );
		print_m( $get->getAll() );
		die;

		//	@todo	finish implementation
		//this->addData( 'resources', self::$resource );
	}

	protected function __onInit(): void
	{
		$this->env->getModules()->callHook( 'OAuthServer', 'registerResource', $this );
	}
}
