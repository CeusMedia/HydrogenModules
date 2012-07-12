<?php
class View_Helper_Blog{

	static public function getArticleTitleUrlLabel( $article ){
		$cut	= array( '?', '!', ':', '.', ';', '"', "'" );
		$label	= str_replace( $cut, '', $article->title );											//  remove inappropriate characters
		$label	= str_replace( ' ', '-', $label );													//  replace whitespace by hyphen
		$label	= preg_replace( '/-+/', '-', $label );												//  shorten hyphens to one
		return urlencode( $label );																	//  return encoded URL component
	}
	
	
	static public function renderLatestArticles( $env, $limit ){
		$list	= array();
		$model	= new Model_Article( $env );
		$latest	= $model->getAll( array( 'status' => 1 ), array( 'articleId' => 'DESC' ), array( 0, $limit ) );
		foreach( $latest as $article ){
			$link	= UI_HTML_Tag::create( 'a', $article->title, array( 'href' => 'blog/article/'.$article->articleId.'' ) );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => 'gallery-item' ) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'list-latest-articles' ) );
	}
}
?>