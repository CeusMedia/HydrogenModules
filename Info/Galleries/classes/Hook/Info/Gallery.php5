<?php
class Hook_Info_Gallery extends CMF_Hydrogen_Hook
{
	public static function onViewRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		if( !preg_match( "/(\[gallery:([0-9]+)\])|(\[galleries\])/sU", $payload->content ) )
			return;

		$pattern	= "/^(.*)(\[galleries\])(.*)$/sU";
		if( preg_match( $pattern, $payload->content ) ){
			$moduleConfig	= $env->getConfig()->getAll( 'module.info_galleries.', TRUE );
			switch( strtolower( trim( $moduleConfig->get( 'index.mode' ) ) ) ){
				case 'matrix':
					$helper		= new View_Helper_Info_Gallery_Matrix( $env );
					break;
				default:
					$helper		= new View_Helper_Info_Gallery_List( $env );
					$path		= $env->getCaptain() ->callHook(
						'Controller',
						'onDetectPath',
						$context,
						array( 'controllerName' => 'Info_Gallery' )
					);
					$helper->setBaseUriPath( $path ? $path : 'info/gallery' );
			}
			$payload->content	= preg_replace(
				$pattern,
				"\\1".$helper->render()."\\3",
				$payload->content
			);
		}

		$pattern	= "/^(.*)(\[gallery:([0-9]+)\])(.*)$/sU";
		while( preg_match( $pattern, $payload->content ) ){
			$galleryId	= (int) preg_replace( $pattern, "\\3", $payload->content );
			$helper		= new View_Helper_Info_Gallery_Images( $env );
			$helper->setGallery( $galleryId );
			$payload->content	= preg_replace(
				$pattern,
				"\\1".$helper->render()."\\4",
				$payload->content
			);
		}
	}
}
