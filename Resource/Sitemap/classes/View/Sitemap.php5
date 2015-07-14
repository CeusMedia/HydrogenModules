<?php
class View_Sitemap extends CMF_Hydrogen_View{

	public function index(){
		$logic		= Logic_Sitemap::getInstance( $this->env );
		$sitemap	= self::render( $this->env, $logic );
		header( 'Content-type: '.$sitemap->mimeType );
		print( $sitemap->content );
		exit;
	}

	static public function render( $env, Logic_Sitemap $logic ){
		$config	= $env->getConfig()->getAll( 'module.resource_sitemap.', TRUE );
		$root	= new XML_DOM_Node( 'urlset' );
		$root->setAttribute( 'xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9" );
		foreach( $logic->getLinks() as $link ){
			$child	= new XML_DOM_Node( 'url' );
			$child->addChild( new XML_DOM_Node( 'loc', $link->location ) );
			if( $link->datetime )
				$child->addChild( new XML_DOM_Node( 'lastmod', $link->datetime ) );
			if( $link->frequency )
				$child->addChild( new XML_DOM_Node( 'changefreq', $link->frequency ) );
			if( $link->priority )
				$child->addChild( new XML_DOM_Node( 'priority', $link->priority ) );
			$root->addChild( $child );
		}

		$type	= "application/rss+xml";
		$xml	= XML_DOM_Builder::build( $root );
		switch( $config->get( 'compression' ) ){
			case 'bzip':
				$type	= "application/x-bzip2";
				$xml    = bzcompress( $xml );
				break;
			case 'gzip':
				$type	= "application/x-gzip";
				$xml    = gzencode( $xml );
				break;
		}
		return (object) array(
			'mimeType'	=> $type,
			'content'	=> $xml,
		);
	}
}
