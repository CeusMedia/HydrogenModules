<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\OpenGraph\Node as OpenGraphNode;
use CeusMedia\OpenGraph\Renderer as OpenGraphRenderer;
use CeusMedia\OpenGraph\Structure\Audio as OpenGraphAudio;
use CeusMedia\OpenGraph\Structure\Image as OpenGraphImage;
use CeusMedia\OpenGraph\Structure\Video as OpenGraphVideo;

class View_Helper_MetaTags
{
	protected Environment $env;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function apply(): bool
	{
		$config		= $this->env->getConfig();
		$request	= $this->env->getRequest();
		$language	= $this->env->getLanguage();
		$page		= $this->env->getPage();

		$moduleKey	= 'module.ui_metatags.';
		$tags		= (object) $config->getAll( $moduleKey.'default.', TRUE );

		if( !$config->get( $moduleKey.'enable' ) )
			return FALSE;
		$title			= $tags->get( 'title' );
		$description	= $tags->get( 'description' );
		$keywords		= $tags->get( 'keywords' );
		if( file_exists( $keywords ) ){
			$list	= [];
			foreach( explode( "\n", file_get_contents( $tags->get( 'keywords' ) ) ) as $line )
				if( trim( $line ) )
					$list[]	= trim( $line );
			$keywords	= join( ", ", $list );
		}

		if( $this->env->getModules()->has( 'Info_Pages' ) ){
			/** @var Logic_Page $logic */
			$logic		= Logic_Page::getInstance( $this->env );
			$path		= trim( $request->get( '__path', '' ) );
			try{
				$object		= $logic->getPageFromPath( strlen( $path ) ? $path : 'index' );
				if( $object ){
					if( strlen( trim( $object->title ?? '' ) ) )
						$title			= $object->title;
					if( strlen( trim( $object->description ?? '' ) ) )
						$description	= $object->description;
					if( strlen( trim( $object->keywords ?? '' ) ) )
						$keywords		= $object->keywords;
				}
			}
			catch( Exception $e ){
			}
		}

		$page->addMetaTag( 'http-equiv', 'Content-Language', $language->getLanguage() );
		$page->addMetaTag( 'http-equiv', 'Content-Type', "text/html; charset=UTF-8" );
		$page->addMetaTag( 'http-equiv', 'Content-Script-Type', "text/javascript" );
		$page->addMetaTag( 'http-equiv', 'Content-Style-Type', "text/css" );
		if( $title )
			$page->setTitle( $title );
		if( $description )
			$page->addMetaTag( 'name', 'description', $description );
		if( $keywords )
			$page->addMetaTag( 'name', 'keywords', $keywords );
		if( $tags->get( 'generator' ) )
			$page->addMetaTag( 'name', 'generator', $tags->get( 'generator' ) );
		if( $tags->get( 'publisher' ) )
			$page->addMetaTag( 'name', 'publisher', $tags->get( 'publisher' ) );
		if( $tags->get( 'publisher' ) )
			$page->addMetaTag( 'name', 'author', $tags->get( 'author' ) );
		$page->addMetaTag( 'name', 'date', date( "c" ) );
		if( strlen( trim( $tags->get( 'expires' ) ) ) )
			$page->addMetaTag( 'http-equiv', 'expires', $tags->get( 'expires' ) );
		if( $tags->get( 'cache.control' ) )
			$page->addMetaTag( 'http-equiv', 'cache-control', $tags->get( 'cache.control' ) );
		if( $tags->get( 'cache.pragma' ) )
			$page->addMetaTag( 'http-equiv', 'pragma', $tags->get( 'cache.pragma' ) );

		if( $config->get( $moduleKey.'enable.DublinCore' ) ){
			$page->addMetaTag( 'name', 'DC.Format', "text/html" );
			$page->addMetaTag( 'name', 'DC.Type', "Text" );
			if( $title )
				$page->addMetaTag( 'name', 'DC.Title', $title );
			if( $description )
				$page->addMetaTag( 'name', 'DC.Description', $description );
			if( $keywords )
				$page->addMetaTag( 'name', 'DC.Subject', $keywords );
			if( $tags->get( 'coverage' ) )
				$page->addMetaTag( 'name', 'DC.Coverage', $tags->get( 'coverage' ) );
			if( $tags->get( 'license' ) )
				$page->addMetaTag( 'name', 'DC.Rights', $tags->get( 'license' ) );
			if( $tags->get( 'publisher' ) )
				$page->addMetaTag( 'name', 'DC.Publisher', $tags->get( 'publisher' ) );
			if( $tags->get( 'publisher' ) )
				$page->addMetaTag( 'name', 'DC.Creator', $tags->get( 'author' ) );
		}
		if( $config->get( $moduleKey.'enable.OpenGraph' ) ){
			if( $this->env->getModules()->has( 'UI_MetaTags_OpenGraph' ) ){							//  module UI:MetaTags:OpenGraph is installed
				$ogData	= (object) $config->getAll( 'module.ui_metatags_opengraph.', TRUE );		//  extract module data

				$url	= $this->env->scheme.'://'.$this->env->host.getEnv( 'REQUEST_URI' );
				$ogNode	= new OpenGraphNode( $url );
				$ogNode->setType( 'website' );
				$ogNode->setTitle( $title );
				$ogNode->setDescription( $description );
				if( $ogData->get( 'audio') ){
					$audio	= new OpenGraphAudio( $ogData->get( 'audio' ) );
					if( $ogData->get( 'audio.type' ) )
						$audio->setType( $ogData->get( 'audio.type' ) );
					$ogNode->addAudio( $audio );
				}
				if( $ogData->get( 'image') ){
					$image	= new OpenGraphImage( $ogData->get( 'image' ) );
					if( $ogData->get( 'image.width' ) )
						$image->setWidth( $ogData->get( 'image.width' ) );
					if( $ogData->get( 'image.height' ) )
						$image->setHeight( $ogData->get( 'image.height' ) );
					if( $ogData->get( 'image.type' ) )
						$image->setType( $ogData->get( 'image.type' ) );
					$ogNode->addImage( $image );
				}
				if( $ogData->get( 'video') ){
					$video	= new OpenGraphVideo( $ogData->get( 'video' ) );
					if( $ogData->get( 'video.width' ) )
						$video->setWidth( $ogData->get( 'video.width' ) );
					if( $ogData->get( 'video.height' ) )
						$video->setHeight( $ogData->get( 'video.height' ) );
					if( $ogData->get( 'video.type' ) )
						$video->setType( $ogData->get( 'video.type' ) );
					$ogNode->addVideo( $video );
				}
				$page->addHead( OpenGraphRenderer::render( $ogNode ) );
				$page->addPrefix( 'og', 'https://ogp.me/ns#' );
			}
		}
		return TRUE;
	}
}
