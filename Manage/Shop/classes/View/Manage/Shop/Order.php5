<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Manage_Shop_Order extends View_Manage_Shop
{
	public function add()
	{
	}

	public function edit()
	{
	}

	public function index()
	{
	}

	public static function ___onRegisterTab( Environment $env, $context, $module, $data )
	{
		$words	= (object) $env->getLanguage()->getWords( 'manage/shop' );								//  load words
		$context->registerTab( 'order', $words->tabs['orders'], 1 );									//  register orders tab
//		$context->registerTab( 'shipping', $words->tabs['shipping'], 5 );								//  register shipping tab
	}

	protected function renderList( $orders, $orderId = NULL )
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
