<?php
class View_Helper_Gallery{
	
	static public function getFeedUrl( CMF_Hydrogen_Environment_Abstract $env, $limit = NULL ){
		$limit	= ( $limit !== NULL ) ? '/'.abs( (int) $limit ) : '';
		return $env->getConfig()->get( 'app.base.url' ).'gallery/feed'.$limit;
	}

	static public function renderLatestGalleries( $env, $limit, $offset = 0 ){
		$list		= array();
		$config		= $env->getConfig();
		$path		= $config->get( 'path.images' ).$config->get( 'module.gallery_compact.path' );
		$index		= Folder_RecursiveLister::getFolderList( $path, '/^[0-9]{4}-[0-9]{2}-[0-9]{2} /' );
		foreach( $index as $folder )
			$list[$folder->getFilename()]	= substr( $folder->getPathname(), strlen( $path ) );
		natcasesort( $list );
		$list	= array_reverse( $list );
		$latest	= array_slice( $list, $offset, $limit );
		$list	= array();
		foreach( $latest as $title => $path ){
			$link	= UI_HTML_Tag::create( 'a', $title, array( 'href' => 'gallery/index/'.$path ) );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => 'gallery-item' ) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'list-latest-galleries' ) );
	}
}
?>