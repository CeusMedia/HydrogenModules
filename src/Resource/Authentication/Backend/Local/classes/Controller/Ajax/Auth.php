<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Auth extends AjaxController
{
	/**
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function isAuthenticated(): void
	{
		$this->respondData( ['result' => $this->session->has( 'auth_user_id' )] );
	}

	/**
	 *	@deprecated		use isAuthenticated instead
	 *	@todo			to be removed
	 */
	public function refreshSession(): void
	{
		$this->ajaxIsAuthenticated();
	}
}
