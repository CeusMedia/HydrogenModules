<?php
class View_Helper_ContentConverter{

	static protected $callbacks	= array();

	static public function formatBreaks( $env, $content ){
		$content	= preg_replace( '/(----){4,}/',' <hr/>', $content );
		$content	= preg_replace( '/([^>])\r?\n\r?\n\r?\n/', '\\1<br class="clearfloat">', $content );
		$content	= preg_replace( '/([^>])\r?\n\r?\n/', '\\1<br/><br/>', $content );
		$content	= preg_replace( '/(.+)\t\r?\n/', '\\1<br/>', $content );
		return $content;
	}

	static public function formatCodeBlocks( $env, $content ){
		$pattern	= "/(\r?\n)*code:?(\w+)?>(.*)<code(\r?\n)*/siU";
		$matches	= array();
		preg_match_all( $pattern, $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$type		= $matches[2][$i];
			$code		= $matches[3][$i];
			$attributes	= array( 'class' => $type ? $type : NULL );
			$new		= UI_HTML_Tag::create( 'xmp', $code, $attributes );
			$content	= str_replace( $matches[0][$i], $new, $content );
		}
		return $content;
	}
	
	static public function formatLinks( $env, $content ){
		$matches	= array();
		preg_match_all( '/\[link:(\S+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$title		= trim( $matches[3][$i] );
			$link		= UI_HTML_Elements::Link( $matches[1][$i], $title, 'link-external', '_blank' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	static public function formatImageSearch( $env, $content ){
		$matches	= array();
		preg_match_all( '/\[image-search:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://images.google.com/images?q='.$query;
			$link		= UI_HTML_Elements::Link( $url, $title, 'link-search-image', 'search' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	static public function formatMapSearch( $env, $content ){
		$matches	= array();
		preg_match_all( '/\[map-search:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$query		= trim( $matches[1][$i] );
			$title		= trim( $matches[3][$i] );
			$url		= 'http://maps.google.de/maps?hl=de&q='.$query;
			$link		= UI_HTML_Elements::Link( $url, $title, 'link-search-map', 'search' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	static public function formatCurrencies( $env, $content ){
		$content	= preg_replace( '/([0-9]+) Euro/', '\\1&nbsp;&euro;', $content );
		$content	= preg_replace( '/([0-9]+) Cent/', '\\1&nbsp;&cent;', $content );
		$content	= preg_replace( '/([0-9]+) Pound/', '\\1&nbsp;&pound;', $content );
		$content	= preg_replace( '/([0-9]+) Yen/', '\\1&nbsp;&yen;', $content ); 
		$content	= preg_replace( '/([0-9]+) Dollar/', '\\1&nbsp;&#36;', $content );
		$content	= preg_replace( '/([0-9]+) Baht/', '\\1&nbsp;&#3647;', $content ); 
		$content	= str_replace( '/€', '&euro;', $content ); 
		return $content;
	}

	static public function formatWikiLinks( $env, $content ){
		$matches	= array();
		preg_match_all( '/\[wiki:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count($matches[0]); $i++ ){
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://de.wikipedia.org/wiki/'.$query;
			$link		= UI_HTML_Elements::Link( $url, $title, 'link-wiki', '_blank' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	static public function formatYoutubeLinks( $env, $content ){
		$matches	= array();
		preg_match_all( '/\[youtube:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://www.youtube.com/watch?v='.$query;
			$link		= UI_HTML_Elements::Link( $url, $title, 'link-youtube', '_blank' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	static public function formatMapLinks( $env, $content ){
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
			$link		= UI_HTML_Elements::Link( $url, $title, 'link-map', 'map' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}
	
	static public function formatImdbLinks( $env, $content ){
		$matches	= array();
		preg_match_all( '/\[imdb:(.+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$query		= trim( $matches[1][$i] );
			$title		= empty( $matches[3][$i] ) ? $query : trim( $matches[3][$i] );
			$url		= 'http://www.imdb.com/find?s=tt&q='.$query;
			$link		= UI_HTML_Elements::Link( $url, $title, 'link-imdb', '_blank');
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	static public function formatText( $env, $content ){
		$converters	= array(
			"/####(.+)####\r?\n/U"	=> "<h5>\\1</h5>\n",
			"/###(.+)###\r?\n/U"	=> "<h4>\\1</h4>\n",
			"/##(.+)##\r?\n/U"		=> "<h3>\\1</h3>\n",
			"/#(.+)#\r?\n/U"		=> "<h2>\\1</h2>\n",
			"/\*\*(.+)\*\*/U"		=> "<b>\\1</b>",
			"/\/\/(.+)\/\//U"		=> "<em>\\1</em>",
			"/__(.+)__/U"			=> "<u>\\1</u>",
		);
		foreach( $converters as $key => $value )
			$content	= preg_replace( $key, $value, $content );
		return $content;
	}

	static public function formatLists( $env, $content ){
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

	
	static public function render( $env, $content ){
		foreach( self::$callbacks as $callback ){
#			remark( 'Applying:'.$callback[0].'::'.$callback[1] );
			$content	= call_user_func( $callback, $env, $content );
		}
		return $content;
	}

	static public function register( $class, $method ){
		$key	= $class.'::'.$method;
		if( array_key_exists( $key, self::$callbacks ) )
			throw new RuntimeException( 'Converter with key "'.$key.'" is already registered' );
		self::$callbacks[$key]	= array( $class, $method );
	}

	static public function unregister( $class, $method ){
		$key	= $class.'::'.$method;
		if( !array_key_exists( $key, self::$callbacks ) )
			return FALSE;
		unset( self::$callbacks[$key] );
		return TRUE;
	}
}
?>