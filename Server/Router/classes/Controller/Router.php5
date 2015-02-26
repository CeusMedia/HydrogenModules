<?php
class Controller_Router extends CMF_Hydrogen_Controller{

	static public function ___onAppDispatch( $env, $context, $module, $data = array() ){
		$source	= $env->getConfig()->get( 'module.server_router.source' );
		$path	= $env->getRequest()->get( '__path' );
		$list	= array();
		switch( strtolower( $source ) ){
			case 'xml':
				$fileName	= 'config/routes.xml';
				if( !file_exists( $fileName ) )
					return;
				try{
					$routes	= @XML_ElementReader::readFile( $fileName );
				}
				catch( Exception $e ){
					$message	= 'Route definition file "config/routes.xml" is not valid XML.';
					$env->getMessenger()->noteFailure( $message );
					return;
				}
				foreach( $routes as $route ){
					$list[]	= (object) array(
						'source'	=> (string) $route->source,
						'target'	=> (string) $route->target,
						'regex'		=> (bool) $route->getAttribute( 'regex' ),
						'code'		=> (int) $route->getAttribute( 'code' ),
					);
				}
				break;
			case 'database':
				$controller	= new Controller_Router( $env, FALSE );
				$model		= new Model_Route( $env );
				$list		= $model->getAll();
				break;
		}

		if( !$list )
			return;
		foreach( $list as $route ){
			$match	= $route->source === $path;
			if( $route->regex ){
				$match	= preg_match( $route->source, $path );
				$route->target	= preg_replace( $route->source, $route->target, $path );
			}
			if( $match ){
				if( (int) $route->code < 400 ){
					$controller	= new Controller_Router( $env, FALSE );
					$controller->restart( $env->url.$route->target, FALSE, $route->code );
				}
				Net_HTTP_Status::sendHeader( $route->code );
				$heading	= $route->code.' '.Net_HTTP_Status::getText( $route->code );
				print( UI_HTML_Tag::create( 'h1', $heading ) );
				exit;
			}
		}
	}
}
?>