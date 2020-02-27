<?php
class Hook_Manage_Gallery extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onTinyMCE_getImageList( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$moduleConfig		= $env->getConfig()->getAll( 'module.manage_galleries.', TRUE );
		$frontend			= Logic_Frontend::getInstance( $env );
		$remotePathImages	= $frontend->getPath( 'images' ).( trim( $moduleConfig->get( 'image.path' ) ) );
		$virtualPathImages	= substr( $remotePathImages, strlen( $frontend->getPath() ) );
		$words				= $env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes			= (object) $words['link-prefixes'];
		$list				= array();

		$modelGallery		= new Model_Gallery( $env );
		$modelImage			= new Model_Gallery_Image( $env );
		$galleryConditions	= array( 'status' => array( -1, 0, 1 ) );
		$galleryOrders		= array( 'title' => 'ASC' );
		$imageOrders		= array( 'filename' => 'ASC' );
		foreach( $modelGallery->getAll( $galleryConditions, $galleryOrders ) as $gallery ){
			$imageConditions	= array( 'galleryId' => $gallery->galleryId );
			foreach( $modelImage->getAll( $imageConditions, $imageOrders ) as $image ){
				$list[]	= (object) array(
					'title' => $gallery->title.' / '.$image->filename,
//					'value' => $pathImages.$gallery->path.'/'.$image->filename,
					'value' => $virtualPathImages.$gallery->path.'/'.$image->filename,
				);
			}
		}

		$list   = array( (object) array(
			'title'	=> "Galerie-Bild:",//$prefixes->image,
			'menu'	=> array_values( $list ),
		) );
//		$context->list	= array_merge( $context->list, array_values( $list ) );
		$context->list	= array_merge( $context->list, $list );
	}

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onTinyMCE_getLinkList( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$moduleConfig	= $env->getConfig()->getAll( 'module.manage_galleries.', TRUE );
		$frontend		= Logic_Frontend::getInstance( $env );
		$pathFrontend	= $frontend->getPath();
		$words			= $env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes		= (object) $words['link-prefixes'];
		$list			= array();

		$modelGallery	= new Model_Gallery( $env );
		$galleries	= $modelGallery->getAll( array( 'status' => 1 ), array( 'title' => 'ASC' ) );
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
//		$context->list	= array_merge( $context->list, array_values( $list ) );
		$context->list	= array_merge( $context->list, $list );
	}

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 *	@todo		add hook to module config
	 */
	public static function ___registerHints( CMF_Hydrogen_Environment $env, $context, $module, $payload = NULL ){
		if( class_exists( 'View_Helper_Hint' ) )
			View_Helper_Hint::registerHintsFromModuleHook( $env, $module );
	}
}