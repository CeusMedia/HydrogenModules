<?php

use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Gallery{

	static public function calculateFraction( $fraction, $words = [] ){
		if( !strlen( trim( $fraction ) ) )
			return "";
		if( !$words )
			$words	= ['s', 's'];
		$value	= eval( 'return '.$fraction.';' );
		$label	= $value.$words[1];
		if( $value <= 1 )
			$label	= '1/'.round( 1 / $value ).$words[0];
		return $label;
	}

	static public function formatGalleryLinks( Environment $env, $content ){
		$matches	= [];
		preg_match_all( '/\[gallery:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$uri		= './gallery/index/'.str_replace( '%2F', '/', rawurlencode( $matches[1][$i] ) );
			$title		= trim( $matches[3][$i] );
			$link		= HtmlElements::Link( $uri, $title, 'icon-label link-gallery' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	static public function getFeedUrl( Environment $env, $limit = NULL ){
		$limit	= ( $limit !== NULL ) ? '/'.abs( (int) $limit ) : '';
		return $env->getConfig()->get( 'app.base.url' ).'gallery/feed'.$limit;
	}

	static public function renderGalleryLink( Environment $env, $path, $dateMode = 0, $dateFormat = NULL ){
		$config		= $env->getConfig();
		$pattern	= $config->get( 'module.gallery_compact.latest.regex' );

		$folderName	= basename( $path );
		$pathName	= $path == dirname( $path ) ? '' : dirname( $path ).'/';
		$parts		= explode( " ", $folderName );
		$date		=  NULL;
		$icon		= HtmlTag::create( 'b', '', ['class' => 'fa fa-folder-open fa-fw'] ).'&nbsp';
		$class		= 'not-icon-label not-link-gallery';
		if( $dateMode == 0 || $dateMode == 2 ){
			if( preg_match( str_replace( ' ', '', $pattern ), $parts[0] ) && count( $parts ) > 1 ){
				$date	= array_shift( $parts );
				if( $dateFormat )
					$date	= date( $dateFormat, strtotime( $date ) );
				$date	= '&nbsp;<small class="not-date muted">'.$date.'</small>';
			}
		}
#		if( preg_match( '/^[0-9]+$/', $folderName ) )
		if( strtotime( $folderName ) )
			$class	.= ' link-gallery-year';
		if( $dateMode < 2 )
			$date	=  NULL;
		$label		= implode( " ", $parts );
		$url		= './gallery/index/'.$pathName.rawurlencode( $folderName );
		$link		= HtmlElements::Link( $url, $icon.$label.$date );
		return HtmlTag::create( 'span', $link, ['class' => $class] );

		$attributes	= array(
			'class'	=> 'icon-label link-blog',
			'href'	=> 'blog/article/'.$article->articleId.'/'.rawurlencode( $article->title ),
		);
		return HtmlTag::create( 'a', $article->title, $attributes );
	}

	static public function renderImageLabel( Environment $env, $fileName, $withIcon = TRUE ){
		$ext	= pathinfo( basename( $fileName ), PATHINFO_EXTENSION );
		$ext	= HtmlTag::create( "span", '.'.$ext, ['class' => 'file-ext'] );
		$icon	= HtmlTag::create( 'b', '', ['class' => 'fa fa-image fa-fw'] ).'&nbsp;';
		if( !$withIcon )
			$icon	= '';
		$name	= pathinfo( basename( $fileName ), PATHINFO_FILENAME );
		return $icon.$name.$ext;
	}

	static public function renderImageLink( Environment $env, $pathName ){
		$label	= self::renderImageLabel( $env, $pathName );
		$url	= './gallery/info/'.str_replace( '%2F', '/', rawurlencode( $pathName ) );
		$class	= 'not-icon-label not-link-image';
		return HtmlTag::create( 'a', $label, ['href' => $url, 'class'=> $class] );
	}

	static public function renderLatestGalleries( Environment $env, $limit, $offset = 0, $dateMode = 0 ){
		$config		= $env->getConfig();

		$pattern	= $config->get( 'module.gallery_compact.latest.regex' );
		$dateFormat	= $config->get( 'module.gallery_compact.format.date' );
		$reverse	= $config->get( 'module.gallery_compact.latest.reverse' );

		$list		= [];
		$path		= $config->get( 'path.images' ).$config->get( 'module.gallery_compact.path' );
		$index		= RecursiveFolderLister::getFolderList( $path, $pattern );
		foreach( $index as $folder )
			$list[$folder->getFilename()]	= substr( $folder->getPathname(), strlen( $path ) );
		natcasesort( $list );
		$latest		= array_slice( array_reverse( $list ), $offset, $limit );
		if( !$reverse )
			$latest	= array_reverse( $latest );
		$list		= [];
		foreach( $latest as $title => $path ){
			$link	= self::renderGalleryLink( $env, $path, $dateMode, $dateFormat );
			$list[]	= HtmlTag::create( 'li', $link, ['class' => 'gallery-item'] );
		}
		return HtmlTag::create( 'ul', $list, ['class' => 'list-latest-galleries'] );
	}

	static public function renderStepNavigation( Environment $env, $source ){
		$steps	= [];
		$list	= [];
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
					$ext	= HtmlTag::create( "span", $ext, ['class' => 'file-ext'] );
					$parts[$i]	= self::renderImageLabel( $env, $parts[$i] );
				}
				else
					$parts[$i]	= self::renderGalleryLink( $env, implode( '/', $steps ), 2 );
				$class	= 'not-link-gallery-current';
				$list[]	= HtmlTag::create( "span", $parts[$i], ['class' => $class] );
			}
		}
		$icon		= HtmlTag::create( 'b', '', ['class' => 'fa fa-folder fa-fw'] ).'&nbsp';
		$link		= HtmlTag::create( "a", $icon.'Start', ['href' => './gallery', 'class' => 'not-icon-label not-link-gallery'] );
		array_unshift( $list, $link );
		$icon		= HtmlTag::create( 'b', '', ['class' => 'fa fa-angle-right fa-fw'] );
		$steps		= implode( "&nbsp;".$icon."&nbsp;", $list );
		$steps		= HtmlTag::create( "span", 'Position: ' ) . $steps;
		return HtmlTag::create( "div", $steps, ['class' => 'navi-steps'] );
	}
}
?>
