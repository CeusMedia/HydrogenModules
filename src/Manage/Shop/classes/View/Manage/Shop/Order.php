<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Manage_Shop_Order extends View_Manage_Shop
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

	/**
	 * @deprecated	since not used and incomplete
	 * @todo		to be removed
	 */
	protected function renderList( $orders, $orderId = NULL ): string
	{
		return '[LIST]';
		$list	= [];
		foreach( $articles as $article ){
			$url	= './manage/catalog/article/edit/'.$article->article_id;
			$label	= $article->title;
			$link	= HtmlTag::create( 'a', $label, ['href' => $url] );
			$class	= $articleId == $article->article_id ? "active" : "";
			$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
		}
//		ksort( $list );
		$list	= HtmlTag::create( 'ul', $list, ['class' => 'nav nav-pills nav-stacked boxed'] );
		return $list;
	}
}
