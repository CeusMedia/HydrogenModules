<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\XML\DOM\Builder as XmlBuilder;
use CeusMedia\Common\XML\DOM\Node as XmlNode;
use CeusMedia\HydrogenFramework\View;

class View_Sitemap extends View
{
	public function index()
	{
		$format		= $this->getData( 'format' );
		$links		= $this->getData( 'links' );
		$options	= $this->getData( 'options' );
		if( $options->get( 'html.enabled' ) && $format === 'HTML' ){
			$list	= [];
			foreach( $links as $link ){
				$label	= substr( $link->location, strlen( $this->env->url ) );
				$label	= str_replace( ['ae', 'oe', 'ue'], ['ä', 'ö', 'ü'], $label );
				$label	= str_replace( ['/', '-'], [' &gt; ', ' '], $label );
				$label	= ucwords( $label );
				$link	= HtmlTag::create( 'a', $label, ['href' => $link->location] );
				$list[]	= HtmlTag::create( 'li', $link );
			}
			$list	= HtmlTag::create( 'ul', $list );
			extract( $this->populateTexts( ['top', 'bottom'], 'html/sitemap/' ) );
			return $textTop.$list.$textBottom;
		}
		$sitemap	= $this->renderXml( $links, $options );
		header( 'Content-type: '.$sitemap->mimeType );
		print( $sitemap->content );
		exit;
	}

	protected function renderXml( array $links, $options )
	{
		$root	= new XmlNode( 'urlset' );
		$root->setAttribute( 'xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9" );
		foreach( $links as $link ){
			$child	= new XmlNode( 'url' );
			$child->addChild( new XmlNode( 'loc', $link->location ) );
			if( $link->datetime )
				$child->addChild( new XmlNode( 'lastmod', $link->datetime ) );
			if( $link->frequency )
				$child->addChild( new XmlNode( 'changefreq', $link->frequency ) );
			if( $link->priority )
				$child->addChild( new XmlNode( 'priority', $link->priority ) );
			$root->addChild( $child );
		}

		$type	= "application/rss+xml";
		$xml	= XmlBuilder::build( $root );
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
		return (object) [
			'mimeType'	=> $type,
			'content'	=> $xml,
		];
	}
}
