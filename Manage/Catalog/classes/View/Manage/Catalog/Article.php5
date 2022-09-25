<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Manage_Catalog_Article extends View_Manage_Catalog{

	public function add(){}
	public function edit(){}
	public function index(){}

	protected function renderList( $articles, $articleId = NULL ){
		$list	= [];
		foreach( $articles as $article ){
			$url	= './manage/catalog/article/edit/'.$article->articleId;
			$label	= $article->title;
			$link	= HtmlTag::create( 'a', $label, array( 'href' => $url ) );
			$class	= $articleId == $article->articleId ? "active" : "";
			$list[]	= HtmlTag::create( 'li', $link, array( 'class' => $class ) );
		}
//		ksort( $list );
		$list	= HtmlTag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked boxed' ) );
		return $list;
	}

}
?>
