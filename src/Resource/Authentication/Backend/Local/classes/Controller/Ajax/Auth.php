<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Auth extends AjaxController
{
	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	public function isAuthenticated(): void
	{
		$logic	= Logic_Authentication::getInstance( $this->env );
		$logic->getCurrentUserId( FALSE );
		$this->respondData( ['result' => $this->session->has( 'auth_user_id' )] );
	}

	/**
	 *	@deprecated		use isAuthenticated instead
	 *	@todo			to be removed
	 */
	public function refreshSession(): void
	{
		$this->isAuthenticated();
	}
}
