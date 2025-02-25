<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Work_Mail_Sync extends View
{
	public function add()
	{
	}

	public function addSync()
	{
		$this->env->getPage()->js->addScriptOnReady( 'WorkMailSync.init()' );
	}

	public function editSync(): void
	{
		$this->env->getPage()->js->addScriptOnReady( 'WorkMailSync.init()' );
	}

	public function addHost(): void
	{
		$this->env->getPage()->js->addScriptOnReady( 'WorkMailSync.init()' );
	}

	public function index(): void
	{
	}
}
