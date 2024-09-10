<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Gallery extends Hook
{
	/**
	 *	@return		void
	 */
	public function onTinyMCE_getImageList(): void
	{
		$moduleConfig		= $this->env->getConfig()->getAll( 'module.manage_galleries.', TRUE );
		$frontend			= Logic_Frontend::getInstance( $this->env );
		$remotePathImages	= $frontend->getPath( 'images' ).( trim( $moduleConfig->get( 'image.path' ) ) );
		$virtualPathImages	= substr( $remotePathImages, strlen( $frontend->getPath() ) );
		$words				= $this->env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes			= (object) $words['link-prefixes'];
		$list				= [];

		$modelGallery		= new Model_Gallery( $this->env );
		$modelImage			= new Model_Gallery_Image( $this->env );
		$galleryConditions	= ['status' => [-1, 0, 1]];
		$galleryOrders		= ['title' => 'ASC'];
		$imageOrders		= ['filename' => 'ASC'];
		foreach( $modelGallery->getAll( $galleryConditions, $galleryOrders ) as $gallery ){
			$imageConditions	= ['galleryId' => $gallery->galleryId];
			foreach( $modelImage->getAll( $imageConditions, $imageOrders ) as $image ){
				$list[]	= (object) [
					'title' => $gallery->title.' / '.$image->filename,
//					'value' => $pathImages.$gallery->path.'/'.$image->filename,
					'value' => $virtualPathImages.$gallery->path.'/'.$image->filename,
				];
			}
		}

		$list   = array( (object) array(
			'title'	=> "Galerie-Bild:",//$prefixes->image,
			'menu'	=> array_values( $list ),
		) );
//		$this->context->list	= array_merge( $this->context->list, array_values( $list ) );
		$this->context->list	= array_merge( $this->context->list, $list );
	}

	/**
	 *	@return		void
	 */
	public function onTinyMCE_getLinkList(): void
	{
		$moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_galleries.', TRUE );
		$frontend		= Logic_Frontend::getInstance( $this->env );
		$pathFrontend	= $frontend->getPath();
		$words			= $this->env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes		= (object) $words['link-prefixes'];
		$list			= [];

		$modelGallery	= new Model_Gallery( $this->env );
		$galleries	= $modelGallery->getAll( ['status' => 1], ['title' => 'ASC'] );
		foreach( $galleries as $gallery ){
			$title 		= View_Manage_Gallery::urlencodeTitle( $gallery->title );
			$list[]	= (object) array(
				'title' => str_replace( '/', '-', $gallery->title ),
				'type'	=> 'link:gallery',
				'value' => $pathFrontend.'info/gallery/'.$gallery->galleryId.'-'.$title,
			);
		}

		$list   = array( (object) array(
			'title'	=> "Galerie:",//$prefixes->image,
			'menu'	=> array_values( $list ),
		) );
//		$this->context->list	= array_merge( $this->context->list, array_values( $list ) );
		$this->context->list	= array_merge( $this->context->list, $list );
	}

	/**
	 *	@return		void
	 *	@todo		add hook to module config
	 */
	public function ___registerHints(): void
	{
		if( class_exists( 'View_Helper_Hint' ) )
			View_Helper_Hint::registerHintsFromModuleHook( $this->env, $this->module );
	}
}
