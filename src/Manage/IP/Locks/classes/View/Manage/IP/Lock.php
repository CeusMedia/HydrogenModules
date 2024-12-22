<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_IP_Lock extends View
{
	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	public function index(): void
	{
	}

	/**
	 *	@param		WebEnvironment		$env
	 *	@param		$current
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public static function renderTabs( WebEnvironment $env, $current = 0 ): string
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './manage/ip/lock/' );
		$env->getModules()->callHook( 'IpLock', 'registerTabs', $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}
}
