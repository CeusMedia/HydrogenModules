<?php
class View_Helper_Gallery{

	static public function calculateFraction( $fraction, $words = array() ){
		if( !$words )
			$words	= array( 's', 's' ); 
		$value	= eval( 'return '.$fraction.';' );
		$label	= $value.$words[1];
		if( $value <= 1 )
			$label	= '1/'.round( 1 / $value ).$words[0];
		return $label;
	}

	static public function formatGalleryLinks( $env, $content ){
		$matches	= array();
		preg_match_all( '/\[gallery:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$uri		= './gallery/index/'.str_replace( '%2F', '/', rawurlencode( $matches[1][$i] ) );
			$title		= trim( $matches[3][$i] );
			$link		= UI_HTML_Elements::Link( $uri, $title, 'icon-label link-gallery' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	static public function getFeedUrl( CMF_Hydrogen_Environment_Abstract $env, $limit = NULL ){
		$limit	= ( $limit !== NULL ) ? '/'.abs( (int) $limit ) : '';
		return $env->getConfig()->get( 'app.base.url' ).'gallery/feed'.$limit;
	}

	static public function renderGalleryLink( $env, $path, $dateMode = 0, $dateFormat = NULL ){
		$config		= $env->getConfig();
		$pattern	= $config->get( 'module.gallery_compact.latest.regex' );
		
		$folderName	= basename( $path );
		$pathName	= $path == dirname( $path ) ? '' : dirname( $path ).'/';
		$parts		= explode( " ", $folderName );
		$date		=  NULL;
		$class		= 'icon-label link-gallery';
		if( $dateMode == 0 || $dateMode == 2 ){
			if( preg_match( str_replace( ' ', '', $pattern ), $parts[0] ) && count( $parts ) > 1 ){
				$date	= array_shift( $parts );
				if( $dateFormat )
					$date	= date( $dateFormat, strtotime( $date ) );
				$date	= '<span class="date">'.$date.'</span>';
			}
		}
#		if( preg_match( '/^[0-9]+$/', $folderName ) )
		if( strtotime( $folderName ) )
			$class	.= ' link-gallery-year'; 
		if( $dateMode < 2 )
			$date	=  NULL;
		$label		= implode( " ", $parts );
		$url		= './gallery/index/'.$pathName.rawurlencode( $folderName );
		$link		= UI_HTML_Elements::Link( $url, $label );
		return UI_HTML_Tag::create( 'span', $link.$date, array( 'class' => $class ) );

		$attributes	= array(
			'class'	=> 'link-blog',
			'href'	=> 'blog/article/'.$article->articleId.'/'.rawurlencode( $article->title ),
		);
		return UI_HTML_Tag::create( 'a', $article->title, $attributes );
	}

	static public function renderImageLabel( $env, $fileName ){
		$ext	= pathinfo( basename( $fileName ), PATHINFO_EXTENSION );
		$ext	= UI_HTML_Tag::create( "span", '.'.$ext, array( 'class' => 'file-ext' ) );
		return pathinfo( basename( $fileName ), PATHINFO_FILENAME ).$ext;
	}

	static public function renderImageLink( $env, $pathName ){
		$label	= self::renderImageLabel( $env, $pathName );
		$url	= './gallery/info/'.str_replace( '%2F', '/', rawurlencode( $pathName ) );
		$class	= 'icon-label link-image';
		return UI_HTML_Tag::create( 'a', $label, array( 'href' => $url, 'class'=> $class ) );
	}

	static public function renderLatestGalleries( $env, $limit, $offset = 0, $dateMode = 0 ){
		$config		= $env->getConfig();

		$pattern	= $config->get( 'module.gallery_compact.latest.regex' );
		$dateFormat	= $config->get( 'module.gallery_compact.format.date' );
		$reverse	= $config->get( 'module.gallery_compact.latest.reverse' );
		
		$list		= array();
		$path		= $config->get( 'path.images' ).$config->get( 'module.gallery_compact.path' );
		$index		= Folder_RecursiveLister::getFolderList( $path, $pattern );
		foreach( $index as $folder )
			$list[$folder->getFilename()]	= substr( $folder->getPathname(), strlen( $path ) );
		natcasesort( $list );
		$latest		= array_slice( array_reverse( $list ), $offset, $limit );
		if( !$reverse )
			$latest	= array_reverse( $latest );
		$list		= array();
		foreach( $latest as $title => $path ){
			$link	= self::renderGalleryLink( $env, $path, $dateMode, $dateFormat );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => 'gallery-item' ) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'list-latest-galleries' ) );
	}

	static public function renderStepNavigation( $env, $source ){
		$steps	= array();
		$list	= array();
		$source	= preg_replace( '/\/$/', '', $source ); 
		$parts	= $source ? explode( '/', $source ) : array();
		for( $i=0; $i<count( $parts ); $i++ ){
			if( !trim( $parts[$i] ) )
				continue;
			$steps[]	= $parts[$i];
			if( $i < ( count( $parts ) - 1 ) )														//  not the last step
				$list[]	= self::renderGalleryLink( $env, implode( '/', $steps ), 0 );				//  render gallery forder link
			else{																					//  last step
				if( preg_match( "/\.(jpg|jpe|jpeg|png|gif|bmp|ico)$/i", $parts[$i] ) ){				//  last step is an image
					$ext	= '.'.pathinfo( $parts[$i], PATHINFO_EXTENSION );
					$ext	= UI_HTML_Tag::create( "span", $ext, array( 'class' => 'file-ext' ) );
					$parts[$i]	= self::renderImageLabel( $env, $parts[$i] );
				}
				else
					$parts[$i]	= self::renderGalleryLink( $env, implode( '/', $steps ), 2 );
				$class	= 'link-gallery-current';
				$list[]	= UI_HTML_Tag::create( "span", $parts[$i], array( 'class' => $class ) );
			}
		}
		$link	= UI_HTML_Tag::create( "a", 'Start', array( 'href' => './gallery', 'class' => 'icon-label link-gallery' ) );
		array_unshift( $list, $link );
		$steps		= implode( "&nbsp;&gt;&nbsp;", $list );
		$steps		= UI_HTML_Tag::create( "span", 'Position: ' ) . $steps;
		return UI_HTML_Tag::create( "div", $steps, array( 'class' => 'navi-steps' ) );
	}
}
?>