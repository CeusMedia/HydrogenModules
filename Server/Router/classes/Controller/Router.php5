<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;

class Controller_Router extends Controller{

	static protected function getRouteXmlFilePath(){
		return 'config/routes.xml';												//  @todo get config path from app base config (config.ini)
	}

	static public function ___onAppDispatch( Environment $env, $context, $module, $data = [] ){
		$request		= $env->getRequest();
		$moduleConfig	= $env->getConfig()->getAll( 'module.server_router.', TRUE );
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
					Net_HTTP_Status::sendHeader( $route->code );
					$heading	= $route->code.' '.Net_HTTP_Status::getText( $route->code );
					print( HtmlTag::create( 'h1', $heading ) );
					exit;
				}
				$controller	= new Controller_Router( $env, FALSE );
				if( (int) $route->code >= 300 )
					$controller->restart( $env->url.$route->target, FALSE, $route->code );
				else{
					$request->set( '__path', $route->target );
					$controller->redirect( $route->target, 'index' );
				}
			}
		}
	}

	static protected function readRoutes( $env ){
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

	static protected function readRoutesFromDatabase( $env ){
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

	static protected function readRoutesFromXml( $env ){
		$list		= [];
		$fileName	= self::getRouteXmlFilePath( $env );
		if( !file_exists( $fileName ) )
			return $list;

		try{
			$routes	= @XML_ElementReader::readFile( $fileName );
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
						$valuesYes	= array( 'yes', 'on', '1' );
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
?>
