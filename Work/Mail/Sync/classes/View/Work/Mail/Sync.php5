<?php
class View_Work_Mail_Sync extends CMF_Hydrogen_View
{
	static public function ___onRegisterTab( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
	}

	public function add()
	{
	}

	public function addSync()
	{
		$this->env->getPage()->js->addScriptOnReady( 'WorkMailSync.init()' );
	}

	public function editSync()
	{
		$this->env->getPage()->js->addScriptOnReady( 'WorkMailSync.init()' );
	}

	public function addHost()
	{
		$this->env->getPage()->js->addScriptOnReady( 'WorkMailSync.init()' );
	}

	public function index()
	{
	}
}
