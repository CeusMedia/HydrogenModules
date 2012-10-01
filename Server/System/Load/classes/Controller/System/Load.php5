<?php
class Controller_System_Load extends CMF_Hydrogen_Controller{

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