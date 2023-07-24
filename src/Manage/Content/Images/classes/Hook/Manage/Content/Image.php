<?php

use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileIndex;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;
use Psr\SimpleCache\InvalidArgumentException as InvalidCacheArgumentException;

class Hook_Manage_Content_Image extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		InvalidCacheArgumentException
	 */
	public function onTinyMceGetImageList(): void
	{
		$payload	= ['hidePrefix' => TRUE];
		$this->setPayload( $payload );
		$this->onTinyMceGetLinkList();
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		InvalidCacheArgumentException
	 */
	public function onTinyMceGetLinkList(): void
	{
//		$moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_content_images.', TRUE );
//		$frontend		= Logic_Frontend::getInstance( $this->env );
//		$pathFront		= trim( $frontend->getPath() );
//		$pathImages		= trim( $moduleConfig->get( 'path.images' ) );
//		$pathIgnore		= trim( $moduleConfig->get( 'path.ignore' ) );
		$hidePrefix		= 1 || !empty( $this->payload['hidePrefix'] ) && $this->payload['hidePrefix'];

		$words		= $this->env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes	= (object) $words['link-prefixes'];
//		$label		= $prefixes->image;
		$list		= [];
		$index		= self::getImageList( $this->env );
		foreach( $index as $item ){
			$list[]	= (object) [
				'title'	=> $hidePrefix ? $item->label : $prefixes->image.$item->label,
				'type'	=> 'image',
				'value'	=> $item->uri,
			];
		}
		$list	= [(object) [
			'title'	=> $prefixes->image,
			'menu'	=> array_values( $list ),
		]];
//		$this->context->list	= array_merge( $this->context->list, array_values( $list ) );
		$this->context->list	= array_merge( $this->context->list, $list );
	}

	/**
	 *	@param		Environment		$env
	 *	@return		array<string,object>
	 *	@throws		ReflectionException
	 *	@throws		InvalidCacheArgumentException
	 */
	protected static function getImageList( Environment $env ): array
	{
//		$cache		= $env->getCache();
		if( $list = $env->getCache()->get( 'ManageContentImages.list.static' ) )
			return $list;
		$frontend		= Logic_Frontend::getInstance( $env );
		$moduleConfig	= $env->getConfig()->getAll( 'module.manage_content_images.', TRUE );
		$pathImages		= $frontend->getPath().$moduleConfig->get( 'path.images' );
		$pathIgnore		= trim( $moduleConfig->get( 'path.ignore' ) );
		$extensions		= preg_split( "/\s*,\s*/", $moduleConfig->get( 'extensions' ) );
		$list			= [];

		$regexExt	= "/\.(".join( "|", $extensions ).")$/i";
		$index		= new RecursiveRegexFileIndex( $pathImages, $regexExt );
		foreach( $index as $item ){
			$path	= substr( $item->getPathname(), strlen( $pathImages ) );
			if( $pathIgnore && preg_match( $pathIgnore, $path ) )
				continue;
			$parts	= explode( "/", $path );
//			$level	= count( $parts );
			$file	= array_pop( $parts );
//			$path	= implode( '/', array_slice( $parts, 1 ) );
			$path	= implode( '/', $parts );
			$label	= $path ? $path.'/'.$file : $file;
			$uri	= substr( $item->getPathname(), strlen( $frontend->getPath() ) );
			$key	= str_replace( "/", "_", strtolower( $label ) );
			$list[$key]	= (object) ['label' => $label, 'uri' => $uri];
		}
		ksort( $list );
		$env->getCache()->set( 'ManageContentImages.list.static', $list );
		return $list;
	}
}