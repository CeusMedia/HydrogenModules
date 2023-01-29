<?php

use CeusMedia\HydrogenFramework\View;

class View_Work_Uberlog extends View
{
	public function index(): void
	{
	}

	public function view(): void
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()
			->loadLocalScript( 'WorkUberlogView.js' )
			->runScript( 'WorkUberlogView.init();' )
			->runScript( 'UberlogClient.host = "'.getEnv( 'HTTP_HOST' ).'";' )
			->addThemeStyle( 'site.work.uberlog.css' );
	}
}
