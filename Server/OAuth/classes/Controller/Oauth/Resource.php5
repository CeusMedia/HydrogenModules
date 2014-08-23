<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 *	@todo			code doc
 */
class Controller_Oauth_Resource extends CMF_Hydrogen_Controller{

	static protected $resources	= array();

	public function __onInit(){
		$this->env->getModules()->callHook( 'OAuthServer', 'registerResource', $this );
	}

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL, $arg5 = NULL){
		$path	= '/'.implode( func_get_args() );
		$get	= $this->env->getRequest()->getAllFromSource( 'GET' );
		remark( "Path: ".$path );
		print_m( $get );
		die;
//		if( $this->env->getRequest()->getAllFromSource( 'GET' ) )
//		foreach( self::$resources as $resource ){
//			if( )
//		}
	
	}

	static public function registerResource( $env, $path, $class, $method, $scope = NULL ){
		self::$resources[]	= (object) array(
			'env'		=> $env,
			'path'		=> $path,
			'class'		=> $class,
			'method'	=> $method,
			'scope'		=> $scope,
		);
	}
}