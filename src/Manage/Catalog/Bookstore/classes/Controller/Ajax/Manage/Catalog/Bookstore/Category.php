<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Controller\Ajax as Controller;

class Controller_Manage_Catalog_Bookstore_Category extends Controller
{
	protected Logic_Catalog_BookstoreManager $logic;

	/**
	 *	@param		string		$categoryId
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function getNextRank( string $categoryId ): void
	{
		$nextRank			= 0;
		$categoryArticles	= $this->logic->getCategoryArticles( $categoryId, ['rank' => 'DESC'] );
		if( $categoryArticles ) 
			$nextRank	= $categoryArticles[0]->rank + 1;
		header( 'Content-Type: application/json' );
		$this->respondData( $nextRank );
	}

	public function setTab( string $tabKey ): void
	{
		$this->session->set( 'manage.catalog.bookstore.category.tab', $tabKey );
		exit;
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_Catalog_BookstoreManager( $this->env );
	}
}
