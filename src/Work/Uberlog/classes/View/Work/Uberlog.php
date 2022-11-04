<?php

use CeusMedia\HydrogenFramework\View;

class View_Work_Uberlog extends View
{
	public function index()
	{
	}

	public function view()
	{
	}

	protected function __onInit()
	{
		$this->env->getPage()->loadLocalScript( 'WorkUberlogView.js' );
		$this->env->getPage()->runScript( 'WorkUberlogView.init();' );
		$this->env->getPage()->runScript( 'UberlogClient.host = "'.getEnv( 'HTTP_HOST' ).'";' );
		$this->env->getPage()->addThemeStyle( 'site.work.uberlog.css' );
	}
}
