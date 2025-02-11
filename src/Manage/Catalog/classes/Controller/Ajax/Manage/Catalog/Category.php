<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Manage_Catalog_Category extends AjaxController
{
	/**
	 *	@param		string		$tabKey
	 *	@return		int
	 *	@throws		JsonException
	 */
	public function setTab( string $tabKey ): int
	{
		$this->session->set( 'manage.catalog.category.tab', $tabKey );
		return $this->respondData( TRUE );
	}
}
