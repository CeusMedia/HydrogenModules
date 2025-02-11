<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Gallery extends Hook
{
	public function onViewRenderContent(): void
	{
		if( !preg_match( "/(\[gallery:([0-9]+)\])|(\[galleries\])/sU", $this->payload['content'] ) )
			return;

		$pattern	= "/^(.*)(\[galleries\])(.*)$/sU";
		if( preg_match( $pattern, $this->payload['content'] ) ){
			$moduleConfig	= $this->env->getConfig()->getAll( 'module.info_galleries.', TRUE );
			switch( strtolower( trim( $moduleConfig->get( 'index.mode' ) ) ) ){
				case 'matrix':
					$helper		= new View_Helper_Info_Gallery_Matrix( $this->env );
					break;
				default:
					$helper		= new View_Helper_Info_Gallery_List( $this->env );
					$p2			= ['controllerName' => 'Info_Gallery'];
					$this->env->getCaptain() ->callHook(
						'Controller',
						'onDetectPath',
						$this->context,
						$p2
					);
					$helper->setBaseUriPath( $p2['fullath'] ?: 'info/gallery' );
			}
			$this->payload['content']	= preg_replace(
				$pattern,
				"\\1".$helper->render()."\\3",
				$this->payload['content']
			);
		}

		$pattern	= "/^(.*)(\[gallery:([0-9]+)\])(.*)$/sU";
		while( preg_match( $pattern, $this->payload['content'] ) ){
			$galleryId	= (int) preg_replace( $pattern, "\\3", $this->payload['content'] );
			$helper		= new View_Helper_Info_Gallery_Images( $this->env );
			$helper->setGallery( $galleryId );
			$this->payload['content']	= preg_replace(
				$pattern,
				"\\1".$helper->render()."\\4",
				$this->payload['content']
			);
		}
	}
}
