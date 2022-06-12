<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_Bookmark extends View{

	public function add(){}

	public function edit(){}

	public function index(){}

	protected function renderList( $bookmarkId = NULL ){
		$list	= '<div><small class="muted"><em>Keine vorhanden.</em></small></div>';
		if( ( $bookmarks = $this->getData( 'bookmarks' ) ) ){
			$list	= [];
			foreach( $bookmarks as $entry ){
				$class	= $entry->bookmarkId == $bookmarkId ? 'active' : NULL;
				$link	= UI_HTML_Tag::create( 'a', $entry->title, array( 'href' => './manage/bookmark/edit/'.$entry->bookmarkId ) );
				$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
			}
			$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
		}
		return $list;
	}
}
?>
