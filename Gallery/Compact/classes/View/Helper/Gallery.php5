<?php
class View_Helper_Gallery{
	static public function renderLatestGalleries( $limit ){
		$list		= array();
		$path		= 'contents/gallery/';
		$index		= Folder_RecursiveLister::getFolderList( $path, '/^[0-9]{4}-[0-9]{2}-[0-9]{2} /' );
		foreach( $index as $folder )
			$list[$folder->getFilename()]	= substr( $folder->getPathname(), strlen( $path ) );
		natcasesort( $list );
		$latest	= array_reverse( array_slice( $list, -$limit ) );
		$list	= array();
		foreach( $latest as $title => $path ){
			$link	= UI_HTML_Tag::create( 'a', $title, array( 'href' => 'gallery/index/'.$path ) );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => 'gallery-item' ) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'list-latest-galleries' ) );
	}
}
?>