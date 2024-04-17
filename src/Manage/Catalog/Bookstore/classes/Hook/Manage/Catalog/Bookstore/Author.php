<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Catalog_Bookstore_Author extends Hook
{
	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onTinyMCE_getImageList(): void
	{
		$cache		= $this->env->getCache();
		if( !( $list = $cache->get( 'catalog.tinymce.images.catalog.bookstore.authors' ) ) ){
			$logic		= new Logic_Catalog_BookstoreManager( $this->env );
			$frontend	= Logic_Frontend::getInstance( $this->env );
			$config		= $this->env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );				//  focus module configuration
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
//						'value'	=> $pathImages.$id.'_'.$item->image,
						'value'	=> 'file/bookstore/author/'.$item->image,
					];
				}
			}
			$cache->set( 'catalog.tinymce.images.catalog.bookstore.authors', $list );
		}
		$this->context->list	= array_merge( $this->context->list, [(object) [				//  extend global collection by submenu with list of items
			'title'	=> 'Autoren:',												//  label of submenu @todo extract
			'menu'	=> array_values( $list ),									//  items of submenu
		]] );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onTinyMCE_getLinkList(): void
	{
		$cache		= $this->env->getCache();
		if( !( $authors = $cache->get( 'catalog.tinymce.links.catalog.bookstore.authors' ) ) ){
			$logic		= new Logic_Catalog_BookstoreManager( $this->env );
			$config		= $this->env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );
			$authors	= $logic->getAuthors( [], ['lastname' => 'ASC', 'firstname' => 'ASC'] );
			foreach( $authors as $nr => $item ){
				$label		= ( $item->firstname ? $item->firstname.' ' : '' ).$item->lastname;
				$url		= $logic->getAuthorUri( $item );
				$authors[$nr] = (object) ['title' => $label, 'value' => $url];
			}
			$cache->set( 'catalog.tinymce.links.catalog.bookstore.authors', $authors );
		}
		$words	= $this->env->getLanguage()->getWords( 'manage/catalog/bookstore' );
		$this->context->list  = array_merge( $this->context->list, [(object) [	//  extend global collection by submenu with list of items
			'title'	=> $words['tinymce-menu-links']['authors'],					//  label of submenu
			'menu'	=> array_values( $authors ),								//  items of submenu
		]] );
	}
}
