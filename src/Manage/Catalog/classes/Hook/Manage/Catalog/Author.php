<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Catalog_Author extends Hook
{
	/**
	 *	@return		void
	 * 	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onTinyMCE_getImageList(): void
	{
		$cache		= $this->env->getCache();
		if( !( $list = $cache->get( 'catalog.tinymce.images.authors' ) ) ){
			$logic		= new Logic_Catalog( $this->env );
			$frontend	= Logic_Frontend::getInstance( $this->env );
			$config		= $this->env->getConfig()->getAll( 'module.manage_catalog.', TRUE );				//  focus module configuration
			$pathImages	= $frontend->getPath( 'contents' ).$config->get( 'path.authors' );			//  get path to author images
			$pathImages	= substr( $pathImages, strlen( $frontend->getPath() ) );					//  strip frontend base path
			$list		= [];
			$authors	= $logic->getAuthors( [], ['lastname' => 'ASC', 'firstname' => 'ASC'] );
			foreach( $authors as $item ){
				if( $item->image ){
					$id		= str_pad( $item->authorId, 5, 0, STR_PAD_LEFT );
//					$label	= $item->lastname.( $item->firstname ? ', '.$item->firstname : "" );
					$label	= ( $item->firstname ? $item->firstname.' ' : '' ).$item->lastname;
					$list[] = (object) [
						'title'	=> $label,
						'value'	=> $pathImages.$id.'_'.$item->image,
					];
				}
			}
			$cache->set( 'catalog.tinymce.images.authors', $list );
		}
		$this->context->list  = array_merge( $this->context->list, [(object) [			//  extend global collection by submenu with list of items
			'title'	=> 'Autoren:',												//  label of submenu @todo extract
			'menu'	=> array_values( $list ),								//  items of submenu
		]] );
	}
}