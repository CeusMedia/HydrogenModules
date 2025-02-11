<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Manage_Catalog_Bookstore_Article extends View_Manage_Catalog_Bookstore
{
	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	public function index(): void
	{
	}

	protected function renderList( array $articles, int|string|NULL $articleId = NULL ): string
	{
		$list	= [];
		foreach( $articles as $article ){
			$url	= './manage/catalog/bookstore/article/edit/'.$article->articleId;
			$label	= $article->title;
			$link	= HtmlTag::create( 'a', $label, ['href' => $url] );
			$class	= $articleId == $article->articleId ? "active" : "";
			$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
		}
//		ksort( $list );
		return HtmlTag::create( 'ul', $list, ['class' => 'nav nav-pills nav-stacked boxed'] );
	}
}
