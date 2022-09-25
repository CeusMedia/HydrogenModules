<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Manage_Catalog_Author extends View_Manage_Catalog{

	public function add(){}

	public function edit(){}

	public function index(){}

	protected function renderList( $authors, $authorId = NULL ){
		$list	= [];
		foreach( $authors as $author ){
			$url	= './manage/catalog/author/edit/'.$author->authorId;
			$label	= $author->lastname.', '.$author->firstname;
			$link	= HtmlTag::create( 'a', $label, array( 'href' => $url ) );
			$class	= $authorId == $author->authorId ? "active" : "";
			$list[$author->lastname.'_'.$author->firstname]	= HtmlTag::create( 'li', $link, array( 'class' => $class ) );
		}
		ksort( $list );
		$attributes	= array( 'class' => 'nav nav-pills nav-stacked boxed', 'id' => 'list-authors', 'style' => 'display: none' );
		$list	= HtmlTag::create( 'ul', $list, $attributes );
		return $list;
	}
}
?>
