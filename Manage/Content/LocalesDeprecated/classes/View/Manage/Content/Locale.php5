<?php
/**
 *	Content View.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2014 Ceus Media
 *	@version		$Id$
 */

use CeusMedia\HydrogenFramework\View;

/**
 *	Content View.
 *	@extends		CMF_Hydrogen_View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2014 Ceus Media
 *	@version		$Id$
 */
class View_Manage_Content_Locale extends View{

	public function index() {
		$language	= $this->getData( 'language' );
		$type		= $this->getData( 'type' );
		$types		= $this->getData( 'types' );
		$fileId		= $this->getData( 'fileId', '' );

		$fileTree	= '';
		if( $language && $type ){
			$current	= base64_decode( $fileId );
			$files		= $this->getData( 'files' );
			$folder		= $types[$type]['folder'];
			$baseUrl	= './manage/content/locale/'.$language.'/'.$type.'/';
			$fileTree	= $this->renderTree( $baseUrl, $files, $current, $folder );


		}
		$this->addData( 'fileTree', $fileTree );

		$page	= $this->env->getPage();
		$page->addThemeStyle( 'module.manage.content.locales.css' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'LocaleEditor.js' );

		$script	= '
LocaleEditor.language	= "'.$language.'";
LocaleEditor.type		= "'.$type.'";
LocaleEditor.fileId		= "'.$fileId.'";
LocaleEditor.setupCodeMirror();';
		if( $fileId )
			$page->js->addScriptOnReady( $script, 9 );
	}

	protected function getFolders( $files ){
		$list	= [];
		foreach( array_keys( $files ) as $item ){
			if( preg_match( "/\//", $item ) ){
				$folder	= preg_replace( "/^(.+)\/.*$/U", "\\1", $item );
				if( !in_array( $folder, $list ) )
					$list[]	= $folder;
			}
		}
		return $list;
	}

	public function renderTree( $baseUrl, $files, $current = NULL, $path = NULL, $level = 0 ){
		$dict	= new ADT_List_Dictionary( $files );
		$sub	= $dict->getAll( $path );
		$list	= [];
		foreach( $sub as $filePath => $fileName ){
			if( !preg_match( "/\//", $filePath ) ){
				$extension	= pathinfo( $fileName, PATHINFO_EXTENSION );
				$classes	= array( 'autocut', 'file' );
				if( $extension )
					$classes[]	= 'file-ext-'.$extension;
				$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-file' ) );
				if( $current == $path.$fileName ){
					$classes[]	= 'active';
					$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-file icon-white' ) );
				}
				$url	= $baseUrl.base64_encode( $path.$fileName );
				$ext	= UI_HTML_Tag::create( 'small',  '.'.$extension, array( 'class' => "muted" ) );
				$name	= pathinfo( $fileName, PATHINFO_FILENAME );
				$link	= UI_HTML_Tag::create( 'a', $icon.'&nbsp;'.$name.$ext, array( 'href' => $url ) );
				$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => join( ' ', $classes ) ) );
			}
		}

		foreach( $this->getFolders( $sub ) as $folder )
			$list[]	= $this->renderTree( $baseUrl, $files, $current, $path.$folder.'/', $level + 1 );

		if( !$list )
			return '';
		$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => '' ) );
		if( !$level )
			return $list;
		$folder	= preg_replace( "/^.*\//", "", rtrim( $path, '/' ) );
		$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-folder-open' ) );
		return UI_HTML_Tag::create( 'li', $icon.'&nbsp;'.$folder.$list, array( 'class' => 'folder' ) );
	}
}
?>
