<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/**
 *	Authentication View.
 *	@category		cmApps
 *	@package		Chat.Admin.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class View_Auth extends View
{
	public static function renderTabs( Environment $env, $current = 0 ): string
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './' );
		$env->getModules()->callHook( "Auth", "registerLoginTabs", $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}

	public function confirm()
	{
	}

	public function login()
	{
	}

	public function password()
	{
	}

	public function register()
	{
	}

	public function renderRegisterFormExtensions(): ?bool
	{
		$payload	= [];
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this, $payload );
	}
}
