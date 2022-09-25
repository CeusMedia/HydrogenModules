<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Blog_Compact extends Hook
{
	public static function onPageApplyModules( $env, $context, $module, $payload )
	{
		$env->getLanguage()->load( 'blog' );														//  load blog language file
		$words		= $env->getLanguage()->getWords( 'blog' );										//  get blog feed words
		$context->addHead( HtmlTag::create( 'link', NULL, array(								//  create link with attributes
			'rel'	=> "alternate",																	//
			'type'	=> "application/rss+xml",														//  MIME type of RSS
			'href'	=> $env->getConfig()->get( 'app.base.url' )."blog/feed",						//  URL to RSS feed
			'title'	=> $env->getConfig()->get( 'app.name' ).': '.$words['feed']['title']			//  link title
		) ) );																						//  and add link to page head
		View_Helper_ContentConverter::register( "View_Helper_Blog", "formatBlogLinks" );
		View_Helper_ContentConverter::register( "View_Helper_Blog", "formatEmoticons" );
		View_Helper_ContentConverter::register( "View_Helper_Blog", "formatImages" );
		View_Helper_ContentConverter::register( "View_Helper_Blog", "formatIFrames" );
	}
}
