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
class Controller_System_Load extends CMF_Hydrogen_Controller
{
	protected $config;
	protected $cpuCores;
	protected $moduleConfig;

	/**
	 *	Returns server loads of last 1, 5 and 15 minutes, absolute or relative to number of CPU cores.
	 *	Needs number of CPU cores to be configured to work correctly.
	 *	@static
	 *	@access		public
	 *	@param		boolean		$relative	Calculate load in relation to number of CPU cores
	 *	@param		integer		$cores		Number of CPU cores
	 *	@return		array					Server load of last 1, 5 or 15 minutes as list floats
	 */
	public static function getLoads( bool $relative = FALSE, int $cores = 1 ): array
	{
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

	public function ajaxGetLoad( int $mode = 0, bool $relative = FALSE )
	{
		if( !$this->env->getRequest()->isAjax() )													//  not an AJAX request
			throw new RuntimeException( 'Accessible using AJAX only' );								//  quit with exception
		$load		= $this->getLoad( (int) $mode, (bool) $relative );								//  get system load
		$this->respondJson( array( 'load' => $load, 'time' => time() ) );							//  send loads as JSON response
	}

	public function ajaxGetLoads( bool $relative = FALSE )
	{
		if( !$this->env->getRequest()->isAjax() )													//  not an AJAX request
			throw new RuntimeException( 'Accessible using AJAX only' );								//  quit with exception
		$loads	= self::getLoads( (bool) $relative, $this->cpuCores );								//  get system loads
		$this->respondAsJson( array( 'load' => $loads, 'time' => time() ) );						//  send loads as JSON response
	}

	public function ajaxRenderDashboardPanel( string $panelId )
	{
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
	public function ajaxRenderIndicator( int $mode = 0, bool $relative = FALSE )
	{
		$this->addData( 'load', $this->getLoad( (int) $mode, (boolean) $relative ) );				//  append selected system load to view
	}

	protected function __onInit()
	{
		$this->config		= $this->env->getConfig();
		$this->moduleConfig	= $this->config->getAll( 'module.server_system_load.', TRUE );			//  shortcut module configuration
		$this->cpuCores		= (int) $this->moduleConfig->get( 'cores' );							//  get number of cpu cores from module config
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'cpuCores', $this->cpuCores );
	}

	/**
	 *	Returns server load (of last 1, 5 or 15 minutes), absolute or relative to number of CPU cores.
	 *	Needs number of CPU cores to be configured to work correctly.
	 *	@access		public
	 *	@param		integer		$mode		Get load for last 1, 5 or 15 minutes (mode: 0, 1, 2)
	 *	@param		boolean		$relative	Calculate load in relation to number of CPU cores
	 *	@return		float					Server load of last 1, 5 or 15 minutes (depending on mode)
	 */
	protected function getLoad( int $mode = 0, bool $relative = FALSE ): float
	{
		$mode	= max( 0, min( 2, (int) $mode ) );													//  make sure mode is of integer within {0, 1, 2}
		$loads	= self::getLoads( (bool) $relative, $this->cpuCores );								//  get server loads of last 1, 5 and 15 minutes
		return $loads[$mode];																		//  return one selected load value as float
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
	protected function respondAsJson( $data, int $status = 200, array $headers = [] )
	{
		$response	= $this->env->getResponse();													//  prepare request response
		$response->addHeaderPair( 'Content-type', 'application/json' );								//  set response MIME type fo JSON
		foreach( $headers as $key => $value )														//  iterate additional headers
			$response->addHeaderPair( $key, $value );												//  add additional header to response
		$response->setBody( json_encode( $data ) );													//  add JSON encoded data to response
		Net_HTTP_Response_Sender::sendResponse( $response, NULL, TRUE );							//  send response
		exit;																						//  and quit execution
	}
}
