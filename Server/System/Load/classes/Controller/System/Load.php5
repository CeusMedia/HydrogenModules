<?php
class Controller_System_Load extends CMF_Hydrogen_Controller{

	static public function ___onEnvInit( $env, $context, $module, $arguments = array() ){
		$cores	= $env->getConfig()->get( 'module.server_system_load.cores' );								//  get number of cpu cores from module config
		$max	= $env->getConfig()->get( 'module.server_system_load.max' );								//  get maximum load from module config
		$retry	= $env->getConfig()->get( 'module.server_system_load.retryAfter' );							//  get seconds to retry after from module config
		$loads	= sys_getloadavg();
		$load	= array_shift( $loads ) / $cores;															//  calculate load relative to number of cores
		if( $max > 0 && $load > $max ){																		//  a maximum load is set and load is higer than that
			if( is_a( $env, 'CMF_Hydrogen_Environment_Remote' ) )											//  if application is accessed remotely
				throw new RuntimeException( 'Service not available: server load too high', 503 );			//  throw exception instead of HTTP response
			header( 'HTTP/1.1 503 Service Unavailable' );													//  send HTTP 503 code
			header( 'Content-type: text/html; charset=utf-8' );												//  send MIME type header for UTF-8 html error page
			if( (int) $retry > 0 )																			//  seconds to retry after are set
				header( 'Retry-After: '.$retry );															//  send retry header
			$message	= '<h1>Service not available</h1><p>Due to heavy load this service is temporarily not available.<br/>Please try again later.</p>';
			$language	= $env->getLanguage()->getLanguage();
			$pathLocale	= $env->getConfig()->get( 'path.locales' ).$language.'/';							//  get path of locales
			$fileName	= $pathLocale.'html/error/503.html';												//  error page file name
			if( file_exists( $fileName ) )																	//  error page file exists
				$message	= File_Reader::load( $fileName );												//  load error page content
			print( $message );																				//  display error message
			exit;																							//  and quit application
		}
	}

	public function ajaxGetLoad( $mode = 0, $relative = NULL ){
		if( !$this->env->getRequest()->isAjax() )
			throw new RuntimeException( 'Accessible using AJAX only' );
		$load		= $this->getLoad( $mode, $relative );
		$response	= $this->env->getResponse();
		$response->addHeaderPair( 'Content-type', 'application/json' );
		$response->setBody( json_encode( array( 'load' => $load, 'time' => time() ) ) );
		Net_HTTP_Response_Sender::sendResponse( $response, NULL, TRUE );
		exit;
	}

	public function ajaxGetLoads( $relative = NULL ){
		if( !$this->env->getRequest()->isAjax() )
			throw new RuntimeException( 'Accessible using AJAX only' );
		$loads		= sys_getloadavg();
		if( $relative )
			if( $cores	= $this->env->getConfig()->get( 'module.server_system_load.cores' ) )
				for( $i=0; $i<=2; $i++)
					$loads[$i]	= $loads[$i] / $cores;
		$response	= $this->env->getResponse();
		$response->addHeaderPair( 'Content-type', 'application/json' );
		$response->setBody( json_encode( array( 'load' => $loads, 'time' => time() ) ) );
		Net_HTTP_Response_Sender::sendResponse( $response, NULL, TRUE );
		exit;
	}

	public function ajaxRenderIndicator( $mode = 0, $relative = NULL ){
		$this->addData( 'load', $this->getLoad( $mode, $relative ) );
	}

	protected function getLoad( $mode = 0, $relative = NULL ){
		$mode	= (int) $mode;
		if( $mode < 0 || $mode > 2 )
			$mode	= 0;
		$loads		= sys_getloadavg();
		if( $relative ){
			$cores			= $this->env->getConfig()->get( 'module.server_system_load.cores' );
			$loads[$mode]	= $loads[$mode] / $cores;
		}
		return $loads[$mode];
	}
}
?>
