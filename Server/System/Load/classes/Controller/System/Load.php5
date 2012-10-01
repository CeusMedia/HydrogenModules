<?php
class Controller_System_Load extends CMF_Hydrogen_Controller{

	protected function ajaxGetLoad( $mode = 0 ){
		if( $mode < 0 || $mode > 2 )
			$mode	= 0;
		$loads		= sys_getloadavg();
		header( 'Content-type: application/json' );
		print( json_encode( $loads[$mode] ) );
		exit;
	}
	
//	public function get( $relative = FALSE ){
//	}

	public function ajaxRenderIndicator( $mode = 0 ){
		$this->addData( 'load', $this->getLoad( $mode ) );
	}
}
?>