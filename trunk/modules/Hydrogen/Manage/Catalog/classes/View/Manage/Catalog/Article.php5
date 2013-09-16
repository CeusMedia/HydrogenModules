<?php
class View_Manage_Catalog_Article extends View_Manage_Catalog{

	public function add(){}
	public function edit(){}
	public function index(){}

	protected function renderList( $articles, $articleId = NULL ){
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
