<?php
class View_Helper_BlogContentFormat
{
	public static function formatLinks( & $content )
	{
		$matches	= array();
		preg_match_all( '/\[link:(\S+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ )
		{
			$uri		= $matches[1][$i];
			$title		= trim( $matches[3][$i] );
			$link		= UI_HTML_Elements::Link( $uri, $title, 'external', '_blank' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
	}

	public static function formatImages( & $content, $path, $articleId )
	{
		$matches	= array();
		preg_match_all( '/\[image:(\S+)(\|(.*))?\]\r?\n/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ )
		{
			$title		= trim( $matches[3][$i] );
			$fileName	= $matches[1][$i];
			$uri		= $path.$fileName;
			$thumb		= './blog/thumb/'.$fileName;
			$image		= UI_HTML_Elements::Image( $thumb, $title, 'thumb' );
			$attributes	= array(
					'href'	=> $uri,
					'class'	=> 'no-thickbox layer-image',
					'title'	=> $title,
					'rel'	=> 'gallery-article-'.$articleId,
			);
			$link		= UI_HTML_Tag::create( 'a', $image, $attributes );
			$container	= UI_HTML_Tag::create( 'div', $link, array( 'class'=>'image' ) );
			$content	= str_replace( $matches[0][$i], $container, $content );
		}
	}

	public static function formatText( & $content )
	{
		$content	= preg_replace( '/\*\*(.+)\*\*/U', '<b>\\1</b>', $content );
		$content	= preg_replace( '/__(.+)__/U',' <em>\\1</em>', $content );
		$content	= preg_replace( '/(----){4,}/',' <hr/>', $content );
		$content	= preg_replace( '/([^>])\r?\n\r?\n\r?\n/', '\\1<br class="clearfloat">', $content );
		$content	= preg_replace( '/([^>])\r?\n\r?\n/', '\\1<br/><br/>', $content );
#		$content	= preg_replace( '/([^>])\r?\n/', '\\1<br/>', $content );
	}

	public static function formatImageSearch( & $content )
	{
		$matches	= array();
		preg_match_all( '/\[search-image:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ )
		{
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://images.google.com/images?q='.$query;
			$link		= UI_HTML_Elements::Link( $url, $title, 'link-search-image', 'search' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
	}

	public static function formatMapSearch( & $content )
	{
		$matches	= array();
		preg_match_all( '/\[search-map:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ )
		{
			$query		= trim( $matches[1][$i] );
			$title		= trim( $matches[3][$i] );
			$url		= 'http://maps.google.de/maps?hl=de&q='.$query;
			$link		= UI_HTML_Elements::Link( $url, $title, 'link-search-image', 'search' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
	}

	public static function formatIFrames( & $content )
	{
		$matches	= array();
		preg_match_all( '/\[iframe:(\S+)\]\r?\n/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ )
		{
			$uri		= $matches[1][$i];
			$attributes	= array(
				'src'			=> $uri,
				'width'			=> 600,
				'height'		=> 460,
				'frameborder'	=> 0,
				'scrolling'		=> 'no',
				'marginheight'	=> 0,
				'marginwidth'	=> 0,
			);
			$frame		= UI_HTML_Tag::create( 'iframe', '', $attributes );
			$content	= str_replace( $matches[0][$i], $frame, $content );
		}
	}

	public static function formatCurrencies( & $content )
	{
		$content	= preg_replace( '/([0-9]+) Baht/', '\\1&nbsp;&#3647;', $content ); 
		$content	= preg_replace( '/([0-9]+) Dollar/', '\\1&nbsp;&#36;', $content );
		$content	= preg_replace( '/([0-9]+) Cent/', '\\1&nbsp;&cent;', $content );
		$content	= preg_replace( '/([0-9]+) Euro/', '\\1&nbsp;&euro;', $content );
		$content	= str_replace( '/€', '&euro;', $content ); 
	}

	public static function formatEmoticons( & $content )
	{
		$path		= './images/emoticons/';
		$content	= str_replace( ' :)', ' '.UI_HTML_Elements::Image( $path.'1.ico', ':)', 16, 16 ), $content );
		$content	= str_replace( ' :(', ' '.UI_HTML_Elements::Image( $path.'3.ico', ':(', 16, 16 ), $content );
		$content	= str_replace( ' ;)', ' '.UI_HTML_Elements::Image( $path.'6.ico', ';)', 16, 16 ), $content );
		$content	= str_replace( ' :D', ' '.UI_HTML_Elements::Image( $path.'7.ico', ':D', 16, 16 ), $content );
		$content	= str_replace( ' <3', ' '.UI_HTML_Elements::Image( $path.'14.ico', '<3', 16, 16 ), $content );
	}

	public static function formatMapLinks( & $content )
	{
		$matches	= array();
		preg_match_all( '/\[link-map:([0-9,.]+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ )
		{
			$geocode	= trim( $matches[1][$i] );
			$parts		= explode( ',', $geocode );
			$longitude	= $parts[0];
			$lattitude	= $parts[1];
			$zoomlevel	= empty( $parts[2] ) ? 16 : $parts[2];
			$title		= trim( $matches[3][$i] );
			$url		= 'http://maps.google.de/maps?hl=de&ll='.$longitude.','.$lattitude.'&z='.$zoomlevel;
			$link		= UI_HTML_Elements::Link( $url, $title, 'link-map', 'map' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
	}

	public static function formatImdbLinks( & $content )
	{
		$matches	= array();
		preg_match_all( '/\[imdb:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ )
		{
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://www.imdb.com/find?s=tt&q='.$query;
			$link		= UI_HTML_Elements::Link( $url, $title, 'link-imdb', '_blank');
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
	}

	public static function formatWikiLinks( & $content )
	{
		$matches	= array();
		preg_match_all( '/\[wiki:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count($matches[0]); $i++ )
		{
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://de.wikipedia.org/wiki/'.$query;
			$link		= UI_HTML_Elements::Link( $url, $title, 'link-wiki', '_blank' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
	}

	public static function formatYoutubeLinks( & $content )
	{
		$matches	= array();
		preg_match_all( '/\[youtube:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ )
		{
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://www.youtube.com/watch?v='.$query;
			$link		= UI_HTML_Elements::Link( $url, $title, 'link-youtube', '_blank' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
	}
}
?>