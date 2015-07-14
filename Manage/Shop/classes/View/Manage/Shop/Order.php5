<?php
class View_Manage_Shop_Order extends View_Manage_Shop{

	public function add(){}
	public function edit(){}
	public function index(){}

	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/shop' );								//  load words
		$context->registerTab( 'order', $words->tabs['orders'], 1 );									//  register orders tab
//		$context->registerTab( 'shipping', $words->tabs['shipping'], 5 );								//  register shipping tab
	}

	protected function renderList( $orders, $orderId = NULL ){
		return '[LIST]';
		$list	= array();
		foreach( $articles as $article ){
			$url	= './manage/catalog/article/edit/'.$article->article_id;
			$label	= $article->title;
			$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
			$class	= $articleId == $article->article_id ? "active" : "";
			$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
		}
//		ksort( $list );
		$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked boxed' ) );
		return $list;
	}

}
?>
