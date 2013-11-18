<?php
class View_Manage_Catalog_Author extends View_Manage_Catalog{

	public function add(){}

	public function edit(){}

	public function index(){}

	protected function renderList( $authors, $authorId = NULL ){
		$list	= array();
		foreach( $authors as $author ){
			$url	= './manage/catalog/author/edit/'.$author->authorId;
			$label	= $author->lastname.', '.$author->firstname;
			$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
			$class	= $authorId == $author->authorId ? "active" : "";
			$list[$author->lastname.'_'.$author->firstname]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
		}
		ksort( $list );
		$attributes	= array( 'class' => 'nav nav-pills nav-stacked boxed', 'id' => 'list-authors', 'style' => 'display: none' );
		$list	= UI_HTML_Tag::create( 'ul', $list, $attributes );
		return $list;
	}
}
?>
