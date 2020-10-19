<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@todo			finish implementation
 *	@todo			code doc
 */
class Controller_Oauth_Resource extends CMF_Hydrogen_Controller
{
	static protected $resources	= array();

	public function __construct( CMF_Hydrogen_Environment $env, $setupView = TRUE )
	{
		parent::__construct( $env, FALSE );
	}

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL, $arg5 = NULL )
	{
		$path	= '/'.implode( func_get_args() );
		$get	= $this->env->getRequest()->getAllFromSource( 'GET', TRUE );
		remark( "Path: ".$path );
		print_m( $get->getAll() );
		die;

		//	@todo	finish implementation
		//this->addData( 'resources', self::$resource );
	}

	public static function registerResource( CMF_Hydrogen_Environment $env, $path, $class, $method, $scope = NULL )
	{
		self::$resources[]	= (object) array(
			'env'		=> $env,
			'path'		=> $path,
			'class'		=> $class,
			'method'	=> $method,
			'scope'		=> $scope,
		);
	}

	protected function __onInit()
	{
		$this->env->getModules()->callHook( 'OAuthServer', 'registerResource', $this );
	}
}
