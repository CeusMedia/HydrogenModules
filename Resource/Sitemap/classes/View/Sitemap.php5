<?php
class View_Sitemap extends CMF_Hydrogen_View{

	public function index(){
		$format		= $this->getData( 'format' );
		$links		= $this->getData( 'links' );
		$options	= $this->getData( 'options' );
		if( $options->get( 'html.enabled' ) && $format === 'HTML' ){
			$list	= array();
			foreach( $links as $link ){
				$label	= substr( $link->location, strlen( $this->env->url ) );
				$label	= str_replace( array( 'ae', 'oe', 'ue' ), array( 'ä', 'ö', 'ü' ), $label );
				$label	= str_replace( array( '/', '-' ), array( ' &gt; ', ' ' ), $label );
				$label	= ucwords( $label );
				$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $link->location ) );
				$list[]	= UI_HTML_Tag::create( 'li', $link );
			}
			$list	= UI_HTML_Tag::create( 'ul', $list );
			extract( $this->populateTexts( array( 'top', 'bottom' ), 'html/sitemap/' ) );
			return $textTop.$list.$textBottom;
		}
		$sitemap	= $this->renderXml( $links, $options );
		header( 'Content-type: '.$sitemap->mimeType );
		print( $sitemap->content );
		exit;
	}

	protected function renderXml( $links, $options ){
		$root	= new XML_DOM_Node( 'urlset' );
		$root->setAttribute( 'xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9" );
		foreach( $links as $link ){
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
		switch( $options->get( 'compression' ) ){
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
