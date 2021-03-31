<?php
class Controller_DevCenter extends CMF_Hydrogen_Controller_Ajax
{
	public function __onInit()
	{
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
	}

	public function setHeight()
	{
		$height		= round( (float) $this->request->get( 'height' ), 6 );
		$this->session->set( 'DevCenterHeight', $height );
		$this->respondData( TRUE );
	}

	public function setState()
	{
		$this->session->set( 'DevCenterStatus', (bool) $this->request->get( 'open' ) );
		$this->respondData( TRUE );
	}

	public function setTab()
	{
		$this->session->set( 'DevCenterTab', $this->request->get( 'tab' ) );
		$this->respondData( TRUE );
	}
}
