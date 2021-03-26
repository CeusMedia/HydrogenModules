<?php
use CMF_Hydrogen_Environment as Environment;

class View_Helper_ContentConverter
{
	public static $linkClass		= 'icon-label';

	public static $linkTarget		= '_self';

	protected static $callbacks		= array();

	public static function formatBreaks( Environment $env, string $content ): string
	{
		$content	= preg_replace( "/(----){4,}\r?\n/",' <hr/>', $content );						//  four dashes make a horizontal row
		$content	= preg_replace( "/([^>])\r?\n\r?\n\r?\n/", '\\1<br class="clearfloat">', $content );
		$content	= preg_replace( "/([^>])\r?\n\r?\n/", '\\1<br/><br/>', $content );
		$content	= preg_replace( "/(.+)\t\r?\n/", '\\1<br/>', $content );						//  special break: line ends with tab
		$content	= preg_replace( "/([^*\/])\/\r?\n/", '\\1<br/>', $content );					//  special break: line ends with / but not with */ or //
		return $content;
	}

	public static function formatCodeBlocks( Environment $env, string $content ): string
	{
		$content	= preg_replace( '/<(\/?)code>/', "_____\\1code_____", $content );				//  preserve <code> tags
		$pattern	= "/(\r?\n)*code:?(\w+)?>(.*)<code(\r?\n)*/siU";
		$matches	= array();
		preg_match_all( $pattern, $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$type		= $matches[2][$i];
			$code		= trim( $matches[3][$i] );
			$attributes	= array( 'class' => $type ? $type : 'code' );
			$new		= UI_HTML_Tag::create( 'xmp', $code, $attributes );
			$content	= str_replace( $matches[0][$i], $new, $content );
		}
		$content	= preg_replace( '/_____(\/?)code_____/', '<\\1code>', $content );				//  recreate <code> tags
		return $content;
	}

	public static function formatLinks( Environment $env, string $content ): string
	{
		$matches	= array();
		preg_match_all( '/\[link:(\S+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$url		= $matches[1][$i];
			$title		= str_replace( ' ', '&nbsp;', trim( $matches[3][$i] ) );
			$class		= ( self::$linkClass ? self::$linkClass.' ' : '' ).'link-external';
			$link		= UI_HTML_Elements::Link( $url, $title, $class, self::$linkTarget );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	public static function formatImageSearch( Environment $env, string $content ): string
	{
		$matches	= array();
		preg_match_all( '/\[image-search:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://images.google.com/images?q='.$query;
			$class		= ( self::$linkClass ? self::$linkClass.' ' : '' ).'link-search-image';
			$link		= UI_HTML_Elements::Link( $url, $title, $class, self::$linkTarget );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	public static function formatMapSearch( Environment $env, string $content ): string
	{
		$matches	= array();
		preg_match_all( '/\[map-search:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$query		= trim( $matches[1][$i] );
			$title		= trim( $matches[3][$i] );
			$url		= 'http://maps.google.de/maps?hl=de&q='.$query;
			$class		= ( self::$linkClass ? self::$linkClass.' ' : '' ).'link-search-map';
			$link		= UI_HTML_Elements::Link( $url, $title, $class, self::$linkTarget );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	public static function formatCurrencies( Environment $env, string $content ): string
	{
		$content	= preg_replace( '/([0-9]+) Euro/', '\\1&nbsp;&euro;', $content );
		$content	= preg_replace( '/([0-9]+) Cent/', '\\1&nbsp;&cent;', $content );
		$content	= preg_replace( '/([0-9]+) Pound/', '\\1&nbsp;&pound;', $content );
		$content	= preg_replace( '/([0-9]+) Yen/', '\\1&nbsp;&yen;', $content );
		$content	= preg_replace( '/([0-9]+) Dollar/', '\\1&nbsp;&#36;', $content );
		$content	= preg_replace( '/([0-9]+) Baht/', '\\1&nbsp;&#3647;', $content );
		$content	= str_replace( '/€', '&euro;', $content );
		return $content;
	}

	public static function formatWikiLinks( Environment $env, string $content ): string
	{
		$matches	= array();
		preg_match_all( '/\[wiki:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count($matches[0]); $i++ ){
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://de.wikipedia.org/wiki/'.$query;
			$class		= ( self::$linkClass ? self::$linkClass.' ' : '' ).'link-wiki';
			$link		= UI_HTML_Elements::Link( $url, $title, $class, self::$linkTarget );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	public static function formatYoutubeLinks( Environment $env, string $content ): string
	{
		$matches	= array();
		preg_match_all( '/\[youtube:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://www.youtube.com/watch?v='.$query;
			$class		= ( self::$linkClass ? self::$linkClass.' ' : '' ).'link-youtube';
			$link		= UI_HTML_Elements::Link( $url, $title, $class, self::$linkTarget );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	public static function formatMapLinks( Environment $env, string $content ): string
	{
		$matches	= array();
		preg_match_all( '/\[(link-)?map:([0-9,.]+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$geocode	= trim( $matches[2][$i] );
			$parts		= explode( ',', $geocode );
			$longitude	= $parts[0];
			$lattitude	= $parts[1];
			$zoomlevel	= empty( $parts[2] ) ? 16 : $parts[2];
			$title		= trim( $matches[4][$i] );
			$url		= 'http://maps.google.de/maps?hl=de&ll='.$longitude.','.$lattitude.'&z='.$zoomlevel;
			$class		= ( self::$linkClass ? self::$linkClass.' ' : '' ).'link-map';
			$link		= UI_HTML_Elements::Link( $url, $title, $class, self::$linkTarget );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	public static function formatDiscogsLinks( Environment $env, string $content ): string
	{
		$matches	= array();
		preg_match_all( '/\[discogs:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://www.discogs.com/'.$query;
			$class		= ( self::$linkClass ? self::$linkClass.' ' : '' ).'link-discogs';
			$link		= UI_HTML_Elements::Link( $url, $title, $class, self::$linkTarget );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	public static function formatMyspaceLinks( Environment $env, string $content ): string
	{
		$matches	= array();
		preg_match_all( '/\[myspace:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://www.myspace.com/'.$query;
			$class		= ( self::$linkClass ? self::$linkClass.' ' : '' ).'link-myspace';
			$link		= UI_HTML_Elements::Link( $url, $title, $class, self::$linkTarget );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	public static function formatImdbLinks( Environment $env, string $content ): string
	{
		$matches	= array();
		preg_match_all( '/\[imdb:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://www.imdb.com/find?s=tt&q='.$query;
			$class		= ( self::$linkClass ? self::$linkClass.' ' : '' ).'link-imdb';
			$link		= UI_HTML_Elements::Link( $url, $title, $class, self::$linkTarget );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	public static function formatText( Environment $env, string $content ): string
	{
		$converters	= array(
			"/####(.+)####\r?\n/U"	=> "<h5>\\1</h5>\n",
			"/###(.+)###\r?\n/U"	=> "<h4>\\1</h4>\n",
			"/##(.+)##\r?\n/U"		=> "<h3>\\1</h3>\n",
			"/#(.+)#\r?\n/U"		=> "<h2>\\1</h2>\n",
			"/([^:])\*\*(.+)\*\*/U"	=> "\\1<b>\\2</b>",
			"/([^:])\/\/(.+)\/\//U"	=> "\\1<em>\\2</em>",
			"/__(.+)__/U"			=> "<u>\\1</u>",
			"/>>(.+)<</U"			=> "<small>\\1</small>",
			"/<<(.+)>>/U"			=> "<big>\\1</big>",
			"/--(.+)--/U"			=> "<strike>\\1</strike>",
		);
		foreach( $converters as $key => $value )
			$content	= preg_replace( $key, $value, $content );
		return $content;
	}

	public static function formatLists( Environment $env, string $content ): string
	{
		$pattern	= "/(\r?\n)*(o|u)?list:?(\w+)?>(.*)<(o|u)?list(\r?\n)*/siU";
		$matches	= array();
		preg_match_all( $pattern, $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$type		= $matches[2][$i] ? $matches[2][$i] : 'u';
			$class		= $matches[3][$i];
			$lines		= explode( "\n", trim( $matches[4][$i] ) );
			foreach( $lines as $nr => $line )
				$lines[$nr]	= preg_replace( '/^- /', '<li>', trim( $lines[$nr] ) ).'</li>';
			$lines	= implode( "\n", $lines );
			$attributes	= array( 'class' => $class ? $class : 'list');
			$new		= UI_HTML_Tag::create( $type.'l', $lines, $attributes );
			$content	= str_replace( $matches[0][$i], $new, $content );
		}
		return $content;
	}


	public static function render( Environment $env, string $content ): string
	{
		foreach( self::$callbacks as $callback ){
#			remark( 'Applying:'.$callback[0].'::'.$callback[1] );
			$content	= call_user_func( $callback, $env, $content );
		}
		return $content;
	}

	public static function register( string $class, string $method ): string
	{
		$key	= $class.'::'.$method;
		if( array_key_exists( $key, self::$callbacks ) )
			throw new RuntimeException( 'Converter with key "'.$key.'" is already registered' );
		self::$callbacks[$key]	= array( $class, $method );
	}

	public static function unregister( string $class, string $method ): bool
	{
		$key	= $class.'::'.$method;
		if( !array_key_exists( $key, self::$callbacks ) )
			return FALSE;
		unset( self::$callbacks[$key] );
		return TRUE;
	}
}
