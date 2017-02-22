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

	protected $config;
	protected $cpuCores;
	protected $moduleConfig;

	public function __onInit(){
		$this->config		= $this->env->getConfig();
		$this->moduleConfig	= $this->config->getAll( 'module.server_system_load.', TRUE );			//  shortcut module configuration
		$this->cpuCores		= (int) $this->moduleConfig->get( 'cores' );							//  get number of cpu cores from module config
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'cpuCores', $this->cpuCores );
	}

	static public function ___onEnvInit( $env, $context, $module, $arguments = array() ){
		$moduleConfig		= $env->getConfig()->getAll( 'module.server_system_load.', TRUE );		//  shortcut module configuration

		$cores	= (int) $moduleConfig->get( 'cores' );												//  get number of cpu cores from module config
		$max	= (float) $moduleConfig->get( 'max' );												//  get maximum load from module config

		$loads	= sys_getloadavg();																	//  get system load values
		$load	= array_shift( $loads ) / $cores;													//  get load of last minute relative to number of cores
		if( $max > 0 && $load > $max ){																//  a maximum load is set and load is higher than that
			if( is_a( $env, 'CMF_Hydrogen_Environment_Remote' ) )									//  if application is accessed remotely
				throw new RuntimeException( 'Service not available: server load too high', 503 );	//  throw exception instead of HTTP response
			header( 'HTTP/1.1 503 Service Unavailable' );											//  send HTTP 503 code
			header( 'Content-type: text/html; charset=utf-8' );										//  send MIME type header for UTF-8 HTML error page
			if( $moduleConfig->get( 'retryAfter' ) > 0 )										//  seconds to retry after are set
				header( 'Retry-After: '.$moduleConfig->get( 'retryAfter' ) );					//  send retry header
			$message	= '<h1>Service not available</h1><p>Due to heavy load this service is temporarily not available.<br/>Please try again later.</p>';
			$language	= $env->getLanguage()->getLanguage();										//  get default language
			$pathLocale	= $env->getConfig()->get( 'path.locales' ).$language.'/';					//  get path of locales
			$fileName	= $pathLocale.'html/error/503.html';										//  error page file name
			if( file_exists( $fileName ) )															//  error page file exists
				$message	= FS_File_Reader::load( $fileName );									//  load error page content
			print( $message );																		//  display error message
			exit;																					//  and quit application
		}
	}

	static public function ___onRegisterDashboardPanels( $env, $context, $module, $data = array() ){
		$context->registerPanel( 'system-server-load', array(
			'url'		=> './system/load/ajaxRenderDashboardPanel',
			'icon'		=> 'fa fa-fw fa-bar-chart',
			'title'		=> 'System: Auslastung',
			'heading'	=> 'System: Auslastung',
			'refresh'	=> 10,
		) );
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
		$loads	= self::getLoads( (bool) $relative, $this->cpuCores );								//  get system loads
		$this->respondAsJson( array( 'load' => $loads, 'time' => time() ) );						//  send loads as JSON response
	}

	public function ajaxRenderDashboardPanel( $panelId ){
		$this->addData( 'panelId', $panelId );
		switch( $panelId ){
			case 'system-server-load-current':
			default:
				$this->addData( 'loads', self::getLoads( TRUE, $this->cpuCores ) );					//  append system load values relative to CPU core
		}
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
		$loads	= self::getLoads( (bool) $relative, $this->cpuCores );								//  get server loads of last 1, 5 and 15 minutes
		return $loads[$mode];																		//  return one selected load value as float
	}

	/**
	 *	Returns server loads of last 1, 5 and 15 minutes, absolute or relative to number of CPU cores.
	 *	Needs number of CPU cores to be configured to work correctly.
	 *	@static
	 *	@access		public
	 *	@param		boolean		$relative	Calculate load in relation to number of CPU cores
	 *	@param		integer		$cores		Number of CPU cores
	 *	@return		array					Server load of last 1, 5 or 15 minutes as list floats
	 */
	static public function getLoads( $relative = FALSE, $cores = 1 ){
		$loads	= sys_getloadavg();
		$cores	= max( 1, floor( (float) $cores ) );
		if( $relative ){
			if( $cores < 1 )
				throw new InvalidArgumentException( 'Number of core must be atleast 1' );
			if( $cores > 1 ){
				foreach( $loads as $nr => $load ){
					$loads[$nr]	= $load / $cores;
				}
			}
		}
		return $loads;
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
