<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\View;

/**
 *	Authentication View.
 *	@category		cmApps
 *	@package		Chat.Admin.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Ceus Media (https://ceusmedia.de/)
 */
class View_Auth_Oauth2 extends View
{
	public function confirm()
	{
	}

	public function index()
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

	/**
	 *	@throws		ReflectionException
	 */
	public function renderRegisterFormExtensions(): ?bool
	{
		$payload	= [];
		return $this->env->getCaptain()->callHook( 'Auth', 'renderRegisterFormExtensions', $this, $payload );
	}
}
