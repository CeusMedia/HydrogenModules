<?php
class Browser{

	protected $env;

	public function __construct(CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
		$this->time1	= $this->env->getClock()->stop( 3, 1 );
	}

	public function render( $body, $headers = array() ){
		$config			= $this->env->getConfig();
		$request		= $this->env->getRequest();

		$controller		= $request->get( 'controller' );
		$action			= $request->get( 'action' );

		if( !$controller )
			$controller	= 'index';
		
		$disclosure		= new CMF_Hydrogen_Environment_Resource_Disclosure();
		$options		= array( 'classPrefix' => 'Controller_', 'readMethods' => FALSE );
		$controllers	= array();
		foreach( array_keys( $disclosure->reflect( 'classes/Controller/', $options ) ) as $item )
			if( $item !== "Abstract" ){
				$path	= str_replace( '_', '/', strtolower( $item ) );
				$controllers[$path]	= $item;
			}
		natcasesort( $controllers );
		
		$actions		= array( 'index' => '(index)' );
		$arguments		= array();
		$options		= array( 'classPrefix' => 'Controller_', 'readParameters' => TRUE );
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
		$data				= (object) array( 'view' => NULL, 'exception' => NULL, 'debug' => NULL );
		$data->view			= isset( $response->data ) ? $response->data : '';
		$data->debug		= isset( $response->debug ) ? $response->debug : '';
		$data->exception	= isset( $response->exception ) ? $response->exception : '';

		$exception	= (object) array( 'message' => NULL, 'code' => NULL, 'view' => NULL );
		if( isset( $response->exception ) ){
			$exception->message = $response->exception;
			if( 1 || $response->data <= -100 ){
				if( isset( $response->serial ) ){
					$instance = unserialize( $response->serial );
					if( 1 || ( $instance->getCode() >= 200 && $instance->getCode() < 300 ) ){
						$exception->message	= $instance->getMessage();
						$exception->code	= $instance->getCode();
						$exception->view	= UI_HTML_Exception_View::render( $instance );
					}
					unset( $response->serial );
				}
				switch( $exception->code ){
					// ...
				}
/*				switch( $data ){
					case -105:
						$data	= 'Error: '.$exception->message.'.';
						unset( $response->exception );
						$exception	= (object) array( 'message' => NULL, 'code' => NULL, 'view' => NULL );
						break;
				}
*/
				if( preg_match( '/Access denied:/', $exception->message ) ){
					$data->view	= 'Error: '.$exception->message.'.';
					$data->exception	= '';
					unset( $response->exception );
					$exception	= (object) array( 'message' => NULL, 'code' => NULL, 'view' => NULL );
				}
			}
		}
		if( $exception->view )
			$data->exception	= '<br/><h3>Exception</h3><div style="float: left">'.$exception->view.'</div>';
		else if( $exception->message )
			$data->exception	= '<h3>Exception</h3><xmp class="code">'.$exception->message.'</xmp>';

		if( strlen( $data->debug ) ){
			$data->debug	= '<br/><h3>Debug Notice</h3><xmp class="code">'.$data->debug.'</xmp>';
		}
		if( is_object( $data->view ) || is_array( $data->view ) ){
			$view		= trim( ADT_JSON_Formater::format( json_encode( $data->view ) ) );
			$data->view	= '<xmp class="js" style="max-height: 500px">'.$view.'</xmp>';
		}
		else
			$data->view	= '<xmp class="code" style="max-height: 250px">'.$data->view.'</xmp>';
		$data		= array(
			'config'		=> $config->getAll(),
			'request'		=> $request->getAll(),
			'body'			=> $body,
			'data'			=> $data,
			'response'		=> wordwrap( $body, 180, "\n", TRUE ),
			'json'			=> array(
				'raw'		=> ADT_JSON_Formater::format( json_encode( $response ) ),
				'object'	=> $response
			),
			'controller'	=> $controller,
			'action'		=> $action,
			'arguments'		=> implode( '/', $arguments ),
			'token'			=> $request->get( 'token' ),
			'path'			=> implode( '/', $request->get( 'arguments' ) ),
			'post'			=> $request->get( 'post' ),
			'optController'	=> UI_HTML_Elements::Options( $controllers, $controller ),
			'optAction'		=> UI_HTML_Elements::Options( $actions, $action ),
		);
#		print_m( $exception );
#		die;

		$data['time_init']		= round( $this->time1, 1 ); 
		$data['time_render']	= round( $this->env->getClock()->stop( 3, 1 ) - $this->time1, 1 ); 
		$data['url']			= getEnv( 'REQUEST_URI' );
	
		$pathJs		= $config->get( 'path.scripts' );
		$pathCss	= $config->get( 'path.themes' );
		$pathTmpl	= $config->get( 'path.templates' );
		$pathLibJs	= $config->get( 'path.scripts.lib' );

		$page	= new UI_HTML_PageFrame();
		$page->setTitle( $config->get( 'app.name' ) );
		$page->setBaseHref( $this->env->getBaseUrl() );
//		$page->addStylesheet( $pathLibCss.'blueprint/reset.css' );
//		$page->addStylesheet( $pathLibCss.'blueprint/typography.css' );
//		$page->addStylesheet( $pathLibCss.'layout.column.css' );
//		$page->addStylesheet( $pathLibCss.'xmp.formats.css' );
		$page->addStylesheet( $pathCss.'custom/css/bootstrap.min.css' );
		$page->addStylesheet( $pathCss.'custom/css/browser.css' );
		$page->addStylesheet( $pathLibJs.'jquery/cmExceptionView/0.2.css' );
		$page->addJavaScript( $pathLibJs.'jquery/1.7.min.js' );
		$page->addJavaScript( $pathLibJs.'jquery/cmExceptionView/0.2.js' );
		$page->addJavaScript( $pathJs.'jquery.deparam.js' );
		$page->addJavaScript( $pathJs.'LocalServerFrontendController.js' );
		$page->addJavaScript( $pathJs.'browser.js' );
		$page->addBody( UI_Template::render( $pathTmpl.'browser.tmpl', $data ) );
		return $page->build();
	}
}
?>