<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Disclosure;
use CeusMedia\HydrogenFramework\View;

/**
 *	@todo		apply module config main switch
 */
class Browser
{
	protected $env;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->time1	= $this->env->getClock()->stop( 3, 1 );
	}

	public function render( $body, $headers = [] )
	{
		$config			= $this->env->getConfig();
		$request		= $this->env->getRequest();

		$controller		= $request->get( '__controller' );
		$action			= $request->get( '__action' );

		if( !$controller )
			$controller	= 'index';

		$disclosure		= new Disclosure();
		$options		= ['classPrefix' => 'Controller_', 'readMethods' => FALSE];
		$controllers	= [];
		foreach( array_keys( $disclosure->reflect( 'classes/Controller/', $options ) ) as $item ){
			if( $item !== "Abstract" ){
				$path	= str_replace( '_', '/', strtolower( $item ) );
				$controllers[$path]	= $item;
			}
		}
		natcasesort( $controllers );

		$actions		= array( 'index' => '(index)' );
		$arguments		= [];
		$options		= ['classPrefix' => 'Controller_', 'readParameters' => TRUE];
		foreach( $disclosure->reflect( 'classes/Controller/', $options ) as $className => $class ){
			if( str_replace( '_', '/', strtolower( $className ) ) == $controller ){
				foreach( $class->methods as $methodName => $method ){
					$actions[$methodName]	= $methodName;
					if( $methodName == $action )
						$arguments			= array_keys( $method->parameters );
				}
			}
		}

		if( $action && !array_key_exists( $action, $actions ) )
			$action	= '';
		if( !$action && array_key_exists( 'index', $actions ) )
			$action	= 'index';
		natcasesort( $actions );

		$response	= json_decode( $body );
		$data		= array(
			'config'		=> $config,
			'request'		=> $request,
			'controller'	=> $controller,
			'controllers'	=> $controllers,
			'action'		=> $action,
			'actions'		=> $actions,
			'arguments'		=> implode( '/', $arguments ),
			'token'			=> $request->get( 'token' ),
			'path'			=> implode( '/', $request->get( '__arguments' ) ),
			'post'			=> $request->get( 'post' ),
			'response'		=> (object) array(
				'json'		=> $body,
				'data'		=> isset( $response->data ) ? $response->data : '',
				'debug'		=> isset( $response->debug ) ? $response->debug : '',
				'exception'	=> isset( $response->exception ) ? $response->exception : '',
			),
			'json'			=> (object) [
				'raw'		=> $body,
				'object'	=> $response
			],
			'time_init'		=> round( $this->time1, 1 ),
			'time_render'	=> round( $this->env->getClock()->stop( 3, 1 ) - $this->time1, 1 ),
			'url'			=> getEnv( 'REQUEST_URI' ),
		);

		$view	= new View( $this->env );
		$view->setData( $data );
		$body	= $view->loadTemplateFile( 'browser/index.php' );

		$pathJs		= $config->get( 'path.scripts' );
		$pathCss	= $config->get( 'path.themes' );
		$pathTmpl	= $config->get( 'path.templates' );
		$pathLibJs	= $config->get( 'path.scripts.lib' );

		$page		= $this->env->getPage();
		$page->setTitle( $config->get( 'app.name' ) );
		$page->setBaseHref( $this->env->getBaseUrl() );
		$page->addStylesheet( $pathCss.'custom/css/browser.css' );
		$page->addStylesheet( $pathLibJs.'jquery/cmExceptionView/0.2.css' );
		$page->addJavaScript( $pathLibJs.'jquery/1.7.min.js' );
		$page->addJavaScript( $pathLibJs.'jquery/cmExceptionView/0.2.js' );
		$page->addJavaScript( $pathJs.'jquery.deparam.js' );
		$page->addJavaScript( $pathJs.'LocalServerFrontendController.js' );
		$page->addJavaScript( $pathJs.'browser.js' );
		$page->addBody( $body );
		return $page->build();
	}
}
