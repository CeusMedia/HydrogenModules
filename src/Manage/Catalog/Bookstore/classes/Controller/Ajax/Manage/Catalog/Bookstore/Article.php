<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Controller\Ajax as Controller;

class Controller_Manage_Catalog_Bookstore_Article extends Controller
{

	protected Logic_Catalog_BookstoreManager $logic;

	public function getTags(): void
	{
		$startsWith	= $this->request->get( 'query' );
		$conditions	= ['tag' => $startsWith.'%'];
		$orders		= ['tag' => 'ASC'];
		$limits		= [0, 10];
		$tags		= $this->logic->getTags( $conditions, $orders, $limits );
		$list		= array_map(static fn(object $tag) => $tag->tag, $tags);
		sort( $list );
		$this->respondData( $list);
	}

	/**
	 *	@return		void
	 */
	public function getIsns(): void
	{
		$startsWith	= $this->request->get( 'query' );
		$conditions	= ['isn' => $startsWith.'%'];
		$orders		= ['isn' => 'ASC'];
		$limits		= [0, 10];
		$articles	= $this->logic->getArticles( $conditions, $orders, $limits );
		$list		= array_map(static fn(object $article) => $article->isn, $articles);
		sort( $list );
		$this->respondData( $list );
	}

	public function setTab( string $tabKey ): void
	{
		$this->session->set( 'manage.catalog.bookstore.article.tab', $tabKey );
		exit;
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		parent::__onInit();
		$this->logic			= new Logic_Catalog_BookstoreManager( $this->env );
	}
}
