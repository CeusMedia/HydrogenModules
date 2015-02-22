<?php
/**
 *	Controller for system CPU load handling and indicating.
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	Ceus Media 2015
 */
/**
 *	Controller for system CPU load handling and indicating.
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	Ceus Media 2015
 *	@extends	CMF_Hydrogen_Controller
 */
class Controller_System_Load extends CMF_Hydrogen_Controller{

	static public function ___onEnvInit( $env, $context, $module, $arguments = array() ){
		$cores	= $env->getConfig()->get( 'module.server_system_load.cores' );						//  get number of cpu cores from module config
		$max	= $env->getConfig()->get( 'module.server_system_load.max' );						//  get maximum load from module config
		$retry	= $env->getConfig()->get( 'module.server_system_load.retryAfter' );					//  get seconds to retry after from module config
		$loads	= sys_getloadavg();																	//  get system load values
		$load	= array_shift( $loads ) / $cores;													//  get load of last minute relative to number of cores
		if( $max > 0 && $load > $max ){																//  a maximum load is set and load is higer than that
			if( is_a( $env, 'CMF_Hydrogen_Environment_Remote' ) )									//  if application is accessed remotely
				throw new RuntimeException( 'Service not available: server load too high', 503 );	//  throw exception instead of HTTP response
			header( 'HTTP/1.1 503 Service Unavailable' );											//  send HTTP 503 code
			header( 'Content-type: text/html; charset=utf-8' );										//  send MIME type header for UTF-8 HTML error page
			if( (int) $retry > 0 )																	//  seconds to retry after are set
				header( 'Retry-After: '.$retry );													//  send retry header
			$message	= '<h1>Service not available</h1><p>Due to heavy load this service is temporarily not available.<br/>Please try again later.</p>';
			$language	= $env->getLanguage()->getLanguage();										//  get default language
			$pathLocale	= $env->getConfig()->get( 'path.locales' ).$language.'/';					//  get path of locales
			$fileName	= $pathLocale.'html/error/503.html';										//  error page file name
			if( file_exists( $fileName ) )															//  error page file exists
				$message	= File_Reader::load( $fileName );										//  load error page content
			print( $message );																		//  display error message
			exit;																					//  and quit application
		}
	}

	public function ajaxGetLoad( $mode = 0, $relative = NULL ){
		if( !$this->env->getRequest()->isAjax() )													//  not an AJAX request
			throw new RuntimeException( 'Accessible using AJAX only' );								//  quit with exception
		$load		= $this->getLoad( (int) $mode, (bool) $relative );								//  get system load
		$this->respondJson( array( 'load' => $load, 'time' => time() ) );							//  send loads as JSON response
	}

	public function ajaxGetLoads( $relative = NULL ){
		if( !$this->env->getRequest()->isAjax() )													//  not an AJAX request
			throw new RuntimeException( 'Accessible using AJAX only' );								//  quit with exception
		$loads		= $this->getLoads( (bool) $relative );											//  get system loads
		$this->respondAsJson( array( 'load' => $loads, 'time' => time() ) );						//  send loads as JSON response
	}

	/**
	 *	Show server load (of last 1, 5 or 15 minutes, absolute or relative to number of CPU cores) as HTML indicator.
	 *	@access		public
	 *	@param		integer		$mode		Get load for last 1, 5 or 15 minutes (mode: 0, 1, 2)
	 *	@param		boolean		$relative	Calculate load in relation to number of CPU cores
	 *	@return		void
	 */
	public function ajaxRenderIndicator( $mode = 0, $relative = NULL ){
		$this->addData( 'load', $this->getLoad( (int) $mode, (boolean) $relative ) );				//  append selected system load to view
	}

	/**
	 *	Returns server load (of last 1, 5 or 15 minutes), absolute or relative to number of CPU cores.
	 *	Needs number of CPU cores to be configured to work correctly.
	 *	@access		public
	 *	@param		integer		$mode		Get load for last 1, 5 or 15 minutes (mode: 0, 1, 2)
	 *	@param		boolean		$relative	Calculate load in relation to number of CPU cores
	 *	@return		float					Server load of last 1, 5 or 15 minutes (depending on mode)
	 */
	protected function getLoad( $mode = 0, $relative = NULL ){
		$mode	= max( 0, min( 2, (int) $mode ) );													//  make sure mode is of integer within {0, 1, 2}
		$loads	= $this->getLoads( $relative );														//  get server loads of last 1, 5 and 15 minutes
		return $loads[$mode];																		//  return one selected load value as float
	}

	/**
	 *	Returns server loads of last 1, 5 and 15 minutes, absolute or relative to number of CPU cores.
	 *	Needs number of CPU cores to be configured to work correctly.
	 *	@access		public
	 *	@param		boolean		$relative	Calculate load in relation to number of CPU cores
	 *	@return		array					Server load of last 1, 5 or 15 minutes as list floats
	 */
	protected function getLoads( $relative = NULL ){
		$loads		= sys_getloadavg();																//  get server loads
		if( $relative )																				//  get loads relative to number of CPU cores
			if( $cores	= $this->env->getConfig()->get( 'module.server_system_load.cores' ) )		//  get number of cores from config
				for( $i=0; $i<=2; $i++)																//  iterate modes (1, 5, 15 minutes)
					$loads[$i]	= $loads[$i] / $cores;												//  calculate load relative to cores
		return $loads;																				//  return loads as list of floats
	}

	/**
	 *	Send data as JSON response and quit execution.
	 *	@access		protected
	 *	@param		mixed		$data		Data to respond as JSON
	 *	@param		integer		$status		HTTP status code to set, default: 200
	 *	@param		array		$headers	Map of additional response headers
	 *	@return		void
	 *	@todo		kriss: move this method to Hydrogen controller
	 */
	protected function respondAsJson( $data, $status = 200, $headers = array() ){
		$response	= $this->env->getResponse();													//  prepare request response
		$response->addHeaderPair( 'Content-type', 'application/json' );								//  set response MIME type fo JSON
		foreach( $headers as $key => $value )														//  iterate additional headers
			$response->addHeaderPair( $key, $value );												//  add additional header to response
		$response->setBody( json_encode( $data ) );													//  add JSON encoded data to response
		Net_HTTP_Response_Sender::sendResponse( $response, NULL, TRUE );							//  send response
		exit;																						//  and quit execution		
	}
}
?>
