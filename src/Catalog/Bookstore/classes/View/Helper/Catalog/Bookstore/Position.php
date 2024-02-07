<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Language;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

class View_Helper_Catalog_Bookstore_Position
{
	protected Environment $env;

	/**	@var	Logic_Catalog_Bookstore	$logic */
	protected Logic_Catalog_Bookstore $logic;

	/**	@var	View_Helper_Catalog_Bookstore	$helper */
	protected View_Helper_Catalog_Bookstore $helper;

	protected Language $language;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->logic	= new Logic_Catalog_Bookstore( $env );
		$this->language	= $this->env->getLanguage();
		$this->helper	= new View_Helper_Catalog_Bookstore( $env );
	}

	/**
	 *	@param		object $article
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renderFromArticle( object $article ): string
	{
		$category	= $this->logic->getCategoryOfArticle( $article->articleId );
		$list	= [$category];
		while( $category->parentId ){
			$category	= $this->logic->getCategory( $category->parentId );
			$list[]	= $category;
		}
		$categories	= array_reverse( $list );
		return $this->renderList( $categories );
	}

	/**
	 *	@param		object|NULL		$category
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renderFromCategory( ?object $category = NULL ): string
	{
		if( !is_object( $category ) )
			return $this->renderList( [], FALSE );
		$list	= [$category];
		while( $category->parentId ){
			$category	= $this->logic->getCategory( $category->parentId );
			$list[]	= $category;
		}
		$categories	= array_reverse( $list );
		return $this->renderList( $categories, FALSE );
	}

	protected function renderList( array $categories, bool $linkLast = TRUE ): string
	{
		$level		= count( $categories );
		$labelKey	= 'label_'.$this->language->getLanguage();
		array_unshift( $categories, (object) [
			'label_de'		=> 'Übersicht',
			'categoryId'	=> 0,
		] );
		$list	= [];
		foreach( $categories as $nr => $category ){
			$url	= $this->helper->getCategoryUri( $category );
			$class	= 'category-level-'.$nr;
			$link	= '<a href="'.$url.'" class="'.$class.'">'.$category->$labelKey.'</a>';
			if( !$linkLast && $level == $nr )
				$link	= '<span class="'.$class.'">'.$category->$labelKey.'</span>';
			$list[]	= $link;
		}
		$list	= join( '&nbsp;>&nbsp;', $list );
		return '<div id="layout-position">Position: '.$list.'</div>';
	}
}
