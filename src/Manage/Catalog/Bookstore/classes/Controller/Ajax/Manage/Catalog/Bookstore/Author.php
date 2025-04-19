<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Manage_Catalog_Bookstore_Author extends AjaxController
{
	protected Logic_Catalog_BookstoreManager $logic;

	/**
	 *	@param		string		$tabKey
	 *	@return		void
	 */
	public function setTab( string $tabKey ): void
	{
		$this->session->set( 'manage.catalog.bookstore.author.tab', $tabKey );
		exit;
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->logic		= new Logic_Catalog_BookstoreManager( $this->env );
	}
}
