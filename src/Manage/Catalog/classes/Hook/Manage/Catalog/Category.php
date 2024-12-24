<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Catalog_Category extends Hook
{
	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onTinyMCE_getLinkList(): void
	{
		$cache		= $this->env->getCache();
		if( !( $categories = $cache->get( 'catalog.tinymce.links.categories' ) ) ){
			$logic		= new Logic_Catalog( $this->env );
			$config		= $this->env->getConfig()->getAll( 'module.manage_catalog.', TRUE );
			$language	= $this->env->getLanguage()->getLanguage();
			$conditions	= ['visible' => '> 0', 'parentId' => 0];
			$categories	= $logic->getCategories( $conditions, ['rank' => 'ASC'] );
			foreach( $categories as $nr1 => $item ){
				$conditions	= ['visible' => '> 0', 'parentId' => $item->categoryId];
				$subs		= $logic->getCategories( $conditions, ['rank' => 'ASC'] );
				foreach( $subs as $nr2 => $sub ){
					$subs[$nr2] = (object) [
						'title'	=> $sub->{"label_".$language},
						'value'	=> 'catalog/category/'.$item->categoryId,
					];
				}
				$categories[$nr1] = (object) [
					'title'	=> $item->{"label_".$language},
					'menu'	=> array_values( $subs ),
				];
			}
			$cache->set( 'catalog.tinymce.links.categories', $categories );
		}
		$this->context->list  = array_merge( $this->context->list, [(object) [				//  extend global collection by submenu with list of items
			'title'	=> 'Kategorien:',											//  label of submenu @todo extract
			'menu'	=> array_values( $categories ),									//  items of submenu
		]] );
	}
}
