<?php
class Controller_System_Load extends CMF_Hydrogen_Controller{

	public function ajaxGetLoad( $mode = 0, $relative = NULL ){
		$load	= $this->getLoad( $mode, $relative );
		header( 'Content-type: application/json' );
		print( json_encode( $load ) );
		exit;
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

	public function ajaxRenderIndicator( $mode = 0, $relative = NULL ){
		$this->addData( 'load', $this->getLoad( $mode, $relative ) );
	}
}
?>