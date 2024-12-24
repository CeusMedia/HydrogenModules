<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Manage_Catalog_Author extends AjaxController
{
	/**
	 *	@param		string		$tabKey
	 *	@return		int
	 *	@throws		JsonException
	 */
	public function setTab( string $tabKey ): int
	{
		$this->session->set( 'manage.catalog.author.tab', $tabKey );
		return $this->respondData( TRUE );
	}
}
