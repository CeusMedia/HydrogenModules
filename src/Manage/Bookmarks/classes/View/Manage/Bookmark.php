<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Bookmark extends View
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

	protected function renderList( $bookmarkId = NULL ): string
	{
		$list	= '<div><small class="muted"><em>Keine vorhanden.</em></small></div>';
		if( ( $bookmarks = $this->getData( 'bookmarks' ) ) ){
			$list	= [];
			foreach( $bookmarks as $entry ){
				$class	= $entry->bookmarkId == $bookmarkId ? 'active' : NULL;
				$link	= HtmlTag::create( 'a', $entry->title, ['href' => './manage/bookmark/edit/'.$entry->bookmarkId] );
				$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
			}
			$list	= HtmlTag::create( 'ul', $list, ['class' => 'nav nav-pills nav-stacked'] );
		}
		return $list;
	}
}
