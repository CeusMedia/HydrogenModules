<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Manage_Catalog_Article extends AjaxController
{
	protected Logic_Catalog $logic;
	protected string $sessionPrefix;

	/**
	 *	@return		int
	 *	@throws		JsonException
	 */
	public function getTags(): int
	{
		$startsWith	= $this->request->get( 'query' );
		if( '' === $startsWith )
			return $this->respondData( [] );
		$conditions	= ['tag' => $startsWith.'%'];
		$orders		= ['tag' => 'ASC'];
		$limits		= [0, 10];
		$tags		= $this->logic->getTags( $conditions, $orders, $limits );
		$list		= [];
		foreach( $tags as $tag )
			$list[$tag->tag]	= $tag->tag;
		ksort( $list );
		return $this->respondData( array_keys( $list ) );
	}

	/**
	 *	@return		int
	 *	@throws		JsonException
	 */
	public function getIsns(): int
	{
		$startsWith	= $this->request->get( 'query', '' );
		if( '' === $startsWith )
			return $this->respondData( [] );
		$articles	= $this->logic->getArticles(
			['isn' => $startsWith.'%'],
			['isn' => 'ASC'],
			[0, 10]
		);
		$list		= [];
		foreach( $articles as $article )
			$list[$article->isn]	= $article->isn;
		ksort( $list );
		return $this->respondData( array_keys( $list ) );
	}

	/**
	 *	@param		string		$tabKey
	 *	@return		int
	 *	@throws		JsonException
	 */
	public function setTab( string $tabKey ): int
	{
		$this->session->set( 'manage.catalog.article.tab', $tabKey );
		return $this->respondData( TRUE );
	}

	protected function __onInit(): void
	{
		parent::__onInit();
		$this->logic			= new Logic_Catalog( $this->env );
		$this->sessionPrefix	= 'module.manage_catalog_article.filter.';
	}
}
