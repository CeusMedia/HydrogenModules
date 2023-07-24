<?php

use CeusMedia\Common\Net\HTTP\Status as HttpStatus;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\XML\ElementReader as XmlReader;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Server_Router extends Hook
{
	public static function onAppDispatch( Environment $env, object $context, object $module, array & $payload )
	{
		$request		= $env->getRequest();
		$moduleConfig	= $env->getConfig()->getAll( 'module.server_router.', TRUE );

		//  @todo	use H_REQUEST_PATH_KEY, possible set in .htaccess or webserver vhost env
		$path			= $request->get( '__path' );
		$requestMethod	= $request->getMethod();
		$requestIsAjax	= $request->isAjax();

		try{
			if( !( $list = static::readRoutes( $env ) ) )
				return;
		}
		catch( Exception $e ){
			$env->getMessenger()->noteFailure( $e->getMessage() );
			return;
		}

		foreach( $list as $route ){
			$match	= $route->source === (string) $path;
			if( $route->regex ){
				$match			= preg_match( $route->source, $path );
				$route->target	= preg_replace( $route->source, $route->target, $path );
			}
			if( $match ){
				if( $route->methods && !in_array( $requestMethod, $route->methods ) )
					return;
				if( $requestIsAjax && !$route->ajax)
					return;
				if( (int) $route->code >= 400 ){
					HttpStatus::sendHeader( $route->code );
					$heading	= $route->code.' '.HttpStatus::getText( $route->code );
					print( HtmlTag::create( 'h1', $heading ) );
					exit;
				}
				if( (int) $route->code >= 300 )
					self::restart( $env, $env->url.$route->target, FALSE, $route->code );
				else{
					$request->set( '__path', $route->target );
					self::redirect( $env, $route->target, 'index' );
				}
			}
		}
	}


	protected static function getRouteXmlFilePath(): string
	{
		return 'config/routes.xml';												//  @todo get config path from app base config (config.ini)
	}

	protected static function readRoutes( Environment $env ): array
	{
		$moduleConfig	= $env->getConfig()->getAll( 'module.server_router.', TRUE );
		$requestIsAjax	= $env->getRequest()->isAjax();
		$list			= [];
		$sourceType		= strtolower( $moduleConfig->get( 'source' ) );
		switch( $sourceType ){
			case 'xml':
				$list	= static::readRoutesFromXml( $env );
				break;
			case 'database':
				$list	= static::readRoutesFromDatabase( $env );
				break;
			default:
				throw new \RangeException( 'Unsupported route source type: '.$sourceType );
		}

		return $list;
	}

	protected static function readRoutesFromDatabase( Environment $env ): array
	{
		$model			= new Model_Route( $env );
		$list			= [];
		$indices		= array(
			'status'	=> '> 0',
			'ajax'		=> $env->getRequest()->isAjax() ? 1 : 0,
		);
		foreach( $model->getAllByIndices( $indices ) as $route ){
			$methods	= [];
			if( strlen( trim( $route->methods ) ) ){
				if( $route->methods !== '*' )
					$methods	= preg_split( '/\s*,\s*/', trim( $route->methods ) );
			}
			$route->methods	= $methods;
			$list[]	= $route;
		}
		return $list;
	}

	protected static function readRoutesFromXml( Environment $env ): array
	{
		$list		= [];
		$fileName	= self::getRouteXmlFilePath( $env );
		if( !file_exists( $fileName ) )
			return $list;

		try{
			$routes	= @XmlReader::readFile( $fileName );
		}
		catch( Exception $e ){
			$message	= 'Route definition file "%s" is not valid XML.';
			throw new RuntimeException( sprintf( $message, $fileName ) );
		}
		foreach( $routes as $route ){
			if( $route->hasAttribute( 'status' ) ){
				if( (int) $route->getAttribute( 'status' ) > 0 ){
					$methods	= [];
					$ajax		= FALSE;
					if( $route->hasAttribute( 'methods' ) ){
						$methodsString	= trim( $route->getAttribute( 'methods' ) );
						if( strlen( $methodsString ) && $methodsString !== '*' )
							$methods	= preg_split( '/\s*,\s*/', $methodsString );
					}
					if( $route->hasAttribute( 'ajax' ) ){
						$valuesYes	= ['yes', 'on', '1'];
						if( in_array( (string) $route->getAttribute( 'ajax' ), $valuesYes ) )
							$ajax	= TRUE;
					}
					$list[]	= (object) array(
						'source'	=> (string) $route->source,
						'target'	=> (string) $route->target,
						'regex'		=> (bool) $route->getAttribute( 'regex' ),
						'code'		=> (int) $route->getAttribute( 'code' ),
						'methods'	=> $methods,
						'ajax'		=> $ajax,
					);
				}
			}
		}
		return $list;
	}
}
