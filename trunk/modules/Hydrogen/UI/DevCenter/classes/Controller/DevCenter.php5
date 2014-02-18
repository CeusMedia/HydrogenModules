<?php
class Controller_DevCenter extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
	}

	public function ajaxSetHeight(){
		$height		= round( (float) $this->request->get( 'height' ), 6 );
		$this->session->set( 'DevCenterHeight', $height );
		print( json_encode( TRUE ) );
		exit;
	}

	public function ajaxSetState(){
		$this->session->set( 'DevCenterStatus', (bool) $this->request->get( 'open' ) );
		print( json_encode( TRUE ) );
		exit;
	}

	public function ajaxSetTab(){
		$this->session->set( 'DevCenterTab', $this->request->get( 'tab' ) );
		print( json_encode( TRUE ) );
		exit;
	}
}
?>