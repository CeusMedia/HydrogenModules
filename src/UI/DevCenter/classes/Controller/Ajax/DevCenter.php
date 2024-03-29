<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_DevCenter extends AjaxController
{
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

	protected function __onInit(): void
	{
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
	}
}
