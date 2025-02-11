<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Manage_Catalog_Bookstore_Author extends View_Manage_Catalog_Bookstore
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

	protected function renderList( $authors, $authorId = NULL ): string
	{
		$list	= [];
		foreach( $authors as $author ){
			$url	= './manage/catalog/bookstore/author/edit/'.$author->authorId;
			$label	= $author->lastname.', '.$author->firstname;
			$link	= HtmlTag::create( 'a', $label, ['href' => $url] );
			$class	= $authorId == $author->authorId ? "active" : "";
			$list[$author->lastname.'_'.$author->firstname]	= HtmlTag::create( 'li', $link, ['class' => $class] );
		}
		ksort( $list );
		$attributes	= ['class' => 'nav nav-pills nav-stacked boxed', 'id' => 'list-authors', 'style' => 'display: none'];
		return HtmlTag::create( 'ul', $list, $attributes );
	}
}
